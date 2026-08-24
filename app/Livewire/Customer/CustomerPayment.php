<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use App\Models\Transaction;
use App\Models\Setting;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.customer')]
#[Title('Pembayaran QRIS - Self Order')]
class CustomerPayment extends Component
{
    public $token;
    public $transaction;
    public $isSimulating = false;

    public function mount($token)
    {
        $this->token = $token;
        $this->transaction = Transaction::with(['details.product'])
            ->where('order_token', $token)
            ->firstOrFail();

        // If already paid, go straight to order status
        if ($this->transaction->payment_status === 'paid') {
            return redirect()->route('customer.status', ['token' => $this->token]);
        }
    }

    public function simulatePaymentSuccess()
    {
        $this->isSimulating = true;

        $transaction = Transaction::where('order_token', $this->token)->firstOrFail();
        
        $transaction->update([
            'payment_status' => 'paid',
            'paid' => (float) $transaction->total,
            'change' => 0,
            'status' => 'processing',
        ]);

        return redirect()->route('customer.status', ['token' => $this->token]);
    }

    public function cancelOrder()
    {
        $transaction = Transaction::where('order_token', $this->token)->firstOrFail();
        
        // Only allow cancel if still unpaid
        if ($transaction->payment_status === 'unpaid') {
            $transaction->update([
                'status' => 'cancelled',
                'cancelled_reason' => 'Dibatalkan oleh pelanggan sebelum pembayaran.',
                'cancelled_at' => now(),
            ]);

            $this->dispatch('remove-token-localstorage', token: $this->token);
        }

        return redirect()->route('customer.order');
    }

    public function render()
    {
        $setting = Setting::first();
        return view('livewire.customer.customer-payment', [
            'setting' => $setting,
        ]);
    }
}
