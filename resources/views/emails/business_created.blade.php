<x-mail::message>
<img src="{{ asset("images/kashtre_logo.svg") }}" alt="{{ config('app.name') }}" style="width: 150px; margin-bottom: 20px;">

# Welcome to {{ config('app.name') }}

Dear {{ $business_name }},

We are pleased to inform you that your business account has been successfully created on our platform. Below are your account details:

---

- **Business Name:** {{ $business_name }}
- **Account Number:** {{ $account_number }}
- **Email:** {{ $email }}
- **Phone:** {{ $phone }}


---

## Next Steps

<x-mail::panel>
Our team will proceed to configure your business profile, including the creation of branches and user accounts. You will be notified once the setup is complete and your team is ready to begin using the system.
</x-mail::panel>

Should you have any questions or require support, please do not hesitate to contact us.

<x-mail::button :url="url('/login')">
Access Your Dashboard
</x-mail::button>

Best regards,  
**The {{ config('app.name') }} Team**

</x-mail::message>
