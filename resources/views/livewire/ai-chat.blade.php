<div>
    @if(auth()->check() && auth()->user()->role === 'admin')
    
    {{-- Floating Toggle Button --}}
    <!-- <button onclick="toggleChat()"
        class="fixed bottom-6 right-6 w-14 h-14 bg-black hover:bg-gray-800 dark:bg-slate-700 dark:hover:bg-slate-600 text-white rounded-full shadow-lg flex items-center justify-center transition-all duration-300 z-50 group active:scale-95"
        id="chat-toggle-btn"
        title="Buka AI Asisten">
        <i class="fas fa-robot text-xl group-hover:scale-110 transition-transform" id="chat-icon"></i>
    </button> -->

    {{-- Chat Modal Window --}}
    <div id="chat-modal"
        class="fixed bottom-24 right-4 sm:right-6 w-[92vw] sm:w-[420px] h-[550px] bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 z-50 flex flex-col transition-all duration-300 ease-out {{ $isOpen ? 'scale-100 opacity-100' : 'scale-95 opacity-0 pointer-events-none' }}"
        style="transform-origin: bottom right;">

        {{-- Modal Header --}}
        <div class="bg-black dark:bg-slate-950 text-white p-4 rounded-t-2xl flex items-center justify-between border-b border-gray-800 dark:border-slate-800">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 bg-gradient-to-r from-gray-800 to-gray-600 dark:from-slate-700 dark:to-slate-600 rounded-full flex items-center justify-center animate-pulse">
                    <i class="fas fa-robot text-white text-sm"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-white flex items-center gap-1.5">
                        {{ \App\Models\Setting::first()?->shop_name ?? 'POS Cafe' }} AI
                        <span class="px-1.5 py-0.5 rounded text-[9px] bg-emerald-500/20 text-emerald-400 font-semibold border border-emerald-500/30">Online</span>
                    </h3>
                    <p class="text-[11px] text-gray-300 dark:text-slate-400">Asisten Bisnis & Analisis Cafe</p>
                </div>
            </div>
            <button onclick="toggleChat()" class="text-gray-300 hover:text-white p-1.5 rounded-lg hover:bg-slate-800 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- Chat Body --}}
        <div class="flex-1 p-4 overflow-y-auto bg-slate-50/70 dark:bg-slate-900/50 scrollbar-thin" id="chat-messages">
            <div class="space-y-3.5" id="messages-container">
                @foreach ($messages as $index => $message)
                    <div class="flex {{ $message['type'] === 'user' ? 'justify-end' : 'justify-start' }} message-item animate-slide-in">
                        <div class="max-w-[88%]">
                            <div class="flex items-end space-x-2 {{ $message['type'] === 'user' ? 'flex-row-reverse space-x-reverse' : '' }}">
                                
                                {{-- Icon Avatar --}}
                                @if ($message['type'] === 'bot')
                                    <div class="w-6 h-6 bg-gradient-to-r from-gray-800 to-gray-600 dark:from-slate-700 dark:to-slate-600 rounded-full flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-robot text-white text-xs"></i>
                                    </div>
                                @else
                                    <div class="w-6 h-6 bg-gray-600 dark:bg-slate-600 rounded-full flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-user text-white text-xs"></i>
                                    </div>
                                @endif

                                {{-- Chat Bubble --}}
                                <div class="{{ $message['type'] === 'user' 
                                        ? 'bg-black dark:bg-slate-700 text-white rounded-tr-none shadow-md' 
                                        : 'bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700 text-slate-800 dark:text-slate-100 rounded-tl-none shadow-sm' 
                                    }} rounded-2xl px-4 py-3">
                                    
                                    @if ($message['type'] === 'bot')
                                        <div class="text-xs sm:text-sm markdown-content dark:text-slate-200 leading-relaxed">
                                            {!! \Illuminate\Support\Str::markdown($message['content']) !!}
                                        </div>
                                    @else
                                        <p class="text-xs sm:text-sm whitespace-pre-line leading-relaxed">{{ $message['content'] }}</p>
                                    @endif
                                    
                                    <p class="text-[9px] {{ $message['type'] === 'user' ? 'text-slate-300' : 'text-slate-400 dark:text-slate-500' }} mt-1.5 text-right font-medium">
                                        {{ $message['time'] }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- Typing Indicator --}}
                @if ($isTyping)
                    <div class="flex justify-start message-item animate-slide-in" id="typing-indicator">
                        <div class="flex items-end space-x-2">
                            <div class="w-6 h-6 bg-gradient-to-r from-gray-800 to-gray-600 dark:from-slate-700 dark:to-slate-600 rounded-full flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-robot text-white text-xs"></i>
                            </div>
                            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl rounded-tl-none px-4 py-3 shadow-sm typing-bubble">
                                <div class="flex space-x-1 typing-dots items-center h-4">
                                    <div class="w-2 h-2 bg-slate-400 dark:bg-slate-500 rounded-full animate-bounce"></div>
                                    <div class="w-2 h-2 bg-slate-400 dark:bg-slate-500 rounded-full animate-bounce [animation-delay:0.2s]"></div>
                                    <div class="w-2 h-2 bg-slate-400 dark:bg-slate-500 rounded-full animate-bounce [animation-delay:0.4s]"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Quick Suggestion Chips (Shown on Start) --}}
                @if (count($messages) <= 2 && !$isLoading)
                    <div class="pt-2">
                        <p class="text-[11px] font-semibold text-slate-400 dark:text-slate-500 mb-2 uppercase tracking-wider">Pintasan Analisis Cepat:</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                            <button type="button" wire:click="sendQuickPrompt('Bagaimana ringkasan penjualan cafe hari ini?')"
                                class="text-left p-2 rounded-xl bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700/60 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs transition active:scale-98 flex items-center gap-1.5 shadow-2xs">
                                <span>📊</span> Ringkasan Omset Hari Ini
                            </button>
                            <button type="button" wire:click="sendQuickPrompt('Apa saja menu cafe yang paling laris terjual?')"
                                class="text-left p-2 rounded-xl bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700/60 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs transition active:scale-98 flex items-center gap-1.5 shadow-2xs">
                                <span>🏆</span> Menu Paling Laris
                            </button>
                            <button type="button" wire:click="sendQuickPrompt('Bagaimana rekap shift kasir dan kas laci saat ini?')"
                                class="text-left p-2 rounded-xl bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700/60 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs transition active:scale-98 flex items-center gap-1.5 shadow-2xs">
                                <span>🕒</span> Rekap Shift & Kas Laci
                            </button>
                            <button type="button" wire:click="sendQuickPrompt('Berapa total profit bersih dan margin keuntungan cafe hari ini?')"
                                class="text-left p-2 rounded-xl bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700/60 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs transition active:scale-98 flex items-center gap-1.5 shadow-2xs">
                                <span>💰</span> Analisis Profit & Margin
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Chat Input Form --}}
        <div class="p-3 sm:p-3.5 border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 rounded-b-2xl">
            <form wire:submit.prevent="sendMessage" class="flex space-x-2" id="chat-form">
                <input type="text" 
                    wire:model="message" 
                    placeholder="Tanyakan omset, menu laris, shift kasir..."
                    autocomplete="off"
                    class="flex-1 border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-xl px-3.5 py-2.5 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-slate-900 dark:focus:ring-blue-500 focus:border-transparent transition-all placeholder-slate-400 dark:placeholder-slate-500"
                    {{ $isLoading ? 'disabled' : '' }} 
                    id="chat-input">
                
                <button type="submit"
                    class="bg-slate-900 hover:bg-slate-800 dark:bg-blue-600 dark:hover:bg-blue-700 text-white rounded-xl px-3.5 flex items-center justify-center transition-all disabled:opacity-50 disabled:cursor-not-allowed active:scale-95 shadow-sm"
                    {{ $isLoading ? 'disabled' : '' }} id="send-button">
                    <svg class="w-4 h-4 transform rotate-90" fill="currentColor" viewBox="0 0 20 20"><path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"></path></svg>
                </button>
            </form>
        </div>
    </div>

    @if ($isOpen)
        <div class="fixed inset-0 bg-black/20 dark:bg-black/60 z-40 backdrop-fade-in backdrop-blur-xs" 
             onclick="toggleChat()"
             id="chat-backdrop">
        </div>
    @endif
    @endif
</div>