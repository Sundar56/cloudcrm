<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Api\Customer\Modules\Settings\Models\SettingOption;
use App\Api\Customer\Modules\Settings\Models\SettingModule;

class UpdateSettingOptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:setting-options {dbName}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update setting options and modules in the database';

    public function __construct()
    {
        parent::__construct();
    }
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dbName = $this->argument('dbName');
        $this->configureDatabaseConnection($dbName);

        if (!$this->testDatabaseConnection($dbName)) {
            return;
        }

        $this->updateSettingModules($dbName);
        $this->info('Setting Option details updated successfully!');
    }

    protected function configureDatabaseConnection($dbName)
    {
        config(["database.connections.$dbName" => [
            'driver'    => 'mysql',
            'host'      => env("DB_ROOT_HOST", '127.0.0.1'),
            'port'      => env('DB_ROOT_PORT', '3306'),
            'database'  => $dbName,
            'username'  => env("DB_ROOT_HOST") == 'localhost' ? 'root' : env("DB_ROOT_USERNAME"),
            'password'  => env("DB_ROOT_HOST") == 'localhost' ? null : env("DB_ROOT_PASSWORD"),
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
            'strict'    => true,
            'engine'    => null,
        ]]);
    }

    protected function testDatabaseConnection($dbName)
    {
        try {
            DB::connection($dbName)->getPdo();
            error_log('Successfully connected to the database: ' . $dbName);
            return true;
        } catch (\Exception $e) {
            error_log('MySQL connection failed: ' . $e->getMessage());
            return false;
        }
    }
    protected function updateSettingModules($dbName)
    {
        $settingModulesData = [
            ['settingname' => 'Mail', 'settingstatus' => 1,'session_timeout' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['settingname' => 'SMS', 'settingstatus' => 0,'session_timeout' => 0, 'created_at' => now(), 'updated_at' => now()],
        ];

        foreach ($settingModulesData as $module) {

            $settingModule   = SettingModule::on($dbName)->updateOrInsert(['settingname' => $module['settingname']], $module);
            $settingModuleId = SettingModule::on($dbName)->where('settingname', $module['settingname'])->first()->id;

            $this->updateSettingOptions($dbName, $settingModuleId, $module['settingname']);
            $this->updateSettingVariable($dbName, $settingModuleId, $module['settingname']);
        }
    }

    protected function updateSettingOptions($dbName, $settingModuleId, $settingName)
    {
        $settingOptionsData = [
            'Mail' => [
                ['optionname' => 'SMTPHost', 'optiontype' => 'text', 'label' => 'Host', 'optionrequired' => 1],
                ['optionname' => 'SMTPPort', 'optiontype' => 'text', 'label' => 'Gate', 'optionrequired' => 1],
                ['optionname' => 'SMTPUsername', 'optiontype' => 'text', 'label' => 'User Name', 'optionrequired' => 1],
                ['optionname' => 'SMTPPassword', 'optiontype' => 'text', 'label' => 'Password', 'optionrequired' => 1],
                ['optionname' => 'SMTPEncryption', 'optiontype' => 'dropdown', 'optionvalue' => 'SSL,TLS', 'label' => 'Encryption Type'],
                ['optionname' => 'DefaultEmail', 'optiontype' => 'text', 'label' => 'Default Email'],
                ['optionname' => 'SMTPAuthentication', 'optiontype' => 'checkbox', 'label' => 'Authentication'],
                ['optionname' => 'DefaultSystemSenderName', 'optiontype' => 'text', 'label' => 'Default System Sender Name'],
                ['optionname' => 'DefaultSystemSenderEmail', 'optiontype' => 'text', 'label' => 'Default System Sender Email'],
            ],
            'SMS' => [
                ['optionname' => 'SMSHost', 'optiontype' => 'text', 'label' => 'Host', 'optionrequired' => 1],
                ['optionname' => 'SMSPort', 'optiontype' => 'text', 'label' => 'Port', 'optionrequired' => 1],
                ['optionname' => 'SMSLogontype', 'optiontype' => 'dropdown', 'optionvalue' => 'Normal', 'label' => 'Logon Type'],
                ['optionname' => 'SMSUsername', 'optiontype' => 'text', 'label' => 'User Name', 'optionrequired' => 1],
                ['optionname' => 'SMSPassword', 'optiontype' => 'text', 'label' => 'Password', 'optionrequired' => 1],
            ],
        ];

        $options = $settingOptionsData[$settingName];

        foreach ($options as $option) {
            $option['setting_module_id'] = $settingModuleId;
            SettingOption::on($dbName)->updateOrInsert(['optionname' => $option['optionname']], $option);
        }
    }


    protected function updateSettingVariable($dbName, $settingModuleId, $settingName)
    {
        $settingVariables = [
            'Mail' => [
                'SMTPHost', 'SMTPPort', 'SMTPUsername', 'SMTPPassword', 'SMTPEncryption',
                'DefaultEmail', 'SMTPAuthentication', 'DefaultSystemSenderName', 'DefaultSystemSenderEmail',
            ],
            'SMS' => [
                'SMSHost', 'SMSPort', 'SMSLogontype', 'SMSUsername', 'SMSPassword',
            ],
        ];

        $variables = $settingVariables[$settingName];


        foreach ($variables as $variable) {
            DB::connection($dbName)
            ->table('cloud_variabler')
            ->updateOrInsert(
                ['variabel' => $variable],
                [
                    'variabel'          => $variable,
                    'vaerdi'            => '',
                    'beskrivelse'       => '',
                    'setting_module_id' => $settingModuleId,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ],
            );
        }
    }
}

