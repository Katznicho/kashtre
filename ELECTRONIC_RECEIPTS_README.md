# Electronic Receipts System

## Overview

The Electronic Receipts System automatically sends detailed receipts via email to all parties when a payment is completed. The system sends three types of receipts:

1. **Client Receipt** - Sent to the client with all purchase details
2. **Business Receipt** - Sent to the business with sales information
3. **KashTre Receipt** - Sent to KashTre with transaction records and service charge information

## Features

- ✅ Automatic email sending upon payment completion
- ✅ PDF attachments with detailed receipts
- ✅ Professional email templates with company branding
- ✅ Comprehensive transaction details
- ✅ Error handling and logging
- ✅ Configurable KashTre email address

## Email Recipients

### Client Receipt
- **Recipient**: Client's email address
- **Content**: Complete purchase details, items bought, payment summary
- **Attachment**: PDF receipt with full transaction details

### Business Receipt  
- **Recipient**: Business owner's email address
- **Content**: Sales information, client details, revenue summary
- **Attachment**: PDF receipt for business records

### KashTre Receipt
- **Recipient**: KashTre platform email (configurable)
- **Content**: Transaction record, service charge amount, business/client info
- **Attachment**: PDF receipt for platform records

## Configuration

### Environment Variables

Add to your `.env` file:

```env
# KashTre platform email for receipts
KASHTRE_EMAIL=admin@kashtre.com

# Mail configuration (already configured)
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@kashtre.com
MAIL_FROM_NAME="Kashtre Platform"
```

### Service Charge Information

The system uses the actual service charge from the invoice record. The service charge is already calculated and stored in the `invoices.service_charge` field when the invoice is created.

## Automatic Triggering

Receipts are automatically sent when:

1. A mobile money payment is completed (via cron job)
2. A cash payment is processed immediately
3. The `processPaymentCompleted()` method is called in `MoneyTrackingService`

## Manual Testing

For testing purposes, you can manually trigger receipt sending:

```bash
# Send receipts for a specific invoice
POST /invoices/{invoice_id}/send-receipts
```

**Note**: Remove this testing route in production.

## Email Templates

### Template Locations
- Client: `resources/views/mail/receipts/client.blade.php`
- Business: `resources/views/mail/receipts/business.blade.php`
- KashTre: `resources/views/mail/receipts/kashtre.blade.php`

### Customization
- Modify email templates to match your branding
- Update email subjects and content as needed
- Add additional fields or information
- Service charge information comes directly from the invoice record

## PDF Generation

- Uses existing invoice print view (`resources/views/invoices/print.blade.php`)
- PDFs are saved to `storage/app/receipts/` directory
- Filename format: `receipt_{invoice_number}_{timestamp}.pdf`
- Automatically attached to all emails

## Error Handling

The system includes comprehensive error handling:

- Individual email failures don't stop other emails
- All errors are logged for debugging
- Receipt failure doesn't affect payment completion
- Missing email addresses are logged and skipped

## Logging

All receipt activities are logged with detailed information:

```php
// Success logs
Log::info("Electronic receipts sent successfully", [
    'invoice_id' => $invoice->id,
    'invoice_number' => $invoice->invoice_number
]);

// Error logs
Log::error("Failed to send electronic receipts", [
    'invoice_id' => $invoice->id,
    'error' => $e->getMessage()
]);
```

## File Structure

```
app/
├── Mail/
│   ├── ClientReceipt.php
│   ├── BusinessReceipt.php
│   └── KashTreReceipt.php
└── Services/
    └── ReceiptService.php

resources/views/mail/receipts/
├── client.blade.php
├── business.blade.php
└── kashtre.blade.php

config/
└── mail.php (updated with KashTre email config)
```

## Testing Checklist

- [ ] Client email address is valid
- [ ] Business email address is valid  
- [ ] KashTre email is configured
- [ ] Mail configuration is working
- [ ] PDF generation is working
- [ ] Email templates render correctly
- [ ] Attachments are included
- [ ] Error handling works properly

## Production Considerations

1. **Remove testing route**: Delete the manual receipt sending route
2. **Configure proper email**: Set up production email credentials
3. **Monitor logs**: Check for failed email deliveries
4. **Storage cleanup**: Implement PDF cleanup for old receipts
5. **Email limits**: Monitor SMTP sending limits

## Troubleshooting

### Common Issues

1. **Emails not sending**
   - Check mail configuration in `.env`
   - Verify SMTP credentials
   - Check email addresses are valid

2. **PDF not generating**
   - Ensure DomPDF is installed
   - Check storage permissions
   - Verify invoice print view exists

3. **Missing receipts**
   - Check application logs
   - Verify payment completion flow
   - Ensure receipt service is called

### Debug Commands

```bash
# Check mail configuration
php artisan tinker
>>> config('mail')

# Test email sending
php artisan tinker
>>> Mail::raw('Test email', function($msg) { $msg->to('test@example.com')->subject('Test'); });

# Check receipt storage
ls -la storage/app/receipts/
```

## Support

For issues or questions about the Electronic Receipts System:

1. Check application logs first
2. Verify email configuration
3. Test with manual receipt sending
4. Contact system administrator if issues persist
