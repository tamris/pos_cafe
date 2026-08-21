@php
    use App\Services\ReceiptPrintService;
    $rawbtBase64 = base64_encode(ReceiptPrintService::buildTransactionEscPos($transaction, $setting));
@endphp
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
            font-family: 'Consolas', 'Courier Prime', 'Lucida Console', monospace;
            font-size: 11.5px;
            font-weight: 400;
            -webkit-font-smoothing: antialiased;
            background-color: #f1f5f9;
            color: #000;
            line-height: 1.35;
            padding: 20px 10px;
            margin: 0 auto;
        }

        /* FLOATING ACTION BAR (HANYA MUNCUL DI LAYAR, TIDAK IKUT TERCETAK) */
        .action-bar {
            max-width: 380px;
            margin: 0 auto 16px auto;
            display: flex;
            gap: 8px;
            background: #ffffff;
            padding: 10px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
        }
        .action-btn {
            flex: 1;
            padding: 10px 8px;
            border: none;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-rawbt {
            background-color: #059669;
            color: #ffffff;
        }
        .btn-rawbt:hover {
            background-color: #047857;
        }
        .btn-browser {
            background-color: #475569;
            color: #ffffff;
        }
        .btn-browser:hover {
            background-color: #334155;
        }
        .btn-close {
            background-color: #e2e8f0;
            color: #334155;
            flex: 0 0 auto;
            padding: 10px 14px;
        }

        /* PAPER RECEIPT CARD */
        .receipt-card {
            width: 100%;
            max-width: 58mm;
            background-color: #ffffff;
            margin: 0 auto;
            padding: 12px 6px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            border-radius: 4px;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: 700; }
        .font-normal { font-weight: 400; }
        .uppercase { text-transform: uppercase; }

        /* GARIS PEMISAH DENGAN JARAK PAS */
        .divider {
            border-bottom: 1px dashed #000;
            margin: 6px 0;
            width: 100%;
        }

        /* HEADER */
        .cafe-header {
            margin-bottom: 6px;
        }
        .logo-wrapper {
            margin: 0 auto 8px auto;
            text-align: center;
        }
        .logo-wrapper img {
            max-height: 38px;
            max-width: 140px;
            margin: 0 auto;
            display: block;
            object-fit: contain;
            filter: grayscale(100%) contrast(150%);
        }
        .cafe-header .title {
            font-size: 14px;
            font-weight: 400; /* TIDAK BOLD */
            letter-spacing: 0.5px;
            margin-bottom: 5px;
            line-height: 1.25;
        }
        .cafe-header .info {
            font-size: 11px;
            line-height: 1.3;
            font-weight: 400;
            margin-bottom: 2px;
        }

        /* METADATA */
        .meta-list {
            font-size: 12.5px;
            line-height: 1.35;
            margin: 2px 0;
        }
        .meta-list .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }

        /* DAFTAR ITEM PESANAN */
        .item-table {
            margin: 2px 0;
        }
        .item-block {
            margin-bottom: 4px;
        }
        .item-name {
            font-weight: 400; /* TIDAK BOLD */
            font-size: 12px;
            line-height: 1.25;
            margin-bottom: 1px;
            word-break: break-word;
        }
        .item-calc {
            display: flex;
            justify-content: space-between;
            font-size: 11.5px;
            font-weight: 400;
            line-height: 1.25;
        }
        .item-notes {
            font-size: 11.5px;
            font-style: italic;
            padding-left: 4px;
            color: #111;
            margin-top: 1px;
        }

        /* KALKULASI & TOTAL */
        .total-row {
            font-size: 14px;
            font-weight: 700;
            padding: 4px 0;
            display: flex;
            justify-content: space-between;
        }
        .payment-row {
            font-size: 11.5px;
            font-weight: 400;
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }
        .grand-total {
            display: flex;
            justify-content: space-between;
            font-size: 16px;
            font-weight: 700;
            padding: 4px 0;
            margin: 4px 0;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
        }

        /* WIFI & FOOTER */
        .wifi-inline {
            font-size: 11.5px;
            margin: 4px 0;
            line-height: 1.3;
        }
        .footer {
            margin-top: 8px;
            font-size: 11.5px;
            line-height: 1.3;
        }

        @media print {
            @page {
                margin: 0mm;
                size: 58mm 297mm;
            }
            body {
                background-color: #fff !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
            }
            .action-bar {
                display: none !important;
            }
            .receipt-card {
                max-width: 58mm !important;
                width: 100% !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                padding: 2px 2px !important;
                margin: 0 !important;
            }
        }
    </style>
