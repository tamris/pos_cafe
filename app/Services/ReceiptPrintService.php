<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Transaction;
use App\Models\CashierShift;

class ReceiptPrintService
{
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

        // FEED KERTAS 4 BARIS AGAR PAS DISOBEK
        $raw .= "\r\n\r\n\r\n\r\n";

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
        if ($shift->end_time) {
            $durStr = $shift->start_time->diffInHours($shift->end_time) . " Jam " . ($shift->start_time->diffInMinutes($shift->end_time) % 60) . " Mnt";
            $raw .= self::line32("Durasi", $durStr) . "\r\n";
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

        $raw .= "--------------------------------\r\n";
        $raw .= $ALIGN_CENTER;
        $raw .= "Dicetak pada " . now()->format('d/m/Y H:i:s') . "\r\n";
        $raw .= "\r\n\r\n\r\n\r\n";

        return $raw;
    }
}
