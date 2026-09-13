<div class="min-h-screen bg-[#f8faf9] text-slate-800 font-sans selection:bg-[#0e382c] selection:text-white pb-28 sm:pb-16 overflow-x-hidden">


    {{-- ========================================================================= --}}
    {{-- 1. DINE-IN TABLE ALERT (AUTO-ACTIVE ON TABLE QR SCAN)                     --}}
    {{-- ========================================================================= --}}
    @if(!empty($tableNumber))
        <div class="bg-[#0e382c] text-white py-2.5 px-4 sticky top-0 z-50 shadow-md border-b border-emerald-800/50 reveal-down">
            <div class="max-w-7xl mx-auto flex items-center justify-between gap-3 text-xs sm:text-sm">
                <div class="flex items-center gap-2.5 truncate">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse shrink-0"></span>
                    <span class="truncate">
                        Selamat datang di <strong>Noli Space</strong>! Anda terhubung di <strong>Meja {{ $tableNumber }}</strong>
                    </span>
                </div>
                <a href="{{ route('customer.order', ['table' => $tableNumber]) }}" 
                   class="px-3.5 py-1.5 rounded-xl bg-emerald-400 hover:bg-emerald-300 text-[#0e382c] font-black text-xs transition shadow-sm shrink-0 flex items-center gap-1.5 active:scale-95">
                    <span>Mulai Pesan</span>
                    <i class="fas fa-arrow-right text-[10px]"></i>
                </a>
            </div>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- 2. PREMIUM EDITORIAL NAVBAR                                               --}}
    {{-- ========================================================================= --}}
    <header class="sticky {{ !empty($tableNumber) ? 'top-10' : 'top-0' }} z-40 bg-white/90 backdrop-blur-md border-b border-slate-200/80 transition-all reveal-down">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            
            {{-- Brand Logo & Meta --}}
            <a href="{{ route('customer.landing') }}" class="flex items-center gap-3.5 group">
                @if(!empty($setting->shop_logo))
                    <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl bg-white p-1 border border-slate-200/80 shadow-2xs flex items-center justify-center overflow-hidden shrink-0 group-hover:scale-105 transition">
                        <img src="{{ asset('storage/' . $setting->shop_logo) }}" 
                             alt="{{ $setting->shop_name ?? 'Noli Coffee' }}" 
                             class="w-full h-full object-contain">
                    </div>
                @else
                    <div class="w-12 h-12 rounded-2xl bg-[#0e382c] text-white flex items-center justify-center font-black text-xl shadow-sm shrink-0">
                        N
                    </div>
                @endif

                <div class="leading-tight">
                    <h1 class="text-base sm:text-lg font-black text-slate-900 tracking-tight font-heading group-hover:text-[#0e382c] transition">
                        {{ $setting->shop_name ?? 'Noli Coffee & Space' }}
                    </h1>
                    <span class="text-[11px] font-semibold text-emerald-700 block mt-0.5">
                        Coffee & Space • Slawi Kulon
                    </span>
                </div>
            </a>

            {{-- Desktop Navigation Links --}}
            <nav class="hidden lg:flex items-center gap-8 text-xs font-bold tracking-wide text-slate-600">
                <a href="#hero" class="hover:text-[#0e382c] transition py-1">Beranda</a>
                <a href="#signature-spotlight" class="hover:text-[#0e382c] transition py-1">Menu Unggulan</a>
                <a href="#tentang-noli" class="hover:text-[#0e382c] transition py-1">Tentang Kami</a>
                <a href="#menu-katalog" class="hover:text-[#0e382c] transition py-1">Daftar Menu</a>
                <a href="#space-fasilitas" class="hover:text-[#0e382c] transition py-1">Suasana Space</a>
                <a href="#lokasi-kontak" class="hover:text-[#0e382c] transition py-1">Lokasi</a>
            </nav>

            {{-- Right CTA --}}
            <div class="hidden sm:flex items-center gap-3">
                <a href="{{ route('customer.order', array_filter(['table' => $tableNumber])) }}" 
                   class="px-5 sm:px-6 py-2.5 sm:py-3 rounded-2xl bg-[#0e382c] hover:bg-[#134e3f] active:scale-95 text-white text-xs font-black tracking-wide shadow-md shadow-[#0e382c]/20 transition flex items-center gap-2">
                    <i class="fas fa-bag-shopping text-emerald-400 text-xs"></i>
                    <span>Pesan Sekarang</span>
                </a>
            </div>

        </div>
    </header>

    {{-- ========================================================================= --}}
    {{-- 3. HERO SECTION (IMPRESSIVE, CONFIDENT & ATMOSPHERIC)                     --}}
    {{-- ========================================================================= --}}
    <section id="hero" class="relative pt-12 sm:pt-20 pb-16 sm:pb-24 border-b border-slate-200/80 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
                
                {{-- Left Column: Hero Narrative --}}
                <div class="lg:col-span-7 space-y-6 text-left">
                    

                    {{-- Main Headline --}}
                    <h2 class="text-4xl sm:text-5xl md:text-6xl font-black text-slate-900 tracking-tight leading-[1.1] font-heading reveal-up delay-100">
                        Ruang Bertemu,<br/>
                        Rasa yang <span class="text-[#0e382c] underline decoration-emerald-400 decoration-4 underline-offset-4">Selalu Dirindu.</span>
                    </h2>

                    {{-- Subheadline --}}
                    <p class="text-sm sm:text-base text-slate-600 font-normal leading-relaxed max-w-xl reveal-up delay-200">
                        Dari racikan kopi klasik hingga aneka minuman segar pilihan barista. Ruang ternyaman di Slawi Kulon untuk bekerja, bercerita bersama kawan, dan menikmati setiap momen berkualitas.
                    </p>

                    {{-- Hero Action Buttons --}}
                    <div class="pt-2 flex flex-row items-center gap-3 max-w-md reveal-up delay-300">
                        <a href="{{ route('customer.order', array_filter(['table' => $tableNumber])) }}" 
                           class="flex-1 sm:flex-initial justify-center px-5 sm:px-8 py-3.5 sm:py-4 rounded-2xl bg-[#0e382c] hover:bg-[#134e3f] active:scale-95 text-white font-black text-xs sm:text-sm tracking-wide shadow-lg shadow-[#0e382c]/25 hover:shadow-emerald-900/40 hover:-translate-y-0.5 transition-all flex items-center gap-2 group">
                            <i class="fas fa-cart-shopping text-emerald-300 group-hover:scale-110 transition-transform"></i>
                            <span>Mulai Pesan</span>
                        </a>

                        <a href="#menu-katalog" 
                           class="shrink-0 px-4 sm:px-7 py-3.5 sm:py-4 rounded-2xl bg-[#f8faf9] hover:bg-slate-100 active:scale-95 text-slate-800 font-extrabold text-xs sm:text-sm border border-slate-200 shadow-2xs hover:shadow-sm hover:-translate-y-0.5 transition-all flex items-center gap-2">
                            <i class="fas fa-book-open text-[#0e382c]"></i>
                            <span>Buku Menu</span>
                        </a>
                    </div>

                    {{-- Quick Trust Indicators --}}
                    <div class="pt-6 grid grid-cols-3 gap-4 border-t border-slate-100 max-w-lg reveal-up delay-400">
                        <div>
                            <span class="text-lg sm:text-2xl font-black text-slate-900 block font-heading">{{ $soldFormatted }}</span>
                            <span class="text-[11px] font-semibold text-slate-500">Cup Terjual</span>
                        </div>
                        <div>
                            <span class="text-lg sm:text-2xl font-black text-[#0e382c] block font-heading">4.9 ★</span>
                            <span class="text-[11px] font-semibold text-slate-500">Rating Pengunjung</span>
                        </div>
                        <div>
                            <span class="text-lg sm:text-2xl font-black text-slate-900 block font-heading">100%</span>
                            <span class="text-[11px] font-semibold text-slate-500">Biji Kopi Segar</span>
                        </div>
                    </div>

                </div>

                {{-- Right Column: Showcase Visual Card (Real Photo from Database) --}}
                <div class="hidden lg:flex lg:col-span-5 justify-end reveal-scale delay-200">
                    @if($signatures->count() > 0)
                        @php $leadProduct = $signatures->first(); @endphp
                        <div class="w-full max-w-sm sm:max-w-md bg-[#f8faf9] rounded-3xl p-5 sm:p-6 border border-slate-200/90 shadow-xl relative group animate-float">
                            
                            {{-- Photo Container --}}
                            <div class="w-full aspect-[4/3] rounded-2xl bg-white overflow-hidden relative shadow-inner mb-5 border border-slate-200/70">
                                @if(!empty($leadProduct->image))
                                    <img src="{{ asset('storage/' . $leadProduct->image) }}" 
                                         alt="{{ $leadProduct->name }}" 
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-300">
                                        <i class="fas fa-mug-hot text-5xl"></i>
                                    </div>
                                @endif

                                {{-- Fore-Style Badge with Luxury Sweep Shine --}}
                                <div class="absolute top-3 left-3 {{ $leadProduct->badge_bg ?? 'bg-[#b88646]' }} text-white text-[10.5px] font-black px-3.5 py-1 rounded-full shadow-md {{ $leadProduct->badge_border ?? 'border border-amber-200/40' }} uppercase tracking-wider flex items-center gap-1.5 badge-shine">
                                    <i class="{{ $leadProduct->badge_icon ?? 'fas fa-star text-amber-100' }} text-[10px]"></i>
                                    <span>{{ $leadProduct->badge_label ?? 'Best Seller' }}</span>
                                </div>


                            </div>

                            {{-- Card Details --}}
                            <div class="space-y-2">
                                <span class="text-[10px] font-black uppercase tracking-wider text-emerald-800 block">
                                    {{ $leadProduct->category?->name }}
                                </span>
                                <h3 class="text-xl font-black text-slate-900 font-heading">
                                    {{ $leadProduct->name }}
                                </h3>
                                <p class="text-xs text-slate-600 leading-relaxed line-clamp-2">
                                    {{ !empty($leadProduct->description) ? $leadProduct->description : ($leadProduct->category?->description ?? 'Pilihan racikan kopi & minuman favorit pelanggan di Noli Coffee.') }}
                                </p>
                            </div>

                            {{-- Bottom Action --}}
                            <div class="pt-4 mt-4 border-t border-slate-200/70 flex items-center justify-between">
                                <span class="text-xs text-slate-500 font-medium">Bisa pesan langsung dari meja</span>
                                <a href="{{ route('customer.order', array_filter(['table' => $tableNumber, 'select' => $leadProduct->id])) }}" 
                                   class="px-4 py-2 rounded-xl bg-[#0e382c] hover:bg-[#134e3f] active:scale-95 text-white font-extrabold text-xs transition flex items-center gap-1.5 shadow-2xs">
                                    <span>Pesan Menu Ini</span>
                                    <i class="fas fa-arrow-right text-[10px] text-emerald-300"></i>
                                </a>
                            </div>

                        </div>
                    @endif
                </div>

            </div>
        </div>
    </section>

    {{-- ========================================================================= --}}
    {{-- 4. SIGNATURE SPOTLIGHT (CURATED MENU SHOWCASE)                            --}}
    {{-- ========================================================================= --}}
    @if($signatures->count() > 1)
        <section id="signature-spotlight" class="py-16 sm:py-24 border-b border-slate-200/80 bg-[#f8faf9]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
                
                {{-- Header --}}
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 reveal-on-scroll">
                    <div>
                        <span class="text-xs font-black uppercase tracking-wider text-emerald-800 block">
                            Paling Banyak di Minati
                        </span>
                        <h3 class="text-2xl sm:text-4xl font-black text-slate-900 font-heading mt-1">
                            Menu Pilihan Favorit
                        </h3>
                    </div>
                    <a href="#menu-katalog" class="text-xs font-bold text-[#0e382c] hover:text-emerald-700 flex items-center gap-1.5 self-start sm:self-auto group">
                        <span>Lihat Semua Menu</span>
                        <i class="fas fa-arrow-right text-[10px] group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>

                {{-- Cards Grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-6">
                    @foreach($signatures->take(4) as $sig)
                        <div wire:key="sig-{{ $sig->id }}" class="bg-white rounded-3xl p-4 sm:p-5 border border-slate-200/80 shadow-xs hover:border-[#0e382c]/40 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between group reveal-on-scroll stagger-{{ min($loop->iteration, 4) }}">
                            <div>
                                <div class="w-full aspect-square rounded-2xl bg-slate-100 overflow-hidden relative mb-4 cursor-pointer"
                                     wire:click="openQuickView({{ $sig->id }})">
                                    @if(!empty($sig->image))
                                        <img src="{{ asset('storage/' . $sig->image) }}" 
                                             alt="{{ $sig->name }}" 
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-slate-300">
                                            <i class="fas fa-mug-hot text-4xl"></i>
                                        </div>
                                    @endif

                                    {{-- Fore-Style Badge: Best Seller, Top Ordered, Most Popular with Sweep Shine --}}
                                    <div class="absolute top-2.5 left-2.5 z-10">
                                        <span class="{{ $sig->badge_bg ?? 'bg-[#0e382c]' }} text-white text-[10px] font-black px-3 py-1 rounded-full shadow-xs {{ $sig->badge_border ?? 'border border-emerald-400/30' }} uppercase tracking-wider flex items-center gap-1.5 badge-shine">
                                            <i class="{{ $sig->badge_icon ?? 'fas fa-award text-emerald-300' }} text-[9px]"></i>
                                            <span>{{ $sig->badge_label ?? 'Top Ordered' }}</span>
                                        </span>
                                    </div>
                                </div>

                                <span class="text-[10px] font-black uppercase tracking-wider text-emerald-800 block">
                                    {{ $sig->category?->name }}
                                </span>
                                <h4 class="text-base font-black text-slate-900 mt-1 font-heading group-hover:text-[#0e382c] transition cursor-pointer"
                                    wire:click="openQuickView({{ $sig->id }})">
                                    {{ $sig->name }}
                                </h4>
                                <p class="text-xs text-slate-500 line-clamp-2 mt-1 leading-relaxed">
                                    {{ !empty($sig->description) ? $sig->description : ($sig->category?->description ?? 'Pilihan racikan istimewa barista Noli.') }}
                                </p>
                            </div>

                            <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between gap-2">
                                <span class="text-sm sm:text-base font-black text-slate-900 font-heading">
                                    Rp {{ number_format($sig->price, 0, ',', '.') }}
                                </span>
                                <a href="{{ route('customer.order', array_filter(['table' => $tableNumber, 'select' => $sig->id])) }}" 
                                   class="px-4 py-2 rounded-xl bg-[#0e382c] hover:bg-[#134e3f] active:scale-95 text-white text-xs font-black transition flex items-center gap-1.5 shadow-2xs">
                                    <span>Pesan</span>
                                    <i class="fas fa-plus text-[9px]"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>
        </section>
    @endif

    {{-- ========================================================================= --}}
    {{-- 5. ABOUT NOLI SPACE (OUR STORY & PURPOSE)                                 --}}
    {{-- ========================================================================= --}}
    <section id="tentang-noli" class="py-16 sm:py-24 bg-white border-b border-slate-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-16 items-center">
                
                {{-- Left Image Showcase (Focus on Coffee Craft & Barista Dedication) --}}
                <div class="lg:col-span-6 reveal-on-scroll-left">
                    <div class="relative rounded-3xl overflow-hidden shadow-lg border border-slate-200 aspect-[4/3] group">
                        <img src="https://images.unsplash.com/photo-1511920170033-f8396924c348?auto=format&fit=crop&w=1200&q=80" 
                             alt="Seni Meracik Kopi Noli Coffee" 
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-950/20 to-transparent flex items-end p-6 sm:p-8">
                            <div class="text-white">
                                <span class="text-[10px] font-bold uppercase tracking-widest text-emerald-300 block">Artisan & Quality Craft</span>
                                <h4 class="text-lg sm:text-xl font-black font-heading mt-0.5">Dedikasi Rasa di Setiap Seduhan.</h4>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Narrative --}}
                <div class="lg:col-span-6 space-y-5 reveal-on-scroll-right">
                    <span class="text-xs font-black uppercase tracking-wider text-emerald-800 block">
                        Filosofi & Cerita Rasa
                    </span>
                    <h3 class="text-3xl sm:text-4xl font-black text-slate-900 font-heading leading-tight">
                        Diracik Sepenuh Hati, Disajikan untuk Setiap Momenmu.
                    </h3>
                    <div class="space-y-3.5 text-xs sm:text-sm text-slate-600 leading-relaxed">
                        <p>
                            Berawal dari kecintaan terhadap aroma dan cita rasa kopi sejati, <strong>Noli Coffee & Space</strong> hadir di Slawi Kulon untuk menyajikan racikan istimewa yang menemani setiap obrolan, ide kreatif, dan momen santaimu.
                        </p>
                        <p>
                            Setiap cangkir melalui proses ekstraksi yang presisi menggunakan biji kopi pilihan terbaik. Dipadukan dengan aneka kreasi minuman segar khas Noli, kami berkomitmen memberikan pengalaman rasa yang konsisten dan berkesan di setiap tegukan.
                        </p>
                    </div>

                    <div class="pt-3 flex flex-wrap items-center gap-4 sm:gap-6 text-xs text-slate-700 font-bold">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-check-circle text-[#0e382c]"></i>
                            <span>Biji Kopi Pilihan</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fas fa-check-circle text-[#0e382c]"></i>
                            <span>Ekstraksi Presisi</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fas fa-check-circle text-[#0e382c]"></i>
                            <span>Racikan Khas Barista</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- ========================================================================= --}}
    {{-- 6. FULL DIGITAL MENU CATALOG (AUTHENTIC DATABASE PRODUCTS)                --}}
    {{-- ========================================================================= --}}
    <main id="menu-katalog" class="py-16 sm:py-24 bg-[#f8faf9] border-b border-slate-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            
            {{-- Header & Search --}}
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-5 reveal-on-scroll">
                <div>
                    <span class="text-xs font-black uppercase tracking-wider text-emerald-800 block">
                        Katalog Online
                    </span>
                    <h3 class="text-2xl sm:text-4xl font-black text-slate-900 font-heading mt-1">
                        Daftar Menu Noli Coffee
                    </h3>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">
                        Klik menu untuk melihat komposisi atau tekan pesan untuk langsung memesan dari meja.
                    </p>
                </div>

                {{-- Clean Search --}}
                <div class="w-full md:w-80 relative">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" 
                           wire:model.live.debounce.300ms="search" 
                           placeholder="Cari kopi, taro, matcha..." 
                           class="w-full pl-10 pr-4 py-3 rounded-2xl bg-white border border-slate-200 text-xs font-medium text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#0e382c] shadow-xs transition">
                    @if(!empty($search))
                        <button type="button" wire:click="$set('search', '')" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                            <i class="fas fa-times-circle text-xs"></i>
                        </button>
                    @endif
                </div>
            </div>

            {{-- Category Filter Bar --}}
            <div class="sticky top-[76px] sm:top-[80px] z-30 bg-[#f8faf9]/95 backdrop-blur-md py-2 -mx-4 px-4 sm:mx-0 sm:px-0">
                <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-1">
                    <button type="button" 
                            wire:click="selectCategory('all')"
                            class="px-5 py-2.5 rounded-2xl text-xs font-black whitespace-nowrap transition-all flex items-center gap-2 {{ $selectedCategory === 'all' ? 'bg-[#0e382c] text-white shadow-md' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50' }}">
                        <i class="fas fa-border-all text-[11px]"></i>
                        <span>Semua Menu</span>
                        <span class="text-[10px] px-1.5 py-0.5 rounded-full {{ $selectedCategory === 'all' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500' }}">
                            {{ $products->count() }}
                        </span>
                    </button>

                    @foreach($categories as $cat)
                        <button type="button" 
                                wire:click="selectCategory({{ $cat->id }})"
                                class="px-5 py-2.5 rounded-2xl text-xs font-black whitespace-nowrap transition-all flex items-center gap-2 {{ $selectedCategory == $cat->id ? 'bg-[#0e382c] text-white shadow-md' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50' }}">
                            <span>{{ $cat->name }}</span>
                            <span class="text-[10px] px-1.5 py-0.5 rounded-full {{ $selectedCategory == $cat->id ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500' }}">
                                {{ $cat->products_count }}
                            </span>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Products Grid --}}
            @if($products->isEmpty())
                <div class="bg-white rounded-3xl p-12 text-center border border-slate-200">
                    <div class="w-14 h-14 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3 text-2xl">
                        <i class="fas fa-mug-hot"></i>
                    </div>
                    <h4 class="text-sm font-black text-slate-800">Menu Tidak Ditemukan</h4>
                    <p class="text-xs text-slate-500 mt-1">Coba gunakan kata kunci pencarian yang lain.</p>
                </div>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                    @foreach($products as $product)
                        <div wire:key="prod-{{ $product->id }}" class="bg-white rounded-3xl p-3.5 sm:p-4 border border-slate-200/80 shadow-xs flex flex-col justify-between group hover:border-[#0e382c]/40 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 reveal-on-scroll stagger-{{ (($loop->index % 4) + 1) }}">
                            
                            <div>
                                {{-- Thumbnail --}}
                                <div class="w-full aspect-square rounded-2xl bg-slate-100 overflow-hidden relative mb-3 cursor-pointer"
                                     wire:click="openQuickView({{ $product->id }})">
                                    @if(!empty($product->image))
                                        <img src="{{ asset('storage/' . $product->image) }}" 
                                             alt="{{ $product->name }}" 
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                    @else
                                        <div class="w-full h-full flex flex-col items-center justify-center text-slate-300 bg-slate-50">
                                            <i class="fas fa-coffee text-3xl mb-1 text-slate-300"></i>
                                            <span class="text-[9px] text-slate-400 font-bold uppercase">{{ $product->category?->name ?? 'Menu' }}</span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Category & Title --}}
                                <span class="text-[9.5px] font-black uppercase tracking-wider text-emerald-800 block">
                                    {{ $product->category?->name }}
                                </span>
                                <h4 class="text-xs sm:text-sm font-black text-slate-900 font-heading leading-tight mt-0.5 line-clamp-1 cursor-pointer hover:text-[#0e382c] transition"
                                    wire:click="openQuickView({{ $product->id }})"
                                    title="{{ $product->name }}">
                                    {{ $product->name }}
                                </h4>

                                {{-- Description from DB --}}
                                <p class="text-[11px] text-slate-500 line-clamp-2 mt-1 leading-relaxed">
                                    {{ !empty($product->description) ? $product->description : ($product->category?->description ?? 'Racikan pilihan istimewa barista Noli.') }}
                                </p>
                            </div>

                            {{-- Price & Direct Order Action --}}
                            <div class="pt-3 mt-3 border-t border-slate-100 flex items-center justify-between">
                                <span class="text-xs sm:text-sm font-black text-slate-900 font-heading">
                                    Rp {{ number_format($product->price, 0, ',', '.') }}
                                </span>

                                <a href="{{ route('customer.order', array_filter(['table' => $tableNumber, 'select' => $product->id])) }}" 
                                   class="px-3 py-1.5 rounded-xl bg-[#0e382c] hover:bg-[#134e3f] active:scale-95 text-white text-[11px] font-extrabold transition flex items-center gap-1 shadow-2xs">
                                    <span>Pesan</span>
                                    <i class="fas fa-plus text-[9px]"></i>
                                </a>
                            </div>

                        </div>
                    @endforeach
                </div>
            @endif

        </div>
    </main>

    {{-- ========================================================================= --}}
    {{-- 7. SPACE AMENITIES & EXPERIENCE                                           --}}
    {{-- ========================================================================= --}}
    <section id="space-fasilitas" class="py-16 sm:py-24 bg-white border-b border-slate-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
            
            <div class="text-center max-w-xl mx-auto space-y-2 reveal-on-scroll">
                <span class="text-xs font-black uppercase tracking-wider text-emerald-800 block">
                    Kenyamanan & Fasilitas
                </span>
                <h3 class="text-2xl sm:text-4xl font-black text-slate-900 font-heading">
                    Dirancang untuk Kenyamananmu
                </h3>
                <p class="text-xs sm:text-sm text-slate-500">
                    Bukan sekadar ngopi sebentar, Noli Space siap menemani produktivitas dan relaksasimu.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 sm:gap-8">
                
                <div class="bg-[#f8faf9] rounded-3xl p-6 sm:p-8 border border-slate-200/80 space-y-4 reveal-on-scroll stagger-1">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-800 flex items-center justify-center text-xl">
                        <i class="fas fa-laptop-code"></i>
                    </div>
                    <h4 class="text-base font-black text-slate-900 font-heading">Work & Study Friendly</h4>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        Koneksi WiFi berkecepatan tinggi dengan ketersediaan stopkontak di banyak titik meja untuk kenyamanan laptop dan gadgetmu.
                    </p>
                </div>

                <div class="bg-[#f8faf9] rounded-3xl p-6 sm:p-8 border border-slate-200/80 space-y-4 reveal-on-scroll stagger-2">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-800 flex items-center justify-center text-xl">
                        <i class="fas fa-couch"></i>
                    </div>
                    <h4 class="text-base font-black text-slate-900 font-heading">Indoor AC & Outdoor</h4>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        Pilihan ruang dingin ber-AC yang tenang untuk fokus kerja, atau area outdoor yang asri untuk bercengkerama santai di sore hari.
                    </p>
                </div>

                <div class="bg-[#f8faf9] rounded-3xl p-6 sm:p-8 border border-slate-200/80 space-y-4 reveal-on-scroll stagger-3">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-800 flex items-center justify-center text-xl">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h4 class="text-base font-black text-slate-900 font-heading">Musholla & Kebersihan Terjaga</h4>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        Fasilitas ibadah musholla yang bersih dan toilet terawat untuk memastikan kenyamanan Anda selama berkunjung.
                    </p>
                </div>

            </div>

        </div>
    </section>

    {{-- ========================================================================= --}}
    {{-- 8. CUSTOMER REVIEWS (SOCIAL PROOF)                                        --}}
    {{-- ========================================================================= --}}
    <section class="py-16 sm:py-24 bg-[#f8faf9] border-b border-slate-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
            
            <div class="text-center max-w-xl mx-auto space-y-2 reveal-on-scroll">
                <span class="text-xs font-black uppercase tracking-wider text-emerald-800 block">
                    Kata Pengunjung
                </span>
                <h3 class="text-2xl sm:text-4xl font-black text-slate-900 font-heading">
                    Kesan Hangat di Noli Space
                </h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-4 reveal-on-scroll stagger-1">
                    <div class="flex items-center gap-1 text-amber-400 text-xs">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-700 leading-relaxed">
                        "Tempat ngopi paling nyaman di Slawi. Rasa kopinya pas, tempatnya estetik dan bersih banget. Nyaman banget buat nugas berjam-jam."
                    </p>
                    <div class="pt-2 border-t border-slate-100">
                        <strong class="text-xs font-bold text-slate-900 block">Rizky Pratama</strong>
                        <span class="text-[10px] text-slate-400">Pengunjung Setia</span>
                    </div>
                </div>

                <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-4 reveal-on-scroll stagger-2">
                    <div class="flex items-center gap-1 text-amber-400 text-xs">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-700 leading-relaxed">
                        "Butterscotch Sea Salt-nya juara! Self order lewat HP di mejanya juga praktis banget, nggak perlu ngantre di kasir."
                    </p>
                    <div class="pt-2 border-t border-slate-100">
                        <strong class="text-xs font-bold text-slate-900 block">Anisa Dian</strong>
                        <span class="text-[10px] text-slate-400">Coffee Enthusiast</span>
                    </div>
                </div>

                <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-4 reveal-on-scroll stagger-3">
                    <div class="flex items-center gap-1 text-amber-400 text-xs">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-700 leading-relaxed">
                        "Staff-nya ramah, musholanya bersih dan wangi. Area indoor-nya dingin dan outdoor-nya asik kalau sore ke malam."
                    </p>
                    <div class="pt-2 border-t border-slate-100">
                        <strong class="text-xs font-bold text-slate-900 block">Bagus Wicaksono</strong>
                        <span class="text-[10px] text-slate-400">WFC Slawi</span>
                    </div>
                </div>

            </div>

        </div>
    </section>

    {{-- ========================================================================= --}}
    {{-- 9. LOCATION & CONTACT (GRAND CALLOUT CARD)                                --}}
    {{-- ========================================================================= --}}
    <section id="lokasi-kontak" class="py-16 sm:py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-[#0e382c] rounded-3xl sm:rounded-[2.5rem] p-8 sm:p-14 text-white shadow-xl reveal-on-scroll-scale">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                    
                    <div class="lg:col-span-8 space-y-4">
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-emerald-300 text-xs font-bold uppercase tracking-wider">
                            <i class="fas fa-location-dot"></i>
                            <span>Kunjungi Noli Coffee & Space</span>
                        </span>
                        
                        <h3 class="text-2xl sm:text-4xl font-black font-heading text-white">
                            Singgah & Rasakan Pengalamannya Sendiri.
                        </h3>

                        <div class="space-y-2 text-xs sm:text-sm text-emerald-100/90 leading-relaxed">
                            <p class="flex items-start gap-2.5">
                                <i class="fas fa-map-pin text-emerald-400 mt-1 shrink-0"></i>
                                <span>{{ $setting->address ?? 'Jl. Kh Wahid Hasyim, Slawi Kulon Kec. Slawi, Kab. Tegal' }}</span>
                            </p>
                            <p class="flex items-center gap-2.5">
                                <i class="fas fa-clock text-emerald-400 shrink-0"></i>
                                <span>Buka Setiap Hari: <strong>08:00 - 22:00 WIB</strong></span>
                            </p>
                            @if(!empty($setting->phone))
                                <p class="flex items-center gap-2.5">
                                    <i class="fab fa-whatsapp text-emerald-400 shrink-0"></i>
                                    <span>Kontak WhatsApp: <strong>{{ $setting->phone }}</strong></span>
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="lg:col-span-4 flex flex-col sm:flex-row lg:flex-col gap-3">
                        <a href="{{ route('customer.order', array_filter(['table' => $tableNumber])) }}" 
                           class="w-full py-4 px-6 rounded-2xl bg-emerald-400 hover:bg-emerald-300 active:scale-95 text-[#0e382c] font-black text-xs sm:text-sm text-center shadow-md transition flex items-center justify-center gap-2">
                            <i class="fas fa-bag-shopping"></i>
                            <span>Pesan Sekarang (Self-Order)</span>
                        </a>

                        <a href="https://maps.google.com/?q={{ urlencode($setting->address ?? 'Noli Coffee Space Slawi') }}" 
                           target="_blank"
                           class="w-full py-4 px-6 rounded-2xl bg-white/10 hover:bg-white/20 active:scale-95 text-white font-bold text-xs sm:text-sm text-center border border-white/20 transition flex items-center justify-center gap-2">
                            <i class="fas fa-map-location-dot"></i>
                            <span>Buka di Google Maps</span>
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </section>

    {{-- ========================================================================= --}}
    {{-- 10. EDITORIAL FOOTER                                                      --}}
    {{-- ========================================================================= --}}
    <footer class="bg-white border-t border-slate-200/80 py-10 text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 text-center sm:text-left">
                <div>
                    <strong class="text-slate-900 font-black text-sm block">{{ $setting->shop_name ?? 'Noli Coffee & Space' }}</strong>
                    <span class="text-[11px] text-slate-400">Slawi Kulon, Kec. Slawi, Kabupaten Tegal, Jawa Tengah.</span>
                </div>
                <div class="flex items-center gap-6 text-xs font-bold text-slate-600">
                    <a href="#hero" class="hover:text-[#0e382c]">Beranda</a>
                    <a href="#signature-spotlight" class="hover:text-[#0e382c]">Menu Unggulan</a>
                    <a href="#menu-katalog" class="hover:text-[#0e382c]">Daftar Menu</a>
                    <a href="{{ route('customer.order', array_filter(['table' => $tableNumber])) }}" class="text-[#0e382c] font-black hover:underline">Self-Order</a>
                </div>
            </div>
            <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-2 text-[11px] text-slate-400">
                <p>© {{ date('Y') }} {{ $setting->shop_name ?? 'Noli Coffee & Space' }}. All rights reserved.</p>
                <p>Designed for seamless in-store & online cafe dining experience.</p>
            </div>
        </div>
    </footer>

    {{-- ========================================================================= --}}
    {{-- 11. MOBILE STICKY ORDER BUTTON                                            --}}
    {{-- ========================================================================= --}}
    <div class="sm:hidden fixed bottom-4 left-4 right-4 z-40">
        <div class="bg-[#0e382c]/95 backdrop-blur-md text-white p-3 rounded-2xl shadow-2xl border border-emerald-900/60 flex items-center justify-between gap-3">
            <div class="min-w-0 pl-1">
                <span class="text-[10px] text-emerald-300 font-bold uppercase tracking-wider block">
                    {{ !empty($tableNumber) ? 'Meja ' . $tableNumber : 'Noli Coffee' }}
                </span>
                <span class="text-xs font-black text-white truncate block">Pesan Mudah Tanpa Antre</span>
            </div>
            <a href="{{ route('customer.order', array_filter(['table' => $tableNumber])) }}" 
               class="px-5 py-2.5 rounded-xl bg-emerald-400 hover:bg-emerald-300 text-[#0e382c] font-black text-xs uppercase tracking-wider active:scale-95 transition shadow-sm shrink-0 flex items-center gap-1.5">
                <span>Pesan</span>
                <i class="fas fa-arrow-right text-[10px]"></i>
            </a>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 12. QUICK VIEW MODAL                                                      --}}
    {{-- ========================================================================= --}}
    @if($showQuickView && $previewProduct)
        <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4"
             @keydown.window.escape="$wire.closeQuickView()">
            
            {{-- Backdrop (Click outside anywhere to close) --}}
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity cursor-pointer"
                 wire:click="closeQuickView"
                 title="Klik di luar untuk menutup"></div>

            {{-- Modal Content Card --}}
            <div class="bg-white w-full max-w-md rounded-t-3xl sm:rounded-3xl p-6 shadow-2xl relative max-h-[90vh] overflow-y-auto z-10 animate-pop"
                 wire:click.stop
                 onclick="event.stopPropagation()">
                {{-- Mobile Pull Bar (also tap to close) --}}
                <div class="w-12 h-1.5 bg-slate-200 hover:bg-slate-300 rounded-full mx-auto mb-4 sm:hidden cursor-pointer"
                     wire:click="closeQuickView"
                     title="Tutup"></div>

                {{-- Image --}}
                <div class="w-full aspect-[4/3] rounded-2xl bg-slate-100 overflow-hidden relative mb-4 border border-slate-200">
                    @if(!empty($previewProduct->image))
                        <img src="{{ asset('storage/' . $previewProduct->image) }}" 
                             alt="{{ $previewProduct->name }}" 
                             class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-slate-300">
                            <i class="fas fa-mug-hot text-5xl"></i>
                        </div>
                    @endif

                    <div class="absolute top-3 left-3 bg-[#0e382c] text-white text-[10px] font-black px-2.5 py-1 rounded-xl shadow-sm uppercase">
                        {{ $previewProduct->category?->name }}
                    </div>
                </div>

                {{-- Details --}}
                <div class="space-y-3">
                    <div>
                        <h3 class="text-xl font-black text-slate-900 font-heading">
                            {{ $previewProduct->name }}
                        </h3>
                        <span class="text-base font-black text-[#0e382c] font-heading block mt-0.5">
                            Rp {{ number_format($previewProduct->price, 0, ',', '.') }}
                        </span>
                    </div>

                    {{-- Description --}}
                    <div class="pt-2 border-t border-slate-100">
                        <label class="text-[10px] font-black uppercase tracking-wider text-slate-400 block mb-1">
                            Deskripsi Menu
                        </label>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            {{ !empty($previewProduct->description) ? $previewProduct->description : ($previewProduct->category?->description ?? 'Racikan pilihan istimewa barista Noli Coffee.') }}
                        </p>
                    </div>

                    {{-- Action Button --}}
                    <div class="pt-4 mt-4 border-t border-slate-100 flex items-center gap-3">
                        <button type="button" 
                                wire:click="closeQuickView" 
                                class="w-1/3 py-3 rounded-2xl border border-slate-200 text-slate-600 font-bold text-xs hover:bg-slate-50 transition">
                            Tutup
                        </button>

                        <a href="{{ route('customer.order', array_filter(['table' => $tableNumber, 'select' => $previewProduct->id])) }}" 
                           class="w-2/3 py-3 rounded-2xl bg-[#0e382c] hover:bg-[#134e3f] active:scale-95 text-white font-extrabold text-xs shadow-md transition flex items-center justify-center gap-2">
                            <span>Pesan Menu Ini</span>
                            <i class="fas fa-arrow-right text-[10px] text-emerald-300"></i>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    @endif

</div>


