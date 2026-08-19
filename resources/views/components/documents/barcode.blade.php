@props([
    'value',
    'type' => 'C128',
    'height' => 40,
    'width' => 2,
    'showValue' => true,
])
@php
    $svg = '';
    try {
        $generator = new \Picqer\Barcode\BarcodeGeneratorSVG();
        $codeType = match(strtoupper($type)) {
            'C39' => \Picqer\Barcode\BarcodeGeneratorSVG::TYPE_CODE_39,
            'EAN13' => \Picqer\Barcode\BarcodeGeneratorSVG::TYPE_EAN_13,
            default => \Picqer\Barcode\BarcodeGeneratorSVG::TYPE_CODE_128,
        };
        $svg = $generator->getBarcode($value, $codeType, $width, $height);
    } catch (\Throwable $e) {
        $svg = '';
    }
@endphp
<div class="barcode-container" style="text-align: center; margin: 5px 0;">
    @if($svg)
        <div style="display: inline-block;">
            {!! $svg !!}
        </div>
        @if($showValue)
            <div style="font-family: monospace; font-size: 11px; font-weight: bold; margin-top: 2px; letter-spacing: 1px;">
                {{ $value }}
            </div>
        @endif
    @else
        <div style="font-family: monospace; font-size: 12px; font-weight: bold; border: 1px dashed #999; padding: 4px 8px; display: inline-block;">
            *{{ $value }}*
        </div>
    @endif
</div>
