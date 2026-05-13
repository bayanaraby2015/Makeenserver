@component('mail::message')
# {{ $title }}

{{ $body }}

@component('mail::button', ['url' => $url])
{{ __('initiatives.actions.view') }}
@endcomponent

{{ config('brand.platform_name_ar', config('app.name')) }}
@endcomponent
