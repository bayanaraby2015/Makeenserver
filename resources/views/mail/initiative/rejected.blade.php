@component('mail::message')
{{ __('mail.initiative.rejected.greeting', ['name' => optional($organization?->members()->first())->name ?? '']) }}

{{ __('mail.initiative.rejected.intro', ['initiative' => $initiative->name_ar]) }}

@component('mail::table')
| {{ __('initiatives.fields.name_ar') }} | {{ __('initiatives.fields.organization') }} | {{ __('initiatives.fields.domain') }} | {{ __('initiatives.fields.status') }} |
| --- | --- | --- | --- |
| {{ $initiative->name_ar }} | {{ $organization?->name_ar ?? '-' }} | {{ __('initiatives.domains.'.$initiative->domain) }} | {{ __('initiatives.statuses.'.$initiative->status) }} |
@endcomponent

**{{ __('mail.initiative.rejected.reason_heading') }}**

> {{ $reason }}

{{ __('mail.initiative.rejected.next_steps') }}

{{ __('mail.initiative.rejected.support_contact') }}

{{ __('mail.initiative.rejected.thanks') }}

{{ __('mail.initiative.rejected.team') }}
@endcomponent
