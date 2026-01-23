<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail as MailFacade;

class InsuranceCompanyPasswordReset extends Mailable
{
    use Queueable, SerializesModels;

    public $userEmail;
    public $userName;
    public $username;
    public $resetUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(string $userEmail, string $userName, string $username, string $resetUrl)
    {
        $this->userEmail = $userEmail;
        $this->userName = $userName;
        $this->username = $username;
        $this->resetUrl = $resetUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Set Your Password - Third-Party System Access',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.insurance-company-password-reset',
            with: [
                'userName' => $this->userName,
                'username' => $this->username,
                'resetUrl' => $this->resetUrl,
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

    /**
     * Send the email
     */
    public static function sendEmail(string $userEmail, string $userName, string $username, string $resetUrl): void
    {
        try {
            $mailable = new self($userEmail, $userName, $username, $resetUrl);
            MailFacade::to($userEmail)->send($mailable);
            
            \Illuminate\Support\Facades\Log::info('Email sent via Mail facade', [
                'to' => $userEmail,
                'mailer' => config('mail.default'),
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error sending email', [
                'to' => $userEmail,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
