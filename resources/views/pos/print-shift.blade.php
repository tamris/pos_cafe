<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Shift - {{ $shift->user->name ?? 'Kasir' }}</title>
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

        .divider {
            border-bottom: 1px dashed #000;
            margin: 8px 0;
            width: 100%;
        }

        .cafe-header {
            margin-bottom: 6px;
        }
        .cafe-header .title {
            font-size: 13px;
            font-weight: 900;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        .cafe-header .badge {
            font-size: 11px;
            font-weight: bold;
            margin: 4px 0 2px 0;
        }
        .cafe-header .info {
            font-size: 9px;
            line-height: 1.3;
        }

        .meta-list {
            font-size: 9.5px;
            line-height: 1.5;
            margin: 3px 0;
        }
        .meta-list .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1px;
        }

        .section-title {
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            margin: 4px 0 2px 0;
        }

        .calc-list {
            font-size: 10px;
            line-height: 1.5;
            margin: 2px 0;
        }
        .calc-list .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1px;
        }

        .highlight-box {
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 5px 0;
            margin: 5px 0;
        }
        .highlight-row {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            font-weight: 900;
        }

        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            padding: 0 4px;
            font-size: 9px;
        }
        .sig-box {
            text-align: center;
            width: 45%;
        }
        .sig-space {
            height: 35px;
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

    {{-- HEADER CAFE --}}
    <div class="cafe-header text-center">
        <div class="title uppercase">{{ $setting->shop_name ?? 'POS CAFE' }}</div>
        <div class="info">{{ $setting->address ?? 'Alamat Outlet' }}</div>
        @if (!empty($setting->phone))
            <div class="info">Telp: {{ $setting->phone }}</div>
        @endif
        <div class="divider"></div>
        <div class="badge uppercase">*** REKAP SHIFT KASIR ***</div>
        <div class="info">{{ $shift->status === 'closed' ? 'STATUS: DITUTUP (FINAL)' : 'STATUS: SHIFT AKTIF' }}</div>
    </div>

    <div class="divider"></div>

    {{-- METADATA SHIFT --}}
    <div class="meta-list">
        <div class="row">
            <span>Shift ID:</span>
            <span class="font-bold">#SFT-{{ str_pad($shift->id, 5, '0', STR_PAD_LEFT) }}</span>
        </div>
        <div class="row">
            <span>Kasir:</span>
            <span class="font-bold">{{ $shift->user->name ?? '-' }}</span>
        </div>
        <div class="row">
            <span>Buka Shift:</span>
            <span>{{ $shift->start_time ? $shift->start_time->format('d/m/y H:i') : '-' }}</span>
        </div>
        <div class="row">
            <span>Tutup Shift:</span>
            <span>{{ $shift->end_time ? $shift->end_time->format('d/m/y H:i') : '(Belum Ditutup)' }}</span>
        </div>
        @if ($shift->end_time)
        <div class="row">
            <span>Durasi:</span>
            <span>{{ $shift->start_time->diffInHours($shift->end_time) }} Jam {{ $shift->start_time->diffInMinutes($shift->end_time) % 60 }} Mnt</span>
        </div>
        @endif
    </div>

    <div class="divider"></div>

    {{-- RINGKASAN PENJUALAN --}}
    <div class="section-title">RINCIAN PENJUALAN</div>
    <div class="calc-list">
        <div class="row">
            <span>Total Struk:</span>
            <span class="font-bold">{{ number_format($shift->total_transactions, 0, ',', '.') }} Transaksi</span>
        </div>
        <div class="row">
            <span>Penjualan Tunai:</span>
            <span>Rp {{ number_format($shift->cash_sales, 0, ',', '.') }}</span>
        </div>
        <div class="row">
            <span>Penjualan QRIS:</span>
            <span>Rp {{ number_format($shift->qris_sales, 0, ',', '.') }}</span>
        </div>
        <div class="row">
            <span>Penjualan Transfer:</span>
            <span>Rp {{ number_format($shift->transfer_sales, 0, ',', '.') }}</span>
        </div>
    </div>

    <div class="highlight-box">
        <div class="highlight-row">
            <span>TOTAL OMSET:</span>
            <span>Rp {{ number_format($shift->total_sales, 0, ',', '.') }}</span>
        </div>
    </div>

    {{-- REKONSILIASI KAS LACI --}}
    <div class="section-title">REKONSILIASI KAS LACI</div>
    <div class="calc-list">
        <div class="row">
            <span>Modal Kas Awal:</span>
            <span>Rp {{ number_format($shift->starting_cash, 0, ',', '.') }}</span>
        </div>
        <div class="row">
            <span>(+) Total Uang Tunai:</span>
            <span>Rp {{ number_format($shift->cash_sales, 0, ',', '.') }}</span>
        </div>
        <div class="row font-bold" style="margin-top: 2px;">
            <span>(=) Kas Diharapkan:</span>
            <span>Rp {{ number_format($shift->expected_cash, 0, ',', '.') }}</span>
        </div>
        @if ($shift->status === 'closed')
            <div class="row font-bold">
                <span>Uang Fisik Dihitung:</span>
                <span>Rp {{ number_format($shift->actual_cash ?? 0, 0, ',', '.') }}</span>
            </div>
        @endif
    </div>

    @if ($shift->status === 'closed')
        <div class="highlight-box" style="margin-top: 3px;">
            <div class="highlight-row">
                <span>SELISIH KAS:</span>
                @php $diff = (float) ($shift->difference ?? 0); @endphp
                @if ($diff == 0)
                    <span>Rp 0 (PAS)</span>
                @elseif ($diff > 0)
                    <span>+Rp {{ number_format($diff, 0, ',', '.') }} (LEBIH)</span>
                @else
                    <span>-Rp {{ number_format(abs($diff), 0, ',', '.') }} (KURANG)</span>
                @endif
            </div>
        </div>
    @endif

    @if (!empty($shift->notes))
        <div class="meta-list" style="margin-top: 4px;">
            <div style="font-size: 9px; font-style: italic;">Catatan: {{ $shift->notes }}</div>
        </div>
    @endif

    <div class="signatures">
        <div class="sig-box">
            <div>Kasir</div>
            <div class="sig-space"></div>
            <div>( {{ $shift->user->name ?? 'Kasir' }} )</div>
        </div>
        <div class="sig-box">
            <div>Supervisor/Owner</div>
            <div class="sig-space"></div>
            <div>( .................... )</div>
        </div>
    </div>

    <div class="divider"></div>
    <div class="text-center" style="font-size: 8.5px; margin-top: 6px; color: #444;">
        Dicetak pada {{ now()->format('d/m/Y H:i:s') }}
    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
