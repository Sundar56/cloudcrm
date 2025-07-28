<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Api\Systemadmin\Modules\Company\Models\CompanyDatabase;
use Illuminate\Http\Request;

class CompanyDatabaseService
{



    /**
     * Configure the database connection dynamically.
     *
     * @param object $dbDetails
     * @return void
     */
    public function configureDatabaseConnection($dbDetails)
    {
        config([
            "database.connections.$dbDetails->db_name" => [
                'driver' => 'mysql',
                'host' => env('DB_ROOT_HOST', '127.0.0.1'),
                'port' => env('DB_ROOT_PORT', '3306'),
                'database' => $dbDetails->db_name,
                'username' => $dbDetails->dbuser_name,
                'password' => $dbDetails->db_password,
                'charset' => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix' => '',
                'strict' => true,
                'engine' => null,
            ]
        ]);
        error_log("Database configuration set for: {$dbDetails->db_name}");
    }

    /**
     * Test if the connection to the database is successful.
     *
     * @param string $dbName
     * @return bool
     */
    public function databaseConnection($dbName)
    {
        try {
            DB::connection($dbName)->getPdo();
            error_log("Successfully connected to the database: " . $dbName);
            return true;
        } catch (\Exception $e) {
            error_log("MySQL connection failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get database details for the given company ID.
     */
    public function getDatabaseDetails($company_id)
    {
        return CompanyDatabase::where('company_id', $company_id)->first();
    }

     /**
     * Connect company the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Array
     *
     */
    public function connect($companyId)
    {

        $dbDetails = $this->getDatabaseDetails($companyId);

        if (!$dbDetails) {
            return [
                'status'     => false,
                'message'    => 'Validation Error',
                'errors'     => ["error" => array("Database details not found.")],
                'statusCode' => 422
            ];
        }

        $this->configureDatabaseConnection($dbDetails);

        if (!$this->databaseConnection($dbDetails->db_name)) {
            return [
                'status'     => false,
                'message'    => 'Server Error',
                'errors'     => ["error" => ["MySQL connection failed."]],
                'statusCode' => 500,
            ];
        }

        return [
            'status'     => true,
            'dbName'  => $dbDetails->db_name,
        ];
    }
}
