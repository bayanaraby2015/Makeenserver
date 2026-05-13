@component('mail::message')
{{ __('mail.organization.approved.greeting', ['name' => optional($organization->members()->first())->name ?? '']) }}

{{ __('mail.organization.approved.intro', ['org' => $organization->name_ar]) }}

@component('mail::table')
| {{ __('organizations.fields.name_ar') }} | {{ __('organizations.fields.city') }} | {{ __('organizations.fields.license_number') }} | {{ __('organizations.fields.status') }} |
| --- | --- | --- | --- |
| {{ $organization->name_ar }} | {{ $organization->city ?? '-' }} | {{ $organization->license_number ?? '-' }} | {{ __('organizations.statuses.'.$organization->status) }} |
@endcomponent

@component('mail::button', ['url' => $loginUrl])
{{ __('mail.organization.approved.cta') }}
@endcomponent

**{{ __('mail.organization.approved.next_steps') }}**

1. {{ __('mail.organization.approved.step_login') }}
2. {{ __('mail.organization.approved.step_profile') }}
3. {{ __('mail.organization.approved.step_initiative') }}

{{ __('mail.organization.approved.thanks') }}

{{ __('mail.organization.approved.team') }}
@endcomponent
