<x-mail::message>
# Set Your Password

Hello {{ $userName }},

Your third party vendor has been successfully registered in the third-party system. To access your account, please set your password using the link below.

**Username:** {{ $username }}

<x-mail::button :url="$resetUrl">
Set Your Password
</x-mail::button>

This password reset link will expire in 60 minutes.

If you did not request this, please contact your administrator.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
