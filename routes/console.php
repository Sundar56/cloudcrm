<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\CompanyDatabaseMigration;
use App\Console\Commands\AdminDashboardCount;
use Illuminate\Support\Facades\Schedule;
use App\Api\Systemadmin\Modules\Company\Models\Company;


// Artisan::command('inspire', function () {
//     $this->comment(Inspiring::quote());
// })->purpose('Display an inspiring quote')->everyFiveMinutes();

// Artisan::command('app:company-database-migration', function () {
//     $this->comment('Company database migration command');
// })->purpose('Company database migration')->everyMinute();

Schedule::command('app:company-database-migration')->everyMinute();
// Schedule::command('app:admin-dashboard-count')->everyTenSeconds();

Schedule::call(function () {
    $companies = Company::all(); 

    foreach ($companies as $company) {
        Artisan::call('app:admin-dashboard-count', [
            'company_id' => $company->id,
        ]);
    }
})->everyTenSeconds();