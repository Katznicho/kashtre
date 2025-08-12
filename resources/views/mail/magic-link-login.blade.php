<x-mail::message>
<img src="{{ asset('images/kashtre_logo.svg') }}" alt="{{ config('app.name') }}" style="width: 150px; margin-bottom: 20px;">

# Login to {{ config('app.name') }}

Hello {{ $user->name }},

You requested a login link for your {{ config('app.name') }} account.

<x-mail::button :url="$loginUrl">
Login to Your Account
</x-mail::button>

This login link will expire in **15 minutes** for security reasons.

If you didn't request this login link, please ignore this email. Your account is secure.

**Security Tips:**
- Never share this link with anyone
- Always log out when using shared devices
- Enable two-factor authentication for extra security

Thanks,<br>
{{ config('app.name') }} Team
</x-mail::message>
