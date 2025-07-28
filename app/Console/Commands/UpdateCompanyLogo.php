<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class UpdateCompanyLogo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-company-logo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update company logo';
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $companyId = $this->ask('Enter the company ID');
        $company = DB::table('companies')->find($companyId);

        if (!$company) {
            $this->error("Company not found.");
            return self::FAILURE;
        }

        if (empty($company->company_logo)) {
            $this->error("Logo path is empty for company ID: $companyId");
            return self::FAILURE;
        }
        $logoPath = $company->company_logo;

        if (str_starts_with($logoPath, '/assets/')) {
            $fullLogoPath = public_path(ltrim($logoPath, '/'));
        } elseif (str_starts_with($logoPath, '/uploadassets/')) {
            $fullLogoPath = public_path($logoPath);
        } else {
            $this->error("Unsupported logo path format: $logoPath");
            return self::FAILURE;
        }
        $this->info("Resolved logo path: $fullLogoPath");

        if (!File::exists($fullLogoPath)) {
            $this->error("Logo file does not exist: $fullLogoPath");
            return self::FAILURE;
        }
        $filename = basename($fullLogoPath);
        $baseFolder = env('COMPANY_FOLDER_CREATION');
        $companyFolder = 'cloudcrm';
        // $companyFolder = strtolower($company->companyname);
        $destinationMap = [
            'cloud-router-profile/images' => 'logo.png',
            'cloudcrm/assets/img' => 'logoLille.png',
        ];

        foreach ($destinationMap as $relativePath => $newFileName) {
            $destinationFolder = "$baseFolder/$companyFolder/$relativePath";
            File::ensureDirectoryExists($destinationFolder);

            $destinationPath = "$destinationFolder/$newFileName";

            if (File::exists($destinationPath)) {
                File::delete($destinationPath);
            }

            if (copy($fullLogoPath, $destinationPath)) {
                $this->info("Logo copied as '$newFileName' to: $destinationFolder");
            } else {
                $this->warn("Failed to copy to: $destinationFolder/$newFileName");
            }
        }
        return self::SUCCESS;
    }
}
