<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $password;
    public $username;
    public $subject;
    public $portalType;
    public $companyLogo;
    public $companyId;
    public $loginUrl;

    /**
     * Create a new message instance.
     */
    public function __construct($password, $username, $subject,$portalType,$companyLogo,$companyId,$loginUrl)
    {
        $this->password    = $password;
        $this->username    = $username;
        $this->subject     = $subject;
        $this->portalType  = $portalType;
        $this->companyLogo = $companyLogo;
        $this->companyId   = $companyId;
        $this->loginUrl    = $loginUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'auth.reset_password_email',
            with: [
                'password'    => $this->password,
                'username'    => $this->username,
                'portalType'  => $this->portalType,
                'companyLogo' => $this->companyLogo,
                'companyId'   => $this->companyId,
                'loginUrl'    => $this->loginUrl,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
