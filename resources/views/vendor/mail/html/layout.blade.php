@php
    $__mailLocale = app()->getLocale();
    $__mailDir = in_array($__mailLocale, ['ar', 'he', 'fa', 'ur'], true) ? 'rtl' : 'ltr';
@endphp
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="{{ str_replace('_', '-', $__mailLocale) }}" dir="{{ $__mailDir }}">
<head>
<title>{{ config('app.name') }}</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="color-scheme" content="light">
<meta name="supported-color-schemes" content="light">
<style>
@media only screen and (max-width: 600px) {
.inner-body {
width: 100% !important;
}

.footer {
width: 100% !important;
}
}

@media only screen and (max-width: 500px) {
.button {
width: 100% !important;
}
}

/* RTL-specific overrides — many e-mail clients (Gmail, Outlook
   Desktop, Apple Mail) only honour explicit text-align: right and
   ignore "text-align: start" or the document-level dir attribute,
   so we force RTL alignment on the body content only. The brand
   header, the action button and the footer stay centered. */
html[dir="rtl"] .content-cell,
html[dir="rtl"] .content-cell p,
html[dir="rtl"] .content-cell h1,
html[dir="rtl"] .content-cell h2,
html[dir="rtl"] .content-cell h3,
html[dir="rtl"] .content-cell ul,
html[dir="rtl"] .content-cell ol,
html[dir="rtl"] .content-cell blockquote {
direction: rtl !important;
text-align: right !important;
}

html[dir="rtl"] .content-cell table th,
html[dir="rtl"] .content-cell table td.cell,
html[dir="rtl"] table.purchase td,
html[dir="rtl"] table.panel td {
text-align: right !important;
}

html[dir="rtl"] .header,
html[dir="rtl"] td.header,
html[dir="rtl"] .header a,
html[dir="rtl"] .footer,
html[dir="rtl"] .footer p,
html[dir="rtl"] .footer td,
html[dir="rtl"] .action,
html[dir="rtl"] .action td {
text-align: center !important;
}
</style>
{!! $head ?? '' !!}
</head>
<body dir="{{ $__mailDir }}" style="direction: {{ $__mailDir }}; text-align: {{ $__mailDir === 'rtl' ? 'right' : 'left' }};">

<table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="center">
<table class="content" width="100%" cellpadding="0" cellspacing="0" role="presentation">
{!! $header ?? '' !!}

<!-- Email Body -->
<tr>
<td class="body" width="100%" cellpadding="0" cellspacing="0" style="border: hidden !important;">
<table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
<!-- Body content -->
<tr>
<td class="content-cell">
{!! Illuminate\Mail\Markdown::parse($slot) !!}

{!! $subcopy ?? '' !!}
</td>
</tr>
</table>
</td>
</tr>

{!! $footer ?? '' !!}
</table>
</td>
</tr>
</table>
</body>
</html>
