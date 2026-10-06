<?php

namespace App\Exports;

use App\Models\Setting;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithCharts;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TopProductsExport implements FromArray, WithTitle, WithStyles, WithColumnFormatting, WithEvents, WithCharts
{
    protected string $dateFrom;
    protected string $dateTo;
    private int $rowCount = 0;
    private int $chartItemCount = 0;
    private string $topProductName = '-';
    private float $topProductShare = 0.0;

    public function __construct(string $dateFrom, string $dateTo)
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    public function title(): string
    {
        return 'Menu Terlaris';
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

        $bestItem = $topProducts->first();
        if ($bestItem) {
            $this->topProductName = $bestItem->name ?? 'Menu';
            $this->topProductShare = $totalRevenueAll > 0 ? round(((float) $bestItem->total_nominal / $totalRevenueAll) * 100, 1) : 0.0;
        }

        $fromFormatted = Carbon::parse($this->dateFrom)->format('d/m/Y');
        $toFormatted = Carbon::parse($this->dateTo)->format('d/m/Y');
        $periodeLabel = ($this->dateFrom === $this->dateTo)
            ? "Tanggal: {$fromFormatted}"
            : "Periode: {$fromFormatted} s/d {$toFormatted}";

        $insightText = $bestItem
            ? "💡 Insight Owner: Menu terfavorit pelanggan periode ini adalah \"{$this->topProductName}\" dengan penjualan {$bestItem->total_sold} Cup ({$this->topProductShare}% dari seluruh omset cafe). Rata-rata margin laba produk kamu tercatat {$avgMargin}% (Kategori Sangat Sehat)!"
            : "💡 Insight Owner: Belum ada transaksi penjualan produk yang tercatat pada periode ini.";

        $rows = [
            // Row 1: Header Brand
            [strtoupper($shopName) . ' — ANALISIS MENU & STRATEGI PRODUK'],
            // Row 2: Subtitle
            ['Peringkat Penjualan, Kontribusi Omset, Margin Keuntungan, & Rekomendasi Bisnis'],
            // Row 3: Metadata
            ["{$periodeLabel}   |   Generated: " . Carbon::now()->translatedFormat('d M Y, H:i') . ' WIB   |   Klasifikasi: Internal Management Report'],
            // Row 4: Spacer
            [''],
            // Row 5: Insight Box Header
            ['💡 RINGKASAN & INSIGHT PRODUK UNTUK OWNER (BAHASA SANTAI)'],
            // Row 6: Insight Box Content
            [$insightText],
            // Row 7: Spacer
            [''],
            // Row 8: KPI Card Labels
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
            // Row 9: KPI Card Values
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
            // Row 10: Spacer
            [''],
            // Row 11: Table Section Title
            ['🏆 PERINGKAT MENU & TINGKAT KEUNTUNGAN PRODUK'],
            // Row 12: Table Column Headers
            [
                'Peringkat',
                'Nama Menu Cafe',
                'Kategori',
                'Cup Terjual',
                'Harga Jual Rata-rata',
                'Total Omset (Rp)',
                'Estimasi Modal Bahan (Rp)',
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
            5 => ['font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FF047857']]],
            11 => ['font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FF0F172A']]],
            12 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF0F172A'], // Dark Slate Navy
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
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

        $endRow = 12 + $this->chartItemCount;

        $dataSeriesLabels = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, '\'Menu Terlaris\'!$D$12', null, 1),
        ];
        $xAxisTickValues = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, '\'Menu Terlaris\'!$B$13:$B$' . $endRow, null, $this->chartItemCount),
        ];
        $dataSeriesValues = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, '\'Menu Terlaris\'!$D$13:$D$' . $endRow, null, $this->chartItemCount),
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

        $chart = new Chart('chart_top_products', $title, null, $plotArea, true, DataSeries::EMPTY_AS_GAP);
        $chart->setTopLeftPosition('M12');
        $chart->setBottomRightPosition('S27');

        return [$chart];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $headerRow = 12;
                $startDataRow = 13;
                $hasData = $this->rowCount > 0;
                $lastRow = $hasData ? ($startDataRow + $this->rowCount - 1) : $startDataRow;
                $totalRow = $lastRow + 1;

                // Explicit Heights
                $sheet->getRowDimension(1)->setRowHeight(24);
                $sheet->getRowDimension(5)->setRowHeight(18);
                $sheet->getRowDimension(6)->setRowHeight(24);
                $sheet->getRowDimension(8)->setRowHeight(18);
                $sheet->getRowDimension(9)->setRowHeight(26);
                $sheet->getRowDimension($headerRow)->setRowHeight(28);

                // Column Widths
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

                // Format Insight Box (Rows 5-6)
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

                // Card 1: Neutral Slate
                $sheet->getStyle('A8:B9')->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF8FAFC']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE2E8F0']]],
                ]);
                $sheet->getStyle('A8')->applyFromArray(['font' => ['bold' => true, 'size' => 8, 'color' => ['argb' => 'FF475569']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);
                $sheet->getStyle('A9')->applyFromArray(['font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FF0F172A']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);

                // Card 2: Emerald (Omset)
                $sheet->getStyle('C8:D9')->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFECFDF5']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFA7F3D0']]],
                ]);
                $sheet->getStyle('C8')->applyFromArray(['font' => ['bold' => true, 'size' => 8, 'color' => ['argb' => 'FF047857']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);
                $sheet->getStyle('C9')->applyFromArray(['font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FF059669']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);

                // Card 3: Blue (Profit)
                $sheet->getStyle('E8:F9')->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFEFF6FF']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFBFDBFE']]],
                ]);
                $sheet->getStyle('E8')->applyFromArray(['font' => ['bold' => true, 'size' => 8, 'color' => ['argb' => 'FF1D4ED8']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);
                $sheet->getStyle('E9')->applyFromArray(['font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FF2563EB']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);

                // Card 4: Violet (Margin)
                $sheet->getStyle('G8:H9')->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF5F3FF']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFDDD6FE']]],
                ]);
                $sheet->getStyle('G8')->applyFromArray(['font' => ['bold' => true, 'size' => 8, 'color' => ['argb' => 'FF6D28D9']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);
                $sheet->getStyle('G9')->applyFromArray(['font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FF7C3AED']], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);

                // Freeze Pane at Table Header
                $sheet->freezePane('C13');

                // Zebra striping & data formatting
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

                // Grand Total Row
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
                        'startColor' => ['argb' => 'FFECFDF5'], // Soft Emerald
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

                // Table Borders
                $sheet->getStyle("A{$headerRow}:K{$totalRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FFE2E8F0'],
                        ],
                    ],
                ]);

                // Strategi Bisnis Box (Bawah Tabel)
                $tipStart = $totalRow + 2;
                $sheet->setCellValue("A{$tipStart}", '💡 3 REKOMENDASI STRATEGI MENU UNTUK OWNER:');
                $sheet->mergeCells("A{$tipStart}:K{$tipStart}");
                $sheet->getStyle("A{$tipStart}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FF1E293B']],
                ]);

                $tip1 = $tipStart + 1;
                $tip2 = $tipStart + 2;
                $tip3 = $tipStart + 3;

                $sheet->setCellValue("A{$tip1}", "1. Jaga Pasokan Bahan Menu Juara : Pastikan stok bahan untuk \"{$this->topProductName}\" selalu aman dan tidak kehabisan saat weekend / jam sibuk.");
                $sheet->mergeCells("A{$tip1}:K{$tip1}");
                $sheet->setCellValue("A{$tip2}", '2. Buat Paket Bundling Margin Tinggi : Padukan produk minuman favorit dengan cemilan/snack yang memiliki margin untung > 70% agar omset per meja naik.');
                $sheet->mergeCells("A{$tip2}:K{$tip2}");
                $sheet->setCellValue("A{$tip3}", '3. Upselling Kasir : Biasakan kasir menawarkan upgrade ukuran cup (+Rp 3.000 - Rp 5.000) atau extra shot / topping manis untuk meningkatkan profit harian.');
                $sheet->mergeCells("A{$tip3}:K{$tip3}");

                $sheet->getStyle("A{$tip1}:A{$tip3}")->applyFromArray([
                    'font' => ['size' => 9, 'color' => ['argb' => 'FF475569']],
                ]);
            },
        ];
    }
}
