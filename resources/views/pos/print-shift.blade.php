@php
    if (!function_exists('escposLine32')) {
        function escposLine32($left, $right, $width = 32) {
            $left = (string) $left;
            $right = (string) $right;
            $leftLen = strlen($left);
            $rightLen = strlen($right);
            if ($leftLen + $rightLen >= $width) {
                return $left . " " . $right;
            }
            $spaces = $width - $leftLen - $rightLen;
            return $left . str_repeat(' ', $spaces) . $right;
        }
    }

    $esc = "\x1b";
    $gs = "\x1d";
    
    $raw = $esc . "@";
    $raw .= $esc . "a\x01"; // Center
    $raw .= $esc . "!\x30" . ($setting->shop_name ?? 'POS CAFE') . "\n" . $esc . "!\x00";
    if (!empty($setting->address)) {
        $raw .= $setting->address . "\n";
    }
    if (!empty($setting->phone)) {
        $raw .= "Telp: " . $setting->phone . "\n";
    }
    $raw .= "--------------------------------\n";
    $raw .= $esc . "E\x01" . "*** REKAP SHIFT KASIR ***\n" . $esc . "E\x00";
    $raw .= ($shift->status === 'closed' ? 'STATUS: DITUTUP (FINAL)' : 'STATUS: SHIFT AKTIF') . "\n";
    $raw .= "--------------------------------\n";
    
    // Meta
    $raw .= $esc . "a\x00"; // Left
    $raw .= escposLine32("Shift ID", "#SFT-" . str_pad($shift->id, 5, '0', STR_PAD_LEFT)) . "\n";
    $raw .= escposLine32("Kasir", $shift->user->name ?? '-') . "\n";
    $raw .= escposLine32("Buka Shift", $shift->start_time ? $shift->start_time->format('d/m/y H:i') : '-') . "\n";
    $raw .= escposLine32("Tutup Shift", $shift->end_time ? $shift->end_time->format('d/m/y H:i') : '(Belum Ditutup)') . "\n";
    if ($shift->end_time) {
        $durStr = $shift->start_time->diffInHours($shift->end_time) . " Jam " . ($shift->start_time->diffInMinutes($shift->end_time) % 60) . " Mnt";
        $raw .= escposLine32("Durasi", $durStr) . "\n";
    }
    $raw .= "--------------------------------\n";
    
    // Penjualan
    $raw .= $esc . "E\x01" . "RINCIAN PENJUALAN\n" . $esc . "E\x00";
    $raw .= escposLine32("Total Struk", number_format($shift->total_transactions, 0, ',', '.') . " Trx") . "\n";
    $raw .= escposLine32("Penjualan Tunai", "Rp " . number_format($shift->cash_sales, 0, ',', '.')) . "\n";
    $raw .= escposLine32("Penjualan QRIS", "Rp " . number_format($shift->qris_sales, 0, ',', '.')) . "\n";
    $raw .= escposLine32("Penjualan Transfer", "Rp " . number_format($shift->transfer_sales, 0, ',', '.')) . "\n";
    $raw .= "--------------------------------\n";
    
    // Total Omset
    $raw .= $esc . "!\x10" . $esc . "E\x01";
    $raw .= escposLine32("TOTAL OMSET", "Rp " . number_format($shift->total_sales, 0, ',', '.')) . "\n";
    $raw .= $esc . "!\x00" . $esc . "E\x00";
    $raw .= "--------------------------------\n";
    
    // Kas Laci
    $raw .= $esc . "E\x01" . "REKONSILIASI KAS LACI\n" . $esc . "E\x00";
    $raw .= escposLine32("Modal Kas Awal", "Rp " . number_format($shift->starting_cash, 0, ',', '.')) . "\n";
    $raw .= escposLine32("(+) Total Tunai", "Rp " . number_format($shift->cash_sales, 0, ',', '.')) . "\n";
    $raw .= $esc . "E\x01" . escposLine32("(=) Kas Harapan", "Rp " . number_format($shift->expected_cash, 0, ',', '.')) . "\n" . $esc . "E\x00";
    if ($shift->status === 'closed') {
        $raw .= $esc . "E\x01" . escposLine32("Uang Fisik Kas", "Rp " . number_format($shift->actual_cash ?? 0, 0, ',', '.')) . "\n" . $esc . "E\x00";
        $diff = (float) ($shift->difference ?? 0);
        $diffStr = $diff == 0 ? "Rp 0 (PAS)" : ($diff > 0 ? "+Rp " . number_format($diff, 0, ',', '.') . " (LEBIH)" : "-Rp " . number_format(abs($diff), 0, ',', '.') . " (KURANG)");
        $raw .= "--------------------------------\n";
        $raw .= $esc . "E\x01" . escposLine32("SELISIH KAS", $diffStr) . "\n" . $esc . "E\x00";
    }
    
    if (!empty($shift->notes)) {
        $raw .= "Catatan: " . $shift->notes . "\n";
    }
    
    $raw .= "--------------------------------\n";
    $raw .= $esc . "a\x01"; // Center
    $raw .= "Dicetak pada " . now()->format('d/m/Y H:i:s') . "\n";
    $raw .= "\n\n\n\n";
    $raw .= $gs . "V\x41\x03";
    
    $rawbtShiftBase64 = base64_encode($raw);
