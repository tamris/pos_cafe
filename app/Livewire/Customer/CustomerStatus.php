<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use App\Models\Transaction;
use App\Models\Setting;
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
    }

    public function render()
    {
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
