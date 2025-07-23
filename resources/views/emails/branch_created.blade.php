<x-mail::message>
# New Branch Created

Dear {{ $company->name }},

Your branch has been successfully added to the {{ $company->name }} company profile.

---

**Branch Name:** {{ $branch->name }}  
**Email:** {{ $branch->email }}  
**Phone:** {{ $branch->phone }}  
**Address:** {{ $branch->address }}

<x-mail::panel>
Your account will be configured shortly. You'll receive more details once it's ready for use.
</x-mail::panel>


Should you have any questions or require support, please do not hesitate to contact us.

<x-mail::button :url="url('/login')">
Access Your Dashboard
</x-mail::button>



Thanks,  
**The {{ config('app.name') }} Team**
</x-mail::message>
