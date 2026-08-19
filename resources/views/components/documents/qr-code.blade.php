@props([
    'value',
    'size' => 70,
])
@php
    $qrSvg = '';
    try {
        if (class_exists(\SimpleSoftwareIO\QrCode\Facades\QrCode::class)) {
            $qrSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::size($size)->generate($value);
        }
    } catch (\Throwable $e) {
        $qrSvg = '';
    }
@endphp
@if($qrSvg)
<div class="qr-container" style="text-align: center; margin: 4px 0;">
    <div style="display: inline-block;">
        {!! $qrSvg !!}
    </div>
</div>
@endif
