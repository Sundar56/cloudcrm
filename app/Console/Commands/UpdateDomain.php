<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\CompanyDatabaseService;

class UpdateDomain extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-domain';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update domain values for a given company';
    protected $companyDatabaseService;

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
        $companyId = $this->ask('Enter the company ID');

        $dbDetails = $this->companyDatabaseService->getDatabaseDetails($companyId);
        if (!$dbDetails) {
            $this->error("Database details not found for company ID: $companyId");
            return;
        }
        $this->companyDatabaseService->configureDatabaseConnection($dbDetails);

        if (!$this->companyDatabaseService->databaseConnection($dbDetails->db_name)) {
            $this->error('Failed to connect to database: ' . $dbDetails->db_name);
            return;
        }

        $databaseName = $dbDetails->db_name;
        $this->info("Using database: {$databaseName}");

        $oldDomain = $this->ask('Enter the old domain (e.g., old.domain.com)');
        $newDomain = $this->ask('Enter the new domain (e.g., new.domain.com)');

        $this->updateDomain('san_shop_indstillinger', ['funktion1', 'funktion2', 'funktion3', 'funktion4'], $oldDomain, $newDomain, $databaseName);
        $this->updateDomain('san_shop_news', ['picture_url_1', 'picture_url_2', 'picture_url_3'], $oldDomain, $newDomain, $databaseName);
        $this->updateDomain('wlanshop_pages', ['html', 'css'], $oldDomain, $newDomain, $databaseName);

        $this->info("Domain update completed.");
        return self::SUCCESS;
    }
    protected function updateDomain(string $table, array $columns, string $oldDomain, string $newDomain, string $databaseName): void
    {
        foreach ($columns as $column) {
            $sql = "
            UPDATE `$table`
            SET `$column` = REPLACE(`$column`, ?, ?)
            WHERE `$column` LIKE ?
        ";

            $affected = DB::connection($databaseName)->statement($sql, [
                $oldDomain,
                $newDomain,
                '%' . $oldDomain . '%'
            ]);
            $this->info("Updated $affected row(s) in $table.$column");
        }
    }
}
