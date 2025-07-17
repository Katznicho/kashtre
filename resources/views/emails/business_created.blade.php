
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Welcome to {{ config('app.name') }}</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f8f9fa; color: #333; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; padding: 24px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .header { text-align: center; margin-bottom: 24px; }
        .footer { margin-top: 32px; text-align: center; color: #888; font-size: 13px; }
        .details { margin: 24px 0; }
        .details strong { display: inline-block; width: 140px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Welcome to {{ config('app.name') }}</h2>
        </div>
        <p>Hello <strong>{{ $business->name }}</strong>,</p>
        <p>Your business account has been created successfully!</p>
        <div class="details">
            <p><strong>Account Number:</strong> {{ $business->account_number }}</p>
            <p><strong>Email:</strong> {{ $business->email }}</p>
            <p><strong>Phone:</strong> {{ $business->phone }}</p>
        </div>
        <p>Thank you for joining us.</p>
        <div class="footer">
            Regards,<br>
            {{ config('app.name') }} Team
        </div>
    </div>
</body>
</html>
