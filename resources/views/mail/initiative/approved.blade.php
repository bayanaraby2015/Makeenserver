@component('mail::message')
{{ __('mail.initiative.approved.greeting', ['name' => optional($organization?->members()->first())->name ?? '']) }}

{{ __('mail.initiative.approved.intro', ['initiative' => $initiative->name_ar, 'org' => $organization?->name_ar ?? '']) }}

@component('mail::table')
| {{ __('initiatives.fields.name_ar') }} | {{ __('initiatives.fields.organization') }} | {{ __('initiatives.fields.domain') }} | {{ __('initiatives.fields.grand_total') }} |
| --- | --- | --- | --- |
| {{ $initiative->name_ar }} | {{ $organization?->name_ar ?? '-' }} | {{ __('initiatives.domains.'.$initiative->domain) }} | {{ \App\Support\DisplayNumber::riyal($initiative->grand_total) }} |
@endcomponent

@component('mail::button', ['url' => $loginUrl])
{{ __('mail.initiative.approved.cta') }}
@endcomponent

**{{ __('mail.initiative.approved.next_steps') }}**

1. {{ __('mail.initiative.approved.step_dashboard') }}
2. {{ __('mail.initiative.approved.step_review') }}
3. {{ __('mail.initiative.approved.step_donor') }}

{{ __('mail.initiative.approved.thanks') }}

{{ __('mail.initiative.approved.team') }}
@endcomponent
