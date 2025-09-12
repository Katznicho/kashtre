<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use App\Models\Invoice;
use App\Models\Client;
use App\Models\Business;
use Illuminate\Support\Facades\Log;

class ClientReceipt extends Mailable
{
    use Queueable, SerializesModels;

    public $invoice;
    public $client;
    public $business;
    public $pdfPath;

    public function __construct(Invoice $invoice, $pdfPath = null)
    {
        $this->invoice = $invoice;
        $this->client = $invoice->client;
        $this->business = $invoice->business;
        $this->pdfPath = $pdfPath;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payment Receipt - Invoice #' . $this->invoice->invoice_number . ' - ' . ($this->business->name ?? 'Business'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.receipts.client',
            with: [
                'invoice' => $this->invoice,
                'client' => $this->client,
                'business' => $this->business,
            ],
        );
    }

    public function attachments(): array
    {
        $attachments = [];
        
        if ($this->pdfPath && file_exists($this->pdfPath)) {
            $attachments[] = Attachment::fromPath($this->pdfPath)
                ->as('Receipt_' . $this->invoice->invoice_number . '.pdf')
                ->withMime('application/pdf');
        }
        
        return $attachments;
    }
}
