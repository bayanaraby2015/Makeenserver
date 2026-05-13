{{-- Dual logos for panel sidebar/header: Makeen + Masar side-by-side using inline styles --}}
<div style="display:flex;align-items:center;gap:0.9rem;padding:0.15rem 0;">
    <img src="{{ asset(config('brand.logo.makeen_header', '/brand/makeen-logo-header.png')) }}"
         alt="{{ config('brand.platform_name_ar', 'مكين') }}"
         style="height:5rem;width:auto;"
         loading="eager">

    <span style="display:block;width:1px;height:3.5rem;background:#d1d5db;" aria-hidden="true"></span>

    <img src="{{ asset(config('brand.logo.masar_header', '/brand/masar-logo-header.png')) }}"
         alt="{{ config('brand.parent_program_name_ar', 'مسار الإجادة') }}"
         style="height:3.5rem;width:auto;"
         loading="eager">
</div>
