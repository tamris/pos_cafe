<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Setting;
use App\Models\Transaction;
use App\Services\LoyaltyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerApiController extends Controller
{
    /**
     * Dapatkan daftar member terbaru / aktif untuk tampilan awal modal POS (Hybrid).
     * Endpoint: GET /api/pos/customers
     */
    public function index(Request $request)
    {
        $limit = min(20, max(5, (int) $request->input('limit', 10)));
        $setting = LoyaltyService::getSetting();

        // 1. Ambil ID customer dari transaksi terbaru yang memiliki customer_id
        $recentCustomerIds = Transaction::whereNotNull('customer_id')
            ->orderBy('id', 'desc')
            ->limit(50)
            ->pluck('customer_id')
            ->unique()
            ->take($limit)
            ->values();

        // 2. Query data customer berdasarkan urutan transaksi terbaru
        $customers = Customer::whereIn('id', $recentCustomerIds)
            ->get()
            ->sortBy(function ($c) use ($recentCustomerIds) {
                return $recentCustomerIds->search($c->id);
            })
            ->values();

        // 3. Jika belum mencapai limit, gabungkan dengan customer terbaru yang baru terdaftar
        if ($customers->count() < $limit) {
            $needed = $limit - $customers->count();
            $existingIds = $customers->pluck('id')->toArray();
            $extra = Customer::whereNotIn('id', $existingIds)
                ->latest()
                ->limit($needed)
                ->get();
            $customers = $customers->concat($extra);
        }

        $data = $customers->map(function ($c) use ($setting) {
            $points = (int) $c->points_balance;
            $pointValue = LoyaltyService::calculatePointValue($points, $setting);
            $minRedeem = (int) ($setting->point_min_redeem ?? 5);

            return [
                'id' => $c->id,
                'name' => $c->name,
                'phone' => $c->phone,
                'points_balance' => $points,
                'points_rupiah_value' => $pointValue,
                'points_rupiah_formatted' => 'Rp ' . number_format($pointValue, 0, ',', '.'),
                'can_redeem' => $points >= $minRedeem,
                'min_redeem' => $minRedeem,
                'exchange_rate' => (int) ($setting->point_exchange_rate ?? 1000),
                'created_at' => $c->created_at?->format('d/m/Y H:i'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Cari member / pelanggan berdasarkan Nomor WhatsApp atau Nama.
     * Endpoint: GET /api/pos/customers/search?query=08123...
     */
    public function search(Request $request)
    {
        $query = trim($request->input('query', ''));

        if (strlen($query) < 2) {
            return response()->json([
                'success' => true,
                'data' => [],
                'message' => 'Masukkan minimal 2 karakter untuk mencari member.',
            ]);
        }

        $setting = LoyaltyService::getSetting();
        $normalizedPhone = LoyaltyService::normalizePhone($query);

        $customers = Customer::where(function ($q) use ($query, $normalizedPhone) {
            if (!empty($normalizedPhone)) {
                $q->where('phone', 'like', "%{$normalizedPhone}%");
            }
            $q->orWhere('name', 'like', "%{$query}%");
            $q->orWhere('phone', 'like', "%{$query}%");
        })
        ->limit(15)
        ->get();

        $data = $customers->map(function ($c) use ($setting) {
            $points = (int) $c->points_balance;
            $pointValue = LoyaltyService::calculatePointValue($points, $setting);
            $minRedeem = (int) ($setting->point_min_redeem ?? 5);

            return [
                'id' => $c->id,
                'name' => $c->name,
                'phone' => $c->phone,
                'points_balance' => $points,
                'points_rupiah_value' => $pointValue,
                'points_rupiah_formatted' => 'Rp ' . number_format($pointValue, 0, ',', '.'),
                'can_redeem' => $points >= $minRedeem,
                'min_redeem' => $minRedeem,
                'exchange_rate' => (int) ($setting->point_exchange_rate ?? 1000),
                'created_at' => $c->created_at?->format('d/m/Y H:i'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Registrasi member baru dari aplikasi POS kasir (1 ketukan).
     * Endpoint: POST /api/pos/customers
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|min:8|max:20',
            'name' => 'nullable|string|max:100',
        ], [
            'phone.required' => 'Nomor WhatsApp wajib diisi.',
            'phone.min' => 'Nomor WhatsApp minimal 8 digit.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data pendaftaran member tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $normalizedPhone = LoyaltyService::normalizePhone($request->input('phone'));
        $name = trim($request->input('name', '')) ?: null;

        // Cek apakah nomor sudah pernah terdaftar
        $existing = Customer::where('phone', $normalizedPhone)->first();
        if ($existing) {
            // Jika ada nama baru dan nama lama masih default, update
            if (!empty($name) && ($existing->name === 'Pelanggan' || empty($existing->name))) {
                $existing->update(['name' => $name]);
            }

            $setting = LoyaltyService::getSetting();
            $points = (int) $existing->points_balance;
            $pointValue = LoyaltyService::calculatePointValue($points, $setting);
            $minRedeem = (int) ($setting->point_min_redeem ?? 5);

            return response()->json([
                'success' => true,
                'message' => 'Member sudah terdaftar sebelumnya.',
                'is_new' => false,
                'data' => [
                    'id' => $existing->id,
                    'name' => $existing->name,
                    'phone' => $existing->phone,
                    'points_balance' => $points,
                    'points_rupiah_value' => $pointValue,
                    'points_rupiah_formatted' => 'Rp ' . number_format($pointValue, 0, ',', '.'),
                    'can_redeem' => $points >= $minRedeem,
                    'min_redeem' => $minRedeem,
                    'exchange_rate' => (int) ($setting->point_exchange_rate ?? 1000),
                    'created_at' => $existing->created_at?->format('d/m/Y H:i'),
                ],
            ]);
        }

        // Buat member baru
        $customer = Customer::create([
            'phone' => $normalizedPhone,
            'name' => $name ?? 'Pelanggan',
            'points_balance' => 0,
        ]);

        $setting = LoyaltyService::getSetting();
        $minRedeem = (int) ($setting->point_min_redeem ?? 5);

        return response()->json([
            'success' => true,
            'message' => 'Member berhasil didaftarkan.',
            'is_new' => true,
            'data' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'points_balance' => 0,
                'points_rupiah_value' => 0.0,
                'points_rupiah_formatted' => 'Rp 0',
                'can_redeem' => false,
                'min_redeem' => $minRedeem,
                'exchange_rate' => (int) ($setting->point_exchange_rate ?? 1000),
                'created_at' => $customer->created_at?->format('d/m/Y H:i'),
            ],
        ], 201);
    }

    /**
     * Dapatkan detail member beserta riwayat transaksi & poin.
     * Endpoint: GET /api/pos/customers/{id}
     */
    public function show($id)
    {
        $customer = Customer::with(['pointHistories' => function ($q) {
            $q->latest()->limit(25);
        }])->find($id);

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Data member tidak ditemukan.',
            ], 404);
        }

        $setting = LoyaltyService::getSetting();
        $points = (int) $customer->points_balance;
        $pointValue = LoyaltyService::calculatePointValue($points, $setting);
        $minRedeem = (int) ($setting->point_min_redeem ?? 5);

        $histories = $customer->pointHistories->map(function ($h) {
            return [
                'id' => $h->id,
                'type' => $h->type,
                'points' => (int) $h->points,
                'balance_after' => (int) $h->balance_after,
                'description' => $h->description,
                'date' => $h->created_at?->format('d/m/Y H:i'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'points_balance' => $points,
                'points_rupiah_value' => $pointValue,
                'points_rupiah_formatted' => 'Rp ' . number_format($pointValue, 0, ',', '.'),
                'can_redeem' => $points >= $minRedeem,
                'min_redeem' => $minRedeem,
                'exchange_rate' => (int) ($setting->point_exchange_rate ?? 1000),
                'created_at' => $customer->created_at?->format('d/m/Y H:i'),
                'histories' => $histories,
            ],
        ]);
    }
}