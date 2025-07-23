<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewBusinessCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    // public $business;
    public $business_name;
    public $account_number;
    public $email;
    public $phone;


    /**
     * Create a new message instance.
     */
    public function __construct($business)
    {
        // $this->business = $business;
        $this->business_name = $business->name;
        $this->account_number = $business->account_number;
        $this->email = $business->email;
        $this->phone = $business->phone;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Welcome to ' . config('app.name') . '!')
                    ->markdown('emails.business_created')
                    ->with([
                        // 'business' => $this->business,
                        'business_name' => $this->business_name,
                        'account_number' => $this->account_number,
                        'email' => $this->email,
                        'phone' => $this->phone,
                    ]);
    }
}
