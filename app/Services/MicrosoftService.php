<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Services\CompanyDatabaseService;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Api\Systemadmin\Modules\Company\Models\Company;
use App\Api\Customer\Modules\Employees\Models\Employees;
use App\Api\Customer\Modules\CompanyLogin\Models\UserloginActivity;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use App\Api\Customer\Modules\SocialAccounts\Models\SocialAccounts;
use App\Api\Customer\Modules\SocialAccounts\Models\MicrosoftCalendarEvent;
use App\Api\Customer\Modules\SocialAccounts\Models\AppVariable;
use App\Api\Customer\Modules\SocialAccounts\Models\AppModule;
use App\Api\Customer\Modules\SocialAccounts\Models\AppOption;
use App\Api\Customer\Modules\Settings\Models\SettingModule;
use App\Api\Customer\Modules\SocialAccounts\Models\MicrosoftSyncStatus;
use App\Api\Customer\Modules\CompanyLogin\Models\FailedLogin;



class MicrosoftService
{
    protected $companyDatabaseService;
    protected $provider;
    protected $clientId;
    protected $secretId;
    protected $redirectUrl;
    public function __construct(CompanyDatabaseService $companyDatabaseService)
    {
        $this->companyDatabaseService = $companyDatabaseService;
        $this->provider = 'microsoft';
        $this->clientId = 'clientId';
        $this->secretId = 'secretId';
        $this->redirectUrl = 'redirectUrl';
    }

