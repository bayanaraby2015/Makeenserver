@component('mail::message')
{{ __('mail.organization.rejected.greeting', ['name' => optional($organization->members()->first())->name ?? '']) }}

{{ __('mail.organization.rejected.intro', ['org' => $organization->name_ar]) }}

@component('mail::table')
| {{ __('organizations.fields.name_ar') }} | {{ __('organizations.fields.city') }} | {{ __('organizations.fields.license_number') }} | {{ __('organizations.fields.status') }} |
| --- | --- | --- | --- |
| {{ $organization->name_ar }} | {{ $organization->city ?? '-' }} | {{ $organization->license_number ?? '-' }} | {{ __('organizations.statuses.'.$organization->status) }} |
@endcomponent

**{{ __('mail.organization.rejected.reason_heading') }}**

> {{ $reason }}

{{ __('mail.organization.rejected.next_steps') }}

{{ __('mail.organization.rejected.support_contact') }}

{{ __('mail.organization.rejected.thanks') }}

{{ __('mail.organization.rejected.team') }}
@endcomponent
