@component('mail::message')
# {{ $title }}

{{ $body }}

@component('mail::table')
| {{ __('consultations.fields.subject') }} | {{ __('consultations.fields.initiative') }} | {{ __('consultations.fields.requester_organization') }} | {{ __('consultations.fields.status') }} |
| --- | --- | --- | --- |
| {{ $consultation->subject }} | {{ $consultation->initiative?->name_ar ?? '-' }} | {{ $consultation->requesterOrganization?->name_ar ?? '-' }} | {{ __('consultations.statuses.'.$consultation->status) }} |
@endcomponent

@if ($consultation->scheduled_at || $consultation->meeting_url)
@component('mail::panel')
@if ($consultation->scheduled_at)
**{{ __('consultations.fields.scheduled_at') }}:** {{ $consultation->scheduled_at->format('Y-m-d H:i') }}
@endif

@if ($consultation->meeting_url)
**{{ __('consultations.fields.meeting_url') }}:** {{ $consultation->meeting_url }}
@endif

@if ($consultation->meeting_password)
**{{ __('consultations.fields.meeting_password') }}:** {{ $consultation->meeting_password }}
@endif
@endcomponent
@endif

@component('mail::button', ['url' => $url])
{{ __('initiatives.actions.view') }}
@endcomponent

{{ config('brand.platform_name_ar', config('app.name')) }}
@endcomponent
