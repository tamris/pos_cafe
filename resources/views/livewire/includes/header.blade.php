<header class="sticky top-0 z-30 flex items-center justify-between h-16 px-4 sm:px-6 bg-white border-b border-slate-200 dark:bg-slate-800 dark:border-slate-700 transition-colors duration-300">
    
    <div class="flex items-center space-x-3">
        {{-- TOMBOL BURGER MENU (DESKTOP & MOBILE TOGGLE) --}}
        <button @click.stop="toggleSidebar()"
            type="button"
            class="text-slate-600 hover:text-slate-900 p-2 rounded-xl hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-700/60 transition-all focus:outline-none cursor-pointer flex items-center justify-center active:scale-95 border border-slate-200/80 dark:border-slate-700/80 shadow-2xs"
            title="Buka / Tutup Sidebar"
            aria-label="Toggle Sidebar">
            <svg class="w-5 h-5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>

        <div>
            <h1 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white leading-tight">{{ $title ?? 'Page' }}</h1>
            @if(isset($subtitle))
                <p class="text-xs text-slate-500 dark:text-slate-400 hidden sm:block">{{ $subtitle }}</p>
            @endif
        </div>
    </div>

    <div class="flex items-center space-x-2 sm:space-x-3">
        {{-- 1. JAM DIGITAL REALTIME (KIRI) --}}
        <div x-data="{ 
                dateTime: '',
                updateClock() {
                    const now = new Date();
                    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                    const day = String(now.getDate()).padStart(2, '0');
                    const month = months[now.getMonth()];
                    const year = now.getFullYear();
                    const hours = String(now.getHours()).padStart(2, '0');
                    const minutes = String(now.getMinutes()).padStart(2, '0');
                    this.dateTime = `${day} ${month} ${year}, ${hours}:${minutes}`;
                }
              }" 
              x-init="updateClock(); setInterval(() => updateClock(), 1000)"
              class="hidden md:inline-flex items-center gap-2 text-xs font-semibold text-slate-600 dark:text-slate-300 bg-slate-100/80 dark:bg-slate-800/80 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700/80 font-mono tracking-tight shadow-2xs">
            <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span x-text="dateTime">{{ now()->format('d M Y, H:i') }}</span>
        </div>

        {{-- 2. WIDGET PRINTER THERMAL (TENGAH) --}}
        <div x-data="{
                showModal: false,
                btConnected: false,
                btName: '',
                connectionType: '',
                isSupported: false,
                isLoading: false,
                connectingMode: '',
                init() {
                    this.isSupported = window.posBluetooth && window.posBluetooth.isSupported();
                    if (window.posBluetooth) {
                        window.posBluetooth.onStatusChange((connected, name, type) => {
                            this.btConnected = connected;
                            this.btName = name;
                            this.connectionType = type || 'bluetooth';
                        });
                    }
                },
                async connectBt() {
                    this.isLoading = true;
                    this.connectingMode = 'bluetooth';
                    try {
                        await window.posBluetooth.connectBluetooth();
                    } catch (err) {
                        if (err.name !== 'NotFoundError') {
                            alert(err.message);
                        }
                    } finally {
                        this.isLoading = false;
                        this.connectingMode = '';
                    }
                },
                async connectUsb() {
                    this.isLoading = true;
                    this.connectingMode = 'usb';
                    try {
                        await window.posBluetooth.connectSerial();
                    } catch (err) {
                        if (err.name !== 'NotFoundError') {
                            alert(err.message);
                        }
                    } finally {
                        this.isLoading = false;
                        this.connectingMode = '';
                    }
                },
                async runTestPrint() {
                    this.isLoading = true;
                    try {
                        await window.posBluetooth.testPrint();
                    } catch (err) {
                        alert('Gagal mencetak test: ' + err.message);
                    } finally {
                        this.isLoading = false;
                    }
                },
                disconnectPrinter() {
                    window.posBluetooth.disconnect();
                }
             }"
             class="inline-flex items-center">
            
            {{-- TOMBOL DI NAVBAR HEADER --}}
            <button @click="showModal = true"
                    type="button"
                    class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-bold transition-all shadow-2xs cursor-pointer border"
                    :class="btConnected 
                        ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-950/60 dark:text-emerald-300 dark:hover:bg-emerald-900/60 border-emerald-300 dark:border-emerald-700/80' 
                        : 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 border-slate-200 dark:border-slate-700'">
                
                {{-- Icon Printer --}}
                <svg class="w-3.5 h-3.5 shrink-0" :class="btConnected ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-500 dark:text-slate-400'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 9V2h12v7"></path>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                    <path d="M6 14h12v8H6z"></path>
                </svg>

                {{-- Status Dot --}}
                <span class="w-2 h-2 rounded-full shrink-0" :class="btConnected ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400'"></span>

                {{-- Text Label --}}
                <span class="hidden md:inline font-semibold" x-text="btConnected ? btName : 'Printer'"></span>
                <span class="md:hidden font-semibold" x-text="btConnected ? 'Printer' : 'Printer'"></span>
            </button>

            {{-- CUSTOM MODAL TAILWIND UI (KONSISTEN DENGAN TEMA CAFE) --}}
            <template x-teleport="body">
                <div x-show="showModal" 
                     x-cloak
                     class="fixed inset-0 z-50 overflow-y-auto"
                     aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    
                    {{-- Backdrop Blur --}}
                    <div x-show="showModal"
                         x-transition:enter="ease-out duration-200"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="ease-in duration-150"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         @click="showModal = false"
                         class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity">
                    </div>

                    <div class="flex min-h-screen items-center justify-center p-4 text-center">
                        <div x-show="showModal"
                             x-transition:enter="ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                             x-transition:leave="ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                             class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-md">
                            
                            {{-- Modal Header --}}
                            <div class="px-5 sm:px-6 py-4 border-b border-slate-100 dark:border-slate-700/60 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center border border-emerald-200 dark:border-emerald-800/80">
                                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M6 9V2h12v7"></path>
                                            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                                            <path d="M6 14h12v8H6z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white" id="modal-title">
                                            Printer Struk Thermal
                                        </h3>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">
                                            Koneksi langsung dari browser ke printer kasir
                                        </p>
                                    </div>
                                </div>
                                <button @click="showModal = false" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>

                            {{-- Modal Body --}}
                            <div class="p-5 sm:p-6 space-y-4">
                                
                                {{-- 1. KONDISI SUDAH TERHUBUNG --}}
                                <template x-if="btConnected">
                                    <div class="space-y-4">
                                        {{-- Banner Status Terhubung --}}
                                        <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/80 flex items-center justify-between">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-lg bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"></path></svg>
                                                </div>
                                                <div>
                                                    <div class="text-xs font-bold text-slate-900 dark:text-white" x-text="btName || 'Thermal 58mm'"></div>
                                                    <div class="text-[11px] text-emerald-700 dark:text-emerald-400 font-medium flex items-center gap-1.5">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                        <span x-text="connectionType === 'serial' ? 'Terhubung via USB Serial' : 'Terhubung via Bluetooth'"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-100 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-300 uppercase tracking-wider">
                                                Aktif
                                            </span>
                                        </div>

                                        {{-- Action Buttons --}}
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                            <button @click="runTestPrint()" 
                                                    :disabled="isLoading"
                                                    type="button" 
                                                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-xs active:scale-95 disabled:opacity-50 cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"></path></svg>
                                                <span x-text="isLoading ? 'Mencetak...' : 'Uji Cetak (Test)'"></span>
                                            </button>
                                            <button @click="disconnectPrinter()" 
                                                    type="button" 
                                                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-rose-200 dark:border-rose-800/60 bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 hover:bg-rose-100 dark:hover:bg-rose-900/60 text-xs font-bold transition-all active:scale-95 cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                                <span>Putus Koneksi</span>
                                            </button>
                                        </div>
                                    </div>
                                </template>

                                {{-- 2. KONDISI BELUM TERHUBUNG (PILIHAN KONEKSI DENGAN ANIMASI LOADING) --}}
                                <template x-if="!btConnected">
                                    <div class="space-y-3">
                                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                                            Pilih metode untuk menghubungkan printer kasir:
                                        </p>

                                        {{-- OPSI 1: BLUETOOTH DIRECT --}}
                                        <button @click="connectBt()" 
                                                :disabled="isLoading"
                                                type="button"
                                                class="w-full p-3.5 rounded-xl border transition-all text-left group flex items-center justify-between cursor-pointer disabled:cursor-wait"
                                                :class="isLoading && connectingMode === 'bluetooth'
                                                    ? 'border-emerald-500 bg-emerald-50/80 dark:bg-emerald-950/60 ring-2 ring-emerald-500/20'
                                                    : 'border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-900/40 hover:bg-emerald-50/50 dark:hover:bg-emerald-950/30 hover:border-emerald-400 dark:hover:border-emerald-600'">
                                            
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-xl flex items-center justify-center border transition-all shrink-0"
                                                     :class="isLoading && connectingMode === 'bluetooth'
                                                        ? 'bg-emerald-600 text-white border-emerald-600 shadow-md'
                                                        : 'bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800 group-hover:scale-105'">
                                                    
                                                    <template x-if="isLoading && connectingMode === 'bluetooth'">
                                                        <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                                        </svg>
                                                    </template>
                                                    <template x-if="!(isLoading && connectingMode === 'bluetooth')">
                                                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                                            <path d="m7 7 10 10-5 5V2l5 5L7 17"></path>
                                                        </svg>
                                                    </template>
                                                </div>
                                                <div>
                                                    <div class="text-xs font-bold text-slate-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors"
                                                         x-text="isLoading && connectingMode === 'bluetooth' ? 'Menghubungkan ke Bluetooth...' : 'Bluetooth Wireless'">
                                                    </div>
                                                    <div class="text-[11px] text-slate-500 dark:text-slate-400"
                                                         x-text="isLoading && connectingMode === 'bluetooth' ? 'Pilih printer di jendela browser Chrome...' : 'Cocok untuk Android Tablet, iPad, atau Laptop'">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="shrink-0">
                                                <template x-if="isLoading && connectingMode === 'bluetooth'">
                                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600 dark:text-emerald-400 animate-pulse">
                                                        Memindai...
                                                    </span>
                                                </template>
                                                <template x-if="!(isLoading && connectingMode === 'bluetooth')">
                                                    <svg class="w-4 h-4 text-slate-400 group-hover:text-emerald-500 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"></path></svg>
                                                </template>
                                            </div>
                                        </button>

                                        {{-- OPSI 2: USB / SERIAL CABLE --}}
                                        <button @click="connectUsb()" 
                                                :disabled="isLoading"
                                                type="button"
                                                class="w-full p-3.5 rounded-xl border transition-all text-left group flex items-center justify-between cursor-pointer disabled:cursor-wait"
                                                :class="isLoading && connectingMode === 'usb'
                                                    ? 'border-blue-500 bg-blue-50/80 dark:bg-blue-950/60 ring-2 ring-blue-500/20'
                                                    : 'border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-900/40 hover:bg-blue-50/50 dark:hover:bg-blue-950/30 hover:border-blue-400 dark:hover:border-blue-600'">
                                            
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-xl flex items-center justify-center border transition-all shrink-0"
                                                     :class="isLoading && connectingMode === 'usb'
                                                        ? 'bg-blue-600 text-white border-blue-600 shadow-md'
                                                        : 'bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-400 border-blue-200 dark:border-blue-800 group-hover:scale-105'">
                                                    
                                                    <template x-if="isLoading && connectingMode === 'usb'">
                                                        <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                                        </svg>
                                                    </template>
                                                    <template x-if="!(isLoading && connectingMode === 'usb')">
                                                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <path d="M12 2v6"></path>
                                                            <path d="M6 8a6 6 0 0 0 12 0V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2z"></path>
                                                            <path d="M12 14v8"></path>
                                                            <path d="M9 18h6"></path>
                                                        </svg>
                                                    </template>
                                                </div>
                                                <div>
                                                    <div class="text-xs font-bold text-slate-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors"
                                                         x-text="isLoading && connectingMode === 'usb' ? 'Membuka Port USB...' : 'Kabel USB / Serial'">
                                                    </div>
                                                    <div class="text-[11px] text-slate-500 dark:text-slate-400"
                                                         x-text="isLoading && connectingMode === 'usb' ? 'Pilih port printer di jendela browser Chrome...' : 'Koneksi stabil langsung untuk PC Desktop & Laptop'">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="shrink-0">
                                                <template x-if="isLoading && connectingMode === 'usb'">
                                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-blue-600 dark:text-blue-400 animate-pulse">
                                                        Menghubungkan...
                                                    </span>
                                                </template>
                                                <template x-if="!(isLoading && connectingMode === 'usb')">
                                                    <svg class="w-4 h-4 text-slate-400 group-hover:text-blue-500 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"></path></svg>
                                                </template>
                                            </div>
                                        </button>
                                    </div>
                                </template>

                                {{-- Info Box Catatan --}}
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200/60 dark:border-slate-700/60 text-[11px] text-slate-500 dark:text-slate-400 flex items-start gap-2">
                                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"></path></svg>
                                    <span>Setelah terhubung, struk akan <strong>otomatis tercetak</strong> setiap kali pembayaran selesai dilakukan.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- 3. TOMBOL TEMA DARK/LIGHT (KANAN) --}}
        <button @click="toggleTheme()" 
                type="button"
                class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-700 transition-colors focus:outline-none cursor-pointer"
                title="Ganti Tema">
            
           <svg x-show="darkMode" style="display: none;" class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path>
            </svg>

            <svg x-show="!darkMode" class="w-5 h-5 text-slate-600" fill="currentColor" viewBox="0 0 20 20">
                <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
            </svg>
        </button>
    </div>
</header>