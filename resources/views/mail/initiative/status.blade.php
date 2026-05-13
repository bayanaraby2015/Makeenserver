@component('mail::message')
# {{ $title }}

{{ $body }}

| {{ __('initiatives.fields.name_ar') }} | {{ __('initiatives.fields.organization') }} | {{ __('initiatives.fields.status') }} |
| --- | --- | --- |
| {{ $initiative->name_ar }} | {{ $initiative->organization?->name_ar ?? '-' }} | {{ __('initiatives.statuses.'.$initiative->status) }} |

@if(filled($reason))
**{{ __('initiatives.fields.reviewer_notes') }}**

{{ $reason }}
@endif

@component('mail::button', ['url' => $url])
{{ __('initiatives.actions.view_initiative') }}
@endcomponent

{{ config('app.name') }}
@endcomponent
