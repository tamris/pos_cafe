<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;

class SalesReportExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithStyles,
    WithColumnFormatting,
    WithEvents
{
    protected $dateFrom;
    protected $dateTo;
    private $totalRows = 0;

    public function __construct(string $dateFrom, string $dateTo)
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    public function query()
    {
        $start = $this->dateFrom . ' 00:00:00';
        $end = $this->dateTo . ' 23:59:59';

        $query = Transaction::with(['user', 'details'])
            ->withSum('details', 'profit')
            ->whereBetween('created_at', [$start, $end])
            ->where('status', 'completed')
            ->latest();

        $this->totalRows = $query->count(); 
            
        return $query;
    }

    public function headings(): array
    {
        $formattedStart = Carbon::parse($this->dateFrom)->translatedFormat('d F Y');
        $formattedEnd = Carbon::parse($this->dateTo)->translatedFormat('d F Y');

        $shopName = \App\Models\Setting::first()?->shop_name ?? 'CAFE & EATERY';
        return [
            ['LAPORAN PENJUALAN ' . strtoupper($shopName)],
            ['Periode: ' . $formattedStart . ' - ' . $formattedEnd],
            [''],
            [
                'Invoice',
                'Tanggal',
                'Kasir / Barista',
                'Tipe Pesanan',
                'Meja / Pelanggan',
                'Subtotal',
                'Diskon',
                'Pajak',
                'Total Penjualan',
                'Total Profit',
                'Metode Pembayaran',
            ]
        ];
    }

    public function map($transaction): array
    {
        $orderTypeLabel = strtoupper(str_replace('_', ' ', $transaction->order_type ?? 'dine_in'));
        $tableInfo = ($transaction->order_type === 'dine_in')
            ? ($transaction->table_number ? 'Meja: ' . $transaction->table_number : '-')
            : ($transaction->customer_name ? 'Cust: ' . $transaction->customer_name : '-');

        return [
            $transaction->invoice_number,
            $transaction->created_at->format('d/m/Y H:i'),
            $transaction->user->name ?? 'Admin',
            $orderTypeLabel,
            $tableInfo,
            (float) $transaction->subtotal,
            (float) $transaction->discount,
            (float) $transaction->tax,
            (float) $transaction->total,
            (float) ($transaction->details_sum_profit ?? 0),
            ucfirst($transaction->payment_method),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => 'FFB45309']]], // Amber brown
            2 => ['font' => ['bold' => true, 'size' => 11]],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'F' => '#,##0',
            'G' => '#,##0',
            'H' => '#,##0',
            'I' => '#,##0',
            'J' => '#,##0',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                $headerRow = 4;
                $startDataRow = 5;
                $hasData = $this->totalRows > 0;
                $lastRow = $hasData ? ($startDataRow + $this->totalRows - 1) : $startDataRow;
                $totalRow = $lastRow + 1; 

                $sheet->mergeCells('A1:K1');
                $sheet->mergeCells('A2:K2');
                $sheet->getStyle('A1:A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Styling Header Tabel (Warna Cokelat Kopi Amber)
                $sheet->getStyle("A{$headerRow}:K{$headerRow}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['argb' => 'FFFFFFFF'], 
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFD97706'], // Amber 600
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getRowDimension($headerRow)->setRowHeight(25);

                $sheet->freezePane('A5'); 

                // Set Baris Grand Total
                $sheet->setCellValue("A{$totalRow}", 'GRAND TOTAL');
                $sheet->mergeCells("A{$totalRow}:E{$totalRow}");
                
                if ($hasData) {
                    $sheet->setCellValue("F{$totalRow}", "=SUM(F{$startDataRow}:F{$lastRow})");
                    $sheet->setCellValue("G{$totalRow}", "=SUM(G{$startDataRow}:G{$lastRow})");
                    $sheet->setCellValue("H{$totalRow}", "=SUM(H{$startDataRow}:H{$lastRow})");
                    $sheet->setCellValue("I{$totalRow}", "=SUM(I{$startDataRow}:I{$lastRow})");
                    $sheet->setCellValue("J{$totalRow}", "=SUM(J{$startDataRow}:J{$lastRow})");
                } else {
                    $sheet->setCellValue("F{$totalRow}", 0);
                    $sheet->setCellValue("G{$totalRow}", 0);
                    $sheet->setCellValue("H{$totalRow}", 0);
                    $sheet->setCellValue("I{$totalRow}", 0);
                    $sheet->setCellValue("J{$totalRow}", 0);
                }

                $sheet->getStyle("A{$totalRow}:K{$totalRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FEF3C7'], // Amber 100
                    ],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                    'borders' => [
                        'top' => ['borderStyle' => Border::BORDER_DOUBLE], 
                    ]
                ]);

                $sheet->getStyle("F{$totalRow}:J{$totalRow}")
                      ->getNumberFormat()
                      ->setFormatCode('#,##0');

                $sheet->getStyle("A{$headerRow}:K{$totalRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FFE2E8F0'],
                        ],
                    ],
                ]);

                if ($hasData) {
                    $sheet->getStyle("B{$startDataRow}:E{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("K{$startDataRow}:K{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Warna Kondisional Pembayaran
                    for ($i = $startDataRow; $i <= $lastRow; $i++) {
                        $paymentMethod = $sheet->getCell("K{$i}")->getValue();
                        $color = null;

                        if (strcasecmp($paymentMethod, 'Cash') == 0) {
                            $color = 'FFE2E8F0';
                        } elseif (strcasecmp($paymentMethod, 'Transfer') == 0) {
                            $color = 'FFD1FAE5';
                        } elseif (strcasecmp($paymentMethod, 'Qris') == 0) {
                            $color = 'FFDBEAFE';
                        }

                        if ($color) {
                            $sheet->getStyle("K{$i}")->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()->setARGB($color);
                        }
                    }
                }
            },
        ];
    }
}