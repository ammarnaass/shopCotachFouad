<!DOCTYPE html>
<html lang="ar">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title ?? 'مستند' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans Condensed', 'DejaVu Sans', 'Arial Unicode MS', Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            line-height: 1.6;
            direction: rtl;
            text-align: right;
        }
        [dir="ltr"] { direction: ltr; unicode-bidi: embed; text-align: left; }
        [dir="rtl"] { direction: rtl; unicode-bidi: embed; text-align: right; }
    </style>
</head>
<body>
    {!! $content !!}
</body>
</html>
