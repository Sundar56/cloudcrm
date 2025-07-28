<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Mail\TestConfigMail;
use Illuminate\Support\Facades\Mail;
use App\Services\CompanyDatabaseService;
use Illuminate\Support\Facades\DB;
use App\Api\Systemadmin\Modules\Company\Models\Company;
use App\Traits\sendNotification;

class SmtpConfigJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,sendNotification;

    protected $email;
    protected $companyId;
    protected $companyName;
    protected $companyDatabaseService;

    /**
     * Create a new job instance.
     */
    public function __construct($email,$companyId,$companyName,CompanyDatabaseService $companyDatabaseService = null)
    {
        $this->email                  = $email;
        $this->companyId              = $companyId;
        $this->companyName            = $companyName;
        $this->companyDatabaseService = $companyDatabaseService;
    }
    /**
     * Set dynamic email configuration based on portal type.
     */
    public function setDynamicMailConfig()
    {
        $companyId    = $this->companyId;
        $smtpSettings = $this->getSmtpSettings($companyId);

        if (!$smtpSettings || empty($smtpSettings)) {
                return [
                    'status'     => false,
                    'message'    => 'SMTP settings not found.',
                    'errors'     => ["error" => array("SMTP settings not found.")],
                    'statusCode' => 401,
            ];
        }
        config([
                'mail.mailers.smtp.host'       => $smtpSettings['SMTPHost'],
                'mail.mailers.smtp.port'       => $smtpSettings['SMTPPort'],
                'mail.mailers.smtp.username'   => $smtpSettings['SMTPUsername'],
                'mail.mailers.smtp.password'   => $smtpSettings['SMTPPassword'],
                'mail.mailers.smtp.encryption' => $smtpSettings['SMTPEncryption'],
                'mail.from.address'            => $smtpSettings['DefaultSystemSenderEmail'],
                'mail.from.name'               => $smtpSettings['DefaultSystemSenderName'],
        ]);
    }
    public function getSmtpSettings($companyId)
    {
       $connection = $this->companyDatabaseService->connect($this->companyId);

       if (!$connection['status']) {
        return [
            'status'     => false,
            'message'    => $connection['message'],
            'errors'     => $connection['errors'],
            'statusCode' => $connection['statusCode']
          ];
       }
    $dbname  = $connection['dbName'];
    $mapping = [
        'SMTPHost',
        'SMTPPort',
        'SMTPUsername',
        'SMTPPassword',
        'SMTPEncryption',
        'DefaultEmail',
        'SMTPAuthentication',
        'DefaultSystemSenderName',
        'DefaultSystemSenderEmail',
    ];

    $smtpSettings = [];
    foreach ($mapping as $key) {
        $setting = DB::connection($dbname)
        ->table('cloud_variabler')
        ->where('variabel', $key)
        ->first();

        if ($setting) {
            $smtpSettings[$key] = $setting->vaerdi; 
        } else {
            $smtpSettings[$key] = null; 
        }
    }
        return $smtpSettings;
    }
    /**
     * Execute the job.
     */
    public function handle(): void
    {
       try { 
            $this->setDynamicMailConfig();
            $email = new TestConfigMail($this->companyName);
            Mail::to($this->email)->send($email);

            $company = Company::where('id',$this->companyId)->first();
            if ($company) {
                $company->mail_config = 1;
                $company->save();
            }
            $channel = 'mailconfigchannel';
            $this->sendNotification($channel, [
            'config'  => 1,
            'state'   => 0,
            'message' => "Mail configured with this Smtp credentials",
        ]);
        } catch (\Exception $e) {
            $company = Company::where('id',$this->companyId)->first();
            if ($company) {
                $company->mail_config = 0;
                $company->save();
            }
            $channel = 'mailconfigchannel';
            $this->sendNotification($channel, [
            'config'  => 0,
            'state'   => 0,
            'message' => "Mail not Configured with this Smtp credentials",
        ]);
        }
    }
}