@endphp
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
            font-size: 13.5px;
            font-weight: 700;
            background-color: #f1f5f9;
            color: #000;
            line-height: 1.35;
            padding: 20px 10px;
            margin: 0 auto;
        }

        /* FLOATING ACTION BAR */
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
        .font-bold { font-weight: 900; }
        .uppercase { text-transform: uppercase; }

        .divider {
            border-bottom: 1.5px dashed #000;
            margin: 6px 0;
            width: 100%;
        }

        .cafe-header {
            margin-bottom: 4px;
        }
        .cafe-header .title {
            font-size: 16px;
            font-weight: 900;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        .cafe-header .badge {
            font-size: 13px;
            font-weight: 900;
            margin: 3px 0 1px 0;
        }
        .cafe-header .info {
            font-size: 11.5px;
            line-height: 1.25;
        }

        .meta-list {
            font-size: 12.5px;
            line-height: 1.35;
            margin: 2px 0;
        }
        .meta-list .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1px;
        }

        .section-title {
            font-size: 13px;
            font-weight: 900;
            text-transform: uppercase;
            margin: 3px 0 1px 0;
        }

        .calc-list {
            font-size: 12.5px;
            line-height: 1.35;
            margin: 2px 0;
        }
        .calc-list .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1px;
        }

        .highlight-box {
            border-top: 1.5px dashed #000;
            border-bottom: 1.5px dashed #000;
            padding: 4px 0;
            margin: 4px 0;
        }
        .highlight-row {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            font-weight: 900;
        }

        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 16px;
            padding: 0 4px;
            font-size: 11.5px;
        }
        .sig-box {
            text-align: center;
            width: 45%;
        }
        .sig-space {
            height: 30px;
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
        <button onclick="printRawBT()" class="action-btn btn-rawbt" title="Cetak Langsung via RawBT Android">
            <span>⚡</span>
            <span>Cetak RawBT</span>
        </button>
        <button onclick="printBrowser()" class="action-btn btn-browser" title="Cetak via Dialog Browser">
            <span>🖨️</span>
            <span>Browser Print</span>
        </button>
        <button onclick="window.close()" class="action-btn btn-close" title="Tutup Halaman">
            <span>✕</span>
        </button>
    </div>

    {{-- KONTEN STRUK 58MM --}}
    <div class="receipt-card">
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
                <div style="font-size: 11px; font-style: italic;">Catatan: {{ $shift->notes }}</div>
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
        <div class="text-center" style="font-size: 10px; margin-top: 6px; color: #444;">
            Dicetak pada {{ now()->format('d/m/Y H:i:s') }}
        </div>
    </div>

    <script>
        const rawbtShiftData = @json($rawbtShiftBase64);

        function printRawBT() {
            // Format resmi RawBT Android Intent (package: ru.a402d.rawbtprinter)
            const intentUrl = "rawbt:base64," + rawbtShiftData + "#Intent;scheme=rawbt;package=ru.a402d.rawbtprinter;end;";
            window.location.href = intentUrl;
        }

        function printBrowser() {
            window.print();
        }

        // Auto-print saat halaman dibuka
        window.addEventListener('DOMContentLoaded', () => {
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
