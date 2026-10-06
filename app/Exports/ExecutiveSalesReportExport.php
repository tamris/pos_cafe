<?php

namespace App\Exports;

use App\Models\CashMovement;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithCharts;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExecutiveSalesReportExport implements WithMultipleSheets
{
    protected string $dateFrom;
    protected string $dateTo;

    public function __construct(string $dateFrom, string $dateTo)
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    public function sheets(): array
    {
        return [
            new ExecutiveDashboardSheet($this->dateFrom, $this->dateTo),
            new ProfitAndLossSheet($this->dateFrom, $this->dateTo),
            new TopProductsRankingSheet($this->dateFrom, $this->dateTo),
            new SalesTransactionsJournalSheet($this->dateFrom, $this->dateTo),
        ];
    }
}

/**
 * Sheet 1: Executive Overview Dashboard with Human Narrative & Dual Charts (Pie & Bar)
 */
class ExecutiveDashboardSheet implements FromArray, WithTitle, WithStyles, WithColumnFormatting, WithEvents, WithCharts
{
    protected string $dateFrom;
    protected string $dateTo;
    private int $topCount = 0;

    public function __construct(string $dateFrom, string $dateTo)
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    public function title(): string
    {
        return 'Ringkasan Eksekutif';
    }

    public function array(): array
    {
        $start = $this->dateFrom . ' 00:00:00';
        $end = $this->dateTo . ' 23:59:59';
        $shopName = Setting::first()?->shop_name ?? 'POS CAFE';

        $transactions = Transaction::whereBetween('created_at', [$start, $end])
            ->where('status', 'completed')
            ->get();

        $totalRevenue = (float) $transactions->sum('total');
        $totalTrx = $transactions->count();
        $avgBasket = $totalTrx > 0 ? round($totalRevenue / $totalTrx) : 0;

        $txIds = $transactions->pluck('id');
        $totalCups = (int) TransactionDetail::whereIn('transaction_id', $txIds)->sum('quantity');
        $rawProfit = (float) TransactionDetail::whereIn('transaction_id', $txIds)->sum('profit');
        $detailSubtotal = (float) TransactionDetail::whereIn('transaction_id', $txIds)->sum('subtotal');
        $cogs = max(0, $detailSubtotal - $rawProfit);
        $grossProfit = max(0, $totalRevenue - $cogs);
        $grossMargin = $totalRevenue > 0 ? round(($grossProfit / $totalRevenue) * 100, 1) : 0;

        $totalCashOut = (float) CashMovement::where('type', 'out')
            ->whereBetween('movement_date', [$start, $end])
            ->sum('amount');

        $netProfit = $grossProfit - $totalCashOut;
        $netMargin = $totalRevenue > 0 ? round(($netProfit / $totalRevenue) * 100, 1) : 0;

        // Payment Breakdown
        $cashSales = (float) $transactions->filter(fn($t) => strtolower($t->payment_method ?? '') === 'cash')->sum('total');
        $cashCount = $transactions->filter(fn($t) => strtolower($t->payment_method ?? '') === 'cash')->count();

        $qrisSales = (float) $transactions->filter(fn($t) => strtolower($t->payment_method ?? '') === 'qris')->sum('total');
        $qrisCount = $transactions->filter(fn($t) => strtolower($t->payment_method ?? '') === 'qris')->count();

        $transferSales = (float) $transactions->filter(fn($t) => in_array(strtolower($t->payment_method ?? ''), ['transfer', 'debit']))->sum('total');
        $transferCount = $transactions->filter(fn($t) => in_array(strtolower($t->payment_method ?? ''), ['transfer', 'debit']))->count();

        // Top 5 Products
        $top5 = TransactionDetail::select(
            'products.name',
            'categories.name as category_name',
            DB::raw('SUM(transaction_details.quantity) as total_sold'),
            DB::raw('SUM(transaction_details.subtotal) as total_nominal')
        )
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->leftJoin('products', 'transaction_details.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('transactions.created_at', [$start, $end])
            ->where('transactions.status', 'completed')
            ->groupBy('products.name', 'categories.name')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        $this->topCount = $top5->count();

        $fromFormatted = Carbon::parse($this->dateFrom)->format('d/m/Y');
        $toFormatted = Carbon::parse($this->dateTo)->format('d/m/Y');
        $periodeLabel = ($this->dateFrom === $this->dateTo)
            ? "Tanggal: {$fromFormatted}"
            : "Periode: {$fromFormatted} s/d {$toFormatted}";

        $topName = $top5->first()?->name ?? 'Belum ada';
        $healthStatus = $netMargin >= 50 ? 'SANGAT SEHAT & PROFITABEL 🚀' : ($netMargin >= 25 ? 'CUKUP SEHAT 👍' : 'PERLU EVALUASI BIAYA ⚠️');

        $humanSummary = "Halo Bos! Dalam periode ini tokomu membukukan Total Omset Rp " . number_format($totalRevenue, 0, ',', '.') .
            " dari {$totalTrx} transaksi ({$totalCups} Cup terjual). Setelah dipotong modal bahan (Rp " . number_format($cogs, 0, ',', '.') .
            ") dan kas operasional (Rp " . number_format($totalCashOut, 0, ',', '.') . "), ESTIMASI UANG BERSIH (NET PROFIT) yang kamu dapatkan adalah Rp " .
            number_format($netProfit, 0, ',', '.') . " (Margin Bersih {$netMargin}%). Status keuangan tokomu: {$healthStatus} Menu terlaris nomor 1: \"{$topName}\"!";

        $rows = [
            // Row 1: Brand Title
            [strtoupper($shopName) . ' — EXECUTIVE FINANCIAL DASHBOARD'],
            // Row 2: Subtitle
            ['Laporan Ringkasan Kinerja Penjualan, Arus Kas Bersih, & Analisis Bisnis Cafe'],
            // Row 3: Metadata
            ["{$periodeLabel}   |   Generated: " . Carbon::now()->translatedFormat('d M Y, H:i') . ' WIB   |   Status: ✅ Data Terverifikasi Lunas'],
            // Row 4: Spacer
            [''],
            // Row 5: Executive Reading Box Header
            ['💡 BACA CEPAT KONDISI CAFE KAMU (RINGKASAN UNTUK OWNER)'],
            // Row 6: Narrative Text
            [$humanSummary],
            // Row 7: Spacer
            [''],
            // Row 8: KPI Card Labels
            [
                '💰 TOTAL OMSET PENJUALAN',
                '',
                '📦 MODAL BAHAN BAKU (HPP)',
                '',
                '💸 PENGELUARAN KAS KELUAR',
                '',
                '🚀 KEUNTUNGAN BERSIH (NET PROFIT)',
                '',
            ],
            // Row 9: KPI Card Values
            [
                'Rp ' . number_format($totalRevenue, 0, ',', '.'),
                '',
                'Rp ' . number_format($cogs, 0, ',', '.'),
                '',
                'Rp ' . number_format($totalCashOut, 0, ',', '.'),
                '',
                'Rp ' . number_format($netProfit, 0, ',', '.') . " ({$netMargin}%)",
                '',
            ],
            // Row 10: Spacer
            [''],
            // Row 11: Section 1 Header
            ['1. METODE PEMBAYARAN (UANG MASUK LEWAT MANA AJA?)'],
            // Row 12: Table 1 Headers
            [
                'Metode Bayar',
                'Jumlah Nota',
                'Total Uang (Rp)',
                'Porsi Omset',
            ],
            // Rows 13-15: Table 1 Rows
            ['QRIS (Digital QR)', $qrisCount, $qrisSales, '=C13/C$16'],
            ['Tunai (Cash Fisik)', $cashCount, $cashSales, '=C14/C$16'],
            ['Transfer / Debit', $transferCount, $transferSales, '=C15/C$16'],
            // Row 16: Table 1 Total
            ['TOTAL UANG MASUK', '=SUM(B13:B15)', '=SUM(C13:C15)', 1.0],
            // Row 17: Spacer
            [''],
            // Row 18: Section 2 Header
            ['2. TOP 5 MENU PALING LAKU PERIODE INI'],
            // Row 19: Table 2 Headers
            [
                'Peringkat',
                'Nama Menu Favorit',
                'Kategori',
                'Cup Terjual',
            ],
        ];

        $rank = 1;
        $medals = ['🥇 Juara 1', '🥈 Juara 2', '🥉 Juara 3', 'Top 4', 'Top 5'];

        foreach ($top5 as $p) {
            $rows[] = [
                $medals[$rank - 1] ?? ('Top ' . $rank),
                $p->name ?? 'Produk',
                $p->category_name ?? 'Umum',
                (int) $p->total_sold,
            ];
            $rank++;
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 15, 'color' => ['argb' => 'FF0F172A']]],
            2 => ['font' => ['size' => 10, 'color' => ['argb' => 'FF475569']]],
            3 => ['font' => ['italic' => true, 'size' => 9, 'color' => ['argb' => 'FF64748B']]],
            5 => ['font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FF047857']]],
            11 => ['font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FF0F172A']]],
            12 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0F172A']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
            18 => ['font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FF0F172A']]],
            19 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0F172A']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => '#,##0',
            'C' => '#,##0',
            'D' => '#,##0',
        ];
    }

    public function charts()
    {
        $charts = [];

        // 1. Pie Chart Metode Pembayaran
        $labels1 = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, '\'Ringkasan Eksekutif\'!$C$12', null, 1),
        ];
        $cat1 = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, '\'Ringkasan Eksekutif\'!$A$13:$A$15', null, 3),
        ];
        $val1 = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, '\'Ringkasan Eksekutif\'!$C$13:$C$15', null, 3),
        ];

        $series1 = new DataSeries(
            DataSeries::TYPE_PIECHART,
            null,
            [0],
            $labels1,
            $cat1,
            $val1
        );

        $plotArea1 = new PlotArea(null, [$series1]);
        $legend1 = new Legend(Legend::POSITION_RIGHT, null, false);
        $title1 = new Title('Porsi Metode Pembayaran (Uang Masuk)');

        $chart1 = new Chart('chart_payment_pie', $title1, $legend1, $plotArea1, true, DataSeries::EMPTY_AS_GAP);
        $chart1->setTopLeftPosition('F11');
        $chart1->setBottomRightPosition('K17');
        $charts[] = $chart1;

        // 2. Column Chart Top 5 Products
        if ($this->topCount > 0) {
            $endRow = 19 + $this->topCount;
            $labels2 = [
                new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, '\'Ringkasan Eksekutif\'!$D$19', null, 1),
            ];
            $cat2 = [
                new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, '\'Ringkasan Eksekutif\'!$B$20:$B$' . $endRow, null, $this->topCount),
            ];
            $val2 = [
                new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, '\'Ringkasan Eksekutif\'!$D$20:$D$' . $endRow, null, $this->topCount),
            ];

            $series2 = new DataSeries(
                DataSeries::TYPE_BARCHART,
                DataSeries::GROUPING_CLUSTERED,
                [0],
                $labels2,
                $cat2,
                $val2,
                DataSeries::DIRECTION_COL
            );

            $plotArea2 = new PlotArea(null, [$series2]);
            $title2 = new Title('Top 5 Menu Paling Laku (Cup Terjual)');

            $chart2 = new Chart('chart_top_bar', $title2, null, $plotArea2, true, DataSeries::EMPTY_AS_GAP);
            $chart2->setTopLeftPosition('F19');
            $chart2->setBottomRightPosition('K26');
            $charts[] = $chart2;
        }

        return $charts;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->getRowDimension(1)->setRowHeight(24);
                $sheet->getRowDimension(5)->setRowHeight(18);
                $sheet->getRowDimension(6)->setRowHeight(28);
                $sheet->getRowDimension(8)->setRowHeight(18);
                $sheet->getRowDimension(9)->setRowHeight(26);
                $sheet->getRowDimension(12)->setRowHeight(26);
                $sheet->getRowDimension(19)->setRowHeight(26);

                $sheet->getColumnDimension('A')->setWidth(26);
                $sheet->getColumnDimension('B')->setWidth(26);
                $sheet->getColumnDimension('C')->setWidth(22);
                $sheet->getColumnDimension('D')->setWidth(18);
                $sheet->getColumnDimension('E')->setWidth(4); // Spacer
                $sheet->getColumnDimension('F')->setWidth(12);
                $sheet->getColumnDimension('G')->setWidth(12);
                $sheet->getColumnDimension('H')->setWidth(12);
                $sheet->getColumnDimension('I')->setWidth(12);
                $sheet->getColumnDimension('J')->setWidth(12);
                $sheet->getColumnDimension('K')->setWidth(12);

                // Format Reading Box
                $sheet->mergeCells('A5:K5');
                $sheet->mergeCells('A6:K6');
                $sheet->getStyle('A5:K6')->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF0FDF4']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFBBF7D0']]],
                ]);
                $sheet->getStyle('A6')->applyFromArray([
                    'font' => ['size' => 10, 'color' => ['argb' => 'FF166534']],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                ]);

                // Format KPI Cards (Rows 8-9)
                $sheet->mergeCells('A8:B8');
                $sheet->mergeCells('A9:B9');
                $sheet->mergeCells('C8:D8');
                $sheet->mergeCells('C9:D9');
                $sheet->mergeCells('E8:F8');
                $sheet->mergeCells('E9:F9');
                $sheet->mergeCells('G8:H8');
                $sheet->mergeCells('G9:H9');

                // Card 1: Emerald (Omset)
                $sheet->getStyle('A8:B9')->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFECFDF5']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFA7F3D0']]],
                ]);
                $sheet->getStyle('A8')->applyFromArray(['font' => ['bold' => true, 'size' => 8, 'color' => ['argb' => 'FF047857']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);
                $sheet->getStyle('A9')->applyFromArray(['font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FF059669']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);

                // Card 2: Sky Blue (Modal/HPP)
                $sheet->getStyle('C8:D9')->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF0F9FF']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFBAE6FD']]],
                ]);
                $sheet->getStyle('C8')->applyFromArray(['font' => ['bold' => true, 'size' => 8, 'color' => ['argb' => 'FF0369A1']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);
                $sheet->getStyle('C9')->applyFromArray(['font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FF0284C7']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);

                // Card 3: Orange (Kas Keluar)
                $sheet->getStyle('E8:F9')->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFFBEB']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFFDE68A']]],
                ]);
                $sheet->getStyle('E8')->applyFromArray(['font' => ['bold' => true, 'size' => 8, 'color' => ['argb' => 'FFB45309']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);
                $sheet->getStyle('E9')->applyFromArray(['font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FFD97706']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);

                // Card 4: Dark Navy / Green (Net Profit)
                $sheet->getStyle('G8:H9')->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0F172A']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF1E293B']]],
                ]);
                $sheet->getStyle('G8')->applyFromArray(['font' => ['bold' => true, 'size' => 8, 'color' => ['argb' => 'FF94A3B8']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);
                $sheet->getStyle('G9')->applyFromArray(['font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FF34D399']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);

                // Table 1 Formatting (Rows 12-16)
                for ($r = 13; $r <= 15; $r++) {
                    $sheet->getRowDimension($r)->setRowHeight(21);
                }
                $sheet->getRowDimension(16)->setRowHeight(24);
                $sheet->getStyle('B13:B16')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('C13:C16')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('D13:D16')->getNumberFormat()->setFormatCode('0.0%');
                $sheet->getStyle('D13:D16')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                $sheet->getStyle('A16:D16')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FF0F172A']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF1F5F9']],
                    'borders' => [
                        'top' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCBD5E1']],
                        'bottom' => ['borderStyle' => Border::BORDER_DOUBLE, 'color' => ['argb' => 'FF64748B']],
                    ],
                ]);
                $sheet->getStyle('A12:D16')->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE2E8F0']]],
                ]);

                // Table 2 Formatting (Rows 19 to 19 + topCount)
                if ($this->topCount > 0) {
                    $endRow = 19 + $this->topCount;
                    for ($r = 20; $r <= $endRow; $r++) {
                        $sheet->getRowDimension($r)->setRowHeight(21);
                        if ($r % 2 === 1) {
                            $sheet->getStyle("A{$r}:D{$r}")->applyFromArray([
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF8FAFC']],
                            ]);
                        }
                    }
                    $sheet->getStyle("A20:A{$endRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("C20:C{$endRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("D20:D{$endRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle("A19:D{$endRow}")->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE2E8F0']]],
                    ]);
                }
            },
        ];
    }
}

