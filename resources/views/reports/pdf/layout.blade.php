<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $pdfTitle ?? 'Report' }}</title>
    <style>
        @page { margin: 18px; }
        body { font-family: DejaVu Sans, Helvetica, Arial, sans-serif; font-size: 12pt; line-height: 1.35; color: #111; }
        h1 { font-size: 18pt; margin: 0 0 8px 0; line-height: 1.2; }
        .sub { font-size: 11pt; color: #444; margin-bottom: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 11.5pt; }
        th, td { border: 1px solid #333; padding: 7px 9px; vertical-align: top; }
        th { background: #e8e8e8; font-weight: bold; font-size: 12pt; }
        .num { text-align: right; }
        .muted { color: #555; font-size: 11pt; }
    </style>
</head>
<body>
<h1>{{ $pdfTitle ?? 'Report' }}</h1>
<div class="sub">{{ config('app.name') }} · Generated {{ $generatedAt ?? now()->format('Y-m-d H:i') }}</div>
@yield('pdf_body')
</body>
</html>
