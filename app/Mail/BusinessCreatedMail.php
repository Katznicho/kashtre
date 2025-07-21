<?php

namespace App\Mail;
use App\Models\Business;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BusinessCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $business;

    /**
     * Create a new message instance.
     */
    public function __construct($business)
    {
        if (!$business) {
            throw new \InvalidArgumentException('Business cannot be null');
        }

        $this->business = $business;

        // Add debug information to the business object
        $this->business->debug = [
            'initial' => $initial,
            'final' => [
                'id' => $this->business->id ?? 'none',
                'email' => $this->business->email ?? 'none',
                'account_number' => $this->business->account_number ?? 'none'
            ]
        ];
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to ' . config('app.name') . ' - Your Business Account is Ready',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        \Log::info('BusinessCreatedMail rendering content with:', [
            'business_id' => $this->business->id ?? 'none',
            'email' => $this->business->email ?? 'none',
            'account_number' => $this->business->account_number ?? 'none'
        ]);
        
        return new Content(
            view: 'emails.business_created',
            with: [
                'business' => $this->business,
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
