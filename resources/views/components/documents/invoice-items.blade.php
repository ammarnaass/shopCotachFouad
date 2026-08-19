@props([
    'items',
    'template',
    'pdfMode' => false,
])
@php
    $showSku   = $template->getSetting('show_sku', true);
    $showImage = $template->getSetting('show_product_image', false);
@endphp
<div class="invoice-section">
    <div class="section-title">المنتجات</div>
    <table class="items-table" cellpadding="0" cellspacing="0" width="100%">
        <thead>
            <tr>
                @if($showImage)<th style="width:8%;">الصورة</th>@endif
                <th style="width:{{ $showSku ? '38%' : '48%' }}; text-align:right;">المنتج</th>
                @if($showSku)<th style="width:12%; text-align:center;">SKU</th>@endif
                <th style="width:12%; text-align:center;">الخيارات</th>
                <th style="width:8%; text-align:center;">الكمية</th>
                <th style="width:10%; text-align:center; direction:ltr;">السعر</th>
                <th style="width:10%; text-align:center; direction:ltr;">الإجمالي</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                @if($showImage)
                <td style="text-align:center; padding:4px;">
                    @php
                        $productImage = $item->product?->primaryImage?->image;
                        $productImageSrc = $productImage
                            ? ($pdfMode ? pdf_image_src('storage/'.$productImage) : asset('storage/'.$productImage))
                            : null;
                    @endphp
                    @if($productImageSrc)
                        <img src="{{ $productImageSrc }}" style="width:30px;height:30px;object-fit:cover;border-radius:4px;">
                    @else
                        <span style="color:#bbb;">—</span>
                    @endif
                </td>
                @endif
                <td>{{ $item->product_name }}</td>
                @if($showSku)<td style="text-align:center; font-family:monospace; font-size:10px;">{{ $item->product_sku ?? '—' }}</td>@endif
                <td style="text-align:center; font-size:10px;">
                    @php
                        $formattedOptions = is_array($item->options_summary)
                            ? collect($item->options_summary)
                                ->map(fn (array $option) => ($option['label'] ?? 'خيار').': '.($option['value'] ?? '—'))
                                ->implode(' | ')
                            : null;
                    @endphp
                    @if($formattedOptions)
                        {{ $formattedOptions }}
                    @elseif($item->custom_text)
                        {{ $item->custom_text }}
                    @else
                        —
                    @endif
                </td>
                <td style="text-align:center; font-weight:700;">{{ $item->quantity }}</td>
                <td style="text-align:center; direction:ltr;">{{ number_format($item->price, 2) }}</td>
                <td style="text-align:center; direction:ltr; font-weight:700;">{{ number_format($item->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
