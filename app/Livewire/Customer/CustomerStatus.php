<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use App\Models\Transaction;
use App\Models\Setting;
use App\Services\MidtransService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.customer')]
#[Title('Status Pesanan - Self Order')]
class CustomerStatus extends Component
{
    public $token;

    public function mount($token)
    {
        $this->token = $token;
        $this->checkMidtransPayment();
    }

    /**
     * Otomatis cek status Midtrans jika transaksi belum lunas
     */
    public function checkMidtransPayment()
    {
        $transaction = Transaction::where('order_token', $this->token)->first();
        if ($transaction && $transaction->payment_status !== 'paid' && $transaction->status !== 'cancelled') {
            try {
                $midtransService = app(MidtransService::class);
                $midtransService->checkTransactionStatus($transaction->invoice_number);
            } catch (\Throwable $e) {
                // Silently ignore network exception on polling
            }
        }
    }

    public function refreshStatus()
    {
        $this->checkMidtransPayment();
    }

    public function render()
    {
        // Jalankan pengecekan status jika masih unpaid
        $this->checkMidtransPayment();

        $transaction = Transaction::with(['details.product'])
            ->where('order_token', $this->token)
            ->firstOrFail();

        $setting = Setting::first();

        return view('livewire.customer.customer-status', [
            'transaction' => $transaction,
            'setting' => $setting,
        ]);
    }
}
