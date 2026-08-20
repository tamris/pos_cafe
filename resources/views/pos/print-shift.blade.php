@php
    use App\Services\ReceiptPrintService;
    $rawbtShiftBase64 = base64_encode(ReceiptPrintService::buildShiftEscPos($shift, $setting));

    // Hitung durasi kerja secara akurat (tanpa pecahan desimal)
    $durationText = '-';
    if ($shift->start_time && $shift->end_time) {
        $totalMinutes = (int) $shift->start_time->diffInMinutes($shift->end_time);
        $hours = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;
        $durationText = ($hours > 0 ? "{$hours} Jam " : "") . "{$minutes} Menit";
    } elseif ($shift->start_time) {
        $totalMinutes = (int) $shift->start_time->diffInMinutes(now());
        $hours = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;
        $durationText = ($hours > 0 ? "{$hours} Jam " : "") . "{$minutes} Menit (Berjalan)";
    }
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Shift #SFT-{{ str_pad($shift->id, 5, '0', STR_PAD_LEFT) }} - {{ $shift->user->name ?? 'Kasir' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 12px;
            color: #1e293b;
            background-color: #f1f5f9;
            line-height: 1.4;
            padding: 24px 12px;
            margin: 0 auto;
        }

        /* FLOATING ACTION BAR (SCREEN ONLY) */
        .action-bar {
            max-width: 380px;
            margin: 0 auto 16px auto;
            display: flex;
            gap: 8px;
            background: #ffffff;
            padding: 10px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
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
        .btn-bt {
            background-color: #2563eb;
            color: #ffffff;
        }
        .btn-bt:hover {
            background-color: #1d4ed8;
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
        .btn-close:hover {
            background-color: #cbd5e1;
        }

        /* REPORT SLIP CARD */
        .report-card {
            width: 100%;
            max-width: 76mm;
            background-color: #ffffff;
            margin: 0 auto;
            padding: 18px 14px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.07);
            border-radius: 10px;
            border: 1px solid #e2e8f0;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: 700; }
        .font-black { font-weight: 900; }
        .uppercase { text-transform: uppercase; }

        /* DIVIDER */
        .divider {
            border-bottom: 1px dashed #cbd5e1;
            margin: 8px 0;
            width: 100%;
        }
        .divider-solid {
            border-bottom: 1.5px solid #0f172a;
            margin: 10px 0;
            width: 100%;
        }

        /* HEADER */
        .header {
            text-align: center;
            margin-bottom: 8px;
        }
        .header .logo {
            max-height: 48px;
            max-width: 130px;
            margin: 0 auto 6px auto;
            display: block;
            object-fit: contain;
        }
        .header .shop-name {
            font-size: 15px;
            font-weight: 900;
            letter-spacing: 0.5px;
            color: #0f172a;
            line-height: 1.2;
        }
        .header .shop-info {
            font-size: 11px;
            color: #64748b;
            line-height: 1.35;
            margin-top: 2px;
        }

        .report-badge {
            display: inline-block;
            background-color: #0f172a;
            color: #ffffff;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.75px;
            padding: 3px 8px;
            border-radius: 4px;
            margin: 6px 0 3px 0;
            text-transform: uppercase;
        }
        .status-badge {
            font-size: 10.5px;
            font-weight: 700;
            color: #475569;
        }
        .status-closed {
            color: #059669;
            font-weight: 800;
        }

        /* SECTION TITLE */
        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 11px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 8px 0 4px 0;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 2px;
        }

        /* ROWS */
        .row-list {
            margin: 3px 0;
        }
        .data-row {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            font-size: 11.5px;
            margin-bottom: 3px;
            line-height: 1.35;
        }
        .data-row .lbl {
            color: #475569;
            flex: 1;
            padding-right: 6px;
        }
        .data-row .val {
            color: #0f172a;
            font-weight: 600;
            text-align: right;
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
        }
        .data-row.strong .lbl {
            font-weight: 700;
            color: #0f172a;
        }
        .data-row.strong .val {
            font-weight: 800;
            color: #0f172a;
        }

        /* HIGHLIGHT BOX (OMSET & SELISIH) */
        .highlight-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 6px 8px;
            margin: 6px 0;
        }
        .highlight-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12.5px;
            font-weight: 900;
            color: #0f172a;
        }

        .diff-tag {
            font-weight: 800;
            font-size: 11.5px;
            padding: 2px 6px;
            border-radius: 4px;
        }
        .diff-pas {
            background-color: #dcfce7;
            color: #15803d;
        }
        .diff-lebih {
            background-color: #dbeafe;
            color: #1d4ed8;
        }
        .diff-kurang {
            background-color: #fee2e2;
            color: #b91c1c;
        }

        /* NOTES */
        .notes-box {
            background-color: #fffbeb;
            border: 1px dashed #fde68a;
            border-radius: 6px;
            padding: 6px 8px;
            font-size: 10.5px;
            color: #92400e;
            margin-top: 6px;
        }

        /* SIGNATURES */
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 18px;
            padding: 0 4px;
        }
        .sig-col {
            width: 45%;
            text-align: center;
            font-size: 10.5px;
        }
        .sig-col .sig-title {
            color: #64748b;
            font-weight: 600;
            margin-bottom: 30px;
        }
        .sig-col .sig-name {
            border-top: 1px solid #94a3b8;
            padding-top: 3px;
            font-weight: 700;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* FOOTER */
        .footer-text {
            text-align: center;
            font-size: 10px;
            color: #94a3b8;
            margin-top: 12px;
        }

        /* PRINT STYLES */
        @media print {
            @page {
                margin: 0;
                size: 58mm auto;
            }
            body {
                background-color: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
                color: #000000 !important;
                font-size: 11px !important;
            }
            .action-bar {
                display: none !important;
            }
            .report-card {
                max-width: 58mm !important;
                width: 100% !important;
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
                padding: 4px 2px !important;
                margin: 0 !important;
            }
            .report-badge {
                background-color: transparent !important;
                color: #000000 !important;
                border: 1px solid #000000 !important;
                padding: 2px 4px !important;
            }
            .highlight-box {
                background-color: transparent !important;
                border: 1px dashed #000000 !important;
            }
            .divider {
                border-bottom: 1px dashed #000000 !important;
            }
            .divider-solid {
                border-bottom: 1.5px solid #000000 !important;
            }
            .diff-pas, .diff-lebih, .diff-kurang {
                background-color: transparent !important;
                color: #000000 !important;
                border: 1px solid #000000 !important;
            }
            .notes-box {
                background-color: transparent !important;
                border: 1px dashed #000000 !important;
                color: #000000 !important;
            }
        }
    </style>
</head>
<body>

    {{-- TOMBOL AKSI CEPAT DI ATAS STRUK --}}
    <div class="action-bar">
        <button onclick="printBluetoothDirect()" class="action-btn btn-bt" title="Cetak Langsung via Web Bluetooth">
            <svg style="width: 14px; height: 14px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="m7 7 10 10-5 5V2l5 5L7 17"></path>
            </svg>
            <span>Bluetooth</span>
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

    {{-- KONTEN LAPORAN REKAP SHIFT --}}
    <div class="report-card">
        {{-- HEADER OUTLET --}}
        <div class="header">
            @if (($setting->show_logo_receipt ?? true) && !empty($setting->shop_logo))
                <img src="{{ asset('storage/' . $setting->shop_logo) }}" alt="Logo Cafe" class="logo">
            @endif
            <div class="shop-name uppercase">{{ $setting->shop_name ?? 'POS CAFE & INVENTORY' }}</div>
            <div class="shop-info">{{ $setting->address ?? 'Alamat Outlet' }}</div>
            @if (!empty($setting->phone))
                <div class="shop-info">Telp: {{ $setting->phone }}</div>
            @endif
            
            <div class="divider"></div>
            <div class="report-badge">REKAP SHIFT KASIR</div>
            <div class="status-badge {{ $shift->status === 'closed' ? 'status-closed' : '' }}">
                {{ $shift->status === 'closed' ? '● STATUS: DITUTUP (FINAL)' : '● STATUS: SHIFT AKTIF' }}
            </div>
        </div>

        <div class="divider-solid"></div>

        {{-- 1. INFORMASI SHIFT --}}
        <div class="row-list">
            <div class="data-row">
                <span class="lbl">No. Shift:</span>
                <span class="val font-black">#SFT-{{ str_pad($shift->id, 5, '0', STR_PAD_LEFT) }}</span>
            </div>
            <div class="data-row">
                <span class="lbl">Kasir Bertugas:</span>
                <span class="val font-bold">{{ $shift->user->name ?? '-' }}</span>
            </div>
            <div class="data-row">
                <span class="lbl">Waktu Buka:</span>
                <span class="val">{{ $shift->start_time ? $shift->start_time->format('d/m/Y H:i') : '-' }}</span>
            </div>
            <div class="data-row">
                <span class="lbl">Waktu Tutup:</span>
                <span class="val">{{ $shift->end_time ? $shift->end_time->format('d/m/Y H:i') : '(Belum Ditutup)' }}</span>
            </div>
            <div class="data-row strong">
                <span class="lbl">Durasi Shift:</span>
                <span class="val">{{ $durationText }}</span>
            </div>
        </div>

        <div class="divider"></div>

        {{-- 2. RINCIAN TRANSAKSI & PENJUALAN --}}
        <div class="section-header">
            <span>Rincian Penjualan</span>
        </div>
        <div class="row-list">
            <div class="data-row">
                <span class="lbl">Total Struk / Transaksi:</span>
                <span class="val font-bold">{{ number_format($shift->total_transactions, 0, ',', '.') }} Transaksi</span>
            </div>
            <div class="data-row">
                <span class="lbl">Penjualan Tunai (Cash):</span>
                <span class="val">Rp {{ number_format($shift->cash_sales, 0, ',', '.') }}</span>
            </div>
            <div class="data-row">
                <span class="lbl">Penjualan QRIS:</span>
                <span class="val">Rp {{ number_format($shift->qris_sales, 0, ',', '.') }}</span>
            </div>
            <div class="data-row">
                <span class="lbl">Penjualan Transfer:</span>
                <span class="val">Rp {{ number_format($shift->transfer_sales, 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- TOTAL OMSET --}}
        <div class="highlight-box">
            <div class="highlight-row">
                <span>TOTAL OMSET:</span>
                <span>Rp {{ number_format($shift->total_sales, 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- 3. REKONSILIASI KAS LACI --}}
        <div class="section-header">
            <span>Rekonsiliasi Kas Laci</span>
        </div>
        <div class="row-list">
            <div class="data-row">
                <span class="lbl">Modal Kas Awal:</span>
                <span class="val">Rp {{ number_format($shift->starting_cash, 0, ',', '.') }}</span>
            </div>
            <div class="data-row">
                <span class="lbl">(+) Total Kas Tunai:</span>
                <span class="val">Rp {{ number_format($shift->cash_sales, 0, ',', '.') }}</span>
            </div>
            <div class="data-row strong">
                <span class="lbl">(=) Kas Diharapkan di Laci:</span>
                <span class="val">Rp {{ number_format($shift->expected_cash, 0, ',', '.') }}</span>
            </div>
            @if ($shift->status === 'closed')
                <div class="data-row strong">
                    <span class="lbl">Uang Fisik Dihitung:</span>
                    <span class="val">Rp {{ number_format($shift->actual_cash ?? 0, 0, ',', '.') }}</span>
                </div>
            @endif
        </div>

        {{-- STATUS SELISIH --}}
        @if ($shift->status === 'closed')
            @php $diff = (float) ($shift->difference ?? 0); @endphp
            <div class="highlight-box">
                <div class="highlight-row">
                    <span style="font-size: 11.5px;">SELISIH KAS:</span>
                    @if ($diff == 0)
                        <span class="diff-tag diff-pas">Rp 0 (PAS)</span>
                    @elseif ($diff > 0)
                        <span class="diff-tag diff-lebih">+Rp {{ number_format($diff, 0, ',', '.') }} (LEBIH)</span>
                    @else
                        <span class="diff-tag diff-kurang">-Rp {{ number_format(abs($diff), 0, ',', '.') }} (KURANG)</span>
                    @endif
                </div>
            </div>
        @endif

        {{-- CATATAN SHIFT --}}
        @if (!empty($shift->notes))
            <div class="notes-box">
                <strong>Catatan Kasir:</strong><br>
                {{ $shift->notes }}
            </div>
        @endif

        {{-- 4. TANDA TANGAN VALIDASI --}}
        <div class="signatures">
            <div class="sig-col">
                <div class="sig-title">Kasir Bertugas</div>
                <div class="sig-name">{{ $shift->user->name ?? 'Kasir' }}</div>
            </div>
            <div class="sig-col">
                <div class="sig-title">Supervisor / Owner</div>
                <div class="sig-name">( .................... )</div>
            </div>
        </div>

        <div class="divider" style="margin-top: 14px;"></div>
        <div class="footer-text">
            Dicetak pada {{ now()->format('d/m/Y H:i:s') }}
        </div>
    </div>

    <script src="{{ asset('js/bluetooth-printer.js') }}"></script>
    <script>
        const rawbtShiftData = @json($rawbtShiftBase64);

        async function printBluetoothDirect() {
            try {
                if (!window.posBluetooth.isConnected) {
                    await window.posBluetooth.connect();
                }
                const uint8Array = window.posBluetooth.base64ToUint8Array(rawbtShiftData);
                await window.posBluetooth.printRawData(uint8Array);
            } catch (err) {
                alert('Bluetooth Print: ' + err.message);
            }
        }

        function printRawBT() {
            const intentUrl = "rawbt:base64," + rawbtShiftData + "#Intent;scheme=rawbt;package=ru.a402d.rawbtprinter;end;";
            window.location.href = intentUrl;
        }

        function printBrowser() {
            window.print();
        }

        // Auto-print saat halaman dibuka
        window.addEventListener('DOMContentLoaded', async () => {
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
