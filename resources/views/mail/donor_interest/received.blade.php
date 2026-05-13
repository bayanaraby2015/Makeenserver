@component('mail::message')
{{ __('mail.donor_interest.greeting') }}

{{ __('mail.donor_interest.intro', ['donor' => $donor?->name ?? '', 'initiative' => $initiative?->name_ar ?? '']) }}

@component('mail::table')
| {{ __('initiatives.fields.name_ar') }} | {{ __('initiatives.fields.organization') }} | {{ __('mail.donor_interest.proposed_amount') }} |
| --- | --- | --- |
| {{ $initiative?->name_ar ?? '-' }} | {{ $initiative?->organization?->name_ar ?? '-' }} | {{ $interest->proposed_amount ? \App\Support\DisplayNumber::riyal($interest->proposed_amount) : '-' }} |
@endcomponent

@if ($interest->proposed_amount)
- **{{ __('mail.donor_interest.proposed_amount') }}:** {{ \App\Support\DisplayNumber::riyal($interest->proposed_amount) }}
@endif

@if ($interest->message)
**{{ __('mail.donor_interest.message_heading') }}**

> {{ $interest->message }}
@endif

{{ __('mail.donor_interest.thanks') }}

{{ __('mail.donor_interest.team') }}
@endcomponent
