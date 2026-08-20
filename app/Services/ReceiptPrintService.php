<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Transaction;
use App\Models\CashierShift;
use Illuminate\Support\Facades\Storage;

class ReceiptPrintService
{
    /**
     * Konversi file gambar logo menjadi ESC/POS Monochrome Raster Bit-Image (GS v 0)
     */
    public static function convertImageToEscPosRaster(?string $imageRelativePath, int $maxWidth = 280): string
    {
        if (empty($imageRelativePath)) {
            return '';
        }

        $fullPath = Storage::disk('public')->path($imageRelativePath);
        if (!file_exists($fullPath)) {
            $fullPath = public_path('storage/' . $imageRelativePath);
            if (!file_exists($fullPath)) {
                return '';
            }
        }

        if (!extension_loaded('gd')) {
            return '';
        }

        try {
            $imageContent = file_get_contents($fullPath);
            if ($imageContent === false) {
                return '';
            }

            $img = @imagecreatefromstring($imageContent);
            if (!$img) {
                return '';
            }

            $w = imagesx($img);
            $h = imagesy($img);
            if ($w <= 0 || $h <= 0) {
                imagedestroy($img);
                return '';
            }

            // 1. Auto-detect content bounding box (Trim spasi kosong / transparan di sekeliling logo)
            $minX = $w; $minY = $h; $maxX = 0; $maxY = 0;
            $hasContent = false;

            for ($y = 0; $y < $h; $y++) {
                for ($x = 0; $x < $w; $x++) {
                    $rgba = imagecolorat($img, $x, $y);
                    $colors = imagecolorsforindex($img, $rgba);
                    $isTransparent = (isset($colors['alpha']) && $colors['alpha'] > 80);
                    $isWhite = ($colors['red'] > 245 && $colors['green'] > 245 && $colors['blue'] > 245);

                    if (!$isTransparent && !$isWhite) {
                        $hasContent = true;
                        if ($x < $minX) $minX = $x;
                        if ($x > $maxX) $maxX = $x;
                        if ($y < $minY) $minY = $y;
                        if ($y > $maxY) $maxY = $y;
                    }
                }
            }

            if ($hasContent) {
                $pad = 4;
                $cropX = max(0, $minX - $pad);
                $cropY = max(0, $minY - $pad);
                $cropW = min($w - $cropX, ($maxX - $minX + 1) + ($pad * 2));
                $cropH = min($h - $cropY, ($maxY - $minY + 1) + ($pad * 2));

                $cropped = imagecreatetruecolor($cropW, $cropH);
                imagealphablending($cropped, false);
                imagesavealpha($cropped, true);
                $transparent = imagecolorallocatealpha($cropped, 255, 255, 255, 127);
                imagefilledrectangle($cropped, 0, 0, $cropW, $cropH, $transparent);
                imagecopy($cropped, $img, 0, 0, $cropX, $cropY, $cropW, $cropH);
                imagedestroy($img);
                $img = $cropped;
                $origWidth = $cropW;
                $origHeight = $cropH;
            } else {
                $origWidth = $w;
                $origHeight = $h;
            }

            // 2. Hitung ukuran proporsional
            $targetWidth = min($origWidth, $maxWidth);
            $targetHeight = (int) round(($origHeight / $origWidth) * $targetWidth);

            // Sesuaikan width agar kelipatan 8 (byte aligned)
            $width = (int) (ceil($targetWidth / 8) * 8);
            $height = $targetHeight;

            // Buat canvas baru warna putih
            $resized = imagecreatetruecolor($width, $height);
            $white = imagecolorallocate($resized, 255, 255, 255);
            imagefill($resized, 0, 0, $white);

            // Copy and resize
            imagecopyresampled($resized, $img, 0, 0, 0, 0, $width, $height, $origWidth, $origHeight);
            imagedestroy($img);

            $widthBytes = (int) ($width / 8);
            $xL = $widthBytes % 256;
            $xH = (int) floor($widthBytes / 256);
            $yL = $height % 256;
            $yH = (int) floor($height / 256);

            $rawBytes = '';

            for ($y = 0; $y < $height; $y++) {
                for ($xByte = 0; $xByte < $widthBytes; $xByte++) {
                    $byte = 0;
                    for ($bit = 0; $bit < 8; $bit++) {
                        $x = ($xByte * 8) + $bit;
                        $rgb = imagecolorat($resized, $x, $y);
                        $colors = imagecolorsforindex($resized, $rgb);

                        // Check transparency / alpha
                        if (isset($colors['alpha']) && $colors['alpha'] > 80) {
                            $isBlack = 0; // Transparan dianggap putih
                        } else {
                            $r = $colors['red'];
                            $g = $colors['green'];
                            $b = $colors['blue'];
                            // Luminance threshold
                            $luminance = (0.299 * $r) + (0.587 * $g) + (0.114 * $b);
                            $isBlack = ($luminance < 160) ? 1 : 0;
                        }

                        $byte = ($byte << 1) | $isBlack;
                    }
                    $rawBytes .= chr($byte);
                }
            }

            imagedestroy($resized);

            // Perintah GS v 0 m xL xH yL yH
            $rasterCommand = "\x1b\x61\x01" . "\x1d\x76\x30\x00" . chr($xL) . chr($xH) . chr($yL) . chr($yH) . $rawBytes . "\r\n";
            return $rasterCommand;
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * Format baris 32 kolom presisi untuk thermal printer 58mm (Font A Standard)
     */
    public static function line32(string $left, string $right, int $width = 32): string
    {
        $left = trim($left);
        $right = trim($right);
        $leftLen = strlen($left);
        $rightLen = strlen($right);

        if ($leftLen + $rightLen >= $width) {
            $maxLeft = $width - $rightLen - 1;
            if ($maxLeft > 0) {
                $left = substr($left, 0, $maxLeft);
            }
            $spaces = 1;
        } else {
            $spaces = $width - $leftLen - $rightLen;
        }

        return $left . str_repeat(' ', $spaces) . $right;
    }

    public static function buildTransactionEscPos(Transaction $transaction, ?Setting $setting = null): string
    {
        if (!$setting) {
            $setting = Setting::first();
        }

        $esc = "\x1b";
        $INIT = $esc . "@";
        $ALIGN_CENTER = $esc . "a\x01";
        $ALIGN_LEFT = $esc . "a\x00";
        $FONT_NORMAL = $esc . "!\x00";
        $FONT_BOLD = $esc . "!\x08";

        $raw = $INIT . $FONT_NORMAL;

        // 0. LOGO CAFE ESC/POS (JIKA ADA & DIAKTIFKAN)
        if (($setting->show_logo_receipt ?? true) && !empty($setting->shop_logo)) {
            $logoEscPos = self::convertImageToEscPosRaster($setting->shop_logo);
            if (!empty($logoEscPos)) {
                $raw .= $logoEscPos;
            }
        }

        // 1. HEADER TOKO (CENTER & BOLD)
        $raw .= $ALIGN_CENTER;
        $shopName = strtoupper($setting->shop_name ?? 'POS CAFE');
        $raw .= $FONT_BOLD . $shopName . $FONT_NORMAL . "\r\n";
        
        if (!empty($setting->address)) {
            $raw .= $setting->address . "\r\n";
        }
        if (!empty($setting->phone)) {
            $raw .= "Telp: " . $setting->phone . "\r\n";
        }
        $raw .= "--------------------------------\r\n";

        // 2. METADATA TRANSAKSI (LEFT)
        $raw .= $ALIGN_LEFT;
        $raw .= self::line32("No. Inv", $transaction->invoice_number) . "\r\n";
        $raw .= self::line32("Waktu", date('d/m/Y H:i', strtotime($transaction->created_at))) . "\r\n";
        $raw .= self::line32("Kasir", $transaction->user->name ?? 'Staff') . "\r\n";

        if (!empty($transaction->table_number) || !empty($transaction->customer_name)) {
            $orderTypeStr = (($transaction->order_type ?? 'dine_in') === 'dine_in')
                ? 'DINE IN' . ($transaction->table_number ? ' (MEJA ' . $transaction->table_number . ')' : '')
                : ((($transaction->order_type ?? '') === 'take_away') ? 'TAKE AWAY' : 'DELIVERY');
            $raw .= self::line32("Pesanan", $orderTypeStr) . "\r\n";

            if (!empty($transaction->customer_name)) {
                $raw .= self::line32("Pelanggan", $transaction->customer_name) . "\r\n";
            }
        }
        
        $raw .= "--------------------------------\r\n";

        // 3. DAFTAR ITEM PESANAN
        foreach ($transaction->details as $detail) {
            $productName = $detail->product->name ?? 'Item';
            $raw .= $FONT_BOLD . $productName . $FONT_NORMAL . "\r\n";
            
            $qtyPrice = $detail->quantity . " x " . number_format($detail->price, 0, ',', '.');
            $subtotal = number_format($detail->subtotal, 0, ',', '.');
            $raw .= self::line32($qtyPrice, $subtotal) . "\r\n";

            if (!empty($detail->notes)) {
                $raw .= " * " . $detail->notes . "\r\n";
            }
        }
        
        $raw .= "--------------------------------\r\n";

        // 4. SUB TOTAL & DISKON & PAJAK
        $raw .= self::line32("Subtotal", number_format($transaction->subtotal, 0, ',', '.')) . "\r\n";

        if ($transaction->discount > 0) {
            $discNominal = ($transaction->discount <= 100)
                ? ($transaction->subtotal * $transaction->discount / 100)
                : $transaction->discount;
            $discLabel = "Diskon" . ($transaction->discount <= 100 ? ' (' . $transaction->discount . '%)' : '');
            $raw .= self::line32($discLabel, "-" . number_format($discNominal, 0, ',', '.')) . "\r\n";
        }

        if ($transaction->tax > 0) {
            $taxNominal = ($transaction->tax <= 100)
                ? ($transaction->subtotal * $transaction->tax / 100)
                : $transaction->tax;
            $taxLabel = "Pajak" . ($transaction->tax <= 100 ? ' (' . $transaction->tax . '%)' : '');
            $raw .= self::line32($taxLabel, "+" . number_format($taxNominal, 0, ',', '.')) . "\r\n";
        }

        // 5. GRAND TOTAL (LEBIH BESAR & BOLD)
        $raw .= "--------------------------------\r\n";
        $raw .= $esc . "!\x18" . self::line32("TOTAL", "Rp " . number_format($transaction->total, 0, ',', '.')) . $FONT_NORMAL . "\r\n";
        $raw .= "--------------------------------\r\n";

        // 6. PEMBAYARAN & KEMBALIAN
        $payMethod = strtoupper($transaction->payment_method === 'cash' ? 'TUNAI' : ($transaction->payment_method === 'transfer' ? 'TRANSFER' : 'QRIS'));
        $raw .= self::line32("Bayar (" . $payMethod . ")", number_format($transaction->paid, 0, ',', '.')) . "\r\n";
        $raw .= self::line32("Kembali", number_format($transaction->change, 0, ',', '.')) . "\r\n";

        // 7. WIFI CAFE
        if (!empty($setting->wifi_name) || !empty($setting->wifi_password)) {
            $raw .= "--------------------------------\r\n";
            $raw .= $ALIGN_CENTER;
            $wifiStr = "WiFi: " . ($setting->wifi_name ?? '-');
            if (!empty($setting->wifi_password)) {
                $wifiStr .= " | Pass: " . $setting->wifi_password;
            }
            $raw .= $wifiStr . "\r\n";
        }

        // 8. FOOTER
        $raw .= "--------------------------------\r\n";
        $raw .= $ALIGN_CENTER;
        $raw .= ($setting->receipt_footer ?? 'Terima kasih telah berkunjung ke Cafe!') . "\r\n";
        $raw .= "-- Have a Good Coffee Day --\r\n";

        // FEED KERTAS 4 BARIS & PERINTAH AUTO-CUT (GS V 0)
        $raw .= "\r\n\r\n\r\n\r\n\x1d\x56\x00";

        return $raw;
    }

    public static function buildShiftEscPos(CashierShift $shift, ?Setting $setting = null): string
    {
        if (!$setting) {
            $setting = Setting::first();
        }

        $esc = "\x1b";
        $INIT = $esc . "@";
        $ALIGN_CENTER = $esc . "a\x01";
        $ALIGN_LEFT = $esc . "a\x00";
        $FONT_NORMAL = $esc . "!\x00";
        $FONT_BOLD = $esc . "!\x08";

        $raw = $INIT . $FONT_NORMAL;

        // Logo Shift Header
        if (($setting->show_logo_receipt ?? true) && !empty($setting->shop_logo)) {
            $logoEscPos = self::convertImageToEscPosRaster($setting->shop_logo);
            if (!empty($logoEscPos)) {
                $raw .= $logoEscPos;
            }
        }

        // Header
        $raw .= $ALIGN_CENTER;
        $raw .= $FONT_BOLD . strtoupper($setting->shop_name ?? 'POS CAFE') . $FONT_NORMAL . "\r\n";
        if (!empty($setting->address)) {
            $raw .= $setting->address . "\r\n";
        }
        if (!empty($setting->phone)) {
            $raw .= "Telp: " . $setting->phone . "\r\n";
        }
        $raw .= "--------------------------------\r\n";
        $raw .= $FONT_BOLD . "*** REKAP SHIFT ***" . $FONT_NORMAL . "\r\n";
        $raw .= ($shift->status === 'closed' ? 'STATUS: DITUTUP (FINAL)' : 'STATUS: SHIFT AKTIF') . "\r\n";
        $raw .= "--------------------------------\r\n";

        // Meta
        $raw .= $ALIGN_LEFT;
        $raw .= self::line32("Shift ID", "#SFT-" . str_pad($shift->id, 5, '0', STR_PAD_LEFT)) . "\r\n";
        $raw .= self::line32("Kasir", $shift->user->name ?? '-') . "\r\n";
        $raw .= self::line32("Buka Shift", $shift->start_time ? $shift->start_time->format('d/m/y H:i') : '-') . "\r\n";
        $raw .= self::line32("Tutup Shift", $shift->end_time ? $shift->end_time->format('d/m/y H:i') : '(Belum Ditutup)') . "\r\n";
        if ($shift->end_time && $shift->start_time) {
            $totalMinutes = (int) $shift->start_time->diffInMinutes($shift->end_time);
            $hours = intdiv($totalMinutes, 60);
            $minutes = $totalMinutes % 60;
            $durStr = $hours > 0 ? "{$hours} Jam {$minutes} Mnt" : "{$minutes} Menit";
            $raw .= self::line32("Durasi Kerja", $durStr) . "\r\n";
        }
        $raw .= "--------------------------------\r\n";

        // Penjualan
        $raw .= $FONT_BOLD . "RINCIAN PENJUALAN" . $FONT_NORMAL . "\r\n";
        $raw .= self::line32("Total Struk", number_format($shift->total_transactions, 0, ',', '.') . " Trx") . "\r\n";
        $raw .= self::line32("Penjualan Tunai", "Rp " . number_format($shift->cash_sales, 0, ',', '.')) . "\r\n";
        $raw .= self::line32("Penjualan QRIS", "Rp " . number_format($shift->qris_sales, 0, ',', '.')) . "\r\n";
        $raw .= self::line32("Penjualan Transfer", "Rp " . number_format($shift->transfer_sales, 0, ',', '.')) . "\r\n";
        $raw .= "--------------------------------\r\n";

        // Total Omset (Lebih Besar & Bold)
        $raw .= $esc . "!\x18" . self::line32("TOTAL OMSET", "Rp " . number_format($shift->total_sales, 0, ',', '.')) . $FONT_NORMAL . "\r\n";
        $raw .= "--------------------------------\r\n";

        // Kas Laci
        $raw .= $FONT_BOLD . "REKONSILIASI KAS LACI" . $FONT_NORMAL . "\r\n";
        $raw .= self::line32("Modal Kas Awal", "Rp " . number_format($shift->starting_cash, 0, ',', '.')) . "\r\n";
        $raw .= self::line32("(+) Total Tunai", "Rp " . number_format($shift->cash_sales, 0, ',', '.')) . "\r\n";
        $raw .= $FONT_BOLD . self::line32("(=) Kas Harapan", "Rp " . number_format($shift->expected_cash, 0, ',', '.')) . $FONT_NORMAL . "\r\n";

        if ($shift->status === 'closed') {
            $raw .= self::line32("Uang Fisik Kas", "Rp " . number_format($shift->actual_cash ?? 0, 0, ',', '.')) . "\r\n";
            $diff = (float) ($shift->difference ?? 0);
            $diffStr = $diff == 0 ? "Rp 0 (PAS)" : ($diff > 0 ? "+Rp " . number_format($diff, 0, ',', '.') . " (LEBIH)" : "-Rp " . number_format(abs($diff), 0, ',', '.') . " (KURANG)");
            $raw .= "--------------------------------\r\n";
            $raw .= $FONT_BOLD . self::line32("SELISIH KAS", $diffStr) . $FONT_NORMAL . "\r\n";
        }

        if (!empty($shift->notes)) {
            $raw .= "Catatan: " . $shift->notes . "\r\n";
        }

        // Tanda Tangan
        $raw .= "\r\n";
        $raw .= self::line32("   Kasir", "Supervisor  ") . "\r\n\r\n\r\n";
        $kasirName = substr($shift->user->name ?? 'Kasir', 0, 10);
        $raw .= self::line32(" ( " . $kasirName . " )", "( .......... )") . "\r\n";

        $raw .= "--------------------------------\r\n";
        $raw .= $ALIGN_CENTER;
        $raw .= "Dicetak: " . now()->format('d/m/Y H:i:s') . "\r\n";
        $raw .= "\r\n\r\n\r\n\r\n\x1d\x56\x00";

        return $raw;
    }

    /**
     * Format Tiket Pesanan Khusus Dapur / Kitchen / Barista (Tanpa info harga)
     */
    public static function buildKitchenEscPos(Transaction $transaction, ?Setting $setting = null): string
    {
        if (!$setting) {
            $setting = Setting::first();
        }

        $esc = "\x1b";
        $INIT = $esc . "@";
        $ALIGN_CENTER = $esc . "a\x01";
        $ALIGN_LEFT = $esc . "a\x00";
        $FONT_NORMAL = $esc . "!\x00";
        $FONT_BOLD = $esc . "!\x08";
        $FONT_LARGE = $esc . "!\x18"; // Double Height + Bold

        $raw = $INIT . $FONT_NORMAL;

        // 1. HEADER DAPUR / KITCHEN
        $raw .= $ALIGN_CENTER;
        $raw .= $FONT_LARGE . "*** TIKET DAPUR ***" . $FONT_NORMAL . "\r\n";
        $raw .= $FONT_BOLD . strtoupper($setting->shop_name ?? 'POS CAFE') . $FONT_NORMAL . "\r\n";
        $raw .= "--------------------------------\r\n";

        // 2. METADATA ORDER
        $raw .= $ALIGN_LEFT;
        $raw .= self::line32("No. Inv", $transaction->invoice_number) . "\r\n";
        $raw .= self::line32("Waktu", date('d/m/Y H:i:s', strtotime($transaction->created_at))) . "\r\n";
        $raw .= self::line32("Kasir", $transaction->user->name ?? 'Kasir') . "\r\n";

        // 3. TIPE PESANAN & MEJA (BESAR & TEBAL)
        $raw .= "--------------------------------\r\n";
        $raw .= $ALIGN_CENTER;
        $orderTypeStr = (($transaction->order_type ?? 'dine_in') === 'dine_in')
            ? 'DINE IN' . ($transaction->table_number ? ' (MEJA ' . $transaction->table_number . ')' : '')
            : ((($transaction->order_type ?? '') === 'take_away') ? 'TAKE AWAY (BUNGKUS)' : 'DELIVERY (KIRIM)');
        $raw .= $FONT_LARGE . $orderTypeStr . $FONT_NORMAL . "\r\n";

        if (!empty($transaction->customer_name)) {
            $raw .= $FONT_BOLD . "Pelanggan: " . $transaction->customer_name . $FONT_NORMAL . "\r\n";
        }
        $raw .= "--------------------------------\r\n";

        // 4. DAFTAR ITEM PESANAN UNTUK BARISTA / CHEF
        $raw .= $ALIGN_LEFT;
        $totalItems = 0;
        foreach ($transaction->details as $detail) {
            $productName = $detail->product->name ?? 'Item';
            $totalItems += (int) $detail->quantity;

            // Baris Item (Double Height + Bold)
            $raw .= $FONT_LARGE . $detail->quantity . "x  " . $productName . $FONT_NORMAL . "\r\n";

            // Catatan Khusus Menu (Jika ada, cetak bold jelas)
            if (!empty($detail->notes)) {
                $raw .= $FONT_BOLD . " >> CATATAN: " . $detail->notes . $FONT_NORMAL . "\r\n";
            }
            $raw .= "\r\n";
        }

        // 5. TOTAL ITEM
        $raw .= "--------------------------------\r\n";
        $raw .= $FONT_LARGE . self::line32("TOTAL ITEM", $totalItems . " Menu") . $FONT_NORMAL . "\r\n";
        $raw .= "--------------------------------\r\n";

        // 6. FOOTER DAPUR
        $raw .= $ALIGN_CENTER;
        $raw .= "-- SEGERA DISIAPKAN --\r\n";
        $raw .= "\r\n\r\n\r\n\r\n\x1d\x56\x00";

        return $raw;
    }
}
