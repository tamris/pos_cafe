<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\CashierShift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Get list of active cashiers/users for quick selection.
     */
    public function getCashiers()
    {
        $cashiers = User::active()
            ->select('id', 'name', 'email', 'role')
            ->orderBy('name', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $cashiers,
        ]);
    }

    /**
     * 6-Digit PIN Login for Cashiers.
     */
    public function pinLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pin' => 'required|string|size:6',
            'user_id' => 'nullable|integer|exists:users,id',
            'device_name' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Format PIN tidak valid (wajib 6 digit angka).',
                'errors' => $validator->errors(),
            ], 422);
        }

        $pin = $request->input('pin');
        $userId = $request->input('user_id');

        $query = User::where('pin', $pin);

        if ($userId) {
            $query->where('id', $userId);
        }

        $user = $query->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'PIN salah atau pengguna tidak ditemukan.',
            ], 401);
        }

        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda dinonaktifkan oleh administrator.',
            ], 403);
        }

        // Revoke older tokens for clean session if needed
        $deviceName = $request->input('device_name', 'Flutter-POS-Mobile');
        $token = $user->createToken($deviceName)->plainTextToken;

        // Check for active cashier shift
        $activeShift = CashierShift::where('user_id', $user->id)
            ->where('status', 'open')
            ->latest()
            ->first();

        if ($activeShift) {
            $activeShift->recalculateTotals();
        }

        return response()->json([
            'success' => true,
            'message' => 'Login kasir berhasil.',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
                'has_active_shift' => !is_null($activeShift),
                'active_shift' => $activeShift ? [
                    'id' => $activeShift->id,
                    'start_time' => $activeShift->start_time?->format('Y-m-d H:i:s'),
                    'starting_cash' => (float) $activeShift->starting_cash,
                    'total_sales' => (float) $activeShift->total_sales,
                    'cash_sales' => (float) $activeShift->cash_sales,
                    'qris_sales' => (float) $activeShift->qris_sales,
                    'transfer_sales' => (float) $activeShift->transfer_sales,
                    'total_transactions' => (int) $activeShift->total_transactions,
                    'expected_cash' => (float) $activeShift->expected_cash,
                ] : null,
            ],
        ]);
    }

    /**
     * Get current authenticated user details.
     */
    public function me(Request $request)
    {
        $user = $request->user();

        $activeShift = CashierShift::where('user_id', $user->id)
            ->where('status', 'open')
            ->latest()
            ->first();

        if ($activeShift) {
            $activeShift->recalculateTotals();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
                'has_active_shift' => !is_null($activeShift),
                'active_shift' => $activeShift,
            ],
        ]);
    }

    /**
     * Logout and revoke token.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil logout dari sistem POS.',
        ]);
    }
}
