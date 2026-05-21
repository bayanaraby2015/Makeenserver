@props(['url'])
<tr>
<td class="header" align="center" style="text-align: center;">
<a href="{{ $url }}" style="display: inline-block;">
<span style="display:inline-block;background:#ffffff;border:1px solid #e5e9f2;border-radius:14px;padding:12px 18px;box-shadow:0 10px 24px rgba(40,57,121,.10);">
<img src="{{ asset(config('brand.logo.makeen_header', '/brand/makeen-logo-header.png')) }}" alt="{{ config('brand.platform_name_en', 'Makeen') }}" style="display:inline-block;height:42px;width:auto;vertical-align:middle;margin:0 10px;">
<span style="display:inline-block;height:34px;width:1px;background:#d8dee9;vertical-align:middle;"></span>
<img src="{{ asset(config('brand.logo.masar_header', '/brand/masar-logo-header.png')) }}" alt="{{ config('brand.parent_program_name_en', 'Masar Al Ejadh') }}" style="display:inline-block;height:38px;width:auto;vertical-align:middle;margin:0 10px;">
</span>
</a>
</td>
</tr>
