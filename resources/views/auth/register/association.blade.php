@extends('layouts.guest')

@section('title', __('register.page_title'))

@section('content')
<div class="card">
    <h1>{{ __('register.page_title') }}</h1>
    <p class="subtitle">{{ __('register.page_subtitle') }}</p>

    @if ($errors->any())
        <div style="background:#fef2f2; border:1px solid #fecaca; color:#b91c1c; padding:.85rem 1rem; border-radius:10px; margin-bottom:1.25rem;">
            <strong>{{ __('Please correct the following:') }}</strong>
            <ul style="margin:.5rem 0 0; padding-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}:1.25rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register.association.store') }}" novalidate>
        @csrf

        <div class="section-title">{{ __('register.sections.organization') }}</div>
        <div class="grid">
            <div class="field">
                <label for="org_name_ar">{{ __('register.fields.org_name_ar') }} *</label>
                <input id="org_name_ar" type="text" name="org_name_ar" value="{{ old('org_name_ar') }}" required>
                @error('org_name_ar')<div class="error">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label for="org_name_en">{{ __('register.fields.org_name_en') }}</label>
                <input id="org_name_en" type="text" name="org_name_en" value="{{ old('org_name_en') }}">
                @error('org_name_en')<div class="error">{{ $message }}</div>@enderror
            </div>

            <div class="field">
                <label for="license_number">{{ __('register.fields.license_number') }} *</label>
                <input id="license_number" type="text" name="license_number" value="{{ old('license_number') }}" required>
                @error('license_number')<div class="error">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label for="license_authority">{{ __('register.fields.license_authority') }} *</label>
                <input id="license_authority" type="text" name="license_authority" value="{{ old('license_authority') }}" required placeholder="{{ __('register.placeholders.license_authority') }}">
                @error('license_authority')<div class="error">{{ $message }}</div>@enderror
            </div>

            <div class="field">
                <label for="city">{{ __('register.fields.city') }} *</label>
                <input id="city" type="text" name="city" value="{{ old('city') }}" required>
                @error('city')<div class="error">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label for="org_phone">{{ __('register.fields.org_phone') }} *</label>
                <input id="org_phone" type="tel" name="org_phone" value="{{ old('org_phone') }}" required dir="ltr" style="text-align:start;">
                @error('org_phone')<div class="error">{{ $message }}</div>@enderror
            </div>

            <div class="field full">
                <label for="address">{{ __('register.fields.address') }}</label>
                <textarea id="address" name="address" rows="2">{{ old('address') }}</textarea>
                @error('address')<div class="error">{{ $message }}</div>@enderror
            </div>

            <div class="field">
                <label for="org_email">{{ __('register.fields.org_email') }} *</label>
                <input id="org_email" type="email" name="org_email" value="{{ old('org_email') }}" required dir="ltr" style="text-align:start;">
                @error('org_email')<div class="error">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label for="website">{{ __('register.fields.website') }}</label>
                <input id="website" type="url" name="website" value="{{ old('website') }}" dir="ltr" style="text-align:start;" placeholder="{{ __('register.placeholders.website') }}">
                @error('website')<div class="error">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="section-title">{{ __('register.sections.manager') }}</div>
        <div class="grid">
            <div class="field">
                <label for="manager_name">{{ __('register.fields.manager_name') }} *</label>
                <input id="manager_name" type="text" name="manager_name" value="{{ old('manager_name') }}" required>
                @error('manager_name')<div class="error">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label for="manager_phone">{{ __('register.fields.manager_phone') }} *</label>
                <input id="manager_phone" type="tel" name="manager_phone" value="{{ old('manager_phone') }}" required dir="ltr" style="text-align:start;">
                @error('manager_phone')<div class="error">{{ $message }}</div>@enderror
            </div>
            <div class="field full">
                <label for="manager_email">{{ __('register.fields.manager_email') }} *</label>
                <input id="manager_email" type="email" name="manager_email" value="{{ old('manager_email') }}" required dir="ltr" style="text-align:start;">
                @error('manager_email')<div class="error">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="section-title">{{ __('register.sections.security') }}</div>
        <div class="grid">
            <div class="field">
                <label for="password">{{ __('register.fields.password') }} *</label>
                <input id="password" type="password" name="password" required autocomplete="new-password">
                @error('password')<div class="error">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label for="password_confirmation">{{ __('register.fields.password_confirmation') }} *</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
            </div>

            <div class="full">
                <div class="checkbox">
                    <input id="accept_terms" type="checkbox" name="accept_terms" value="1" {{ old('accept_terms') ? 'checked' : '' }}>
                    <label for="accept_terms">{{ __('register.fields.accept_terms') }} *</label>
                </div>
                @error('accept_terms')<div class="error">{{ $message }}</div>@enderror
            </div>
        </div>

        <div style="margin-top:2rem; display:flex; align-items:center; flex-wrap:wrap;">
            <button type="submit">{{ __('register.submit') }}</button>
            <a href="{{ url('/association/login') }}" class="alt-link">{{ __('register.login_link') }}</a>
        </div>
    </form>
</div>
@endsection
