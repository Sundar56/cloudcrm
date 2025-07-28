<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Traits\sendNotification;
use Illuminate\Support\Facades\DB;
use App\Api\Customer\Modules\CompanyLogin\Models\FailedLogin;
use App\Services\CompanyDatabaseService;

class AdminDashboardCount extends Command
{
    use sendNotification;

    protected $companyDatabaseService;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:admin-dashboard-count {company_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public function __construct(CompanyDatabaseService $companyDatabaseService)
    {
        parent::__construct();
        $this->companyDatabaseService = $companyDatabaseService;
    }
    /**
     * Execute the console command.
     */
    public function handle()
    {   
        // $dbName = 'comp_securenext';
        // $companyId = 7 ;
        $companyId  = $this->argument('company_id');
        $connection = $this->companyDatabaseService->connect($companyId);
        $dbName     = $connection['dbName'];
        // $totalEmployees = DB::connection($dbName)
        // ->table('cloud_sso_users')
        // ->count();

        // $totalSuccesLogins = DB::connection($dbName)
        // ->table('tbl_user_login_activity')
        // ->count();

        // $totalFailedLogin  = FailedLogin::on($dbName)->count();

        // $totalBlockedCount = DB::connection($dbName)
        // ->table('cloud_sso_users')
        // ->where('hideuser', 1)
        // ->count();
        
        // $countDetails = [
        //     'totalEmployees'    => $totalEmployees,
        //     'totalSuccesLogins' => $totalSuccesLogins,
        //     'totalFailedLogin'  => $totalFailedLogin,
        //     'totalBlockedCount' => $totalBlockedCount,
        // ];

        // $channel = 'admindashboardchannel';
        // $this->sendNotification($channel, [
        //     'adminDashboardcount' => $countDetails,
        //     'state'               => 0,
        //     'message'             => "Processnig For Admin Dashboard count",
        // ]);


        $globalCountDetails = $this->getGlobalCountDetails($dbName);
        $this->sendGlobalDashboardNotification($globalCountDetails);

        $adminCountDetails = $this->getAdminDashboardCountDetails($dbName);
        $this->sendAdminDashboardNotification($adminCountDetails);
    }

    private function getGlobalCountDetails($dbName)
    {
        $totalCustomers = DB::connection($dbName)
            ->table('cloud_crm_firmainfo')
            ->count();

        $totalProjects = DB::connection($dbName)
            ->table('cloud_crm_leads')
            ->count();

        $totalSales = DB::connection($dbName)
            ->table('cloud_crm_sager')
            ->count();

        $totalTasks = DB::connection($dbName)
            ->table('cloud_crm_aktiviteter')
            ->count();

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

        return [
            'totalCustomers'        => $totalCustomers,
            'totalProjects'         => $totalProjects,
            'totalSales'            => $totalSales,
            'totalTasks'            => $totalTasks,
            'criticalPriorityUsers' => $criticalPriorityUsers,
            'highPriorityUsers'     => $highPriorityUsers,
            'criticalCasesCount'    => $criticalCasesCount,
            'lowerCasesCount'       => $lowerCasesCount,
        ];
    }

    private function sendGlobalDashboardNotification($globalCountDetails)
    {
        $channel = 'globaldashboardchannel';
        $this->sendNotification($channel, [
            'globalCountDetails'  => $globalCountDetails,
            'state'               => 0,
            'message'             => "Processing For Global Dashboard count",
        ]);
    }

    private function getAdminDashboardCountDetails($dbName)
    {
        $totalEmployees = DB::connection($dbName)
            ->table('cloud_sso_users')
            ->count();

        $totalSuccesLogins = DB::connection($dbName)
            ->table('tbl_user_login_activity')
            ->count();

        $totalFailedLogin  = FailedLogin::on($dbName)->count();

        $totalBlockedCount = DB::connection($dbName)
            ->table('cloud_sso_users')
            ->where('hideuser', 1)
            ->count();

        return [
            'totalEmployees'    => $totalEmployees,
            'totalSuccesLogins' => $totalSuccesLogins,
            'totalFailedLogin'  => $totalFailedLogin,
            'totalBlockedCount' => $totalBlockedCount,
        ];
    }

    private function sendAdminDashboardNotification($countDetails)
    {
        $channel = 'admindashboardchannel';
        $this->sendNotification($channel, [
            'adminDashboardcount' => $countDetails,
            'state'               => 0,
            'message'             => "Processing For Admin Dashboard count",
        ]);
    }
}
