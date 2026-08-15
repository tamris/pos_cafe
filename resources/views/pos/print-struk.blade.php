<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk - {{ $transaction->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            width: 58mm;
            background-color: #fff;
            color: #000;
            line-height: 1.25;
        }

        .container {
            padding: 8px 4px;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        .mb-1 { margin-bottom: 4px; }
        
        .border-dashed {
            border-bottom: 1px dashed #000;
            margin: 6px 0;
        }

        .cafe-logo {
            font-size: 20px;
            margin-bottom: 2px;
        }

        .order-badge {
            border: 1.5px solid #000;
            padding: 5px 2px;
            text-align: center;
            font-weight: bold;
            font-size: 12px;
            margin: 8px 0;
            letter-spacing: 0.5px;
        }

        .meta-table {
            width: 100%;
            font-size: 10px;
            margin-bottom: 4px;
        }
        
        .item-block {
            margin-bottom: 5px;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
        }

        .item-notes {
            font-size: 9px;
            font-style: italic;
            margin-left: 6px;
            color: #222;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
            font-size: 11px;
        }

        .grand-total {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            font-weight: bold;
            padding: 4px 0;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            margin: 4px 0;
        }

        .wifi-box {
            border: 1px dashed #000;
            padding: 6px 4px;
            margin: 8px 0;
            font-size: 10px;
            line-height: 1.3;
            background-color: #fafafa;
        }

        @media print {
            @page {
                margin: 0;
                size: 58mm auto;
            }
            body {
                margin: 0;
            }
            .wifi-box {
                background-color: transparent;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        {{-- HEADER CAFE --}}
        <div class="text-center">
            <div class="cafe-logo">☕</div>
            <h2 class="font-bold uppercase" style="font-size: 15px;">{{ $setting->shop_name ?? 'CAFE & EATERY' }}</h2>
            <p style="font-size: 9.5px; margin-top: 2px;">{{ $setting->address ?? 'Coffee & Eatery' }}</p>
            <p style="font-size: 9.5px;">Telp: {{ $setting->phone ?? '-' }}</p>
        </div>

        <div class="border-dashed"></div>

        {{-- BADGE TIPE PESANAN --}}
        <div class="order-badge uppercase">
            [ {{ str_replace('_', ' ', $transaction->order_type) }}
            @if($transaction->order_type === 'dine_in' && $transaction->table_number)
                - MEJA {{ $transaction->table_number }}
            @endif ]
        </div>

        {{-- METADATA TRANSAKSI --}}
        <div class="meta-table">
            <div style="display: flex; justify-content: space-between;">
                <span>No Inv: {{ $transaction->invoice_number }}</span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span>Waktu : {{ date('d/m/Y H:i', strtotime($transaction->created_at)) }}</span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span>Barista: {{ auth()->user()->name ?? 'Staff Cafe' }}</span>
            </div>
            @if($transaction->customer_name)
                <div style="display: flex; justify-content: space-between;">
                    <span>Pelanggan: <strong>{{ $transaction->customer_name }}</strong></span>
                </div>
            @endif
        </div>

        <div class="border-dashed"></div>

        {{-- DETAIL PESANAN --}}
        @foreach($transaction->details as $detail)
            <div class="item-block">
                <p class="font-bold">{{ $detail->product->name }}</p>
                <div class="item-row">
                    <span>{{ $detail->quantity }} x {{ number_format($detail->price, 0, ',', '.') }}</span>
                    <span>{{ number_format($detail->subtotal, 0, ',', '.') }}</span>
                </div>
                @if($detail->notes)
                    <p class="item-notes">* {{ $detail->notes }}</p>
                @endif
            </div>
        @endforeach

        <div class="border-dashed"></div>

        {{-- PERHITUNGAN HARGA --}}
        <div class="total-row">
            <span>Subtotal:</span>
            <span>Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</span>
        </div>
        
        @if($transaction->discount > 0)
        <div class="total-row">
            <span>Diskon:</span>
            <span>-Rp {{ number_format($transaction->discount, 0, ',', '.') }}</span>
        </div>
        @endif

        @if($transaction->tax > 0)
        <div class="total-row">
            <span>Pajak:</span>
            <span>+Rp {{ number_format($transaction->tax, 0, ',', '.') }}</span>
        </div>
        @endif

        <div class="grand-total">
            <span>TOTAL:</span>
            <span>Rp {{ number_format($transaction->total, 0, ',', '.') }}</span>
        </div>
        
        <div class="total-row">
            <span>Bayar ({{ strtoupper($transaction->payment_method) }}):</span>
            <span>Rp {{ number_format($transaction->paid, 0, ',', '.') }}</span>
        </div>
        <div class="total-row">
            <span>Kembali:</span>
            <span>Rp {{ number_format($transaction->change, 0, ',', '.') }}</span>
        </div>

        {{-- SECTION WIFI CAFE --}}
        @if(!empty($setting->wifi_name) || !empty($setting->wifi_password))
            <div class="wifi-box text-center">
                <p class="font-bold" style="letter-spacing: 0.5px;">📶 FREE WIFI CAFE</p>
                @if(!empty($setting->wifi_name))
                    <p>SSID: <span class="font-bold">{{ $setting->wifi_name }}</span></p>
                @endif
                @if(!empty($setting->wifi_password))
                    <p>PASS: <span class="font-bold">{{ $setting->wifi_password }}</span></p>
                @endif
            </div>
        @else
            <div class="border-dashed"></div>
        @endif

        {{-- FOOTER STRUK --}}
        <div class="text-center" style="margin-top: 6px;">
            <p class="font-bold uppercase" style="font-size: 11px;">{{ $setting->shop_name ?? 'CAFE & EATERY' }}</p>
            <p style="font-size: 9.5px; margin-top: 2px; white-space: pre-line;">{{ $setting->receipt_footer ?? 'Terima Kasih Atas Kunjungan Anda!' }}</p>
            <p style="font-size: 8.5px; margin-top: 6px; letter-spacing: 0.5px;">-- PAUSED TO REFRESH & ENJOY --</p>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>