    /**
     * Handle the user Microsoft login.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function auth(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'domain_name'  => 'required',
                'authCode' => 'required',
                'codeVerifier' => 'required'
            ], [
                'domain_name.required'  => 'Domain Name is required',
                'authCode.required'     => 'AuthCode is required',
                'codeVerifier.required'     => 'codeVerifier is required',
            ]);

            if ($validator->fails()) {
                return [
                    'status'     => false,
                    'message'    => 'Validation Error',
                    'errors'     => $validator->errors(),
                    'statusCode' => 422
                ];
            }
            $domainName = $request->domain_name;
            $company = Company::select('id', 'company_name')
                ->where('domain_name', $domainName)
                ->first();

            if (!$company) {
                return [
                    'status'     => false,
                    'message'    => 'Company not found via Microsoft login',
                    'errors'     => ["error" => array("Company not found via Microsoft login")],
                    'statusCode' => 404,
                ];
            }
            $companyId =  $company->id;
            $connection = $this->companyDatabaseService->connect($companyId);

            if (!$connection['status']) {
                return [
                    'status'     => false,
                    'message'    => $connection['message'],
                    'errors'     => $connection['errors'],
                    'statusCode' => $connection['statusCode']
                ];
            }

            $dbname = $connection['dbName'];
            $appModule = $this->checkAppExisting($dbname);
            if (!$appModule) {
                return [
                    'status'     => false,
                    'message'    => 'Microsoft app missing or disabled',
                    'errors'     => ['error' => 'Microsoft app missing or disabled'],
                    'statusCode' => 400,
                ];
            }
            $ClientId = $this->getValue($dbname, $appModule->id, $this->clientId);
            $secretId = $this->getValue($dbname, $appModule->id, $this->secretId);
            $redirectUrl = $this->getValue($dbname, $appModule->id, $this->redirectUrl);
            $response = Http::asForm()->post(env('MICROSOFT_TOKEN_URL'), [
                'client_id' => $ClientId->appvalue,
                'client_secret' => $secretId->appvalue,
                'code' => $request->authCode,
                'redirect_uri' => $redirectUrl->appvalue,
                'grant_type' => 'authorization_code',
                'code_verifier' => $request->codeVerifier,
            ]);

            if ($response->successful()) {
                $tokenData = $response->json();
                $accessToken = $tokenData['access_token'];
                $refreshToken =   $tokenData['refresh_token'];
                $expires_in =   $tokenData['expires_in'];
            } else {
                $errorData = [
                    'status'     => false,
                    'message'    => $response->json(),
                    'errors'     => ["error" => array("Invalid Authcode via microsoft login")],
                    'statusCode' => $response->status()
                ];

                $failedData = new FailedLogin([
                    'error_details' => json_encode($errorData),
                    'failedat'      => now(),
                ]);
                $failedData->setConnection($dbname)->save();

                return [
                    'status'     => false,
                    'message'    => $response->json(),
                    'errors'     => 'Invalid Authcode',
                    'statusCode' => $response->status(),
                ];
            }

            $microsftUserDetails = Http::withToken($accessToken)->get(env('MICROSOFT_GET_USER_URL'));
            $loginEmail = '';
            if ($microsftUserDetails->successful()) {
                $microsftUser = $microsftUserDetails->json();
                $loginEmail = $microsftUser['mail'];
            } else {
                // $errorData = [
                //     'status'     => false,
                //     'message'    => $microsftUserDetails->json(),
                //     'errors'     => ["error" => array("Failed to access user info via microsoft login")],
                //     'statusCode' => $microsftUserDetails->status()
                // ];

                // $failedData = new FailedLogin([
                //     'username'      => $microsftUser['mail'],
                //     'error_details' => json_encode($errorData),
                //     'failedat'      => now(),
                // ]);
                // $failedData->setConnection($dbname)->save();
                return [
                    'status'     => false,
                    'message'    => 'Failed to access user info',
                    'errors'     => $microsftUserDetails->json(),
                    'statusCode' => $microsftUserDetails->status()
                ];
            }
            $userLogin = Employees::on($dbname)
                ->select('id', 'navn', 'email', 'brugernavn', 'mfa')
                ->where('email', $loginEmail)->first();
            if (!$userLogin) {

                // $errorData = [
                //     'status'     => false,
                //     'message'    => 'Unauthorised',
                //     'errors'     => ["error" => array("User not available via microsoft login")],
                //     'statusCode' => 400
                // ];

                // $failedData = new FailedLogin([
                //     'username'      => $microsftUser['mail'],
                //     'error_details' => json_encode($errorData),
                //     'failedat'      => now(),
                // ]);
                // $failedData->setConnection($dbname)->save();
                return [
                    'status'     => false,
                    'message'    => 'Unauthorised',
                    'errors'     =>  ["error" => array("User not available.")],
                    'statusCode' => 400
                ];
            }
            $expiresInSeconds = (int) $expires_in;
            $user = SocialAccounts::on($dbname)->updateOrInsert(
                [
                    'cloud_sso_user_id' => $userLogin->id,
                    'provider' => $this->provider,
                ],
                [
                    'access_token' => $accessToken,
                    'refresh_token' => $refreshToken,
                    'provider' => $this->provider,
                    'provider_name' => $this->provider,
                    'token_expires_at' => now()->addSeconds($expiresInSeconds),
                    'email' => $loginEmail,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $employee        = new Employees();
            $employee->id    = $userLogin->id;
            $employee->navn  = $userLogin->navn;
            $employee->email = $userLogin->email;
            $employee->brugernavn = $userLogin->brugernavn;

            // Create JWT token for the employee
            $customClaims = [
                'id'        => $employee->id,
                'email'     => $employee->email,
                'name'      => $employee->navn,
                'username'  => $employee->brugernavn,
                'iss'       => 'CustomerPortal',
                'iat'       => (int) now()->timestamp,
                'companyId' => $companyId,
                //  'exp'   => (int) now()->addDay()->timestamp,
            ];
            $token = JWTAuth::claims($customClaims)->fromUser($employee);
            $mfa = 0;
            $checkMfaExisting  =  SettingModule::on($dbname)
                ->where('settingstatus', 1)->first();
            if ($checkMfaExisting) {
                if ($userLogin->mfa == 1) {
                    $mfa = 1;
                }
            }
            $userData = [
                'email'       => $employee->email,
                'userId'      => $employee->id,
                'username'    => $employee->brugernavn,
                'companyName' => $company->company_name,
                'companyId'   => $companyId,
                'token'       => $token,
                'mfa'         => $mfa,
                'expire_in'   => config('jwt.ttl') * 60
            ];
            $lastRecord = UserloginActivity::on($dbname)->where('userid', $employee->id)->latest()->first();

            if (!$lastRecord->logouttime) {
                $tokenExpiry = config('jwt.ttl');
                $loginTime   = Carbon::parse($lastRecord->logintime);
                $logoutTime  = $loginTime->addMinutes($tokenExpiry);

                $lastRecord->logouttime = $logoutTime;
                $lastRecord->duration   = DB::raw("TIMESTAMPDIFF(SECOND, logintime, '$logoutTime')");
                $lastRecord->save();
            }

            $loginTime   = now();
            $userSetting = new UserloginActivity([
                'userid'    => $employee->id,
                'logintime' => $loginTime,
                'ipaddress' => $request->ip(),
                'useragent' => $request->userAgent(),
            ]);
            $userSetting->setConnection($dbname)->save();

            DB::connection($dbname)->table('cloud_sso_users')
                ->where('id', $employee->id)
                ->update([
                    'status'    => 0,
                    'lastlogin' => $loginTime,
                ]);
            return [
                'status'     => true,
                'message'    => 'Login Successfully.',
                'data'       => $userData,
                'statusCode' => 200
            ];
        } catch (\Exception $e) {
             $errorData = [
                    'status'     => false,
                    'message'    => 'An error occurred via microsoft login',
                    'errors'     => ["error" => $e->getMessage()],
                    'statusCode' => 500
                ];

                $failedData = new FailedLogin([
                    'error_details' => json_encode($errorData),
                    'failedat'      => now(),
                ]);
                $failedData->setConnection($dbname)->save();
            return [
                'status'     => false,
                'message'    => 'An error occurred via microsoft login',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }

    /**
     * Get microsoft all calendar events.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCalendarevents(Request $request)
    {
        try {
            $dbName = $request->get('dbName');
            $userId = $request->get('userId');
            $startTime = $request->get('startTime');
            $endTime = $request->get('endTime');
            $socialAccounts = $this->getSocialAccountData($dbName, $userId);
            if (!$socialAccounts)
                return $this->socialAccountFailureMessage();

            // $checksync = MicrosoftSyncStatus::on($dbName)->where('cloud_sso_user_id', $userId)->first();
            // //check sync enable
            // if ($checksync && $checksync->sync_status) {
                // $syncResult = $this->syncMicrosoftEvents($request);
                // if (!$syncResult['status']) {
                //     return [
                //         'status'     => $syncResult['status'],
                //         'message'    => $syncResult['message'],
                //         'errors'     => $syncResult['errors'],
                //         'statusCode' => $syncResult['statusCode'],
                //     ];
                // }
            // }

            $accessToken = $this->getMicrosoftAccessToken($socialAccounts, $dbName);
            if (isset($accessToken['status']) && $accessToken['status'] == false) {
                return [
                    'status'     => false,
                    'message'    => 'Re-connect your microsoft',
                    'errors'     => 'Failed to microsoft Login',
                    'statusCode' => 400
                ];
            }
            $response = Http::withToken($accessToken)->get('https://graph.microsoft.com/v1.0/me/calendar/calendarView?startDateTime='.$startTime.'&endDateTime='.$endTime.'&$top=200');
            if ($response->successful()) {
                $events = $response->json()['value'];
                $this->syncEvents($events, $dbName, $socialAccounts);
                $externalIds = collect($events)->pluck('id')->toArray();
                MicrosoftCalendarEvent::on($dbName)->where('tbl_social_account_id', $socialAccounts->id)
                    ->whereNotIn('microsoft_event_id', $externalIds)
                    ->where('start_time', '>=', $startTime)
                    ->where('end_time', '<=', $endTime)
                    ->update(['is_deleted' => 1]);
                // $checksync->sync_at =  now();
                // $checksync->save();
                $calendarEvents = MicrosoftCalendarEvent::on($dbName)
                ->where('tbl_social_account_id', $socialAccounts->id)
                ->where('is_deleted', 0)
                ->where('start_time', '>=', $startTime)
                ->where('end_time', '<=', $endTime)
                ->get();
                return [
                    'status'     => true,
                    'message'    => 'Microsoft calendar events.',
                    'data'       => $calendarEvents,
                    'statusCode' => 200
                ];
            } else {
                return [
                    'status'     => false,
                    'message'    => 'Unauthorised',
                    'errors'     =>  ["error" => $response->json()],
                    'statusCode' => $response->status()
                ];
            }
            
           
        } catch (\Exception $e) {
            return [
                'status'     => false,
                'message'    => 'An error occurred.',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }

    /**
     * Handle the store Microsoft calendar Event.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeCalendarEvent(Request $request)
    {
        try {
            $dbName = $request->get('dbName');
            $userId = $request->get('userId');

            $socialAccounts = $this->getSocialAccountData($dbName, $userId);

            if (!$socialAccounts)
                return $this->socialAccountFailureMessage();
            $validator = Validator::make($request->all(), [
                'microsoft_event_id' => 'nullable|string|max:255|unique:tbl_microsoft_calendar_events,microsoft_event_id',
                'title' => 'required|string|max:255',
                'body_content' => 'nullable|string',
                'body_content_type' => 'nullable|in:HTML,PlainText',
                'isAllDay' => 'nullable|boolean',
                'isCancelled' => 'nullable|boolean',
                'start_time' => 'required|date|after_or_equal:now',
                'end_time' => 'required|date|after:start_time',
                'timezone' => 'nullable|string|max:50',
                'onlineMeetingUrl' => 'nullable|url',
                'isOnlineMeeting' => 'nullable|boolean',
                'location_displayName' => 'nullable|string|max:255',
                'location_locationType' => 'nullable|string|max:255',
                'organizer_name' => 'nullable|string|max:255',
                'organizer_address' => 'nullable|email|max:255',
                'attendees' => 'nullable|array',
                'attendees.*.emailAddress.address' => 'required_with:attendees|email',
                'attendees.*.emailAddress.name' => 'required_with:attendees|string|max:255',
                'attendees.*.type' => 'nullable|in:required,optional',
                'locations' => 'nullable|array',
            ], [
                'title.required' => 'The event title is required.',
                'start_time.after_or_equal' => 'The start time must be in the future.',
                'end_time.after' => 'The end time must be after the start time.',
                'attendees.*.emailAddress.address.required_with' => 'Each attendee must have a valid email address.',
            ]);

            if ($validator->fails()) {
                return [
                    'status'     => false,
                    'message'    => 'Validation Error',
                    'errors'     => $validator->errors(),
                    'statusCode' => 422
                ];
            }

            $attendeeEmails = collect($request->attendees)->pluck('emailAddress.address')->toArray();

            //Fetch only emails that exist in the database
            $existingEmails = Employees::on($dbName)
                ->whereIn('email', $attendeeEmails)
                ->pluck('email')
                ->toArray();

            // Filter attendees who DO NOT exist in the database
            $nonExistingAttendees = array_filter($request->attendees ?? [], function ($attendee) use ($existingEmails) {
                return !in_array($attendee['emailAddress']['address'], $existingEmails);
            });

            $attendeeEmails = collect($nonExistingAttendees)->pluck('emailAddress.address')->toArray();

            if (!empty($attendeeEmails)) {
                return [
                    'status'     => false,
                    'message'    => 'Please use your organization email address.',
                    'errors'     => 'Failed to create event',
                    'statusCode' => 400
                ];
            }


            $payload = [
                'subject' => $request->title,
                'body' => [
                    'contentType' => $request->body_content_type ?? 'HTML',
                    'content' => $request->body_content ?? '',
                ],
                'start' => [
                    'dateTime' => $request->start_time,
                    'timeZone' => $request->timezone ?? 'UTC',
                ],
                'end' => [
                    'dateTime' => $request->end_time,
                    'timeZone' => $request->timezone ?? 'UTC',
                ],
                'location' => [
                    'displayName' => $request->location_displayName ?? '',
                ],
                'attendees' => array_map(function ($attendee) {
                    return [
                        'emailAddress' => [
                            'address' => $attendee['emailAddress']['address'],
                            'name' => $attendee['emailAddress']['name'],
                        ],
                        'type' => $attendee['type'] ?? 'required',
                    ];
                }, $request->attendees ?? []),
            ];

            if ($request->isOnlineMeeting) {
                $payload['isOnlineMeeting'] = true;
            }



            //access token is stored for the social_accounts
            $accessToken = $this->getMicrosoftAccessToken($socialAccounts, $dbName);
            // dd($accessToken);
            if (isset($accessToken['status']) && $accessToken['status'] == false) {
                return [
                    'status'     => false,
                    'message'    => 'Re-connect your microsoft',
                    'errors'     => 'Failed to microsoft Login',
                    'statusCode' => 400
                ];
            }
            $response = Http::withToken($accessToken)->post(env('MICROSOFT_STORE_EVENT_URL'), $payload);
            if ($response->successful()) {
                $eventData = $response->json();

                // Save event to the database
                $event =   MicrosoftCalendarEvent::on($dbName)->updateOrInsert(
                    ['microsoft_event_id' => $eventData['id'], 'tbl_social_account_id' => $socialAccounts->id],
                    [
                        'tbl_social_account_id' => $socialAccounts->id,
                        'microsoft_event_id' => $eventData['id'],
                        'title' => $eventData['subject'],
                        'body_content' => $eventData['body']['content'] ?? null,
                        'body_content_type' => $eventData['body']['contentType'] ?? 'HTML',
                        'isAllDay' => $eventData['isAllDay'] ?? false,
                        'isCancelled' => $eventData['isCancelled'] ?? false,
                        'isOrganizer' => $eventData['isOrganizer'] ?? false,
                        'showAs' => $eventData['showAs'] ?? null,
                        'response_status' => $eventData['responseStatus']['response'] ?? null,
                        'response_time' => isset($eventData['responseStatus']['time'])
                            ? Carbon::parse($eventData['responseStatus']['time'])->format('Y-m-d H:i:s')
                            : null,
                        'type' => $eventData['type'] ?? null,
                        'webLink' => $eventData['webLink'] ?? null,
                        'start_time' => $eventData['start']['dateTime'],
                        'end_time' => $eventData['end']['dateTime'],
                        'timezone' => $eventData['start']['timeZone'] ?? 'UTC',
                        'onlineMeetingUrl' => $eventData['onlineMeeting']['joinUrl'] ?? null,
                        'isOnlineMeeting' => isset($eventData['onlineMeeting']),
                        'location_displayName' => $eventData['location']['displayName'] ?? null,
                        'organizer_name' => $eventData['organizer']['emailAddress']['name'] ?? null,
                        'organizer_address' => $eventData['organizer']['emailAddress']['address'] ?? null,
                        'attendees' => json_encode($eventData['attendees'] ?? []),
                        'locations' => json_encode($eventData['locations'] ?? []),
                        'created_by' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                return [
                    'status'     => true,
                    'message'    => 'Event created successfully!',
                    'data'       => $event,
                    'statusCode' => 200
                ];
            } else {
                return [
                    'status'     => false,
                    'message'    => 'Failed to create event',
                    'errors'     => $response->json(),
                    'statusCode' => $response->status()
                ];
            }
        } catch (\Exception $e) {
            return [
                'status'     => false,
                'message'    => 'An error occurred.',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }

    /**
     * Delete Microsoft calendar Event.
     *
     * @param   Microsoft calendar Event $id 
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteCalendarEvent($id, Request $request)
    {

        try {
            $dbName = $request->get('dbName');
            $userId = $request->get('userId');
            $socialAccounts = $this->getSocialAccountData($dbName, $userId);
            if (!$socialAccounts)
                return $this->socialAccountFailureMessage();
            //access token is stored for the social_accounts
            $accessToken = $this->getMicrosoftAccessToken($socialAccounts, $dbName);
            if (isset($accessToken['status']) && $accessToken['status'] == false) {
                return [
                    'status'     => false,
                    'message'    => 'Re-connect your microsoft',
                    'errors'     => 'Failed to microsoft Login',
                    'statusCode' => 400
                ];
            }
            // Make the DELETE request to Microsoft Graph API
            $response = Http::withToken($accessToken)
                ->delete(env('MICROSOFT_DELETE_EVENT_URL') . $id);
            if ($response->successful()) {
                $deleteEvent = MicrosoftCalendarEvent::on($dbName)
                    ->where('microsoft_event_id', $id)
                    ->where('tbl_social_account_id', $socialAccounts->id)
                    ->delete();
                if ($deleteEvent) {
                    return [
                        'status'     => true,
                        'message'    => 'Event deleted successfully!',
                        'data'       => null,
                        'statusCode' => 200
                    ];
                } else {

                    return [
                        'status'     => false,
                        'message'    => 'Event not found or not deleted.',
                        'errors'     =>  ["error" => array("Event not found or not deleted.")],
                        'statusCode' => 404
                    ];
                }
            } else {
                return [
                    'status'     => false,
                    'message'    => 'Failed to delete the event in Microsoft',
                    'errors'     =>  ["error" => $response->json()],
                    'statusCode' =>  $response->status()
                ];
            }
        } catch (\Exception $e) {
            return [
                'status'     => false,
                'message'    => 'An error occurred.',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }

    /**
     * update Microsoft calendar Event.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCalendarEvent($id, Request $request)
    {

        try {
            $dbName = $request->get('dbName');
            $userId = $request->get('userId');

            $socialAccounts = $this->getSocialAccountData($dbName, $userId);

            if (!$socialAccounts)
                return $this->socialAccountFailureMessage();

            $validator = Validator::make($request->all(), [
                'microsoft_event_id' => 'nullable|string|max:255|unique:tbl_microsoft_calendar_events,microsoft_event_id',
                'title' => 'required|string|max:255',
                'body_content' => 'nullable|string',
                'body_content_type' => 'nullable|in:HTML,PlainText',
                'isAllDay' => 'nullable|boolean',
                'isCancelled' => 'nullable|boolean',
                'start_time' => 'required|date|after_or_equal:now',
                'end_time' => 'required|date|after:start_time',
                'timezone' => 'nullable|string|max:50',
                'onlineMeetingUrl' => 'nullable|url',
                'isOnlineMeeting' => 'nullable|boolean',
                'location_displayName' => 'nullable|string|max:255',
                'location_locationType' => 'nullable|string|max:255',
                'organizer_name' => 'nullable|string|max:255',
                'organizer_address' => 'nullable|email|max:255',
                'attendees' => 'nullable|array',
                'attendees.*.emailAddress.address' => 'required_with:attendees|email',
                'attendees.*.emailAddress.name' => 'required_with:attendees|string|max:255',
                'attendees.*.type' => 'nullable|in:required,optional',
                'locations' => 'nullable|array',
            ], [
                'title.required' => 'The event title is required.',
                'start_time.after_or_equal' => 'The start time must be in the future.',
                'end_time.after' => 'The end time must be after the start time.',
                'attendees.*.emailAddress.address.required_with' => 'Each attendee must have a valid email address.',
            ]);

            if ($validator->fails()) {
                return [
                    'status'     => false,
                    'message'    => 'Validation Error',
                    'errors'     => $validator->errors(),
                    'statusCode' => 422
                ];
            }

            $attendeeEmails = collect($request->attendees)->pluck('emailAddress.address')->toArray();

            //Fetch only emails that exist in the database
            $existingEmails = Employees::on($dbName)
                ->whereIn('email', $attendeeEmails)
                ->pluck('email')
                ->toArray();
            // Filter attendees who DO NOT exist in the database
            $nonExistingAttendees = array_filter($request->attendees ?? [], function ($attendee) use ($existingEmails) {
                return !in_array($attendee['emailAddress']['address'], $existingEmails);
            });

            $attendeeEmails = collect($nonExistingAttendees)->pluck('emailAddress.address')->toArray();
            if (!empty($attendeeEmails)) {
                return [
                    'status'     => false,
                    'message'    => 'Failed to create event',
                    'errors'     => 'You have sent an email to an address outside your organization',
                    'statusCode' => 400
                ];
            }

            $payload = [
                'subject' => $request->title,
                'body' => [
                    'contentType' => $request->body_content_type ?? 'HTML',
                    'content' => $request->body_content ?? '',
                ],
                'start' => [
                    'dateTime' => $request->start_time,
                    'timeZone' => $request->timezone ?? 'UTC',
                ],
                'end' => [
                    'dateTime' => $request->end_time,
                    'timeZone' => $request->timezone ?? 'UTC',
                ],
                'location' => [
                    'displayName' => $request->location_displayName ?? '',
                ],
                'attendees' => array_map(function ($attendee) {
                    return [
                        'emailAddress' => [
                            'address' => $attendee['emailAddress']['address'],
                            'name' => $attendee['emailAddress']['name'],
                        ],
                        'type' => $attendee['type'] ?? 'required',
                    ];
                }, $request->attendees ?? []),
            ];

            if ($request->isOnlineMeeting) {
                $payload['isOnlineMeeting'] = true;
            }


            //access token is stored for the social_accounts
            $accessToken = $this->getMicrosoftAccessToken($socialAccounts, $dbName);
            if (isset($accessToken['status']) && $accessToken['status'] == false) {
                return [
                    'status'     => false,
                    'message'    => 'Re-connect your microsoft',
                    'errors'     => 'Failed to microsoft Login',
                    'statusCode' => 400
                ];
            }
            //update microsoft calendar
            $response = Http::withToken($accessToken)->patch(env('MICROSOFT_UPDATE_EVENT_URL') . $id, $payload);
            if ($response->successful()) {
                $eventData = $response->json();

                // Save event to the database
                $event =  MicrosoftCalendarEvent::on($dbName)->where('microsoft_event_id', $eventData['id'])
                    ->where('tbl_social_account_id', $socialAccounts->id)
                    ->update(
                        [
                            'title' => $eventData['subject'],
                            'body_content' => $eventData['body']['content'] ?? null,
                            'body_content_type' => $eventData['body']['contentType'] ?? 'HTML',
                            'isAllDay' => $eventData['isAllDay'] ?? false,
                            'isCancelled' => $eventData['isCancelled'] ?? false,
                            'isOrganizer' => $eventData['isOrganizer'] ?? false,
                            'showAs' => $eventData['showAs'] ?? null,
                            'response_status' => $eventData['responseStatus']['response'] ?? null,
                            'response_time' => isset($eventData['responseStatus']['time'])
                                ? Carbon::parse($eventData['responseStatus']['time'])->format('Y-m-d H:i:s')
                                : null,
                            'type' => $eventData['type'] ?? null,
                            'webLink' => $eventData['webLink'] ?? null,
                            'start_time' => $eventData['start']['dateTime'],
                            'end_time' => $eventData['end']['dateTime'],
                            'timezone' => $eventData['start']['timeZone'] ?? 'UTC',
                            'onlineMeetingUrl' => $eventData['onlineMeeting']['joinUrl'] ?? null,
                            'isOnlineMeeting' => isset($eventData['onlineMeeting']),
                            'location_displayName' => $eventData['location']['displayName'] ?? null,
                            'organizer_name' => $eventData['organizer']['emailAddress']['name'] ?? null,
                            'organizer_address' => $eventData['organizer']['emailAddress']['address'] ?? null,
                            'attendees' => json_encode($eventData['attendees'] ?? []),
                            'locations' => json_encode($eventData['locations'] ?? []),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                return [
                    'status'     => true,
                    'message'    => 'Event Updated successfully!',
                    'data'       => $event,
                    'statusCode' => 200
                ];
            } else {
                return [
                    'status'     => false,
                    'message'    => 'Failed to update event',
                    'errors'     =>  $response->json(),
                    'statusCode' => $response->status()
                ];
            }
        } catch (\Exception $e) {
            return [
                'status'     => false,
                'message'    => 'An error occurred.',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }

    /**
     * Get Microsoft calendar Event.
     *
     * @param    $id calendar event id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCalendarEvent($id, Request $request)
    {
        try {
            $dbName = $request->get('dbName');
            $userId = $request->get('userId');

            $socialAccounts = $this->getSocialAccountData($dbName, $userId);

            if (!$socialAccounts)
                return $this->socialAccountFailureMessage();
            // //access token is stored for the social_accounts
            // $accessToken = $this->getMicrosoftAccessToken($socialAccounts, $dbName);
            // $response = Http::withToken($accessToken)->get(env('MICROSOFT_GET_EVENT_URL') . $id);
            // $eventData = $response->json();
            //     dd($eventData);
            $calendarEvent = MicrosoftCalendarEvent::on($dbName)->where(
                [['id', $id], ['tbl_social_account_id', $socialAccounts->id]]
            )->first();

            if (!$calendarEvent)
                return [
                    'status'     => false,
                    'message'    => 'ErrorInvalidId.',
                    'errors'       => ['error' => array('Id is Invalid')],
                    'statusCode' => 422
                ];

            return [
                'status'     => true,
                'message'    => 'Microsoft calendar event.',
                'data'       => $calendarEvent,
                'statusCode' => 200
            ];
        } catch (\Exception $e) {
            return [
                'status'     => false,
                'message'    => 'An error occurred.',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }

    public function microsoftconnect(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'authCode' => 'required',
                'codeVerifier' => 'required'
            ], [
                'authCode.required'  => 'authCode is required',
                'codeVerifier.required'  => 'codeVerifier is required',
            ]);

            if ($validator->fails()) {
                return [
                    'status'     => false,
                    'message'    => 'Validation Error',
                    'errors'     => $validator->errors(),
                    'statusCode' => 422
                ];
            }

            $dbName = $request->get('dbName');
            $userId = $request->get('userId');


            $appModule = $this->checkAppExisting($dbName);
            if (!$appModule) {
                return [
                    'status'     => false,
                    'message'    => 'Microsoft app missing or disabled',
                    'errors'     => ['error' => 'Microsoft app missing or disabled'],
                    'statusCode' => 400,
                ];
            }
            $ClientId = $this->getValue($dbName, $appModule->id, $this->clientId);
            $secretId = $this->getValue($dbName, $appModule->id, $this->secretId);
            $redirectUrl = $this->getValue($dbName, $appModule->id, $this->redirectUrl);
            // dd($ClientId->appvalue);
            $response = Http::asForm()->post(env('MICROSOFT_TOKEN_URL'), [
                'client_id' => $ClientId->appvalue,
                'client_secret' => $secretId->appvalue,
                'code' => $request->authCode,
                'redirect_uri' => env('MICROSOFT_REDIRECT_URI_CONNECT'),
                'grant_type' => 'authorization_code',
                'code_verifier' => $request->codeVerifier,
            ]);

            if ($response->successful()) {
                $tokenData = $response->json();
                $accessToken = $tokenData['access_token'];
                $refreshToken =   $tokenData['refresh_token'];
                $expires_in =   $tokenData['expires_in'];
            } else {
                return [
                    'status'     => false,
                    'message'    => $response->json(),
                    'errors'     => 'Invalid Authcode',
                    'statusCode' => $response->status(),
                ];
            }


            $microsftUserDetails = Http::withToken($accessToken)->get(env('MICROSOFT_GET_USER_URL'));
            $email = '';
            if ($microsftUserDetails->successful()) {
                $microsftUser = $microsftUserDetails->json();
                // dd($microsftUser['mail']);
                $email = $microsftUser['mail'];
            } else {
                return [
                    'status'     => false,
                    'message'    => 'Failed to access user info',
                    'errors'     => $microsftUserDetails->json(),
                    'statusCode' => $microsftUserDetails->status()
                ];
            }
            $userLogin = Employees::on($dbName)
                ->select('id', 'navn', 'email')
                ->where('email', $email)->first();
            if (!$userLogin) {
                return [
                    'status'     => false,
                    'message'    => 'Unauthorised',
                    'errors'     =>  ["error" => array("User not available.")],
                    'statusCode' => 400
                ];
            }
            $expiresInSeconds = (int) $expires_in;
            $user = SocialAccounts::on($dbName)->updateOrInsert(
                [
                    'cloud_sso_user_id' => $userId,
                    'provider' => $this->provider,
                ],
                [
                    'access_token' => $accessToken,
                    'refresh_token' => $refreshToken,
                    'provider' => $this->provider,
                    'provider_name' => $this->provider,
                    'token_expires_at' => now()->addSeconds($expiresInSeconds),
                    'email' => $email,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            if (!$user) {
                return [
                    'status'     => false,
                    'message'    => 'Server error',
                    'errors'     => ['error' => "Something went wrong"],
                    'statusCode' => 500,
                ];
            }
            return [
                'status'     => true,
                'message'    => 'Microsoft Connected.',
                'data'       => null,
                'statusCode' => 200
            ];
        } catch (\Exception $e) {
            return [
                'status'     => false,
                'message'    => 'An error occurred.',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }

    public function syncMicrosoftEvents(Request $request)
    {
        try {
            $dbName = $request->get('dbName');
            $userId =  $request->get('userId');
            $checksync = MicrosoftSyncStatus::on($dbName)->where('cloud_sso_user_id', $userId)->first();
            if (!$checksync || !$checksync->sync_status) {
                return [
                    'status'     => false,
                    'message'    => 'Microsoft sync is disabled',
                    'errors'     =>  ["error" => 'Microsoft sync is disabled'],
                    'statusCode' => 403
                ];
            }
            $socialAccounts = SocialAccounts::on($dbName)->where([['cloud_sso_user_id', $userId], ['provider', $this->provider]])->first();
            if (!$socialAccounts)
                return $this->socialAccountFailureMessage();
            $accessToken = $this->getMicrosoftAccessToken($socialAccounts, $dbName);
            if (isset($accessToken['status']) && $accessToken['status'] == false) {
                return [
                    'status'     => false,
                    'message'    => 'Re-connect your microsoft',
                    'errors'     => 'Failed to microsoft Login',
                    'statusCode' => 400
                ];
            }
            $response = Http::withToken($accessToken)->get(env('MICROSOFT_GET_EVENTS_URL'));
            if ($response->successful()) {
                $events = $response->json()['value'];
                $this->syncEvents($events, $dbName, $socialAccounts);
                // $externalIds = collect($events)->pluck('id')->toArray();
                // MicrosoftCalendarEvent::on($dbName)->where('tbl_social_account_id', $socialAccounts->id)
                //     ->whereNotIn('microsoft_event_id', $externalIds)
                //     ->update(['is_deleted' => 1]);
                // $checksync->sync_at =  now();
                // $checksync->save();
                return [
                    'status'     => true,
                    'message'    => 'Calendar Event sync successfully.',
                    'data'       => null,
                    'statusCode' => 200
                ];
            } else {
                return [
                    'status'     => false,
                    'message'    => 'Unauthorised',
                    'errors'     =>  ["error" => $response->json()],
                    'statusCode' => $response->status()
                ];
            }
        } catch (\Exception $e) {
            return [
                'status'     => false,
                'message'    => 'An error occurred.',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }

    public function getMicrosoftAccessToken($socialAccounts, $dbName)
    {
        if (now()->greaterThan($socialAccounts->token_expires_at)) {
            $appModule = $this->checkAppExisting($dbName);
            if (!$appModule) {
                return [
                    'status'     => false,
                    'message'    => 'Microsoft app missing or disabled',
                    'errors'     => ['error' => 'Microsoft app missing or disabled'],
                    'statusCode' => 400,
                ];
            }
            $ClientId = $this->getValue($dbName, $appModule->id, $this->clientId);
            $secretId = $this->getValue($dbName, $appModule->id, $this->secretId);
            $redirectUrl = $this->getValue($dbName, $appModule->id, $this->redirectUrl);
            $response = Http::asForm()->post(env('MICROSOFT_TOKEN_URL'), [
                'client_id' => $ClientId->appvalue,
                'client_secret' => $secretId->appvalue,
                'grant_type' => 'refresh_token',
                'refresh_token' => $socialAccounts->refresh_token,
                'redirect_uri' => $redirectUrl->appvalue,
            ]);
            if ($response->successful()) {
                $newTokenData = $response->json();

                SocialAccounts::on($dbName)
                    ->where([['cloud_sso_user_id', $socialAccounts->id], ['provider', $this->provider]])
                    ->update([
                        'access_token' => $newTokenData['access_token'],
                        'refresh_token' => $newTokenData['refresh_token'],
                        'token_expires_at' => now()->addSeconds($newTokenData['expires_in']),
                        'updated_at' => now(),
                    ]);

                return $newTokenData['access_token'];
            } else {
                return [
                    'status'     => false,
                    'message'    => 'Failed to refresh access token',
                    'errors'     =>  ["error" => $response->json()],
                    'statusCode' => $response->status()
                ];
            }
        }

        return $socialAccounts->access_token;
    }

    public function syncEvents($events, $dbName, $socialAccounts)
    {
        $batchSize = 100;
        $batchData = [];
        
        foreach ($events as $event) {
            $batchData[] = [
                'tbl_social_account_id' => $socialAccounts->id,
                'microsoft_event_id' => $event['id'],
                'title' => $event['subject'] ?? 'Untitled Event',
                'body_content' => $event['body']['content'] ?? null,
                'body_content_type' => $event['body']['contentType'] ?? 'HTML',
                'isAllDay' => $event['isAllDay'] ?? false,
                'isCancelled' => $event['isCancelled'] ?? false,
                'isOrganizer' => $event['isOrganizer'] ?? false,
                'showAs' => $event['showAs'] ?? null,
                'response_status' => $event['responseStatus']['response'] ?? null,
                'response_time' => isset($event['responseStatus']['time'])
                    ? Carbon::parse($event['responseStatus']['time'])->format('Y-m-d H:i:s')
                    : null,
                'type' => $event['type'] ?? null,
                'webLink' => $event['webLink'] ?? null,
                'start_time' => $event['start']['dateTime'],
                'end_time' => $event['end']['dateTime'],
                'timezone' => $event['start']['timeZone'] ?? 'UTC',
                'onlineMeetingUrl' => $event['onlineMeeting']['joinUrl'] ?? null,
                'isOnlineMeeting' => $event['isOnlineMeeting'] ?? false,
                'location_displayName' => $event['location']['displayName'] ?? null,
                'location_locationType' => $event['location']['locationType'] ?? null,
                'organizer_name' => $event['organizer']['emailAddress']['name'] ?? null,
                'organizer_address' => $event['organizer']['emailAddress']['address'] ?? null,
                'attendees' => json_encode($event['attendees'] ?? []),
                'locations' => json_encode($event['locations'] ?? []),
                'created_at' => now(),
                'updated_at' => now(),
            ];
            if (count($batchData) >= $batchSize) {
                try {
                    // Insert batch data into the database
                    MicrosoftCalendarEvent::on($dbName)->upsert(
                        $batchData,
                        ['microsoft_event_id', 'tbl_social_account_id'],  // Unique identifier column(s) for update
                        ['title', 'body_content', 'body_content_type', 'isAllDay', 'isCancelled', 'isOrganizer', 'showAs', 'response_status', 'response_time', 'type', 'webLink', 'start_time', 'end_time', 'timezone', 'onlineMeetingUrl', 'isOnlineMeeting', 'location_displayName', 'location_locationType', 'organizer_name', 'organizer_address', 'attendees', 'locations', 'updated_at']  // Columns to update if row_id exists
                    );

                    $batchData = [];
                } catch (\Exception $e) {
                    dd("DB Insert Error: " . $e->getMessage());
                }
            }
        }
        
        // Insert any remaining data that didn't fill a complete batch
        if (!empty($batchData)) {
            // dd($batchData);
            try {
                MicrosoftCalendarEvent::on($dbName)->upsert(
                    $batchData,
                    ['microsoft_event_id', 'tbl_social_account_id'],
                    ['title', 'body_content', 'body_content_type', 'isAllDay', 'isCancelled', 'isOrganizer', 'showAs', 'response_status', 'response_time', 'type', 'webLink', 'start_time', 'end_time', 'timezone', 'onlineMeetingUrl', 'isOnlineMeeting', 'location_displayName', 'location_locationType', 'organizer_name', 'organizer_address', 'attendees', 'locations', 'updated_at']
                );
            } catch (\Exception $e) {
                dd("DB Insert Error: " . $e->getMessage());
            }
        }
    }

    public function getSocialAccountData($dbName, $userId)
    {
        $socialAccounts = SocialAccounts::on($dbName)->where([['cloud_sso_user_id', $userId], ['provider', $this->provider]])->first();
        return $socialAccounts;
    }

    public function getIntegrate(Request $request)
    {
        try {
            // $validator = Validator::make($request->all(), [
            //     'clientID' => 'required',
            //     'SecretKey' => 'required',
            //     'redirectUrl' => 'required',
            // ], [
            //     'clientID.required'  => 'clientID is required',
            //     'SecretKey.required'  => 'SecretKey is required',
            //     'redirectUrl.required'  => 'redirectUrl is required',
            // ]);
            $dbName = $request->get('dbName');
            $userId = $request->get('userId');

            $appModule = $this->checkAppExisting($dbName);
            if ($appModule) {
                $appVariable = AppVariable::on($dbName)->where(
                    [
                        ['tbl_appmodule_id', $appModule->id]
                    ]
                )->get();
                return [
                    'status'     => true,
                    'message'    => 'Microsoft data',
                    'data'       => $appVariable,
                    'statusCode' => 200
                ];
            } else {
                return [
                    'status'     => false,
                    'message'    => 'Microsoft app missing or disabled',
                    'errors'     => ['error' => 'Microsoft app missing or disabled'],
                    'statusCode' => 400,
                ];
            }
        } catch (\Exception $e) {
            return [
                'status'     => false,
                'message'    => 'An error occurred.',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }

    public function storeIntegrate(Request $request)
    {
        try {
            $dbName = $request->get('dbName');
            $userId = $request->get('userId');
            $validator = Validator::make($request->all(), [
                'clientID' => 'required',
                'SecretKey' => 'required',
                'redirectUrl' => 'required',
                'integratetUrl' => 'required',
            ], [
                'clientID.required'  => 'clientID is required',
                'SecretKey.required'  => 'SecretKey is required',
                'redirectUrl.required'  => 'redirectUrl is required',
                'integratetUrl.required'  => 'IntegrateUrl is required',
            ]);
            if ($validator->fails()) {
                return [
                    'status'     => false,
                    'message'    => 'Validation Error',
                    'errors'     => $validator->errors(),
                    'statusCode' => 422
                ];
            }
            $appModule = $this->checkAppExisting($dbName);
            if ($appModule) {

                AppVariable::on($dbName)
                    ->where('tbl_appmodule_id', $appModule->id)
                    ->where('appvariable', 'clientId')
                    ->update(['appvalue' => $request->clientID]);
                AppVariable::on($dbName)
                    ->where('tbl_appmodule_id', $appModule->id)
                    ->where('appvariable', 'secretId')
                    ->update(['appvalue' => $request->SecretKey]);
                AppVariable::on($dbName)
                    ->where('tbl_appmodule_id', $appModule->id)
                    ->where('appvariable', 'redirectUrl')
                    ->update(['appvalue' => $request->redirectUrl]);
                AppVariable::on($dbName)
                    ->where('tbl_appmodule_id', $appModule->id)
                    ->where('appvariable', 'integrateRedirectUrl')
                    ->update(['appvalue' => $request->integratetUrl]);

                return [
                    'status'     => true,
                    'message'    => 'Updated successfully',
                    'data'       => null,
                    'statusCode' => 200
                ];
            } else {
                return [
                    'status'     => false,
                    'message'    => 'Microsoft app is missing or disabled.',
                    'errors'     => ['error' => 'Microsoft app is missing or disabled.'],
                    'statusCode' => 400,
                ];
            }
        } catch (\Exception $e) {
            return [
                'status'     => false,
                'message'    => 'An error occurred.',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }

    /**
     * CheckApp existing in tbl_appmodules table
     */
    public function checkAppExisting($dbName)
    {
        return AppModule::on($dbName)->where([['appname', $this->provider], ['appstatus', 1]])->first();
    }

