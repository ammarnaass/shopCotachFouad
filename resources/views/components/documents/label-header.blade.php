@props([
    'store',
    'order',
    'template',
    'pdfMode' => false,
])
@php
    $showLogo = $template->getSetting('show_logo', true);
    $logoSrc = $pdfMode ? pdf_image_src($store['logo'] ?? null) : ($store['logo'] ?? null);
@endphp
<div class="label-header" style="border-bottom: 2px solid #222; padding-bottom: 6px; margin-bottom: 8px;">
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td style="vertical-align: middle;">
                @if($showLogo && $logoSrc)
                    <img src="{{ $logoSrc }}" alt="{{ $store['name'] }}" style="max-height: 35px; max-width: 120px; object-fit: contain;">
                @else
                    <div style="font-size: 16px; font-weight: bold; color: #111;">{{ $store['name'] }}</div>
                @endif
            </td>
            <td style="text-align: left; vertical-align: middle; direction: ltr;">
                <div style="font-size: 14px; font-weight: bold; background: #000; color: #fff; padding: 3px 8px; border-radius: 4px; display: inline-block;">
                    #{{ $order->order_number }}
                </div>
            </td>
        </tr>
    </table>
</div>
