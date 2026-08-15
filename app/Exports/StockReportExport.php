<?php

namespace App\Exports;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;

class StockReportExport implements
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
        $this->dateFrom = $dateFrom . ' 00:00:00';
        $this->dateTo = $dateTo . ' 23:59:59';
    }

    public function query()
    {
        $query = Product::select('products.id', 'products.name', 'products.sku',
                DB::raw('(SELECT COALESCE(SUM(quantity), 0) FROM stock_movements WHERE product_id = products.id AND created_at < ?) as stok_awal'),
                DB::raw('(SELECT COALESCE(SUM(quantity), 0) FROM stock_movements WHERE product_id = products.id AND type = "in" AND created_at BETWEEN ? AND ?) as stok_masuk'),
                DB::raw('(SELECT COALESCE(SUM(quantity), 0) FROM stock_movements WHERE product_id = products.id AND type = "out" AND created_at BETWEEN ? AND ?) as stok_keluar'),
                DB::raw('(SELECT COALESCE(SUM(quantity), 0) FROM stock_movements WHERE product_id = products.id AND type = "adjustment" AND created_at BETWEEN ? AND ?) as koreksi')
            )
            ->addBinding([$this->dateFrom], 'select')
            ->addBinding([$this->dateFrom, $this->dateTo], 'select')
            ->addBinding([$this->dateFrom, $this->dateTo], 'select')
            ->addBinding([$this->dateFrom, $this->dateTo], 'select');

        $this->totalRows = $query->count();
            
        return $query;
    }

    public function headings(): array
    {
        $formattedStart = Carbon::parse(explode(' ', $this->dateFrom)[0])->translatedFormat('d F Y');
        $formattedEnd = Carbon::parse(explode(' ', $this->dateTo)[0])->translatedFormat('d F Y');

        return [
            ['LAPORAN STOK Toko Kendali'], 
            ['Periode: ' . $formattedStart . ' - ' . $formattedEnd], 
            [''], 
            [     
                'Produk',
                'SKU',
                'Stok Awal',
                'Stok Masuk',
                'Stok Keluar',
                'Penyesuaian',
                'Stok Akhir',
            ]
        ];
    }

    public function map($product): array
    {
        $stok_akhir = $product->stok_awal + $product->stok_masuk + $product->stok_keluar + $product->koreksi;
        
        return [
            $product->name,
            $product->sku,
            $product->stok_awal,
            $product->stok_masuk,
            $product->stok_keluar,
            $product->koreksi,
            $stok_akhir,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 16]],
            2 => ['font' => ['bold' => true, 'size' => 11]],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'C' => '#,##0',
            'D' => '#,##0',
            'E' => '#,##0',
            'F' => '#,##0',
            'G' => '#,##0',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                $headerRow = 4;
                $startDataRow = 5;
                $lastRow = $startDataRow + $this->totalRows - 1;
                $totalRow = $lastRow + 1;

                // 1. JUDUL
                $sheet->mergeCells('A1:G1');
                $sheet->mergeCells('A2:G2');
                $sheet->getStyle('A1:A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // 2. HEADER TABEL
                $sheet->getStyle("A{$headerRow}:G{$headerRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF203764']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension($headerRow)->setRowHeight(25);

                // 3. FREEZE PANE
                $sheet->freezePane('A5');

                // 4. TOTAL
                $sheet->setCellValue("A{$totalRow}", 'TOTAL PERGERAKAN');
                $sheet->mergeCells("A{$totalRow}:B{$totalRow}");

                $sheet->setCellValue("C{$totalRow}", "=SUM(C{$startDataRow}:C{$lastRow})");
                $sheet->setCellValue("D{$totalRow}", "=SUM(D{$startDataRow}:D{$lastRow})");
                $sheet->setCellValue("E{$totalRow}", "=SUM(E{$startDataRow}:E{$lastRow})");
                $sheet->setCellValue("F{$totalRow}", "=SUM(F{$startDataRow}:F{$lastRow})");
                $sheet->setCellValue("G{$totalRow}", "=SUM(G{$startDataRow}:G{$lastRow})");

                $sheet->getStyle("A{$totalRow}:G{$totalRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFFF00']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                    'borders' => ['top' => ['borderStyle' => Border::BORDER_DOUBLE]]
                ]);
                $sheet->getStyle("C{$totalRow}:G{$totalRow}")->getNumberFormat()->setFormatCode('#,##0');

                // 5. BORDER ALL
                $sheet->getStyle("A{$headerRow}:G{$totalRow}")->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]]
                ]);

                $sheet->getStyle("B{$startDataRow}:B{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // 6. WARNA KONDISIONAL (FITUR BARU!)
                // Kita loop setiap baris data
                for ($i = $startDataRow; $i <= $lastRow; $i++) {
                    
                    // A. CEK STOK MASUK (Kolom D) - IJO
                    $valMasuk = $sheet->getCell("D{$i}")->getValue();
                    if ($valMasuk != 0) { // Kalau tidak nol
                        $sheet->getStyle("D{$i}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('FFC6EFCE'); // Hijau Pastel
                        
                        // Opsional: Kasih warna text hijau tua biar manis
                        $sheet->getStyle("D{$i}")->getFont()->getColor()->setARGB('FF006100');
                    }

                    // B. CEK STOK KELUAR (Kolom E) - MERAH
                    $valKeluar = $sheet->getCell("E{$i}")->getValue();
                    if ($valKeluar != 0) { // Kalau tidak nol
                        $sheet->getStyle("E{$i}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('FFFFC7CE'); // Merah Pastel
                        
                        // Opsional: Kasih warna text merah tua
                        $sheet->getStyle("E{$i}")->getFont()->getColor()->setARGB('FF9C0006');
                    }

                    // C. CEK PENYESUAIAN (Kolom F) - KUNING
                    $valKoreksi = $sheet->getCell("F{$i}")->getValue();
                    if ($valKoreksi != 0) { // Kalau tidak nol
                        $sheet->getStyle("F{$i}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('FFFFEB9C'); // Kuning Pastel
                        
                        // Opsional: Kasih warna text kuning tua/emas
                        $sheet->getStyle("F{$i}")->getFont()->getColor()->setARGB('FF9C6500');
                    }
                }
            },
        ];
    }
}