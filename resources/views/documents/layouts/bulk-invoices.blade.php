<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>طباعة فواتير مجمعة</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', 'Arial Unicode MS', Arial, sans-serif; font-size: 11px; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    @foreach($allData as $index => $data)
        <div class="{{ !$loop->last ? 'page-break' : '' }}">
            @include($view, $data)
        </div>
    @endforeach
</body>
</html>
