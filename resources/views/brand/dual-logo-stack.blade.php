{{-- Dual logos for login pages: side-by-side, centered, compact --}}
<div style="display:flex;align-items:center;justify-content:center;gap:1rem;margin-bottom:1.5rem;">
    <img src="{{ asset(config('brand.logo.makeen_header', '/brand/makeen-logo-header.png')) }}"
         alt="{{ config('brand.platform_name_ar', 'مكين') }}"
         style="height:2.5rem;width:auto;"
         loading="eager">

    <span style="display:block;width:1px;height:2rem;background:#d1d5db;" aria-hidden="true"></span>

    <img src="{{ asset(config('brand.logo.masar_header', '/brand/masar-logo-header.png')) }}"
         alt="{{ config('brand.parent_program_name_ar', 'مسار الإجادة') }}"
         style="height:2.25rem;width:auto;"
         loading="eager">
</div>