/**
 * Sheet 2: Executive Profit & Loss with Financial Glossary for Beginners
 */
class ProfitAndLossSheet implements FromArray, WithTitle, WithStyles, WithColumnFormatting, WithEvents
{
    protected string $dateFrom;
    protected string $dateTo;

    public function __construct(string $dateFrom, string $dateTo)
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    public function title(): string
    {
        return 'Laba Rugi & Arus Kas';
    }

    public function array(): array
    {
        $start = $this->dateFrom . ' 00:00:00';
        $end = $this->dateTo . ' 23:59:59';
        $shopName = Setting::first()?->shop_name ?? 'POS CAFE';

        $transactions = Transaction::whereBetween('created_at', [$start, $end])
            ->where('status', 'completed')
            ->get();

        $subtotal = (float) $transactions->sum('subtotal');
        $discount = (float) $transactions->sum('discount');
        $totalRevenue = (float) $transactions->sum('total');
        $totalTrx = $transactions->count();
        $avgBasket = $totalTrx > 0 ? round($totalRevenue / $totalTrx) : 0;

        $txIds = $transactions->pluck('id');
        $totalCups = (int) TransactionDetail::whereIn('transaction_id', $txIds)->sum('quantity');
        $rawProfit = (float) TransactionDetail::whereIn('transaction_id', $txIds)->sum('profit');
        $detailSubtotal = (float) TransactionDetail::whereIn('transaction_id', $txIds)->sum('subtotal');
        $cogs = max(0, $detailSubtotal - $rawProfit);
        $grossProfit = max(0, $totalRevenue - $cogs);
        $grossMargin = $totalRevenue > 0 ? round(($grossProfit / $totalRevenue) * 100, 1) : 0;

        $totalCashOut = (float) CashMovement::where('type', 'out')
            ->whereBetween('movement_date', [$start, $end])
            ->sum('amount');

        $netProfit = $grossProfit - $totalCashOut;
        $netMargin = $totalRevenue > 0 ? round(($netProfit / $totalRevenue) * 100, 1) : 0;

        $fromFormatted = Carbon::parse($this->dateFrom)->format('d/m/Y');
        $toFormatted = Carbon::parse($this->dateTo)->format('d/m/Y');
        $periodeLabel = ($this->dateFrom === $this->dateTo)
            ? "Tanggal: {$fromFormatted}"
            : "Periode: {$fromFormatted} s/d {$toFormatted}";

        return [
            // Row 1: Header Brand
            [strtoupper($shopName) . ' — LAPORAN LABA RUGI & ARUS KAS'],
            // Row 2: Subtitle
            ['Struktur Finansial Rinci: Pendapatan, HPP, Kas Operasional, & Laba Bersih'],
            // Row 3: Metadata
            ["{$periodeLabel}   |   Status: Laporan Keuangan Audited   |   Generated: " . Carbon::now()->translatedFormat('d M Y, H:i') . ' WIB'],
            // Row 4: Spacer
            [''],
            // Row 5: Table Header
            ['1. LAPORAN LABA RUGI EKSEKUTIF (PROFIT & LOSS)'],
            // Row 6: Columns
            [
                'Komponen Keuangan',
                'Nominal (Rp)',
                'Porsi Omset',
                'Penjelasan Sederhana untuk Pemula (Anti Ribet)',
            ],
            // Rows 7-13: P&L Rows
            ['(+) Total Omset Penjualan Kotor (Gross Sales)', $subtotal, '=B7/B$9', 'Total harga seluruh produk yang dipesan sebelum kena diskon'],
            ['(-) Diskon & Promo Penjualan', $discount, '=B8/B$9', 'Potongan harga / voucher yang diberikan ke pelanggan'],
            ['(=) PENDAPATAN PENJUALAN BERSIH', $totalRevenue, 1.0, 'Uang riil yang sah diterima kasir dari penjualan pelanggan'],
            ['(-) Harga Pokok Penjualan (Modal Bahan / HPP)', $cogs, '=B10/B$9', 'Estimasi modal riil bahan baku kopi, susu, sirup, cup, dll'],
            ['(=) LABA KOTOR PENJUALAN (GROSS PROFIT)', $grossProfit, '=B11/B$9', "Sisa uang omset setelah dipotong modal bahan (Margin: {$grossMargin}%)"],
            ['(-) Pengeluaran Kasir (Kas Keluar Operasional)', $totalCashOut, '=B12/B$9', 'Belanja kasir untuk es kristal, gas, galon, plastik, dll'],
            ['(=) ESTIMASI LABA BERSIH (NET PROFIT)', $netProfit, '=B13/B$9', "Uang bersih akhir yang siap masuk ke kantong owner (Margin: {$netMargin}%)"],
            // Row 14: Spacer
            [''],
            // Row 15: Operasional Header
            ['2. INDIKATOR VOLUME & EFISIENSI OPERASIONAL'],
            // Row 16: Columns
            [
                'Indikator Bisnis',
                'Kuantitas / Nilai',
                'Satuan',
                'Keterangan & Target Analisis',
            ],
            // Rows 17-19
            ['Rata-rata Belanja per Meja (AOV)', $avgBasket, 'Per Nota', 'Rata-rata rupiah yang dihabiskan satu pelanggan/meja'],
            ['Total Volume Cup Terjual', $totalCups, 'Cup / Porsi', 'Total kuantitas minuman & makanan yang berhasil disajikan'],
            ['Total Transaksi Selesai', $totalTrx, 'Nota Transaksi', 'Akumulasi nota yang lunas dibayar di kasir'],
            // Row 20: Spacer
            [''],
            // Row 21: Glossary Header
            ['📚 KAMUS MINI KEUANGAN CAFE (BACAAN BAGI YANG AWAM FINANCE)'],
            // Rows 22-27: Educational Definitions
            ['1. Omset (Gross Revenue)', 'Total seluruh uang yang dibayarkan pelanggan. Belum dipotong modal apa pun.'],
            ['2. Modal Bahan (HPP / COGS)', 'Biaya riil bahan baku untuk meracik menu (kopi, susu, cup, sedotan).'],
            ['3. Laba Kotor (Gross Profit)', 'Uang sisa penjualan setelah membayar modal bahan baku menu.'],
            ['4. Kas Keluar Kasir', 'Uang dari laci kasir yang dipakai belanja operasional mendadak toko.'],
            ['5. Laba Bersih (Net Profit)', 'Uang keuntungan riil yang benar-benar menjadi profit bersih milik owner.'],
            ['6. AOV (Average Order Value)', 'Rata-rata nominal belanja tiap satu struk. Semakin besar, semakin bagus.'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 15, 'color' => ['argb' => 'FF0F172A']]],
            2 => ['font' => ['size' => 10, 'color' => ['argb' => 'FF475569']]],
            3 => ['font' => ['italic' => true, 'size' => 9, 'color' => ['argb' => 'FF64748B']]],
            5 => ['font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FF0F172A']]],
            6 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0F172A']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
            15 => ['font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FF0F172A']]],
            16 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0F172A']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
            21 => ['font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FF047857']]],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => '#,##0',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->getRowDimension(1)->setRowHeight(24);
                $sheet->getRowDimension(6)->setRowHeight(26);
                $sheet->getRowDimension(16)->setRowHeight(26);

                $sheet->getColumnDimension('A')->setWidth(42);
                $sheet->getColumnDimension('B')->setWidth(24);
                $sheet->getColumnDimension('C')->setWidth(18);
                $sheet->getColumnDimension('D')->setWidth(56);

                // Format Rows 7-13
                for ($r = 7; $r <= 13; $r++) {
                    $sheet->getRowDimension($r)->setRowHeight(22);
                    if ($r % 2 === 1) {
                        $sheet->getStyle("A{$r}:D{$r}")->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF8FAFC']],
                        ]);
                    }
                }

                $sheet->getStyle('B7:B13')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('B7:B13')->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('C7:C13')->getNumberFormat()->setFormatCode('0.0%');
                $sheet->getStyle('C7:C13')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // Highlight Row 9 (Pendapatan Bersih)
                $sheet->getStyle('A9:D9')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FF065F46']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFECFDF5']],
                ]);

                // Highlight Row 11 (Gross Profit)
                $sheet->getStyle('A11:D11')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FF1E40AF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFEFF6FF']],
                ]);

                // Highlight Row 13 (Net Profit)
                $sheet->getStyle('A13:D13')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FF065F46']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD1FAE5']],
                    'borders' => [
                        'top' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF10B981']],
                        'bottom' => ['borderStyle' => Border::BORDER_DOUBLE, 'color' => ['argb' => 'FF059669']],
                    ],
                ]);

                $sheet->getStyle('A6:D13')->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE2E8F0']]],
                ]);

                // Operasional Table (Rows 16-19)
                for ($r = 17; $r <= 19; $r++) {
                    $sheet->getRowDimension($r)->setRowHeight(21);
                }
                $sheet->getStyle('B17:B19')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('C17:C19')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A16:D19')->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE2E8F0']]],
                ]);

                // Format Glossary (Rows 21-27)
                $sheet->mergeCells('A21:D21');
                $sheet->getStyle('A21:D21')->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF0FDF4']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFBBF7D0']]],
                ]);

                for ($r = 22; $r <= 27; $r++) {
                    $sheet->getRowDimension($r)->setRowHeight(20);
                    $sheet->mergeCells("B{$r}:D{$r}");
                    $sheet->getStyle("A{$r}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 9, 'color' => ['argb' => 'FF1E293B']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF8FAFC']],
                    ]);
                    $sheet->getStyle("B{$r}")->applyFromArray([
                        'font' => ['size' => 9, 'color' => ['argb' => 'FF475569']],
                    ]);
                }
                $sheet->getStyle('A21:D27')->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE2E8F0']]],
                ]);
            },
        ];
    }
}

