<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $doc_title ?? 'مستند' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', 'Arial Unicode MS', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 11px;
            color: #191b23;
            line-height: 1.6;
            background: #f4f5f8;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .document-wrapper {
            max-width: 800px;
            margin: 20px auto;
            background: #fff;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            border-radius: 8px;
        }

        /* Print Controls Bar */
        .print-controls {
            max-width: 800px;
            margin: 15px auto 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #1e293b;
            color: #fff;
            padding: 10px 20px;
            border-radius: 8px;
        }

        .btn-print {
            background: #2563eb;
            color: #fff;
            border: none;
            padding: 8px 18px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-print:hover { background: #1d4ed8; }

        .btn-pdf {
            background: #059669;
            color: #fff;
            border: none;
            padding: 8px 18px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-pdf:hover { background: #047857; }

        @media print {
            body { background: #fff !important; }
            .document-wrapper {
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
            }
            .print-controls, nav, .navbar, sidebar, .admin-sidebar, header, button:not(.printable) {
                display: none !important;
            }
            @page {
                margin: 10mm;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    @if(!request()->has('pdf_mode'))
    <div class="print-controls">
        <div style="font-size: 14px; font-weight: bold;">
            {{ $doc_title ?? 'معاينة الطباعة' }}
        </div>
        <div style="display: flex; gap: 10px;">
            <button onclick="window.print()" class="btn-print">
                🖨️ طباعة
            </button>
            {!! $pdf_link ?? '' !!}
        </div>
    </div>
    @endif

    <div class="document-wrapper">
        @if(!empty($view)) @include($view) @endif
    </div>
</body>
</html>
