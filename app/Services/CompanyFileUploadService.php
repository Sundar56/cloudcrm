<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Api\Systemadmin\Modules\Company\Models\Company;
use App\Api\Systemadmin\Modules\Company\Models\Userimage;


class CompanyFileUploadService
{
    public function handleFileUploads(Request $request, $companyId)
    {
        $path = "/uploadassets/company/{$companyId}/";

        // Ensure the directory exists
        if (!File::exists(public_path($path))) {
            File::makeDirectory(public_path($path), 0775, true);
        }

        // Handle company logo upload
        if ($request->hasFile('company_logo')) {
            $companyLogoPath = $this->uploadFile($request->file('company_logo'), $path, 'logo_');
            Company::where('id', $companyId)->update(['company_logo' => $companyLogoPath]);
        }

        // Handle company banner upload
        if ($request->hasFile('company_banner')) {
            $companyBannerPath = $this->uploadFile($request->file('company_banner'), $path, 'banner_');
            Company::where('id', $companyId)->update(['company_banner' => $companyBannerPath]);
        }
    }

    private function uploadFile($file, $path, $prefix)
    {
        $fileName = $prefix . time() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path($path), $fileName);
        return $path . $fileName;
    }

    // public function userImageFileUpload(Request $request, $companyId, $userId)
    // {
    //     $path = "/uploadassets/company/{$companyId}/users/{$userId}/";
    //     // Ensure the directory exists
    //     if (!File::exists(public_path($path))) {
    //         File::makeDirectory(public_path($path), 0775, true);
    //     }

    //     if ($request->hasFile('user_image')) {
    //         $userImagePath = $this->uploadFile($request->file('user_image'), $path, 'logo_');
    //         Userimage::where('company_id', $companyId)
    //             ->where('user_id', $userId)->update(['local_imagepath' => $userImagePath]);
    //     }
    // }
    public function userImageFileUpload(Request $request, $companyId, $userId, $dbName)
    {
        $path = "/uploadassets/company/{$companyId}/users/{$userId}/";
        // Ensure the directory exists
        if (!File::exists(public_path($path))) {
            File::makeDirectory(public_path($path), 0775, true);
        }
        if ($request->hasFile('user_image')) {
            $userImagePath = $this->uploadFile($request->file('user_image'), $path, 'image_');
            DB::connection($dbName)
                ->table('cloud_sso_users')
                ->where('id', $userId)
                ->update([
                    'user_image' => $userImagePath,
                ]);
        }
    }
}