/**
 * Sheet 3: Top Products Ranking Sheet with Scorecards & Column Chart
 */
class TopProductsRankingSheet implements FromArray, WithTitle, WithStyles, WithColumnFormatting, WithEvents, WithCharts
{
    protected string $dateFrom;
    protected string $dateTo;
    private int $rowCount = 0;
    private int $chartItemCount = 0;

    public function __construct(string $dateFrom, string $dateTo)
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    public function title(): string
    {
        return 'Ranking Menu Terlaris';
    }

    public function array(): array
    {
        $start = $this->dateFrom . ' 00:00:00';
        $end = $this->dateTo . ' 23:59:59';
        $shopName = Setting::first()?->shop_name ?? 'POS CAFE';

        $totalRevenueAll = (float) Transaction::whereBetween('created_at', [$start, $end])
            ->where('status', 'completed')
            ->sum('total');

        $topProducts = TransactionDetail::select(
            'products.id',
            'products.name',
            'categories.name as category_name',
            DB::raw('SUM(transaction_details.quantity) as total_sold'),
            DB::raw('SUM(transaction_details.subtotal) as total_nominal'),
            DB::raw('SUM(transaction_details.profit) as total_profit')
        )
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->leftJoin('products', 'transaction_details.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('transactions.created_at', [$start, $end])
            ->where('transactions.status', 'completed')
            ->groupBy('products.id', 'products.name', 'categories.name')
            ->orderByDesc('total_sold')
            ->get();

        $this->rowCount = $topProducts->count();
        $this->chartItemCount = min(5, $this->rowCount);

        $totalQtyAll = (int) $topProducts->sum('total_sold');
        $totalProfitAll = (float) $topProducts->sum('total_profit');
        $avgMargin = $totalRevenueAll > 0 ? round(($totalProfitAll / $totalRevenueAll) * 100, 1) : 0;

        $fromFormatted = Carbon::parse($this->dateFrom)->format('d/m/Y');
        $toFormatted = Carbon::parse($this->dateTo)->format('d/m/Y');
        $periodeLabel = ($this->dateFrom === $this->dateTo)
            ? "Tanggal: {$fromFormatted}"
            : "Periode: {$fromFormatted} s/d {$toFormatted}";

        $rows = [
            // Row 1: Brand Title
            [strtoupper($shopName) . ' — LEADERBOARD & PERFORMA MENU'],
            // Row 2: Subtitle
            ['Peringkat Penjualan Seluruh Menu, Kuantitas Terjual, Margin, dan Kontribusi Toko'],
            // Row 3: Metadata
            ["{$periodeLabel}   |   Total Menu Terjual: " . number_format($totalQtyAll, 0, ',', '.') . ' Cup   |   Generated: ' . Carbon::now()->translatedFormat('d M Y, H:i') . ' WIB'],
            // Row 4: Spacer
            [''],
            // Row 5: KPI Card Labels
            [
                'TOTAL CUP TERJUAL',
                '',
                'TOTAL OMSET MENU',
                '',
                'TOTAL PROFIT MENU',
                '',
                'RATA-RATA MARGIN',
                '',
            ],
            // Row 6: KPI Card Values
            [
                number_format($totalQtyAll, 0, ',', '.') . ' Cup',
                '',
                'Rp ' . number_format($totalRevenueAll, 0, ',', '.'),
                '',
                'Rp ' . number_format($totalProfitAll, 0, ',', '.'),
                '',
                $avgMargin . '%',
                '',
            ],
            // Row 7: Spacer
            [''],
            // Row 8: Section Title
            ['🏆 PERINGKAT SELURUH PRODUK CAFE'],
            // Row 9: Table Headers
            [
                'Peringkat',
                'Nama Menu Cafe',
                'Kategori',
                'Cup Terjual',
                'Harga Rata-rata',
                'Total Omset (Rp)',
                'Modal Bahan (Rp)',
                'Keuntungan Bersih (Rp)',
                'Margin Untung',
                'Porsi ke Toko',
                'Status Menu',
            ],
        ];

        $rank = 1;
        $medals = ['🥇 Juara 1', '🥈 Juara 2', '🥉 Juara 3'];

        foreach ($topProducts as $item) {
            $sold = (int) $item->total_sold;
            $nominal = (float) $item->total_nominal;
            $profit = (float) $item->total_profit;
            $cost = max(0, $nominal - $profit);
            $avgPrice = $sold > 0 ? round($nominal / $sold) : 0;
            $marginPct = $nominal > 0 ? ($profit / $nominal) : 0;
            $sharePct = $totalRevenueAll > 0 ? ($nominal / $totalRevenueAll) : 0;
            $rankLabel = $medals[$rank - 1] ?? ('Top ' . $rank);

            $statusBadge = ($rank <= 3) ? '⭐ Bestseller' : (($rank <= 8) ? '🔥 Populer' : '📦 Reguler');

            $rows[] = [
                $rankLabel,
                $item->name ?? 'Produk Dihapus',
                $item->category_name ?? 'Umum',
                $sold,
                $avgPrice,
                $nominal,
                $cost,
                $profit,
                $marginPct,
                $sharePct,
                $statusBadge,
            ];
            $rank++;
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 15, 'color' => ['argb' => 'FF0F172A']]],
            2 => ['font' => ['size' => 10, 'color' => ['argb' => 'FF475569']]],
            3 => ['font' => ['italic' => true, 'size' => 9, 'color' => ['argb' => 'FF64748B']]],
            8 => ['font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FF0F172A']]],
            9 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0F172A']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'D' => '#,##0',
            'E' => '#,##0',
            'F' => '#,##0',
            'G' => '#,##0',
            'H' => '#,##0',
            'I' => '0.0%',
            'J' => '0.0%',
        ];
    }

    public function charts()
    {
        if ($this->chartItemCount === 0) {
            return [];
        }

        $endRow = 9 + $this->chartItemCount;

        $dataSeriesLabels = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, '\'Ranking Menu Terlaris\'!$D$9', null, 1),
        ];
        $xAxisTickValues = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, '\'Ranking Menu Terlaris\'!$B$10:$B$' . $endRow, null, $this->chartItemCount),
        ];
        $dataSeriesValues = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, '\'Ranking Menu Terlaris\'!$D$10:$D$' . $endRow, null, $this->chartItemCount),
        ];

        $series = new DataSeries(
            DataSeries::TYPE_BARCHART,
            DataSeries::GROUPING_CLUSTERED,
            [0],
            $dataSeriesLabels,
            $xAxisTickValues,
            $dataSeriesValues,
            DataSeries::DIRECTION_COL
        );

        $plotArea = new PlotArea(null, [$series]);
        $title = new Title('Top ' . $this->chartItemCount . ' Menu Paling Laris (Cup Terjual)');

        $chart = new Chart('chart_top_products_sheet', $title, null, $plotArea, true, DataSeries::EMPTY_AS_GAP);
        $chart->setTopLeftPosition('M9');
        $chart->setBottomRightPosition('S24');

        return [$chart];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $headerRow = 9;
                $startDataRow = 10;
                $hasData = $this->rowCount > 0;
                $lastRow = $hasData ? ($startDataRow + $this->rowCount - 1) : $startDataRow;
                $totalRow = $lastRow + 1;

                $sheet->getRowDimension(1)->setRowHeight(24);
                $sheet->getRowDimension(5)->setRowHeight(18);
                $sheet->getRowDimension(6)->setRowHeight(26);
                $sheet->getRowDimension($headerRow)->setRowHeight(28);

                $sheet->getColumnDimension('A')->setWidth(14);
                $sheet->getColumnDimension('B')->setWidth(28);
                $sheet->getColumnDimension('C')->setWidth(18);
                $sheet->getColumnDimension('D')->setWidth(14);
                $sheet->getColumnDimension('E')->setWidth(20);
                $sheet->getColumnDimension('F')->setWidth(20);
                $sheet->getColumnDimension('G')->setWidth(22);
                $sheet->getColumnDimension('H')->setWidth(22);
                $sheet->getColumnDimension('I')->setWidth(15);
                $sheet->getColumnDimension('J')->setWidth(15);
                $sheet->getColumnDimension('K')->setWidth(16);
                $sheet->getColumnDimension('L')->setWidth(4); // Spacer
                $sheet->getColumnDimension('M')->setWidth(12);
                $sheet->getColumnDimension('N')->setWidth(12);
                $sheet->getColumnDimension('O')->setWidth(12);
                $sheet->getColumnDimension('P')->setWidth(12);
                $sheet->getColumnDimension('Q')->setWidth(12);
                $sheet->getColumnDimension('R')->setWidth(12);
                $sheet->getColumnDimension('S')->setWidth(12);

                $sheet->mergeCells('A5:B5');
                $sheet->mergeCells('A6:B6');
                $sheet->mergeCells('C5:D5');
                $sheet->mergeCells('C6:D6');
                $sheet->mergeCells('E5:F5');
                $sheet->mergeCells('E6:F6');
                $sheet->mergeCells('G5:H5');
                $sheet->mergeCells('G6:H6');

                $sheet->getStyle('A5:B6')->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF8FAFC']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE2E8F0']]],
                ]);
                $sheet->getStyle('A5')->applyFromArray(['font' => ['bold' => true, 'size' => 8, 'color' => ['argb' => 'FF475569']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);
                $sheet->getStyle('A6')->applyFromArray(['font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FF0F172A']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);

                $sheet->getStyle('C5:D6')->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFECFDF5']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFA7F3D0']]],
                ]);
                $sheet->getStyle('C5')->applyFromArray(['font' => ['bold' => true, 'size' => 8, 'color' => ['argb' => 'FF047857']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);
                $sheet->getStyle('C6')->applyFromArray(['font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FF059669']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);

                $sheet->getStyle('E5:F6')->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFEFF6FF']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFBFDBFE']]],
                ]);
                $sheet->getStyle('E5')->applyFromArray(['font' => ['bold' => true, 'size' => 8, 'color' => ['argb' => 'FF1D4ED8']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);
                $sheet->getStyle('E6')->applyFromArray(['font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FF2563EB']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);

                $sheet->getStyle('G5:H6')->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF5F3FF']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFDDD6FE']]],
                ]);
                $sheet->getStyle('G5')->applyFromArray(['font' => ['bold' => true, 'size' => 8, 'color' => ['argb' => 'FF6D28D9']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);
                $sheet->getStyle('G6')->applyFromArray(['font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FF7C3AED']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);

                $sheet->freezePane('C10');

                if ($hasData) {
                    for ($r = $startDataRow; $r <= $lastRow; $r++) {
                        $sheet->getRowDimension($r)->setRowHeight(21);
                        if ($r % 2 === 0) {
                            $sheet->getStyle("A{$r}:K{$r}")->applyFromArray([
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF8FAFC']],
                            ]);
                        }
                    }

                    $sheet->getStyle("A{$startDataRow}:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("C{$startDataRow}:C{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("D{$startDataRow}:D{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle("E{$startDataRow}:H{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle("I{$startDataRow}:J{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle("K{$startDataRow}:K{$lastRow}")->applyFromArray([
                        'font' => ['bold' => true],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);
                }

                $sheet->setCellValue("A{$totalRow}", 'TOTAL AKUMULASI PENJUALAN');
                $sheet->mergeCells("A{$totalRow}:C{$totalRow}");

                if ($hasData) {
                    $sheet->setCellValue("D{$totalRow}", "=SUM(D{$startDataRow}:D{$lastRow})");
                    $sheet->setCellValue("E{$totalRow}", "-");
                    $sheet->setCellValue("F{$totalRow}", "=SUM(F{$startDataRow}:F{$lastRow})");
                    $sheet->setCellValue("G{$totalRow}", "=SUM(G{$startDataRow}:G{$lastRow})");
                    $sheet->setCellValue("H{$totalRow}", "=SUM(H{$startDataRow}:H{$lastRow})");
                    $sheet->setCellValue("I{$totalRow}", "=H{$totalRow}/F{$totalRow}");
                    $sheet->setCellValue("J{$totalRow}", 1.0);
                    $sheet->setCellValue("K{$totalRow}", "TOTAL");
                } else {
                    $sheet->setCellValue("D{$totalRow}", 0);
                    $sheet->setCellValue("E{$totalRow}", '-');
                    $sheet->setCellValue("F{$totalRow}", 0);
                    $sheet->setCellValue("G{$totalRow}", 0);
                    $sheet->setCellValue("H{$totalRow}", 0);
                    $sheet->setCellValue("I{$totalRow}", 0);
                    $sheet->setCellValue("J{$totalRow}", 0);
                    $sheet->setCellValue("K{$totalRow}", '-');
                }

                $sheet->getRowDimension($totalRow)->setRowHeight(24);
                $sheet->getStyle("A{$totalRow}:K{$totalRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FF065F46']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFECFDF5'],
                    ],
                    'borders' => [
                        'top' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF10B981']],
                        'bottom' => ['borderStyle' => Border::BORDER_DOUBLE, 'color' => ['argb' => 'FF059669']],
                    ],
                ]);

                $sheet->getStyle("D{$totalRow}:H{$totalRow}")->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle("I{$totalRow}:J{$totalRow}")->getNumberFormat()->setFormatCode('0.0%');
                $sheet->getStyle("E{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("D{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("F{$totalRow}:J{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("K{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle("A{$headerRow}:K{$totalRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FFE2E8F0'],
                        ],
                    ],
                ]);
            },
        ];
    }
}

/**
 * Sheet 4: Clean Detailed Sales Transactions Journal
 */
class SalesTransactionsJournalSheet implements FromArray, WithTitle, WithStyles, WithColumnFormatting, WithEvents
{
    protected string $dateFrom;
    protected string $dateTo;
    private int $rowCount = 0;

    public function __construct(string $dateFrom, string $dateTo)
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    public function title(): string
    {
        return 'Buku Catatan Transaksi';
    }

    public function array(): array
    {
        $start = $this->dateFrom . ' 00:00:00';
        $end = $this->dateTo . ' 23:59:59';
        $shopName = Setting::first()?->shop_name ?? 'POS CAFE';

        $transactions = Transaction::with(['user', 'details'])
            ->withSum('details', 'profit')
            ->whereBetween('created_at', [$start, $end])
            ->where('status', 'completed')
            ->latest('created_at')
            ->get();

        $this->rowCount = $transactions->count();

        $fromFormatted = Carbon::parse($this->dateFrom)->format('d/m/Y');
        $toFormatted = Carbon::parse($this->dateTo)->format('d/m/Y');
        $periodeLabel = ($this->dateFrom === $this->dateTo)
            ? "Tanggal: {$fromFormatted}"
            : "Periode: {$fromFormatted} s/d {$toFormatted}";

        $rows = [
            // Row 1: Brand Title
            [strtoupper($shopName) . ' — BUKU CATATAN TRANSAKSI PENJUALAN'],
            // Row 2: Subtitle
            ['Daftar Rinci Seluruh Nota Penjualan Kasir (Status: Completed / Lunas)'],
            // Row 3: Metadata
            ["{$periodeLabel}   |   Total: {$this->rowCount} Nota Transaksi   |   Generated: " . Carbon::now()->translatedFormat('d M Y, H:i') . ' WIB'],
            // Row 4: Spacer
            [''],
            // Row 5: Table Headers
            [
                'No',
                'No. Invoice',
                'Tanggal & Jam',
                'Kasir / Pelayan',
                'Tipe Pesanan',
                'Meja / Pelanggan',
                'Metode Bayar',
                'Subtotal (Rp)',
                'Diskon (Rp)',
                'Pajak (Rp)',
                'Total Bayar (Rp)',
                'Profit HPP (Rp)',
                'Status',
            ],
        ];

        $no = 1;
        foreach ($transactions as $tx) {
            $orderTypeLabel = strtoupper(str_replace('_', ' ', $tx->order_type ?? 'dine_in'));
            $tableInfo = ($tx->order_type === 'dine_in')
                ? ($tx->table_number ? 'Meja: ' . $tx->table_number : '-')
                : ($tx->customer_name ? 'Cust: ' . $tx->customer_name : '-');

            $rows[] = [
                $no++,
                $tx->invoice_number,
                $tx->created_at->format('d/m/Y H:i'),
                $tx->user->name ?? 'Admin',
                $orderTypeLabel,
                $tableInfo,
                strtoupper($tx->payment_method ?? 'CASH'),
                (float) $tx->subtotal,
                (float) $tx->discount,
                (float) $tx->tax,
                (float) $tx->total,
                (float) ($tx->details_sum_profit ?? 0),
                'LUNAS',
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 15, 'color' => ['argb' => 'FF0F172A']]],
            2 => ['font' => ['size' => 10, 'color' => ['argb' => 'FF475569']]],
            3 => ['font' => ['italic' => true, 'size' => 9, 'color' => ['argb' => 'FF64748B']]],
            5 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0F172A']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'H' => '#,##0',
            'I' => '#,##0',
            'J' => '#,##0',
            'K' => '#,##0',
            'L' => '#,##0',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $headerRow = 5;
                $startDataRow = 6;
                $hasData = $this->rowCount > 0;
                $lastRow = $hasData ? ($startDataRow + $this->rowCount - 1) : $startDataRow;
                $totalRow = $lastRow + 1;

                $sheet->getRowDimension(1)->setRowHeight(24);
                $sheet->getRowDimension($headerRow)->setRowHeight(28);

                $sheet->getColumnDimension('A')->setWidth(8);
                $sheet->getColumnDimension('B')->setWidth(20);
                $sheet->getColumnDimension('C')->setWidth(18);
                $sheet->getColumnDimension('D')->setWidth(18);
                $sheet->getColumnDimension('E')->setWidth(16);
                $sheet->getColumnDimension('F')->setWidth(18);
                $sheet->getColumnDimension('G')->setWidth(16);
                $sheet->getColumnDimension('H')->setWidth(18);
                $sheet->getColumnDimension('I')->setWidth(16);
                $sheet->getColumnDimension('J')->setWidth(16);
                $sheet->getColumnDimension('K')->setWidth(20);
                $sheet->getColumnDimension('L')->setWidth(18);
                $sheet->getColumnDimension('M')->setWidth(14);

                $sheet->freezePane('C6');

                if ($hasData) {
                    for ($r = $startDataRow; $r <= $lastRow; $r++) {
                        $sheet->getRowDimension($r)->setRowHeight(21);
                        if ($r % 2 === 1) {
                            $sheet->getStyle("A{$r}:M{$r}")->applyFromArray([
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF8FAFC']],
                            ]);
                        }
                    }

                    $sheet->getStyle("A{$startDataRow}:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("B{$startDataRow}:B{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("C{$startDataRow}:C{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("D{$startDataRow}:D{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("E{$startDataRow}:E{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("F{$startDataRow}:F{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("G{$startDataRow}:G{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("H{$startDataRow}:L{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle("M{$startDataRow}:M{$lastRow}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['argb' => 'FF059669']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);
                }

                $sheet->setCellValue("A{$totalRow}", 'TOTAL AKUMULASI TRANSAKSI');
                $sheet->mergeCells("A{$totalRow}:G{$totalRow}");

                if ($hasData) {
                    $sheet->setCellValue("H{$totalRow}", "=SUM(H{$startDataRow}:H{$lastRow})");
                    $sheet->setCellValue("I{$totalRow}", "=SUM(I{$startDataRow}:I{$lastRow})");
                    $sheet->setCellValue("J{$totalRow}", "=SUM(J{$startDataRow}:J{$lastRow})");
                    $sheet->setCellValue("K{$totalRow}", "=SUM(K{$startDataRow}:K{$lastRow})");
                    $sheet->setCellValue("L{$totalRow}", "=SUM(L{$startDataRow}:L{$lastRow})");
                    $sheet->setCellValue("M{$totalRow}", "LUNAS");
                } else {
                    $sheet->setCellValue("H{$totalRow}", 0);
                    $sheet->setCellValue("I{$totalRow}", 0);
                    $sheet->setCellValue("J{$totalRow}", 0);
                    $sheet->setCellValue("K{$totalRow}", 0);
                    $sheet->setCellValue("L{$totalRow}", 0);
                    $sheet->setCellValue("M{$totalRow}", "-");
                }

                $sheet->getRowDimension($totalRow)->setRowHeight(24);
                $sheet->getStyle("A{$totalRow}:M{$totalRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FF065F46']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFECFDF5'],
                    ],
                    'borders' => [
                        'top' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF10B981']],
                        'bottom' => ['borderStyle' => Border::BORDER_DOUBLE, 'color' => ['argb' => 'FF059669']],
                    ],
                ]);

                $sheet->getStyle("H{$totalRow}:L{$totalRow}")->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle("H{$totalRow}:L{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("M{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle("A{$headerRow}:M{$totalRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FFE2E8F0'],
                        ],
                    ],
                ]);
            },
        ];
    }
}
