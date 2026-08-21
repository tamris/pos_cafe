<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tiket Dapur - {{ $transaction->invoice_number }}</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Courier+Prime:ital,wght@0,400;0,700;1,400;1,700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    
    <script src="{{ asset('js/bluetooth-printer.js') }}"></script>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: #f1f5f9;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #0f172a;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 24px 16px;
            min-height: 100vh;
        }

        .action-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            width: 100%;
            max-width: 400px;
            justify-content: center;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            color: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .btn-direct {
            background-color: #059669;
        }
        .btn-direct:hover {
            background-color: #047857;
        }

        .btn-browser {
            background-color: #475569;
        }
        .btn-browser:hover {
            background-color: #334155;
        }

        .receipt-card {
            background: #ffffff;
            width: 100%;
            max-width: 380px;
            padding: 24px;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
            font-family: 'Consolas', 'Courier Prime', monospace;
            font-size: 11.5px;
            font-weight: 400;
            line-height: 1.25;
            border: 1px solid #e2e8f0;
        }

        .header {
            text-align: center;
            margin-bottom: 12px;
        }

        .title {
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .divider {
            border-top: 1px dashed #94a3b8;
            margin: 10px 0;
        }

        .order-banner {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            padding: 6px;
            border-radius: 8px;
            text-align: center;
            margin: 6px 0;
            font-size: 13px;
            font-weight: 700;
        }

        .item-row {
            margin-bottom: 6px;
        }

        .item-name {
            font-size: 12.5px;
            font-weight: 700;
        }

        .item-note {
            font-size: 11px;
            font-weight: 400;
            font-style: italic;
            margin-left: 10px;
            margin-top: 2px;
        }

        .footer {
            text-align: center;
            margin-top: 14px;
            font-size: 12px;
            color: #64748b;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }
            .action-bar {
                display: none !important;
            }
            .receipt-card {
                box-shadow: none;
                border: none;
                padding: 0;
                max-width: 100%;
                width: 58mm;
            }
        }
    </style>
</head>
<body>

    <div class="action-bar">
        <button onclick="printDirect()" class="action-btn btn-direct">
            <svg style="width: 16px; height: 16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="m7 7 10 10-5 5V2l5 5L7 17"></path>
            </svg>
            <span>Cetak Langsung (Bluetooth/USB)</span>
        </button>
        <button onclick="window.print()" class="action-btn btn-browser">
            <span>🖨️ Cetak Tab</span>
        </button>
    </div>

    <div class="receipt-card">
        <div class="header">
            <div class="title">*** TIKET DAPUR ***</div>
            <div>{{ strtoupper($setting->shop_name ?? 'POS CAFE') }}</div>
        </div>

        <div class="divider"></div>

        <div>
            <div><strong>No. Inv:</strong> {{ $transaction->invoice_number }}</div>
            <div><strong>Waktu :</strong> {{ date('d/m/Y H:i:s', strtotime($transaction->created_at)) }}</div>
            <div><strong>Kasir :</strong> {{ $transaction->user->name ?? 'Kasir' }}</div>
        </div>

        <div class="order-banner">
            {{ (($transaction->order_type ?? 'dine_in') === 'dine_in')
                ? 'DINE IN' . ($transaction->table_number ? ' (MEJA ' . $transaction->table_number . ')' : '')
                : ((($transaction->order_type ?? '') === 'take_away') ? 'TAKE AWAY' : 'DELIVERY') }}
            @if(!empty($transaction->customer_name))
                <div style="font-size: 12px; font-weight: normal; margin-top: 4px;">
                    Pelanggan: <strong>{{ $transaction->customer_name }}</strong>
                </div>
            @endif
        </div>

        <div class="divider"></div>

        <div>
            @php $totalItems = 0; @endphp
            @foreach($transaction->details as $detail)
                @php $totalItems += (int) $detail->quantity; @endphp
                <div class="item-row">
                    <div class="item-name">{{ $detail->quantity }}x  {{ $detail->product->name ?? 'Item' }}</div>
                    @if(!empty($detail->notes))
                        <div class="item-note">>> CATATAN: {{ $detail->notes }}</div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="divider"></div>

        <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 14px;">
            <span>TOTAL ITEM:</span>
            <span>{{ $totalItems }} Menu</span>
        </div>

        <div class="divider"></div>

        <div class="footer">
            -- SEGERA DISIAPKAN --
        </div>
    </div>

    <script>
        async function printDirect() {
            if (window.posBluetooth && window.posBluetooth.isConnected) {
                try {
                    await window.posBluetooth.printKitchen('{{ $transaction->invoice_number }}');
                    alert('Tiket dapur berhasil dicetak!');
                } catch (err) {
                    alert('Gagal cetak: ' + err.message);
                }
            } else {
                alert('Printer belum terhubung. Silakan hubungkan printer di header halaman POS.');
            }
        }
    </script>
</body>
</html>