    public function getValue($dbName, $appModuleId, $appVariable)
    {
        return  AppVariable::on($dbName)->select('appvalue')->where(
            [
                ['tbl_appmodule_id', $appModuleId],
                ['appvariable', $appVariable]
            ]
        )->first();
    }

    public function integrateMenu(Request $request)
    {
        try {
            $dbName = $request->get('dbName');
            $userId = $request->get('userId');
            $appModule = $this->checkAppExisting($dbName);
            if ($appModule) {
                $appOption = AppOption::on($dbName)->where(
                    [
                        ['appmodule_id', $appModule->id]
                    ]
                )->get();
                return [
                    'status'     => true,
                    'message'    => 'Microsoft data',
                    'data'       => $appOption,
                    'statusCode' => 200
                ];
            } else {
                return [
                    'status'     => false,
                    'message'    => 'Microsoft app missing or disabled',
                    'errors'     => ['error' => 'Microsoft app missing or disabled'],
                    'statusCode' => 400,
                ];
            }
        } catch (\Exception $e) {
            return [
                'status'     => false,
                'message'    => 'An error occurred.',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }

    /**
     * check Microsoft connet.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkconnect(Request $request)
    {
        try {
            $dbName = $request->get('dbName');
            $userId = $request->get('userId');
            $checkMicrosoftConnection = $this->getSocialAccountData($dbName, $userId);
            if (!$checkMicrosoftConnection) {
                return [
                    'status'     => true,
                    'message'    => 'Microsoft connection',
                    'data'       =>  [
                        'status' => false,
                        'service' => 'Microsoft',
                        'message' => 'Microsoft account did not connect'
                    ],
                    'statusCode' => 200
                ];
            }
            return [
                'status'     => true,
                'message'    => 'Microsoft connection',
                'data'       =>  [
                    'status' => true,
                    'service' => 'Microsoft',
                    'message' => 'Microsoft account is successfully connected'
                ],
                'statusCode' => 200
            ];
        } catch (\Exception $e) {
            return [
                'status'     => false,
                'message'    => 'An error occurred.',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }

    public function eventResponse(Request $request)
    {
        try {
            $dbName = $request->get('dbName');
            $userId = $request->get('userId');
            $validator = Validator::make($request->all(), [
                'response' => 'required',
                'eventId' => 'required',
            ], [
                'response.required'  => 'response is required',
                'eventId.required'  => 'event id is required',
            ]);

            $socialAccounts = $this->getSocialAccountData($dbName, $userId);

            if (!$socialAccounts)
                return $this->socialAccountFailureMessage();

            $accessToken = $this->getMicrosoftAccessToken($socialAccounts, $dbName);
            if (isset($accessToken['status']) && $accessToken['status'] == false) {
                return [
                    'status'     => false,
                    'message'    => 'Re-connect your microsoft',
                    'errors'     => 'Failed to microsoft Login',
                    'statusCode' => 400
                ];
            }

            $payload = [

                "comment" => $request->message ? $request->message : '',
                "sendResponse" => true

            ];
            $eventId = $request->eventId;
            $responseStatus = $request->responseStatus;
            switch ($responseStatus) {
                case 'accept':
                    $url = 'https://graph.microsoft.com/v1.0/me/events/' . $eventId . '/accept';
                    $showAs = 'busy';
                    $responseStatus = 'accepted';
                    break;
                case 'tentative':
                    $url = 'https://graph.microsoft.com/v1.0/me/events/' . $eventId . '/tentativelyAccept';
                    $showAs = 'tentative';
                    $responseStatus = 'tentativelyAccepted';
                    break;
                case 'decline':
                    $url = 'https://graph.microsoft.com/v1.0/me/events/' . $eventId . '/decline';
                    $showAs = 'free';
                    $responseStatus = 'decline';
                    break;
            }

            $response = Http::withToken($accessToken)->post($url, $payload);
            if ($response->successful()) {
                MicrosoftCalendarEvent::on($dbName)
                    ->where([
                        ['microsoft_event_id', $eventId],
                        ['tbl_social_account_id', $socialAccounts->id]
                    ])->update(

                        [
                            'showAs' => $showAs,
                            'response_status' => $responseStatus,
                            'updated_at' => now(),
                        ]
                    );
                return [
                    'status'     => true,
                    'message'    => 'Updated response successfully',
                    'data'       => null,
                    'statusCode' => 200
                ];
            } else {
                return [
                    'status'     => false,
                    'message'    => 'Failed to update response',
                    'errors'     =>  $response->json(),
                    'statusCode' => $response->status()
                ];
            }
        } catch (\Exception $e) {
            return [
                'status'     => false,
                'message'    => 'An error occurred.',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }

    public function synctoggle(Request $request)
    {
        try {
            $dbName = $request->get('dbName');
            $userId = $request->get('userId');
            $validator = Validator::make($request->all(), [
                'sync' => 'required',
            ], [
                'sync.required'  => 'sync parameter is required',
            ]);
            if ($validator->fails()) {
                return [
                    'status'     => false,
                    'message'    => 'Validation Error',
                    'errors'     => $validator->errors(),
                    'statusCode' => 422
                ];
            }
            $status = MicrosoftSyncStatus::on($dbName)->updateOrInsert(
                ['cloud_sso_user_id' => $userId],
                [
                    'cloud_sso_user_id' => $userId,
                    'sync_status' => $request->sync ? $request->sync : '0',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],

            );
            if (!$status) {
                return [
                    'status'     => false,
                    'message'    => 'Faliure to update',
                    'errors'     => ['error' => 'check connection'],
                    'statusCode' => 500,
                ];
            }
            return [
                'status'     => true,
                'message'    => 'Updated successfully',
                'data'       => null,
                'statusCode' => 200
            ];
        } catch (\Exception $e) {
            return [
                'status'     => false,
                'message'    => 'An error occurred.',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }
    public function checksynctoggle(Request $request)
    {
        try {
            $dbName = $request->get('dbName');
            $userId = $request->get('userId');
            $checksynctoggle = MicrosoftSyncStatus::on($dbName)->where('cloud_sso_user_id', $userId)->first();
            return [
                'status'     => true,
                'message'    => 'sync status',
                'data'       => [
                    'microsoftSyncToggle' => $checksynctoggle ? $checksynctoggle->sync_status : 0,
                    'sync_at' => $checksynctoggle ? ($checksynctoggle->sync_at ?  $checksynctoggle->sync_at : '') : '',
                ],
                'statusCode' => 200
            ];
        } catch (\Exception $e) {
            return [
                'status'     => false,
                'message'    => 'something went wrong!',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }
    protected function socialAccountFailureMessage()
    {
        return [
            'status'     => false,
            'message'    => 'Connect your Microsoft account',
            'errors'     =>  ["error" => array("Please log in or connect to your Microsoft account.")],
            'statusCode' => 401
        ];
    }
    public function usersMailList(Request $request)
    {
        try {

            $dbName = $request->get('dbName');
            $userId = $request->get('userId');

            $usersMailList = DB::connection($dbName)
                ->table('cloud_sso_users')
                ->select('email')
                ->where('id', '!=', $userId)->get();

            return [
                'status'     => true,
                'message'    => 'Users Mail List',
                'data'       => $usersMailList,
                'statusCode' => 200
            ];
        } catch (\Exception $e) {
            return [
                'status'     => false,
                'message'    => 'something went wrong!',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }
    public function getEventsByDate(Request $request)
    {
        try {
            $dbName = $request->get('dbName');
            $userId =  $request->get('userId');
            $checksync = MicrosoftSyncStatus::on($dbName)->where('cloud_sso_user_id', $userId)->first();
            if (!$checksync || !$checksync->sync_status) {
                return [
                    'status'     => false,
                    'message'    => 'Microsoft sync is disabled',
                    'errors'     =>  ["error" => 'Microsoft sync is disabled'],
                    'statusCode' => 403
                ];
            }
            $socialAccounts = SocialAccounts::on($dbName)->where([['cloud_sso_user_id', $userId], ['provider', $this->provider]])->first();
            if (!$socialAccounts)
                return $this->socialAccountFailureMessage();
            $accessToken = $this->getMicrosoftAccessToken($socialAccounts, $dbName);
            if (isset($accessToken['status']) && $accessToken['status'] == false) {
                return [
                    'status'     => false,
                    'message'    => 'Re-connect your microsoft',
                    'errors'     => 'Failed to microsoft Login',
                    'statusCode' => 400
                ];
            }         
            $startDateTime          = $request->startdatetime;
            $endDateTime            = $request->enddatetime;
            $startDateTimeFormatted = $startDateTime; 
            $endDateTimeFormatted   = $endDateTime; 
            // $eventUrl = 'https://graph.microsoft.com/v1.0/me/calendarView/delta?startdatetime=2025-06-01T00:00:00&enddatetime=2025-06-05T23:59:59';
            $eventUrl = 'https://graph.microsoft.com/v1.0/me/calendarView/delta?startdatetime=' . $startDateTimeFormatted . '&enddatetime=' . $endDateTimeFormatted;
            // dd($eventUrl);
            $response = Http::withToken($accessToken)->get($eventUrl);
            // dd($response->json());
            if ($response->successful()) {
                $events = $response->json()['value'];
                // dd($events);
                $this->syncEvents($events, $dbName, $socialAccounts);
                $externalIds = collect($events)->pluck('id')->toArray();
                // MicrosoftCalendarEvent::on($dbName)->where('tbl_social_account_id', $socialAccounts->id)
                //     ->whereNotIn('microsoft_event_id', $externalIds)
                //     ->update(['is_deleted' => 1]);
                // $checksync->sync_at =  now();
                // $checksync->save();
                return [
                    'status'     => true,
                    'message'    => 'Microsoft calendar events list',
                    'data'       => null,
                    'statusCode' => 200
                ];
            } else {
                return [
                    'status'     => false,
                    'message'    => 'Unauthorised',
                    'errors'     =>  ["error" => $response->json()],
                    'statusCode' => $response->status()
                ];
            }
        } catch (\Exception $e) {
            return [
                'status'     => false,
                'message'    => 'An error occurred.',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }
}
