@props([
    'store',
    'invoice',
    'template',
])
@php
    $thankYou = $template->getSetting('thank_you_message', 'شكراً لتسوقكم من متجرنا');
    $footerText = $template->getSetting('footer_text', '');
@endphp
<div class="invoice-footer" style="margin-top:20px; border-top:1px dashed #ccc; padding-top:12px; text-align:center; font-size:10px; color:#666;">
    @if($thankYou)
        <div style="font-weight:600; font-size:12px; color:#333; margin-bottom:4px;">{{ $thankYou }}</div>
    @endif
    @if($footerText)
        <div style="margin-bottom:4px;">{{ $footerText }}</div>
    @endif
    <div>
        فاتورة رقم <span dir="ltr">{{ $invoice->invoice_number }}</span> • {{ $store['name'] }} @if($store['phone']) • تليفون: <span dir="ltr">{{ $store['phone'] }}</span>@endif
    </div>
</div>
