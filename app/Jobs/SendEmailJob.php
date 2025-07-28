<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Mail\ResetPasswordMail;
use Illuminate\Support\Facades\Mail;
use App\Services\CompanyDatabaseService;
use Illuminate\Support\Facades\DB;


class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $email;
    protected $password;
    protected $username;
    protected $subject;
    protected $portalType;
    protected $companyLogo;
    protected $companyId;
    protected $companyDatabaseService;
    protected $loginUrl;

    /**
     * Create a new job instance.
     */
    public function __construct($email, $password, $username, $subject,$portalType, $companyLogo = null, $companyId = null,
       CompanyDatabaseService $companyDatabaseService = null, $loginUrl = null)
    {

        $this->email       = $email;
        $this->password    = $password;
        $this->username    = $username;
        $this->subject     = $subject;
        $this->portalType  = $portalType;
        $this->companyLogo = $companyLogo;
        $this->companyId   = $companyId;
        $this->companyDatabaseService = $companyDatabaseService;
        $this->loginUrl   = $loginUrl;
    }
    /**
     * Set dynamic email configuration based on portal type.
     */
    public function setDynamicMailConfig()
    {
        // If portalType is 'customer', load dynamic email settings
        if ($this->portalType == 'customer') {
            $companyId = $this->companyId;
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
    }
    public function getSmtpSettings($companyId)
    {
    // Connect to the company database

       $connection = $this->companyDatabaseService->connect($companyId);

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
        $this->setDynamicMailConfig();
        $email = new ResetPasswordMail($this->password, $this->username, $this->subject,$this->portalType, $this->companyLogo,$this->companyId,$this->loginUrl);
        Mail::to($this->email)->send($email);
        // Mail::to($this->email)->send(new ResetPasswordMail($this->password));
    }
}
