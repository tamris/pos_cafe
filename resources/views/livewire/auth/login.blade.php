<div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-8 border border-amber-200">
    <div class="text-center mb-8">
        <div class="w-16 h-16 bg-amber-600 rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-lg text-3xl">
            ☕
        </div>
        <h1 class="text-2xl font-extrabold text-slate-900 mb-1">POS Kasir Cafe</h1>
        <p class="text-slate-500 text-xs font-medium uppercase tracking-wider">Point of Sale & Management System</p>
    </div>

    @if (session()->has('success') || session()->has('status'))
        <div class="mb-5 p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-medium flex items-center gap-2.5 animate-fade-in">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>{{ session('success') ?? session('status') }}</span>
        </div>
    @endif

    <form wire:submit.prevent="login" class="space-y-5">
        <!-- Email -->
        <div>
            <label for="email" class="block text-sm font-medium text-slate-700 mb-2">Email</label>
            <input type="email" id="email" wire:model.defer="email" placeholder="nama@email.com"
                class="block w-full px-3 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-slate-900 focus:border-transparent">
            @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-sm font-medium text-slate-700 mb-2">Password</label>
            <input type="password" id="password" wire:model.defer="password" placeholder="••••••••"
                class="block w-full px-3 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-slate-900 focus:border-transparent">
            @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Remember Me -->
        <div class="flex items-center">
            <input type="checkbox" id="remember" wire:model="remember"
                class="h-4 w-4 text-slate-900 focus:ring-slate-900 border-slate-300 rounded">
            <label for="remember" class="ml-2 block text-sm text-slate-700">Ingat saya</label>
        </div>

        <!-- Submit -->
        <button type="submit" wire:loading.attr="disabled" 
            class="w-full bg-slate-900 text-white py-3 px-4 rounded-lg hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:ring-offset-2 transition-all duration-200 font-medium">
            <span wire:loading.remove>Masuk</span>
            <span wire:loading>
                <svg class="animate-spin h-5 w-5 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                </svg>
            </span>
        </button>
    </form>
</div>
