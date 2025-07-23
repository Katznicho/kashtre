<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BranchCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $branch;
    public $company;

    /**
     * Create a new message instance.
     */
    public function __construct($branch, $company)
    {
        $this->branch = $branch;
        $this->company = $company;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Welcome to ' . config('app.name') . ' - Branch Created')
                    ->to($this->branch->email)
                    ->cc($this->company->email)
                    ->markdown('emails.branch_created')
                    ->with([
                        'branch' => $this->branch,
                        'company' => $this->company,
                    ]);
    }
}