</head>
<body>

    {{-- TOMBOL AKSI CEPAT DI ATAS STRUK --}}
    <div class="action-bar">
        <button onclick="printBluetoothDirect()" class="action-btn btn-rawbt" style="background-color: #2563eb;" title="Cetak Langsung via Web Bluetooth">
            <svg style="width: 14px; height: 14px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="m7 7 10 10-5 5V2l5 5L7 17"></path>
            </svg>
            <span>Bluetooth Print</span>
        </button>
        <button onclick="printRawBT()" class="action-btn btn-rawbt" title="Cetak via RawBT Android">
            <span>⚡</span>
            <span>RawBT</span>
        </button>
        <button onclick="printBrowser()" class="action-btn btn-browser" title="Cetak via Dialog Browser">
            <span>🖨️</span>
            <span>Browser</span>
        </button>
        <button onclick="window.close()" class="action-btn btn-close" title="Tutup Halaman">
            <span>✕</span>
        </button>
    </div>

    {{-- KONTEN STRUK 58MM --}}
    <div class="receipt-card">
        {{-- 1. HEADER CAFE & LOGO --}}
        <div class="cafe-header text-center">
            {{-- LOGO (JIKA ADA) --}}
            @if (($setting->show_logo_receipt ?? true) && !empty($setting->shop_logo))
                <div class="logo-wrapper">
                    <img src="{{ Storage::url($setting->shop_logo) }}" alt="Logo Cafe">
                </div>
            @endif

            <div class="title">{{ strtoupper($setting->shop_name ?? 'POS CAFE') }}</div>
            
            @if (!empty($setting->address))
                <div class="info">{{ $setting->address }}</div>
            @endif
            @if (!empty($setting->phone))
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

        @if($transaction->status === 'pending')
            <div class="text-center font-bold" style="padding: 6px 0; margin: 4px 0; border: 1.5px dashed #000; font-size: 12px;">
                *** TAGIHAN SEMENTARA ***<br>
                <span style="font-size: 10px; font-weight: normal;">(BELUM LUNAS / OPEN BILL)</span>
            </div>
        @else
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
        @endif

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
            <div style="font-size: 10px; color: #555; margin-top: 4px; letter-spacing: 0.5px;">-- Have a Good Coffee Day --</div>
        </div>
    </div>

    <script src="{{ asset('js/bluetooth-printer.js') }}"></script>
    <script>
        const rawbtData = @json($rawbtBase64);

        async function printBluetoothDirect() {
            try {
                if (!window.posBluetooth.isConnected) {
                    await window.posBluetooth.connect();
                }
                const uint8Array = window.posBluetooth.base64ToUint8Array(rawbtData);
                await window.posBluetooth.printRawData(uint8Array);
            } catch (err) {
                alert('Bluetooth Print: ' + err.message);
            }
        }

        function printRawBT() {
            // Format resmi RawBT Android Intent (package: ru.a402d.rawbtprinter)
            const intentUrl = "rawbt:base64," + rawbtData + "#Intent;scheme=rawbt;package=ru.a402d.rawbtprinter;end;";
            window.location.href = intentUrl;
        }

        function printBrowser() {
            window.print();
        }

        // Auto-print saat halaman dibuka
        window.addEventListener('DOMContentLoaded', async () => {
            // Jika Web Bluetooth sudah terkoneksi, langsung tembak ke printer
            if (window.posBluetooth && window.posBluetooth.isConnected) {
                await printBluetoothDirect();
                return;
            }

            const isAndroid = /Android/i.test(navigator.userAgent);
            if (isAndroid) {
                printRawBT();
            } else {
                window.print();
            }
        });
    </script>
</body>
</html>