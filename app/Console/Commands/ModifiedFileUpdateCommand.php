<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Api\Systemadmin\Modules\Company\Models\Company;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;


class ModifiedFileUpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:modified-file-update-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update modified files based on lastfile_updated_at date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get all companies and their lastfile_updated_at dates
        $companies = Company::all();
        foreach ($companies as $company) {
            // Get the company lastfile_updated_at and format it as a Carbon instance
            $lastFileUpdatedAt = Carbon::parse($company->lastfile_updated_at);
            error_log("Checking company: " . $company->company_name . " | Last file updated at: " . $lastFileUpdatedAt);

            // Define the company folder path
            $companyName = $company->company_name;
            $folderPath = env("COMPANY_FOLDER_CREATION") . DIRECTORY_SEPARATOR . $companyName;
            error_log("Company Folder: " . $folderPath);

            // Define source path for files to be checked and moved
            $sourcePath = env("COMPANY_FOLDER_SOURCE_PATH");

            if (!file_exists($folderPath)) {
                error_log("Directory does not exist: " . $folderPath);
                continue;
            }

            $modifiedFiles = $this->checkAndMoveModifiedFiles($sourcePath, $folderPath, $lastFileUpdatedAt);

            // If any files were moved, update the lastfile_updated_at in the company table
            if ($modifiedFiles) {
                $company->lastfile_updated_at = now();

                $company->save();
                error_log("Updated lastfile_updated_at for company: " . $company->company_name);
            }
        }
        $this->info('Files and Folder updated and company table updated.');
    }

    /**
     * Check and move modified files based on the lastfile_updated_at timestamp.
     *
     * @param string $sourcePath
     * @param string $destinationPath
     * @param \Carbon\Carbon $lastFileUpdatedAt
     * @return bool
     */
    public function checkAndMoveModifiedFiles($sourcePath, $destinationPath, $lastFileUpdatedAt)
    {
        $modifiedFiles = false;

        // Get all files and directories in the source path
        $items = scandir($sourcePath);

        foreach ($items as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            $sourceItemPath = $sourcePath . DIRECTORY_SEPARATOR . $item;
            $destinationItemPath = $destinationPath . DIRECTORY_SEPARATOR . $item;

            // Get the modification time of the source item
            $fileModifiedTime = filemtime($sourceItemPath);
            $fileModifiedDate = Carbon::createFromTimestamp($fileModifiedTime);

            // If the file is modified after lastfile_updated_at, move it
            if ($fileModifiedDate->gt($lastFileUpdatedAt)) {
                error_log("File modified: " . $sourceItemPath . " | Modified date: " . $fileModifiedDate);

                $this->moveItem($sourceItemPath, $destinationItemPath);
                $modifiedFiles = true; // Mark that a file was modified and moved
            }
        }
        return $modifiedFiles;
    }


    /**
     * Move the file or directory from source to destination.
     *
     * @param string $source
     * @param string $destination
     */
    public function moveItem($source, $destination)
    {
        try {
            if (is_dir($source)) {
                if (!file_exists($destination)) {
                    mkdir($destination, 0755, true);
                    error_log("Created directory: " . $destination);
                }
                // Recursively copy the directory and its contents
                $this->copyDirectory($source, $destination);
            } else {
                // If it's a file, simply move the file to the destination
                rename($source, $destination);
                error_log("File moved: " . $source . " to " . $destination);
            }
        } catch (\Exception $e) {
            error_log("Failed to move item: " . $e->getMessage());
        }
    }

    /**
     * Recursively copy the contents of a directory.
     *
     * @param string $source
     * @param string $destination
     */
    private function copyDirectory($source, $destination)
    {
        $items = scandir($source);

        foreach ($items as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }
            $sourceItemPath = $source . DIRECTORY_SEPARATOR . $item;
            $destinationItemPath = $destination . DIRECTORY_SEPARATOR . $item;
            if (is_dir($sourceItemPath)) {
                // Recursively copy directories
                if (!file_exists($destinationItemPath)) {
                    mkdir($destinationItemPath, 0755, true);
                }
                $this->copyDirectory($sourceItemPath, $destinationItemPath);
            } else {
                copy($sourceItemPath, $destinationItemPath);
            }
        }
    }
}

//php artisan app:modified-file-update-command
