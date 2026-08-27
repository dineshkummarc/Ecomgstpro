@component('mail::message')
# Aapka message humein mil gaya hai!

Hello {{ $name }},

**EcomgstPro** par contact karne ke liye dhanyawad!

Humein aapka message mil gaya hai. Humari team jald hi aapse judegi.

@component('mail::button', ['url' => config('app.url')])
Visit EcomgstPro
@endcomponent

Best regards,  
**The EcomgstPro Team**

---
*Yeh ek automated email hai. Kripya ispar reply na karein.*
@endcomponent