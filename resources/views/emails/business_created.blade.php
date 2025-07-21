
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
            <p><strong>Account Number:</strong> <code>{{ $business->account_number }}</code></p>
            <p><strong>Business Name:</strong> {{ $business->name }}</p>
            <p><strong>Email:</strong> {{ $business->email }}</p>
            <p><strong>Phone:</strong> {{ $business->phone }}</p>
            <p><strong>Address:</strong> {{ $business->address }}</p>
            @if($business->logo)
                <p><strong>Logo:</strong><br>
                    <img src="{{ asset('storage/' . $business->logo) }}" alt="Business Logo" style="max-width: 150px; max-height: 150px; margin-top: 8px; border-radius: 8px; border: 1px solid #eee;">
                </p>
            @endif
        </div>
        <p>Thank you for joining us.</p>
        <div class="footer">
            Regards,<br>
            {{ config('app.name') }} Team
        </div>
    </div>
</body>
</html>
