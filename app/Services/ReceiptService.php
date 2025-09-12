<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Business;
use App\Mail\ClientReceipt;
use App\Mail\BusinessReceipt;
use App\Mail\KashTreReceipt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class ReceiptService
{
    /**
     * Send electronic receipts to all parties upon payment clearance
     */
    public function sendElectronicReceipts(Invoice $invoice)
    {
        try {
            Log::info("=== SENDING ELECTRONIC RECEIPTS ===", [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'client_id' => $invoice->client_id,
                'business_id' => $invoice->business_id
            ]);

            // Generate PDF receipt
            $pdfPath = $this->generateReceiptPDF($invoice);
            
            // Get KashTre super business (ID = 1)
            $kashtreBusiness = Business::find(1);
            $chargeAmount = $invoice->service_charge; // Use the actual service charge from the invoice

            // Send receipts to all parties
            $this->sendClientReceipt($invoice, $pdfPath);
            $this->sendBusinessReceipt($invoice, $pdfPath);
            $this->sendKashTreReceipt($invoice, $chargeAmount, $pdfPath, $kashtreBusiness);

            Log::info("=== ELECTRONIC RECEIPTS SENT SUCCESSFULLY ===", [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'pdf_generated' => $pdfPath ? 'yes' : 'no'
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to send electronic receipts", [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return false;
        }
    }

    /**
     * Generate PDF receipt for the invoice
     */
    private function generateReceiptPDF(Invoice $invoice)
    {
        try {
            // Generate PDF using the existing invoice print view
            $pdf = Pdf::loadView('invoices.print', compact('invoice'));
            
            // Create filename
            $filename = 'receipt_' . $invoice->invoice_number . '_' . time() . '.pdf';
            $pdfPath = storage_path('app/receipts/' . $filename);
            
            // Ensure directory exists
            if (!file_exists(dirname($pdfPath))) {
                mkdir(dirname($pdfPath), 0755, true);
            }
            
            // Save PDF
            $pdf->save($pdfPath);
            
            Log::info("PDF receipt generated", [
                'invoice_id' => $invoice->id,
                'pdf_path' => $pdfPath,
                'filename' => $filename
            ]);
            
            return $pdfPath;

        } catch (\Exception $e) {
            Log::error("Failed to generate PDF receipt", [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }

    /**
     * Send receipt to client
     */
    private function sendClientReceipt(Invoice $invoice, $pdfPath = null)
    {
        try {
            if (!$invoice->client->email) {
                Log::warning("Client email not found, skipping client receipt", [
                    'invoice_id' => $invoice->id,
                    'client_id' => $invoice->client_id,
                    'client_name' => $invoice->client->name
                ]);
                return;
            }

            Mail::to($invoice->client->email)->send(new ClientReceipt($invoice, $pdfPath));
            
            Log::info("Client receipt sent successfully", [
                'invoice_id' => $invoice->id,
                'client_email' => $invoice->client->email,
                'client_name' => $invoice->client->name
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to send client receipt", [
                'invoice_id' => $invoice->id,
                'client_email' => $invoice->client->email,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send receipt to business
     */
    private function sendBusinessReceipt(Invoice $invoice, $pdfPath = null)
    {
        try {
            if (!$invoice->business->email) {
                Log::warning("Business email not found, skipping business receipt", [
                    'invoice_id' => $invoice->id,
                    'business_id' => $invoice->business_id,
                    'business_name' => $invoice->business->name
                ]);
                return;
            }

            Mail::to($invoice->business->email)->send(new BusinessReceipt($invoice, $pdfPath));
            
            Log::info("Business receipt sent successfully", [
                'invoice_id' => $invoice->id,
                'business_email' => $invoice->business->email,
                'business_name' => $invoice->business->name
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to send business receipt", [
                'invoice_id' => $invoice->id,
                'business_email' => $invoice->business->email,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send receipt to KashTre
     */
    private function sendKashTreReceipt(Invoice $invoice, $chargeAmount, $pdfPath = null, $kashtreBusiness = null)
    {
        try {
            // Use KashTre business email if available, otherwise use a default
            $kashtreEmail = $kashtreBusiness && $kashtreBusiness->email ? 
                $kashtreBusiness->email : 
                config('mail.kashtre_email', 'admin@kashtre.com');

            Mail::to($kashtreEmail)->send(new KashTreReceipt($invoice, $chargeAmount, $pdfPath));
            
            Log::info("KashTre receipt sent successfully", [
                'invoice_id' => $invoice->id,
                'kashtre_email' => $kashtreEmail,
                'charge_amount' => $chargeAmount
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to send KashTre receipt", [
                'invoice_id' => $invoice->id,
                'kashtre_email' => $kashtreEmail ?? 'unknown',
                'error' => $e->getMessage()
            ]);
        }
    }

}
