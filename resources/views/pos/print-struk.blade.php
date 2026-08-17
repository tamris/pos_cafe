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
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        body {
            font-family: 'Courier New', Courier, monospace, 'Lucida Console';
            font-size: 11px;
            width: 58mm;
            background-color: #fff;
            color: #000;
            line-height: 1.45;
            padding: 12px 6px;
            margin: 0 auto;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: 900; }
        .uppercase { text-transform: uppercase; }

        /* GARIS PEMISAH DENGAN JARAK LEGA */
        .divider {
            border-bottom: 1px dashed #000;
            margin: 10px 0;
            width: 100%;
        }

        /* HEADER */
        .cafe-header {
            margin-bottom: 8px;
        }
        .cafe-header .title {
            font-size: 14px;
            font-weight: 900;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }
        .cafe-header .info {
            font-size: 9.5px;
            line-height: 1.35;
        }

        /* METADATA */
        .meta-list {
            font-size: 10px;
            line-height: 1.6;
            margin: 4px 0;
        }
        .meta-list .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }

        /* DAFTAR ITEM PESANAN */
        .item-table {
            margin: 4px 0;
        }
        .item-block {
            margin-bottom: 8px;
        }
        .item-name {
            font-weight: 900;
            font-size: 11px;
            margin-bottom: 2px;
        }
        .item-calc {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
        }
        .item-notes {
            font-size: 9px;
            font-style: italic;
            padding-left: 6px;
            color: #333;
            margin-top: 2px;
        }

        /* KALKULASI & TOTAL */
        .calc-list {
            font-size: 10.5px;
            line-height: 1.6;
            margin: 4px 0;
        }
        .calc-list .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }
        .grand-total {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            font-weight: 900;
            padding: 6px 0;
            margin: 6px 0;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
        }

        /* WIFI & FOOTER */
        .wifi-inline {
            font-size: 9.5px;
            margin: 8px 0;
            line-height: 1.4;
        }
        .footer {
            margin-top: 12px;
            font-size: 9.5px;
            line-height: 1.4;
        }

        @media print {
            @page {
                margin: 0;
                size: 58mm auto;
            }
            body {
                width: 100%;
                padding: 8px 4px;
            }
        }
    </style>
</head>
<body>

    {{-- 1. HEADER CAFE --}}
    <div class="cafe-header text-center">
        <div class="title uppercase">{{ $setting->shop_name ?? 'POS CAFE & ROASTERY' }}</div>
        @if(!empty($setting->address))
            <div class="info">{{ $setting->address }}</div>
        @endif
        @if(!empty($setting->phone))
            <div class="info">Telp: {{ $setting->phone }}</div>
        @endif
    </div>

    <div class="divider"></div>

    {{-- 2. METADATA TRANSAKSI & TIPE PESANAN --}}
    <div class="meta-list">
        <div class="row">
            <span>No. Inv</span>
            <span class="font-bold">{{ $transaction->invoice_number }}</span>
        </div>
        <div class="row">
            <span>Waktu</span>
            <span>{{ date('d/m/Y H:i', strtotime($transaction->created_at)) }}</span>
        </div>
        <div class="row">
            <span>Kasir</span>
            <span>{{ $transaction->user->name ?? (auth()->user()->name ?? 'Staff') }}</span>
        </div>
        @if(!empty($transaction->table_number) || !empty($transaction->customer_name))
            <div class="row">
                <span>Pesanan</span>
                <span class="font-bold uppercase">
                    @if(($transaction->order_type ?? 'dine_in') === 'dine_in')
                        DINE IN {{ $transaction->table_number ? '(MEJA '.$transaction->table_number.')' : '' }}
                    @elseif(($transaction->order_type ?? '') === 'take_away')
                        TAKE AWAY
                    @else
                        DELIVERY
                    @endif
                </span>
            </div>
            @if(!empty($transaction->customer_name))
                <div class="row">
                    <span>Pelanggan</span>
                    <span class="font-bold">{{ $transaction->customer_name }}</span>
                </div>
            @endif
        @endif
    </div>

    <div class="divider"></div>

    {{-- 3. DAFTAR MENU / DETAIL ITEMS --}}
    <div class="item-table">
        @foreach($transaction->details as $detail)
            <div class="item-block">
                <div class="item-name">{{ $detail->product->name }}</div>
                <div class="item-calc">
                    <span>{{ $detail->quantity }} x {{ number_format($detail->price, 0, ',', '.') }}</span>
                    <span class="font-bold">{{ number_format($detail->subtotal, 0, ',', '.') }}</span>
                </div>
                @if($detail->notes)
                    <div class="item-notes">* {{ $detail->notes }}</div>
                @endif
            </div>
        @endforeach
    </div>

    <div class="divider"></div>

    {{-- 4. PERHITUNGAN PEMBAYARAN --}}
    <div class="calc-list">
        <div class="row">
            <span>Subtotal</span>
            <span>{{ number_format($transaction->subtotal, 0, ',', '.') }}</span>
        </div>

        @if($transaction->discount > 0)
            @php
                $discountNominal = ($transaction->discount <= 100) 
                    ? ($transaction->subtotal * $transaction->discount / 100) 
                    : $transaction->discount;
            @endphp
            <div class="row">
                <span>Diskon {{ $transaction->discount <= 100 ? '('.$transaction->discount.'%)' : '' }}</span>
                <span>-{{ number_format($discountNominal, 0, ',', '.') }}</span>
            </div>
        @endif

        @if($transaction->tax > 0)
            @php
                $taxNominal = ($transaction->tax <= 100) 
                    ? ($transaction->subtotal * $transaction->tax / 100) 
                    : $transaction->tax;
            @endphp
            <div class="row">
                <span>Pajak {{ $transaction->tax <= 100 ? '('.$transaction->tax.'%)' : '' }}</span>
                <span>+{{ number_format($taxNominal, 0, ',', '.') }}</span>
            </div>
        @endif
    </div>

    <div class="grand-total">
        <span>TOTAL</span>
        <span>Rp {{ number_format($transaction->total, 0, ',', '.') }}</span>
    </div>

    <div class="calc-list">
        <div class="row">
            <span>Bayar ({{ strtoupper($transaction->payment_method === 'cash' ? 'TUNAI' : ($transaction->payment_method === 'transfer' ? 'TRANSFER' : 'QRIS')) }})</span>
            <span>{{ number_format($transaction->paid, 0, ',', '.') }}</span>
        </div>
        <div class="row font-bold">
            <span>Kembali</span>
            <span>{{ number_format($transaction->change, 0, ',', '.') }}</span>
        </div>
    </div>

    {{-- 5. WIFI CAFE (JIKA ADA) --}}
    @if(!empty($setting->wifi_name) || !empty($setting->wifi_password))
        <div class="divider"></div>
        <div class="wifi-inline text-center">
            <span>WiFi: <strong>{{ $setting->wifi_name ?? '-' }}</strong></span>
            @if(!empty($setting->wifi_password))
                <span> | Pass: <strong>{{ $setting->wifi_password }}</strong></span>
            @endif
        </div>
    @endif

    <div class="divider"></div>

    {{-- 6. FOOTER --}}
    <div class="footer text-center">
        <div>{{ $setting->receipt_footer ?? 'Terima kasih atas kunjungannya!' }}</div>
        <div style="font-size: 8px; color: #555; margin-top: 4px; letter-spacing: 0.5px;">-- Have a Good Coffee Day --</div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>