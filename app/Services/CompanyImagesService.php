<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Services\CompanyDatabaseService;
use Illuminate\Support\Facades\Validator;
use App\Api\Systemadmin\Modules\Company\Models\Company;
use App\Api\Systemadmin\Modules\Company\Models\SsoSettings;
use App\Api\Systemadmin\Modules\Company\Models\CompanyDatabase;
use App\Services\DataSecurityService;

class CompanyImagesService
{
    protected $companyDatabaseService;
    protected $DataSecurityService;

    public function __construct(CompanyDatabaseService $companyDatabaseService, DataSecurityService $DataSecurityService)
    {
        $this->companyDatabaseService = $companyDatabaseService;
        $this->DataSecurityService =  $DataSecurityService;
    }
    public function companyImages(Request $request)
    {
        try 
        {
            $validator = Validator::make($request->all(), [
                'domain_name'    => 'required',
            ], [
                'domain_name.required'    => 'Domain Name is required',
            ]);
            if ($validator->fails()) {
                return [
                    'status'     => false,
                    'message'    => 'Validation Error',
                    'errors'     => $validator->errors(),
                    'statusCode' => 422
                ];
            }
            $domainName    = $request->domain_name;
            $companyImages = Company::select('company_banner', 'company_logo', 'company_name', 'description','id')
            ->where('domain_name', $domainName)
            ->first();
            if (!$companyImages) {
                return [
                    'status'     => false,
                    'message'    => 'Company not found.',
                    'errors'     => ["error" => array("Company details not found.")],
                    'statusCode' => 404,
                ];
            }

            $socialLoginAccess = SsoSettings::select('microsoft_login', 'google_login','footer_setting')
            ->where('company_id', $companyImages->id)
            ->first();
           

            $data = array_merge($companyImages->toArray(), $socialLoginAccess ? $socialLoginAccess->toArray() : []); 
            if(!$socialLoginAccess){
                $data['microsoft_login'] = 0; 
                $data['google_login']    = 0;
                $data['footer_setting']  = 0;
            }             
            
            $dbDetails = $this->getDatabaseDetailsAndValidate($companyImages->id);
            $dbName    = $dbDetails['db_name'];

            $googleAccess = $this->checkAppExisting($dbName, 'google');
            if ($googleAccess) {
                $googleCredentials = DB::connection($dbName)->table('tbl_app_variabler')
                ->select('appvalue')
                ->where('tbl_appmodule_id', $googleAccess->id)
                ->first();
                if ($googleCredentials) {
                    $data['googleClientId'] = $googleCredentials->appvalue;
                } else {
                    $data['googleClientId'] = ''; 
                }
            } else {
                $data['googleClientId'] = '';
            }

            $microsoftAccess = $this->checkAppExisting($dbName, 'microsoft');
            if ($microsoftAccess) {
                $microsoftCredentials = DB::connection($dbName)->table('tbl_app_variabler')
                ->select('appvalue')
                ->where('tbl_appmodule_id', $microsoftAccess->id)
                ->get();

                if ($microsoftCredentials->count() > 0) {
                    $data['microsoftClientId']    = $microsoftCredentials[0]->appvalue ?? '';
                    $data['microsoftSecretId']    = $microsoftCredentials[1]->appvalue ?? '';
                    $data['microsoftRedirectUrl'] = $microsoftCredentials[2]->appvalue ?? '';
                    $data['microsoftIntegrateRedirectUrl'] = $microsoftCredentials[3]->appvalue ?? '';
                } else {
                    $data['microsoftClientId']    = '';
                    $data['microsoftSecretId']    = '';
                    $data['microsoftRedirectUrl'] = '';
                    $data['microsoftIntegrateRedirectUrl'] = '';
                }
            } else {
                $data['microsoftClientId']    = '';
                $data['microsoftSecretId']    = '';
                $data['microsoftRedirectUrl'] = '';
                $data['microsoftIntegrateRedirectUrl'] = '';
            }

            // $bannerImgaeColor         = $this->bannerImageColor($companyImages->company_banner);
            // $data['bannerImageColor'] = $bannerImgaeColor ?? "Light";

            return [
                'status'     => true,
                'data'       =>  $this->DataSecurityService->encrypt($data),
                // 'data'       =>  $data,
                'message'    => 'Company Banner and Logo',
                'statusCode' => 200,
            ];
        } catch (\Exception $e)
        {
            return [
                'status'     => false,
                'message'    => 'An error occurred.',
                'errors'     => ['error' => $e->getMessage()],
                'statusCode' => 500,
            ];
        }
    }
    public function checkAppExisting($dbName,$provider)
    {
        return DB::connection($dbName)->table('tbl_appmodules')->where([['appname', $provider], ['appstatus', 1]])->first();
    }
    public function getDatabaseDetailsAndValidate($company_id)
    {
        $dbDetails = $this->companyDatabaseService->getDatabaseDetails($company_id);
        if (!$dbDetails) {
            return [
                'status'     => false,
                'message'    => 'Validation Error',
                'errors'     => ["error" => ["Database details not found."]],
                'statusCode' => 422
            ];
        }

        $this->companyDatabaseService->configureDatabaseConnection($dbDetails);
        if (!$this->companyDatabaseService->databaseConnection($dbDetails->db_name)) {
            return [
                'status'     => false,
                'message'    => 'Server Error',
                'errors'     => ["error" => ["MySQL connection failed."]],
                'statusCode' => 500
            ];
        }
     return ['status' => true, 'db_name' => $dbDetails->db_name];
    }
    public function bannerImageColor($bannerImage, $sampleSize = 100)
    {
        $imagePath = public_path($bannerImage); 
        if (!File::exists($imagePath)) {
            return false; 
            return [
                'status'     => false,
                'message'    => 'File Not Exist',
                'errors'     => ["error" => array("File Not Exist")],
                'statusCode' => 400
            ];
        }
        // Get image dimensions and type
        $imageInfo = getimagesize($imagePath);
          // dd($imageInfo);
        $width     = $imageInfo[0];
        $height    = $imageInfo[1];
        $imageType = $imageInfo[2];

        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $image = imagecreatefromjpeg($imagePath);
                break;
            case IMAGETYPE_WEBP: 
                $image = imagecreatefromwebp($imagePath);
                break;
            case IMAGETYPE_PNG:
                $image = imagecreatefrompng($imagePath);
                break;       
            default:
                return false; 
        }
        // dd($imagePath);
        $totalBrightness = 0;
        $pixelCount = 0;

        for ($i = 0; $i < $sampleSize; $i++) {
            $x = rand(0, $width - 1);
            $y = rand(0, $height - 1);

            $rgb = imagecolorat($image, $x, $y);
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;

            // Calculate brightness using the perceived luminance formula
            $brightness = ($r * 0.2126 + $g * 0.7152 + $b * 0.0722);
            $totalBrightness += $brightness;
            $pixelCount++;
        }
        // dd($totalBrightness);
        // Average brightness
        $averageBrightness = $totalBrightness / $pixelCount;
         // dd($averageBrightness);

        // Free memory
        imagedestroy($image);

        // Define a threshold (128 is middle of 0-255)
        // return $averageBrightness < 128 ? "Dark" : "Light";
        if ($averageBrightness < 40) {
            return "Very Dark";
        } elseif ($averageBrightness < 80) {
            return "Dark"; 
        } elseif ($averageBrightness < 120) {
            return "Medium Dark"; 
        } elseif ($averageBrightness < 160) {
            return "Medium Light"; 
        } elseif ($averageBrightness < 200) {
            return "Light";
        } else {
            return "Very Light";
        }
    }

}
