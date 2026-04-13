<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Struk #{{ $transaction->invoice_code }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            width: 100%;
            max-width: 300px;
            margin: 0 auto;
            padding: 10px;
            background: #fff;
            color: #000;
            line-height: 1.4;
        }

        .header {
            text-align: center;
            border-bottom: 2px dashed #333;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .logo-placeholder {
            width: 60px;
            height: 60px;
            background: #f0f0f0;
            border-radius: 50%;
            margin: 0 auto 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px dashed #ccc;
            color: #999;
            font-size: 8px;
            text-align: center;
        }

        .store-name {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }

        .tagline {
            font-size: 10px;
            color: #666;
            font-style: italic;
            margin-bottom: 8px;
        }

        .info {
            margin-bottom: 10px;
            font-size: 11px;
            border-bottom: 1px solid #eee;
            padding-bottom: 8px;
        }

        .info div {
            display: flex;
            justify-content: space-between;
        }

        .info span {
            font-weight: bold;
            color: #333;
        }

        .items {
            border-top: 2px dashed #333;
            border-bottom: 2px dashed #333;
            padding: 8px 0;
            margin-bottom: 10px;
        }

        .item {
            margin-bottom: 6px;
        }

        .item-name {
            font-weight: bold;
            display: block;
            font-size: 12px;
        }

        .item-detail {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            padding-left: 5px;
            color: #444;
        }

        .totals {
            padding: 5px 0;
            border-bottom: 2px dashed #333;
        }

        .totals div {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
            font-size: 11px;
        }

        .grand-total {
            font-size: 16px;
            font-weight: bold;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid #000;
            display: flex;
            justify-content: space-between;
        }

        .footer {
            text-align: center;
            padding-top: 10px;
            font-size: 11px;
            line-height: 1.3;
        }

        .footer p {
            margin-bottom: 4px;
        }

        .no-print {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }

        .btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-align: center;
            text-decoration: none;
            font-family: sans-serif;
            font-weight: bold;
        }

        .btn-print {
            background: #4F46E5;
            color: white;
        }

        .btn-back {
            background: #9CA3AF;
            color: white;
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                width: 100%;
                max-width: none;
                margin: 0;
                padding: 0;
            }

            @page {
                margin: 0;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <!-- Placeholder for Store Logo -->
        <div class="logo-placeholder">
            LOGO
        </div>
        <div class="store-name">{{ \App\Models\Setting::get('store_name', config('app.name')) }}</div>
        <div class="tagline">{{ \App\Models\Setting::get('store_address', '') }}</div>
        <div style="font-size: 10px;">Telp: {{ \App\Models\Setting::get('store_phone', '-') }}</div>
    </div>

    <div class="info">
        <div><span>No:</span> <span>{{ $transaction->invoice_code }}</span></div>
        <div><span>Tgl:</span> <span>{{ $transaction->created_at->format('d/m/Y H:i') }}</span></div>
        <div><span>Kasir:</span> <span>{{ $transaction->user->name }}</span></div>
    </div>

    <div class="items">
        @foreach ($transaction->items as $item)
            <div class="item">
                <div class="item-name">{{ $item->product->name }}</div>
                <div class="item-detail">
                    <span>{{ $item->quantity }} x {{ number_format($item->price, 0, ',', '.') }}</span>
                    <span>{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                </div>
            </div>
        @endforeach
    </div>

    <div class="totals">
        <div class="grand-total">
            <span>TOTAL</span>
            <span>{{ number_format($transaction->total, 0, ',', '.') }}</span>
        </div>
        <div><span>Tunai</span> <span>{{ number_format($transaction->cash, 0, ',', '.') }}</span></div>
        <div><span>Kembali</span> <span>{{ number_format($transaction->change, 0, ',', '.') }}</span></div>
    </div>

    <div class="footer">
        <p>{{ \App\Models\Setting::get('receipt_footer', 'Terima kasih atas kunjungan Anda!') }}</p>
        <p>Barang yang sudah dibeli tidak dapat dikembalikan.</p>
    </div>

    <div class="no-print">
        <button class="btn btn-print" onclick="window.print()">🖨️ Cetak</button>
        <a href="{{ route('kasir.pos.index') }}" class="btn btn-back">Kembali</a>
    </div>
</body>

</html>
