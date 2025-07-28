<?php

namespace App\Api\Customer\Modules\CompanyLogin\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\CompanyDatabaseService;
use App\Http\Controllers\Api\BaseController;
use App\Api\Systemadmin\Modules\Company\Models\SsoSettings;
use App\Api\Customer\Modules\CompanyLogin\Models\FailedLogin;
use App\Api\Customer\Modules\Employees\Models\Employees;
use App\Api\Customer\Modules\CompanyLogin\Models\PageModules;
use App\Api\Customer\Modules\CompanyLogin\Models\UserloginActivity;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use App\Traits\sendNotification;

class DashboardController extends BaseController
{
    use sendNotification;

    protected $companyDatabaseService;

    public function __construct(CompanyDatabaseService $companyDatabaseService)
    {
        $this->companyDatabaseService = $companyDatabaseService;
    }
    /**
     * Display the dashboard with relevant statistics.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $dbName    = $request->get('dbName');
            $userId    = $request->get('userId');
            $companyId = $request->get('companyId');

            if (!$companyId) {
                return [
                    'status'     => false,
                    'message'    => 'Unauthorised',
                    'errors'     => ["error" => ["Company ID not found in token"]],
                    'statusCode' => 401,
                ];
            }
            // Employee details
            $employeeDetails = DB::connection($dbName)
                ->table('cloud_sso_users')
                ->select('brugernavn', 'user_image')
                ->where('id', $userId)
                ->first();
            // Total Employee count
            $employeeCount = DB::connection($dbName)
                ->table('cloud_sso_users')
                ->count();
            // Increased Employee count in last week
            $employeeCountLastWeek = DB::connection($dbName)
                ->table('cloud_sso_users')
                ->where('created_at', '>=', now()->subWeek())
                ->count();
            // Succesful login count
            $successfulLogins = DB::connection($dbName)
                ->table('cloud_sso_users')
                ->where('status', 1)
                ->count();
            $successfulLoginsLast24Hours = DB::connection($dbName)
                ->table('cloud_sso_users')
                ->where('lastlogin', '>=', now()->subHours(24))
                ->where('status', 1)
                ->count();
            // Blocked employee count
            $blockedCount = DB::connection($dbName)
                ->table('cloud_sso_users')
                ->where('hideuser', 1)
                ->count();
            $blockedCountLastMonth = DB::connection($dbName)
                ->table('cloud_sso_users')
                ->where('hideuser', 1)
                ->where('updated_at', '>=', now()->subMonth())
                ->count();
            // Failed Login count 
            $failedLogin = FailedLogin::on($dbName)->count();
            $failedLoginLastWeek = FailedLogin::on($dbName)
                ->where('failedat', '>=', now()->subWeek())->count();
            $statistics = [
                'employeeCount'         => $employeeCount,
                'employeeCountLastWeek' => $employeeCountLastWeek,
                'successfulLogins'      => $successfulLogins,
                'loggedInLast24Hrs'     => $successfulLoginsLast24Hours,
                'blockedCount'          => $blockedCount,
                'blockedCountLastMonth' => $blockedCountLastMonth,
                'failedLogin'           => $failedLogin,
                'failedLoginLastWeek'   => $failedLoginLastWeek,
                'employeeDetails'       => $employeeDetails,
            ];
            return $this->sendResponse($statistics, 'Dashboard statistics retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendError('An error occurred while fetching dashboard statistics.', ['error' => $e->getMessage()], 500);
        }
    }
    public function appAccess(Request $request)
    {
        try {
            $dbName    = $request->get('dbName');
            $userId    = $request->get('userId');
            $companyId = $request->get('companyId');
            if (!$companyId) {
                return [
                    'status'     => false,
                    'message'    => 'Unauthorised',
                    'errors'     => ["error" => ["Company ID not found in token"]],
                    'statusCode' => 401,
                ];
            }

            $ssoLoginAccess = SsoSettings::select('crm_setting', 'cms_setting', 'shop_setting')
                ->where('company_id', $companyId)
                ->first();
            if (!$ssoLoginAccess) {
                return [
                    'status'     => false,
                    'message'    => 'Settings not found',
                    'errors'     => ["error" => ["SSO settings not found for company"]],
                    'statusCode' => 404,
                ];
            }

            $userSiteAccess = DB::connection($dbName)
                ->table('cloud_sso_users')
                ->select('siteaccess')
                ->where('id', $userId)
                ->first();
            $siteTypes = config('app.siteaccess');
            $accessArray = explode('-', $userSiteAccess->siteaccess);
            $accessMapping = [];
            $index = 0;
            foreach ($siteTypes as $key => $value) {
                $accessMapping[$key] = isset($accessArray[$index]) ? (int)$accessArray[$index] : 0;
                $index++;
            }
            $deniedAccess = [];
            foreach ($accessMapping as $type => $hasAccess) {
                if ($hasAccess == 0) {
                    $deniedAccess[] = ucfirst($type);
                }
            }
            if (!empty($deniedAccess)) {
                $deniedList = implode(', ', $deniedAccess);
                return $this->sendError(
                    "User does not have access to: {$deniedList}",
                    ['error' => ["User does not have access to: {$deniedList}"]],
                    403
                );
            }

            $responseData = [
                'crm'  => ['access' => false, 'url' => ''],
                'cms'  => ['access' => false, 'url' => ''],
                'shop' => ['access' => false, 'url' => ''],
            ];

            $settingsMap = [
                'crm_setting'  => ['key' => 'crm',  'variabel' => 'cloud_crm_URL'],
                'cms_setting'  => ['key' => 'cms',  'variabel' => 'wlanshop_URL'],
                'shop_setting' => ['key' => 'shop', 'variabel' => 'profilURL'],
            ];

            $activeKeys = [];
            foreach ($settingsMap as $settingKey => $details) {
                if ($ssoLoginAccess->$settingKey) {
                    $activeKeys[] = $details['variabel'];
                }
            }

            if (!empty($activeKeys)) {
                $cloudVariables = DB::connection($dbName)
                    ->table('cloud_variabler')
                    ->whereIn('variabel', $activeKeys)
                    ->pluck('vaerdi', 'variabel');

                foreach ($settingsMap as $settingKey => $details) {
                    if ($ssoLoginAccess->$settingKey && isset($cloudVariables[$details['variabel']])) {
                        $responseData[$details['key']] = [
                            'access' => true,
                            'url'    => $cloudVariables[$details['variabel']],
                        ];
                    }
                }
            }
            return $this->sendResponse($responseData, 'Settings retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendError('An error occurred while fetching dashboard statistics.', ['error' => $e->getMessage()], 500);
        }
    }
    public function pageModuleList(Request $request)
    {
        try {
            $dbName = $request->get('dbName');
            $pageModuleList = PageModules::on($dbName)
                ->select('id', 'pagename', 'pageslug')->get();
            return $this->sendResponse($pageModuleList, 'App activity data retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendError('An error occurred while fetching dashboard statistics.', ['error' => $e->getMessage()], 500);
        }
    }
    public function appActivity(Request $request)
    {
        try {
            // Get input from the request
            $dbName = $request->get('dbName');
            $userId = $request->get('userId');
            $date = $request->date;
            $month = $request->month;
            $year  = $request->year;
            $type  = $request->type ?? 'date';
            $day   = $year . '-' . $month . '-' . $date;

            $pageActivityQuery = DB::connection($dbName)
                ->table('tbl_user_page_activity')
                ->where('pagemodule_id', '!=', 1)
                ->where('cloud_sso_user_id', $userId);

            if ($type === 'date' && $date) {
                $pageActivityQuery->whereDate('tbl_user_page_activity.created_at', '=', $day);
            } elseif ($type === 'month' && $month && $year) {
                $pageActivityQuery->whereYear('tbl_user_page_activity.created_at', '=', $year)
                    ->whereMonth('tbl_user_page_activity.created_at', '=', $month);
            } elseif ($type === 'year' && $year) {
                $pageActivityQuery->whereYear('tbl_user_page_activity.created_at', '=', $year);
            }

            $results = $pageActivityQuery
                ->leftJoin('tbl_pagemodules', function ($join) use ($dbName) {
                    $join->on('tbl_pagemodules.id', '=', 'tbl_user_page_activity.pagemodule_id')
                        ->where('tbl_pagemodules.pageslug', '!=', 'dashboard');
                })
                ->select(
                    'tbl_user_page_activity.pagemodule_id',
                    DB::raw('SUM(tbl_user_page_activity.duration) as total_duration'),
                    'tbl_pagemodules.pagename'
                )
                ->groupBy('tbl_user_page_activity.pagemodule_id', 'tbl_pagemodules.pagename')
                ->get();

            $statistics = [
                'appActivity' => $results
            ];
            return $this->sendResponse($statistics, 'App activity data retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendError('An error occurred while fetching dashboard statistics.', ['error' => $e->getMessage()], 500);
        }
    }
    /**
     * Display the Admin dashboard with relevant statistics.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function adminDashboard(Request $request)
    {
        try {
            $dbName    = $request->get('dbName');
            $userId    = $request->get('userId');
            $companyId = $request->get('companyId');

            if (!$companyId) {
                return [
                    'status'     => false,
                    'message'    => 'Unauthorised',
                    'errors'     => ["error" => ["Company ID not found in token"]],
                    'statusCode' => 401,
                ];
            }
            $userRole     = DB::connection($dbName)->table('cloud_sso_users')->where('id', $userId)->select('userlevel')->first();
            $role         = Role::select('name')->where('id',$userRole->userlevel)->first();
            $userRoleName = $role->name;

            $totalEmployees     = DB::connection($dbName)->table('cloud_sso_users')->count();
            $employeeCount      = $this->getEmployeeCountByPeriod($dbName, $request->get('employee_count_by', 'day'));
            $employeeCountChart = $this->getEmployeesCountChart($dbName);  

            if($userRoleName == "admin") {               
                     $totalSuccesLogins      = UserloginActivity::on($dbName)->count();
                     $successfulLogins       = $this->getSuccessfulLoginsByPeriod($dbName, $request->get('success_login_by', 'day'));
                     $successLoginCountChart = $this->getSuccessLoginCountChart($dbName);
                     $totalFailedLogin       = FailedLogin::on($dbName)->count();
                     $failedLogin            = $this->getFailedLoginCount($dbName, $request->get('failed_login_by', 'day'));
                     $failedLoginCountChart  = $this->getFailedLoginCountChart($dbName);
            }else {
                     $totalSuccesLogins      = UserloginActivity::on($dbName)->where('userid', $userId)->count();   
                     $successfulLogins       = $this->getSuccessfulLoginsByUserId($dbName, $userId, $request->get('success_login_by', 'day'));
                     $successLoginCountChart = $this->getSuccessLoginCountChartByUserId($dbName, $userId);
                     $userEmail              = DB::connection($dbName)->table('cloud_sso_users')->where('id', $userId)->select('email')->first();                           
                     $totalFailedLogin       = FailedLogin::on($dbName)->where('email', $userEmail->email)->count();     
                     $failedLogin            = $this->getFailedLoginCountByUserId($dbName, $userEmail->email, $request->get('failed_login_by', 'day'));  
                     $failedLoginCountChart  = $this->getFailedLoginCountChartByUserId($dbName, $userEmail->email);  
            }

            $totalBlockedCount  = DB::connection($dbName)->table('cloud_sso_users')->where('hideuser', 1)->count();
            $blockedCount       = $this->getBlockedUserCount($dbName, $request->get('blocked_count_by', 'day'));
            $blockedCountChart  = $this->getblockedCountChart($dbName);

            $statistics = [
                'totalEmployees'         => $totalEmployees,
                'employeeCount'          => $employeeCount,
                'totalSuccesLogins'      => $totalSuccesLogins,
                'successfulLogins'       => $successfulLogins,
                'totalFailedLogin'       => $totalFailedLogin,
                'failedLogin'            => $failedLogin,
                'totalBlockedCount'      => $totalBlockedCount,
                'blockedCount'           => $blockedCount,
                'employeeCountChart'     => $employeeCountChart,
                'successLoginCountChart' => $successLoginCountChart,
                'failedLoginCountChart'  => $failedLoginCountChart,
                'blockedCountChart'      => $blockedCountChart,
            ];
            return $this->sendResponse($statistics, 'Dashboard statistics retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendError('An error occurred while fetching dashboard statistics.', ['error' => $e->getMessage()], 500);
        }
    }
    /**
     * Display the Admin dashboard with relevant statistics for chart.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function adminDashboardChart(Request $request)
    {
        try {
            $dbName    = $request->get('dbName');

            $employeeCountChart     = $this->getEmployeesCountChart($dbName);
            $successLoginCountChart = $this->getSuccessLoginCountChart($dbName);
            $failedLoginCountChart  = $this->getFailedLoginCountChart($dbName);
            $blockedCountChart      = $this->getblockedCountChart($dbName);


            $statistics = [
                'employeeCountChart'     => $employeeCountChart,
                'successLoginCountChart' => $successLoginCountChart,
                'failedLoginCountChart'  => $failedLoginCountChart,
                'blockedCountChart'      => $blockedCountChart,
            ];
            return $this->sendResponse($statistics, 'Admin Dashboard Chart statistics retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendError('An error occurred while fetching dashboard statistics.', ['error' => $e->getMessage()], 500);
        }
    }
    public function getFailedLoginCount($dbName, $period)
    {
        $startDate        = $this->getStartDateByPeriod($period);
        $failedLoginQuery = FailedLogin::on($dbName);

        return $failedLoginQuery->where('failedat', '>=', $startDate)->count();
    }
    public function getFailedLoginCountByUserId($dbName,$email,$period)
    {
        $startDate        = $this->getStartDateByPeriod($period);
        $failedLoginQuery = FailedLogin::on($dbName);

        return $failedLoginQuery->where('email',$email)->where('failedat', '>=', $startDate)->count();
    }
    public function getBlockedUserCount($dbName, $period)
    {
        $startDate    = $this->getStartDateByPeriod($period);
        $blockedQuery = DB::connection($dbName)->table('cloud_sso_users')->where('hideuser', 1);

        return $blockedQuery->where('updated_at', '>=', $startDate)->count();
    }

    public function getEmployeeCountByPeriod($dbName, $period)
    {
        $startDate          = $this->getStartDateByPeriod($period);
        $employeeCountQuery = DB::connection($dbName)->table('cloud_sso_users');

        return $employeeCountQuery->where('created_at', '>=', $startDate)->count();
    }

    public function getSuccessfulLoginsByPeriod($dbName, $period)
    {
        $startDate       = $this->getStartDateByPeriod($period);
        $successfulLogin = UserloginActivity::on($dbName);
        $logins = $successfulLogin->where('logintime', '>=', $startDate)
            ->selectRaw('logintime, COUNT(DISTINCT logintime) as login_count')
            ->groupBy('logintime')
            ->get();

        $totalLoginCount = $logins->sum('login_count');
        return $totalLoginCount;
    }
    public function getSuccessfulLoginsByUserId($dbName, $userId, $period)
    {
        $startDate       = $this->getStartDateByPeriod($period);
        $successfulLogin = UserloginActivity::on($dbName);
        $logins = $successfulLogin->where('userId', $userId)
            ->where('logintime', '>=', $startDate)
            ->selectRaw('logintime, COUNT(DISTINCT logintime) as login_count')
            ->groupBy('logintime')
            ->get();

        $totalLoginCount = $logins->sum('login_count');
        return $totalLoginCount;
    }
    private function getStartDateByPeriod($period)
    {
        $now = now();

        switch ($period) {
            case 'day':
                return $now->subHours(24);

            case 'week':
                return $now->subDays(7);

            case 'last_onemonth':
                return $now->subMonth();

            case 'last_sixmonth':
                return $now->subMonths(6);

            case 'last_oneyear':
                return $now->subYear();

            default:
                return $now->subHours(24);
        }
    }
    /**
     * Display the global dashboard with relevant statistics.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function globalDashboard(Request $request)
    {
        try {
            $dbName    = $request->get('dbName');
            $userId    = $request->get('userId');
            $companyId = $request->get('companyId');

            if (!$companyId) {
                return [
                    'status'     => false,
                    'message'    => 'Unauthorised',
                    'errors'     => ["error" => ["Company ID not found in token"]],
                    'statusCode' => 401,
                ];
            }
            $userRole     = DB::connection($dbName)->table('cloud_sso_users')->where('id', $userId)->select('userlevel')->first();
            $role         = Role::select('name')->where('id', $userRole->userlevel)->first();
            $userRoleName = $role->name;
            if($userRoleName == "admin") {
                    $totalCustomers = DB::connection($dbName)->table('cloud_crm_firmainfo')->count();
                    $totalProjects  = DB::connection($dbName)->table('cloud_crm_leads')->count();
                    $totalSales     = DB::connection($dbName)->table('cloud_crm_sager')->count();
                    $totalTasks     = DB::connection($dbName)->table('cloud_crm_aktiviteter')->count();

            }else {
                    $totalCustomers = DB::connection($dbName)->table('cloud_crm_firmainfo')->count();
                    $totalProjects  = DB::connection($dbName)->table('cloud_crm_leads')->count();
                    $totalSales     = DB::connection($dbName)->table('cloud_crm_sager_users')->where('user_id', $userId)->count();
                    $totalTasks     = DB::connection($dbName)->table('cloud_crm_aktiviteter_users')->where('user_id', $userId)->count();
            }

            $task_filter     = $request->taskFilter;
            $customer_filter = $request->customerFilter;
            $sales_filter    = $request->salesFilter;
            $projects_filter = $request->projectFilter;

            $totalTaskFilter     =  DB::connection($dbName)->table('cloud_crm_aktiviteter')->where('sagsStatus', $task_filter)->count();
            $totalCustomerFilter =  DB::connection($dbName)->table('cloud_crm_firmainfo')->where('blocked', $customer_filter)->count();
            $totalSalesFilter    =  DB::connection($dbName)->table('cloud_crm_sager')->where('statusId', $sales_filter)->count();
            $totalProjectFilter  =  DB::connection($dbName)->table('cloud_crm_leads')->where('qualified', $projects_filter)->count();

            if($userRoleName == "admin") {

                $criticalPriorityUsers = DB::connection($dbName)
                    ->table('cloud_crm_aktiviteter')
                    ->where('urgent', 1)
                    ->join('cloud_crm_aktiviteter_users', 'cloud_crm_aktiviteter.id', '=', 'cloud_crm_aktiviteter_users.activity_id')
                    ->count('cloud_crm_aktiviteter_users.user_id');

                $highPriorityUsers = DB::connection($dbName)
                    ->table('cloud_crm_aktiviteter')
                    ->where('urgent', 0)
                    ->join('cloud_crm_aktiviteter_users', 'cloud_crm_aktiviteter.id', '=', 'cloud_crm_aktiviteter_users.activity_id')
                    ->count('cloud_crm_aktiviteter_users.user_id');

                $criticalCasesCount = DB::connection($dbName)
                    ->table('cloud_crm_sager')
                    ->where('sagsType', 1)
                    ->count();
                $lowerCasesCount = DB::connection($dbName)
                    ->table('cloud_crm_sager')
                    ->where('sagsType', 0)
                    ->count();
            }else { 

                $activityUsers         = DB::connection($dbName)->table('cloud_crm_aktiviteter_users')->where('user_id', $userId)->select('activity_id')->get();
                $activityIds           = $activityUsers->pluck('activity_id')->toArray();
                $criticalPriorityUsers = DB::connection($dbName)->table('cloud_crm_aktiviteter')->whereIn('id', $activityIds)->where('urgent', 1)->count();
                $highPriorityUsers     = DB::connection($dbName)->table('cloud_crm_aktiviteter')->whereIn('id', $activityIds)->where('urgent', 0)->count();
                $salesCaseUsers        = DB::connection($dbName)->table('cloud_crm_sager_users')->where('user_id', $userId)->select('case_id')->get();
                $casesIds              = $salesCaseUsers->pluck('case_id')->toArray();
                $criticalCasesCount    = DB::connection($dbName)->table('cloud_crm_sager')->whereIn('id', $casesIds)->where('sagsType', 1)->count();
                $lowerCasesCount       = DB::connection($dbName)->table('cloud_crm_sager')->whereIn('id', $casesIds)->where('sagsType', 0)->count();
            }

            $taskCountChart     = $this->getTaskCountChart($dbName);
            $salesCountChart    = $this->getSalesCountChart($dbName);
            $customerCountChart = $this->getCustomerCountChart($dbName);
            $projectsCountChart = $this->getProjectsCountChart($dbName);

            $statistics = [
                'totalCustomers'        => $totalCustomers,
                'totalProjects'         => $totalProjects,
                'totalSales'            => $totalSales,
                'totalTasks'            => $totalTasks,
                'totalTasksFilter'      => $totalTaskFilter,
                'totalCustomerFilter'   => $totalCustomerFilter,
                'totalSalesFilter'      => $totalSalesFilter,
                'totalProjectsFilter'   => $totalProjectFilter,
                'criticalPriorityUsers' => $criticalPriorityUsers,
                'highPriorityUsers'     => $highPriorityUsers,
                'criticalCasesCount'    => $criticalCasesCount,
                'lowerCasesCount'       => $lowerCasesCount,
                'taskCountChart'        => $taskCountChart,
                'salesCountChart'       => $salesCountChart,
                'customerCountChart'    => $customerCountChart,
                'projectsCountChart'    => $projectsCountChart,
            ];
            return $this->sendResponse($statistics, 'Dashboard statistics retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendError('An error occurred while fetching dashboard statistics.', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the global dashboard task overview  statistics.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function taskOverview(Request $request)
    {
        try {
            $validator  = validator::make($request->all(), [
                'date' => 'required',
                'month' => 'required',
                'year' => 'required',
                'type' => 'required',
            ]);
            if ($validator->fails()) {
                return [
                    'status'     => false,
                    'message'    => 'Validation Error',
                    'errors'     => $validator->errors(),
                    'statusCode' => 400
                ];
            }

            $dbName    = $request->get('dbName');
            $userId    = $request->get('userId');
            $companyId = $request->get('companyId');
            $date  = $request->date;
            $month = $request->month;
            $year  = $request->year;
            $type  = $request->type ?? 'date';
            if ($type == 'date') {
                $day   = $year . '-' . $month . '-' . $date;
                $start = Carbon::parse($day)->startOfDay()->timestamp;
                $end = Carbon::parse($day)->endOfDay()->timestamp;
            } elseif ($type == 'month') {

                $month = $year . '-' . $month;
                $start = Carbon::parse($month . '-01')->startOfMonth()->timestamp;
                $end = Carbon::parse($month . '-01')->endOfMonth()->timestamp;
            } elseif ($type == 'year') {

                $year = $year;
                $start = Carbon::parse($year . '-01-01')->startOfYear()->timestamp;
                $end = Carbon::parse($year . '-12-31')->endOfYear()->timestamp;
            }
            $totalTasks = DB::connection($dbName)
                ->table('cloud_crm_aktiviteter')
                ->select('sagsStatus', DB::raw('COUNT(*) as count'))
                ->where('oprettetSsoId', $userId)
                ->whereBetween('oprettetDato', [$start, $end])
                ->groupBy('sagsStatus')
                ->get();

            $statusData = [
                0 => ['sagsStatus' => 0, 'count' => 0, 'status_label' => 'Not Started'],
                1 => ['sagsStatus' => 1, 'count' => 0, 'status_label' => 'Read through'],
                2 => ['sagsStatus' => 2, 'count' => 0, 'status_label' => 'In Progress'],
                3 => ['sagsStatus' => 3, 'count' => 0, 'status_label' => 'Completed'],
                4 => ['sagsStatus' => 4, 'count' => 0, 'status_label' => 'Cancelled']
            ];

            // Map fetched data into statusData
            foreach ($totalTasks as $task) {
                $statusData[$task->sagsStatus]['count'] = $task->count;
            }
            $totalTasks = collect(array_values($statusData));
            return $this->sendResponse($totalTasks, 'Task Overview Count');
        } catch (\Exception $e) {
            return $this->sendError('An error occurred while fetching task Overview statistics.', ['error' => $e->getMessage()], 500);
        }
    }
    public function getEmployeesCountChart($dbName)
    {

        $month = date('m');
        $year  = date('Y');

        // $firstDayOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $last30Days  = Carbon::now()->subDays(30);
        $currentDate = Carbon::now()->endOfDay();

        $dailyEmployeeCounts = DB::connection($dbName)
            ->table('cloud_sso_users')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as employee_count'))
            ->whereBetween('created_at', [$last30Days, $currentDate])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'asc')
            ->get();

        $allDates = [];
        for ($date = $last30Days; $date <= $currentDate; $date->addDay()) {
            $allDates[] = $date->toDateString();
        }

        $employeeData = [];
        foreach ($allDates as $date) {
            $employeeData[$date] = 0;
        }

        foreach ($dailyEmployeeCounts as $dailyCount) {
            $employeeData[$dailyCount->date] = $dailyCount->employee_count;
        }

        return $employeeData;
    }
    public function getSuccessLoginCountChart($dbName)
    {
        $month = date('m');
        $year  = date('Y');

        // $firstDayOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $last30Days  = Carbon::now()->subDays(30);
        $currentDate = Carbon::now()->endOfDay();

        $dailySuccessLoginCounts = DB::connection($dbName)
            ->table('tbl_user_login_activity')
            ->select(DB::raw('DATE(logintime) as date'), DB::raw('count(*) as successlogin_count'))
            ->whereBetween('logintime', [$last30Days, $currentDate])
            ->groupBy(DB::raw('DATE(logintime)'))
            // ->orderBy('date', 'asc')
            ->get();

        $allDates = [];
        for ($date = $last30Days; $date <= $currentDate; $date->addDay()) {
            $allDates[] = $date->toDateString();
        }

        $successLoginData = [];
        foreach ($allDates as $date) {
            $successLoginData[$date] = 0;
        }

        foreach ($dailySuccessLoginCounts as $dailyCount) {
            $successLoginData[$dailyCount->date] = $dailyCount->successlogin_count;
        }

        return $successLoginData;
    }
    public function getSuccessLoginCountChartByUserId($dbName, $userId)
    {
        $month = date('m');
        $year  = date('Y');

        // $firstDayOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $last30Days  = Carbon::now()->subDays(30);
        $currentDate = Carbon::now()->endOfDay();

        $dailySuccessLoginCounts = DB::connection($dbName)
            ->table('tbl_user_login_activity')
            ->select(DB::raw('DATE(logintime) as date'), DB::raw('count(*) as successlogin_count'))
            ->where('userid', $userId)
            ->whereBetween('logintime', [$last30Days, $currentDate])
            ->groupBy(DB::raw('DATE(logintime)'))
            ->get();

        $allDates = [];
        for ($date = $last30Days; $date <= $currentDate; $date->addDay()) {
            $allDates[] = $date->toDateString();
        }

        $successLoginData = [];
        foreach ($allDates as $date) {
            $successLoginData[$date] = 0;
        }

        foreach ($dailySuccessLoginCounts as $dailyCount) {
            $successLoginData[$dailyCount->date] = $dailyCount->successlogin_count;
        }

        return $successLoginData;
    }
    public function getFailedLoginCountChart($dbName)
    {
        $month = date('m');
        $year  = date('Y');

        // $firstDayOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $last30Days  = Carbon::now()->subDays(30);
        $currentDate = Carbon::now()->endOfDay();

        $dailyFailedCounts = FailedLogin::on($dbName)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as failed_count'))
            ->whereBetween('created_at', [$last30Days, $currentDate])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'asc')
            ->get();

        $allDates = [];
        for ($date = $last30Days; $date <= $currentDate; $date->addDay()) {
            $allDates[] = $date->toDateString();
        }

        $failedLoginData = [];
        foreach ($allDates as $date) {
            $failedLoginData[$date] = 0;
        }

        foreach ($dailyFailedCounts as $dailyCount) {
            $failedLoginData[$dailyCount->date] = $dailyCount->failed_count;
        }

        return $failedLoginData;
    }
    public function getFailedLoginCountChartByUserId($dbName , $email)
    {
        $month = date('m');
        $year  = date('Y');

        // $firstDayOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $last30Days  = Carbon::now()->subDays(30);
        $currentDate = Carbon::now()->endOfDay();

        $dailyFailedCounts = FailedLogin::on($dbName)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as failed_count'))
            ->where('email', $email)
            ->whereBetween('created_at', [$last30Days, $currentDate])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'asc')
            ->get();

        $allDates = [];
        for ($date = $last30Days; $date <= $currentDate; $date->addDay()) {
            $allDates[] = $date->toDateString();
        }

        $failedLoginData = [];
        foreach ($allDates as $date) {
            $failedLoginData[$date] = 0;
        }

        foreach ($dailyFailedCounts as $dailyCount) {
            $failedLoginData[$dailyCount->date] = $dailyCount->failed_count;
        }

        return $failedLoginData;
    }
    public function getblockedCountChart($dbName)
    {

        $month = date('m');
        $year  = date('Y');

        // $firstDayOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $last30Days  = Carbon::now()->subDays(30);
        $currentDate = Carbon::now()->endOfDay();

        $dailyBlockedCounts = DB::connection($dbName)
            ->table('cloud_sso_users')
            ->select(DB::raw('DATE(updated_at) as date'), DB::raw('count(*) as blocked_count'))
            ->where('hideuser', 1)
            ->whereBetween('updated_at', [$last30Days, $currentDate])
            ->groupBy(DB::raw('DATE(updated_at)'))
            ->orderBy('date', 'asc')
            ->get();

        $allDates = [];
        for ($date = $last30Days; $date <= $currentDate; $date->addDay()) {
            $allDates[] = $date->toDateString();
        }

        $blockedData = [];
        foreach ($allDates as $date) {
            $blockedData[$date] = 0;
        }

        foreach ($dailyBlockedCounts as $dailyCount) {
            $blockedData[$dailyCount->date] = $dailyCount->blocked_count;
        }
        return $blockedData;
    }
    public function getTaskCountChart($dbName)
    {

        $month = date('m');
        $year  = date('Y');

        // $firstDayOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $last30Days  = Carbon::now()->subDays(30);
        $currentDate = Carbon::now()->endOfDay();

        $dailyTaskCounts = DB::connection($dbName)
            ->table('cloud_crm_aktiviteter')
            ->select(DB::raw('DATE(FROM_UNIXTIME(oprettetDato)) as date'), DB::raw('count(*) as task_count'))
            ->whereBetween(DB::raw('FROM_UNIXTIME(oprettetDato)'), [$last30Days, $currentDate])
            ->groupBy(DB::raw('DATE(FROM_UNIXTIME(oprettetDato))'))
            ->orderBy('date', 'asc')
            ->get();

        $allDates = [];
        for ($date = $last30Days; $date <= $currentDate; $date->addDay()) {
            $allDates[] = $date->toDateString();
        }

        $taskData = [];
        foreach ($allDates as $date) {
            $taskData[$date] = 0;
        }

        foreach ($dailyTaskCounts as $dailyCount) {
            $taskData[$dailyCount->date] = $dailyCount->task_count;
        }

        return $taskData;
    }
    public function getSalesCountChart($dbName)
    {

        $month = date('m');
        $year  = date('Y');

        // $firstDayOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $last30Days  = Carbon::now()->subDays(30);
        $currentDate = Carbon::now()->endOfDay();

        $dailySalesCounts = DB::connection($dbName)
            ->table('cloud_crm_sager')
            ->select(DB::raw('DATE(FROM_UNIXTIME(startdato)) as date'), DB::raw('count(*) as sales_count'))
            ->whereBetween(DB::raw('FROM_UNIXTIME(startdato)'), [$last30Days, $currentDate])
            ->groupBy(DB::raw('DATE(FROM_UNIXTIME(startdato))'))
            ->orderBy('date', 'asc')
            ->get();

        $allDates = [];
        for ($date = $last30Days; $date <= $currentDate; $date->addDay()) {
            $allDates[] = $date->toDateString();
        }

        $salesData = [];
        foreach ($allDates as $date) {
            $salesData[$date] = 0;
        }

        foreach ($dailySalesCounts as $dailyCount) {
            $salesData[$dailyCount->date] = $dailyCount->sales_count;
        }

        return $salesData;
    }
    public function getCustomerCountChart($dbName)
    {

        $month = date('m');
        $year  = date('Y');

        // $firstDayOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $last30Days  = Carbon::now()->subDays(30);
        $currentDate = Carbon::now()->endOfDay();

        $dailyCustomersCounts = DB::connection($dbName)
            ->table('cloud_crm_firmainfo')
            ->select(DB::raw('DATE(createdAt) as date'), DB::raw('count(*) as customer_count'))
            ->whereBetween('createdAt', [$last30Days, $currentDate])
            ->groupBy(DB::raw('DATE(createdAt)'))
            ->orderBy('date', 'asc')
            ->get();

        $allDates = [];
        for ($date = $last30Days; $date <= $currentDate; $date->addDay()) {
            $allDates[] = $date->toDateString();
        }

        $customersData = [];
        foreach ($allDates as $date) {
            $customersData[$date] = 0;
        }

        foreach ($dailyCustomersCounts as $dailyCount) {
            $customersData[$dailyCount->date] = $dailyCount->customer_count;
        }

        return $customersData;
    }
    public function getProjectsCountChart($dbName)
    {

        $month = date('m');
        $year  = date('Y');

        // $firstDayOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $last30Days  = Carbon::now()->subDays(30);
        $currentDate = Carbon::now()->endOfDay();

        $dailyProjectsCounts = DB::connection($dbName)
            ->table('cloud_crm_leads')
            ->select(DB::raw('DATE(createdAt) as date'), DB::raw('count(*) as projects_count'))
            ->whereBetween('createdAt', [$last30Days, $currentDate])
            ->groupBy(DB::raw('DATE(createdAt)'))
            ->orderBy('date', 'asc')
            ->get();

        $allDates = [];
        for ($date = $last30Days; $date <= $currentDate; $date->addDay()) {
            $allDates[] = $date->toDateString();
        }

        $projectsData = [];
        foreach ($allDates as $date) {
            $projectsData[$date] = 0;
        }

        foreach ($dailyProjectsCounts as $dailyCount) {
            $projectsData[$dailyCount->date] = $dailyCount->projects_count;
        }

        return $projectsData;
    }
}
