@props([
    'url',
    'color' => 'primary',
    'align' => 'center',
])

@php
    $background = $color === 'primary' ? '#0083CB' : '#0E3D4F';
@endphp

<table class="action" align="{{ $align }}" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="{{ $align }}">
<table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="{{ $align }}">
<table border="0" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td>
<a
    href="{{ $url }}"
    class="button button-{{ $color }}"
    target="_blank"
    rel="noopener"
    style="display:inline-block; background:{{ $background }}; color:#ffffff; border-radius:6px; padding:12px 22px; font-family:Lato, Arial, Helvetica, sans-serif; font-size:14px; line-height:1.2; font-weight:800; text-decoration:none; border:1px solid {{ $background }};"
>{!! $slot !!}</a>
</td>
</tr>
</table>
</td>
</tr>
</table>
</td>
</tr>
</table>
