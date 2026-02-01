<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bordro - {{ $payroll->payroll_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; padding: 24px; max-width: 210mm; margin: 0 auto; }
        h1 { font-size: 18px; margin-bottom: 8px; }
        .meta { color: #6b7280; margin-bottom: 24px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #e5e7eb; padding: 8px 12px; text-align: left; }
        th { background: #f3f4f6; font-weight: 600; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: 700; }
        .mt-4 { margin-top: 24px; }
    </style>
</head>
<body>
    <h1>Bordro Belgesi</h1>
    <p class="meta">Bordro No: {{ $payroll->payroll_number }} · Dönem: {{ $payroll->period_start->format('d.m.Y') }} - {{ $payroll->period_end->format('d.m.Y') }}</p>

    <p><strong>Personel:</strong> {{ $payroll->employee->first_name }} {{ $payroll->employee->last_name }}</p>

    <table>
        <thead>
            <tr>
                <th>Kalem</th>
                <th class="text-end">Tutar (₺)</th>
            </tr>
        </thead>
        <tbody>
            <tr><td>Brüt Maaş</td><td class="text-end">{{ number_format($payroll->base_salary, 2) }}</td></tr>
            <tr><td>Fazla Mesai</td><td class="text-end">{{ number_format($payroll->overtime_amount ?? 0, 2) }}</td></tr>
            <tr><td>Prim</td><td class="text-end">{{ number_format($payroll->bonus ?? 0, 2) }}</td></tr>
            <tr><td>Kesinti</td><td class="text-end">-{{ number_format($payroll->deduction ?? 0, 2) }}</td></tr>
            <tr><td>Vergi</td><td class="text-end">-{{ number_format($payroll->tax ?? 0, 2) }}</td></tr>
            <tr><td>SGK</td><td class="text-end">-{{ number_format($payroll->social_security ?? 0, 2) }}</td></tr>
            <tr class="fw-bold"><td>Net Maaş</td><td class="text-end">{{ number_format($payroll->net_salary, 2) }}</td></tr>
        </tbody>
    </table>

    <p class="mt-4 meta">Durum: {{ match($payroll->status) { 'paid' => 'Ödendi', 'finalized' => 'Kesinleşti', default => 'Taslak' } }} · Oluşturulma: {{ $payroll->created_at->format('d.m.Y H:i') }}</p>
</body>
</html>
