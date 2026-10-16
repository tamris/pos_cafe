<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerPointHistory;
use App\Models\Setting;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LoyaltyService
{
    /**
     * Normalisasi nomor telepon ke format standar (08xxx)
     */
    public static function normalizePhone(?string $phone): string
    {
        if (empty($phone)) {
            return '';
        }

        // Hapus karakter non-digit selain tanda tambah
        $cleaned = preg_replace('/[^0-9+]/', '', trim($phone));

        // Ubah +62 atau 62 menjadi 0
        if (str_starts_with($cleaned, '+62')) {
            $cleaned = '0' . substr($cleaned, 3);
        } elseif (str_starts_with($cleaned, '62')) {
            $cleaned = '0' . substr($cleaned, 2);
        }

        return $cleaned;
    }

    /**
     * Dapatkan instance pengaturan cafe
     */
    public static function getSetting(): Setting
    {
        return Setting::first() ?? new Setting([
            'point_enabled' => true,
            'point_spend_per_point' => 10000,
            'point_exchange_rate' => 1000,
            'point_min_redeem' => 5,
        ]);
    }

    /**
     * Hitung poin yang didapatkan dari subtotal belanja
     */
    public static function calculateEarnedPoints(float $subtotal, ?Setting $setting = null): int
    {
        $setting = $setting ?? self::getSetting();

        if (!($setting->point_enabled ?? true)) {
            return 0;
        }

        $spendPerPoint = max(1, (int) ($setting->point_spend_per_point ?? 10000));
        return (int) floor($subtotal / $spendPerPoint);
    }

    /**
     * Hitung nilai rupiah dari sejumlah poin
     */
    public static function calculatePointValue(int $points, ?Setting $setting = null): float
    {
        $setting = $setting ?? self::getSetting();
        $exchangeRate = max(1, (int) ($setting->point_exchange_rate ?? 1000));

        return (float) ($points * $exchangeRate);
    }

    /**
     * Cari atau buat pelanggan baru berdasarkan nomor telepon
     */
    public static function findOrCreateCustomer(string $phone, ?string $name = null): Customer
    {
        $normalized = self::normalizePhone($phone);
        $customer = Customer::where('phone', $normalized)->first();

        if ($customer) {
            if (!empty($name) && ($customer->name === 'Pelanggan' || empty($customer->name))) {
                $customer->update(['name' => trim($name)]);
            }
            return $customer;
        }

        return Customer::create([
            'name' => !empty($name) ? trim($name) : 'Pelanggan',
            'phone' => $normalized,
            'points_balance' => 0,
        ]);
    }

    /**
     * Tambahkan poin ke saldo customer dengan pencatatan mutasi
     */
    public static function addPoints(
        Customer $customer,
        int $points,
        ?Transaction $transaction = null,
        string $description = 'Poin dari belanja'
    ): CustomerPointHistory {
        return DB::transaction(function () use ($customer, $points, $transaction, $description) {
            $customer->increment('points_balance', $points);
            $customer->refresh();

            return CustomerPointHistory::create([
                'customer_id' => $customer->id,
                'transaction_id' => $transaction?->id,
                'type' => 'earn',
                'points' => $points,
                'balance_after' => $customer->points_balance,
                'description' => $description,
            ]);
        });
    }

    /**
     * Tukar / gunakan poin customer dengan pencatatan mutasi
     */
    public static function redeemPoints(
        Customer $customer,
        int $points,
        ?Transaction $transaction = null,
        string $description = 'Tukar poin untuk potongan belanja'
    ): CustomerPointHistory {
        return DB::transaction(function () use ($customer, $points, $transaction, $description) {
            if ($customer->points_balance < $points) {
                throw new \Exception('Saldo poin tidak mencukupi.');
            }

            $customer->decrement('points_balance', $points);
            $customer->refresh();

            return CustomerPointHistory::create([
                'customer_id' => $customer->id,
                'transaction_id' => $transaction?->id,
                'type' => 'redeem',
                'points' => -$points,
                'balance_after' => $customer->points_balance,
                'description' => $description,
            ]);
        });
    }

    /**
     * Generate token unik 1x pakai untuk QR klaim struk
     */
    public static function generateClaimToken(): string
    {
        do {
            $token = Str::upper(Str::random(8));
        } while (Transaction::where('claim_token', $token)->exists());

        return $token;
    }

    /**
     * Dapatkan URL lengkap untuk QR klaim poin struk
     */
    public static function getClaimUrl(string $token): string
    {
        $baseUrl = config('app.customer_url');

        if (empty($baseUrl)) {
            $baseUrl = config('app.url');
        }

        // Jika baseUrl kosong atau masih localhost/127.0.0.1,
        // coba deteksi otomatis host aktif jika request datang dari browser/klien
        $isLocalhost = empty($baseUrl) 
            || str_contains($baseUrl, 'localhost') 
            || str_contains($baseUrl, '127.0.0.1');

        if ($isLocalhost && request()->hasHeader('host')) {
            $currentHost = request()->getHost();
            if (!str_contains($currentHost, 'localhost') && !str_contains($currentHost, '127.0.0.1')) {
                $baseUrl = request()->getSchemeAndHttpHost();
            }
        }

        if (empty($baseUrl)) {
            $baseUrl = url('/');
        }

        return rtrim($baseUrl, '/') . '/claim/' . $token;
    }

    /**
     * Klaim poin dari struk belanja mandiri oleh pelanggan
     */
    public static function claimReceipt(string $claimToken, string $phone, ?string $name = null): array
    {
        $normalizedPhone = self::normalizePhone($phone);

        if (empty($normalizedPhone)) {
            return [
                'success' => false,
                'message' => 'Nomor WhatsApp wajib diisi.',
            ];
        }

        return DB::transaction(function () use ($claimToken, $normalizedPhone, $name) {
            // Kunci baris transaksi dengan lockForUpdate untuk mencegah race condition (double claim)
            $transaction = Transaction::where('claim_token', $claimToken)
                ->lockForUpdate()
                ->first();

            if (!$transaction) {
                return [
                    'success' => false,
                    'message' => 'Kode struk tidak valid atau tidak ditemukan.',
                ];
            }

            // Cek apakah sudah pernah diklaim
            if ($transaction->points_claimed_at !== null || $transaction->customer_id !== null) {
                $claimedTime = $transaction->points_claimed_at 
                    ? $transaction->points_claimed_at->format('d/m/Y H:i') 
                    : 'sebelumnya';
                return [
                    'success' => false,
                    'message' => "Struk ini sudah pernah diklaim pada {$claimedTime}.",
                ];
            }

            // Cek batas kedaluwarsa klaim (24 jam)
            if ($transaction->created_at && $transaction->created_at->diffInHours(now()) > 24) {
                return [
                    'success' => false,
                    'message' => 'Masa klaim poin untuk struk ini sudah kedaluwarsa (maksimal 24 jam).',
                ];
            }

            // Hitung poin jika belum diset
            $pointsEarned = $transaction->points_earned > 0 
                ? $transaction->points_earned 
                : self::calculateEarnedPoints((float) $transaction->subtotal);

            if ($pointsEarned <= 0) {
                return [
                    'success' => false,
                    'message' => 'Transaksi ini tidak memenuhi syarat untuk mendapatkan poin.',
                ];
            }

            $isNew = !Customer::where('phone', $normalizedPhone)->exists();
            $customer = self::findOrCreateCustomer($normalizedPhone, $name);

            // Update transaksi
            $transaction->update([
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone,
                'points_earned' => $pointsEarned,
                'points_claimed_at' => now(),
            ]);

            // Tambahkan poin ke member
            self::addPoints(
                $customer,
                $pointsEarned,
                $transaction,
                "Klaim poin struk #{$transaction->invoice_number}"
            );

            return [
                'success' => true,
                'is_new_customer' => $isNew,
                'message' => "Berhasil klaim +{$pointsEarned} Poin!",
                'customer' => $customer,
                'points_earned' => $pointsEarned,
                'balance_now' => $customer->points_balance,
                'invoice_number' => $transaction->invoice_number,
            ];
        });
    }
}
