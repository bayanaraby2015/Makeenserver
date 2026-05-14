@extends('layouts.guest')

@section('title', __('register.pending.title'))

@section('content')
<div class="card" style="text-align:center;">
    <div style="width:64px; height:64px; margin:0 auto 1rem; background:rgba(33,178,184,.12); color:var(--brand-teal); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:2rem;">
        ✓
    </div>
    <h1>{{ __('register.pending.title') }}</h1>
    <p class="subtitle" style="text-align:center; max-width:560px; margin-inline:auto;">
        {{ __('register.pending.body') }}
    </p>

    @if (! empty($context['organization']))
        <p style="margin-top:.5rem; color:#283979; font-weight:500;">
            {{ $context['organization'] }}
        </p>
    @endif

    @if (! empty($context['email']))
        <p style="margin-top:.25rem; color:#6b7280; font-size:.9rem;">
            سيتم التواصل معك عبر: <strong dir="ltr" style="unicode-bidi:isolate;">{{ $context['email'] }}</strong>
        </p>
    @endif

    <a href="{{ url('/association/login') }}" class="alt-link" style="display:inline-block; margin-top:1.5rem;">
        {{ __('register.pending.home_link') }}
    </a>
</div>
@endsection
