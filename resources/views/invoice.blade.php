@php
    use App\Support\InvoiceView;
    $st = $statuses[$invoice->status] ?? ['label' => $invoice->status, 'color' => 'slate'];
    $palette = ['emerald' => '#059669', 'amber' => '#d97706', 'sky' => '#0284c7', 'rose' => '#e11d48', 'slate' => '#475569'];
    $badge = $palette[$st['color']] ?? '#475569';
    $nonce = \Illuminate\Support\Facades\Vite::cspNonce();
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Invoice {{ $invoice->number }} – {{ $company['name'] }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,600,700,800&display=swap">
    <style>
        *{box-sizing:border-box}
        body{margin:0;background:#f4f1fb;font-family:'Plus Jakarta Sans',system-ui,sans-serif;color:#111827;font-size:14px}
        .toolbar{max-width:860px;margin:24px auto 0;display:flex;gap:8px;justify-content:flex-end;padding:0 16px}
        .btn{border:2px solid #111827;background:#fff;border-radius:12px;padding:10px 16px;font-weight:700;cursor:pointer;box-shadow:3px 3px 0 #111827;font-family:inherit;font-size:14px;text-decoration:none;color:#111827}
        .btn.primary{background:linear-gradient(135deg,#7c3aed,#ec4899);color:#fff}
        .btn:hover{transform:translate(-1px,-1px);box-shadow:4px 4px 0 #111827}
        .paper{max-width:860px;margin:16px auto 40px;background:#fff;border-radius:24px;overflow:hidden;box-shadow:0 20px 60px -20px rgba(76,29,149,.35)}
        .head{background:linear-gradient(135deg,#7c3aed 0%,#db2777 55%,#f97316 100%);color:#fff;padding:32px 40px;display:flex;justify-content:space-between;gap:24px;flex-wrap:wrap}
        .brand{display:flex;gap:14px;align-items:center}
        .logo{width:52px;height:52px;border-radius:16px;background:#fff;display:grid;place-items:center;color:#7c3aed;font-weight:800;font-size:20px}
        .head h1{margin:0;font-size:28px;letter-spacing:-.02em}
        .muted{opacity:.85;font-size:13px;line-height:1.5}
        .body{padding:32px 40px}
        .grid{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:28px}
        .label{font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:#6b7280;font-weight:700;margin-bottom:6px}
        .badge{display:inline-block;padding:4px 12px;border-radius:999px;font-weight:700;font-size:12px;color:#fff;background:{{ $badge }}}
        table{width:100%;border-collapse:collapse}
        th{text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;padding:10px 12px;border-bottom:2px solid #ede9fe}
        td{padding:12px;border-bottom:1px solid #f1f5f9;vertical-align:top}
        .r{text-align:right}
        .totals{margin-left:auto;width:320px;margin-top:16px}
        .totals div{display:flex;justify-content:space-between;padding:6px 0}
        .grand{border-top:2px dashed #ddd6fe;margin-top:6px;padding-top:10px!important;font-size:18px;font-weight:800;color:#6d28d9}
        .box{background:#faf5ff;border:1px solid #ede9fe;border-radius:16px;padding:16px 18px;margin-top:24px}
        .foot{padding:20px 40px;background:#faf5ff;color:#6b7280;font-size:12px;display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap}
        @media (max-width:640px){.grid{grid-template-columns:1fr}.head,.body,.foot{padding:24px}.totals{width:100%}}
        @media print{body{background:#fff}.toolbar{display:none}.paper{box-shadow:none;margin:0;border-radius:0;max-width:none}.head{-webkit-print-color-adjust:exact;print-color-adjust:exact}.badge,.box,.foot{-webkit-print-color-adjust:exact;print-color-adjust:exact}}
    </style>
</head>
<body>
<div class="toolbar">
    @if ($isAdmin)
        <a class="btn" href="{{ route('admin.invoices.show', $invoice) }}">← Kembali</a>
    @endif
    <button class="btn primary" id="print-btn" type="button">Cetak / Simpan PDF</button>
</div>

<div class="paper">
    <div class="head">
        <div class="brand">
            <div class="logo">KS</div>
            <div>
                <div style="font-weight:800;font-size:18px">{{ $company['name'] }}</div>
                <div class="muted">{{ $company['legal_type'] }}@if($company['nib']) · NIB {{ $company['nib'] }}@endif</div>
            </div>
        </div>
        <div style="text-align:right">
            <h1>INVOICE</h1>
            <div class="muted">{{ $invoice->number }}</div>
        </div>
    </div>

    <div class="body">
        <div class="grid">
            <div>
                <div class="label">Ditagihkan kepada</div>
                <div style="font-weight:700;font-size:16px">{{ $invoice->client_name }}</div>
                <div class="muted" style="color:#4b5563">
                    @if($invoice->client_email){{ $invoice->client_email }}<br>@endif
                    @if($invoice->client_phone){{ $invoice->client_phone }}<br>@endif
                    @if($invoice->client_address){!! nl2br(e($invoice->client_address)) !!}@endif
                </div>
            </div>
            <div style="text-align:right">
                <div class="label">Status</div>
                <span class="badge">{{ $st['label'] }}</span>
                <div style="margin-top:12px" class="muted">
                    <span style="color:#6b7280">Tanggal terbit:</span> <b>{{ $invoice->issue_date?->translatedFormat('d F Y') }}</b><br>
                    @if($invoice->due_date)<span style="color:#6b7280">Jatuh tempo:</span> <b>{{ $invoice->due_date->translatedFormat('d F Y') }}</b><br>@endif
                    @if($invoice->order)<span style="color:#6b7280">Kode pesanan:</span> <b>{{ $invoice->order->code }}</b>@endif
                </div>
            </div>
        </div>

        <table>
            <thead><tr><th style="width:44px">#</th><th>Deskripsi</th><th class="r">Qty</th><th class="r">Harga</th><th class="r">Jumlah</th></tr></thead>
            <tbody>
            @foreach ($invoice->items as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item['description'] ?? '' }}</td>
                    <td class="r">{{ rtrim(rtrim(number_format((float) ($item['qty'] ?? 0), 2, ',', '.'), '0'), ',') }}</td>
                    <td class="r">{{ InvoiceView::rupiah($item['price'] ?? 0) }}</td>
                    <td class="r"><b>{{ InvoiceView::rupiah(round(((float) ($item['qty'] ?? 0)) * ((int) ($item['price'] ?? 0)))) }}</b></td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div><span>Subtotal</span><b>{{ InvoiceView::rupiah($invoice->subtotal) }}</b></div>
            @if($invoice->discount > 0)<div><span>Diskon</span><b>-{{ InvoiceView::rupiah($invoice->discount) }}</b></div>@endif
            @if($invoice->tax_percent > 0)<div><span>Pajak ({{ rtrim(rtrim(number_format($invoice->tax_percent, 2, ',', '.'), '0'), ',') }}%)</span><b>{{ InvoiceView::rupiah($invoice->tax_amount) }}</b></div>@endif
            <div class="grand"><span>Total</span><span>{{ InvoiceView::rupiah($invoice->total) }}</span></div>
            @if($invoice->paid_amount > 0)
                <div><span>Sudah dibayar</span><b style="color:#059669">{{ InvoiceView::rupiah($invoice->paid_amount) }}</b></div>
                <div><span>Sisa tagihan</span><b style="color:#e11d48">{{ InvoiceView::rupiah($invoice->balance) }}</b></div>
            @endif
        </div>

        @if($bank['name'] && $bank['account'])
            <div class="box">
                <div class="label">Pembayaran melalui transfer</div>
                <div style="font-size:15px"><b>{{ $bank['name'] }}</b> · {{ $bank['account'] }}<br>a.n. {{ $bank['holder'] }}</div>
            </div>
        @endif

        @if($invoice->notes)
            <div class="box" style="background:#fff7ed;border-color:#fed7aa">
                <div class="label">Catatan</div>
                <div>{!! nl2br(e($invoice->notes)) !!}</div>
            </div>
        @endif

        @if($invoice->payments->isNotEmpty())
            <div style="margin-top:24px">
                <div class="label">Riwayat pembayaran</div>
                <table>
                    @foreach($invoice->payments as $p)
                        <tr><td>{{ $p->paid_at?->translatedFormat('d M Y') }}</td><td>{{ $p->method }}@if($p->reference) · {{ $p->reference }}@endif</td><td class="r"><b>{{ InvoiceView::rupiah($p->amount) }}</b></td></tr>
                    @endforeach
                </table>
            </div>
        @endif
    </div>

    <div class="foot">
        <div>{{ $footer }}</div>
        <div>{{ $company['address'] }}@if($company['email']) · {{ $company['email'] }}@endif @if($company['phone']) · {{ $company['phone'] }}@endif</div>
    </div>
</div>
<script nonce="{{ $nonce }}">document.getElementById('print-btn').addEventListener('click', function () { window.print(); });</script>
</body>
</html>
