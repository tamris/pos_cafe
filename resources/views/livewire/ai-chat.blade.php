<div>
    @if(auth()->check() && auth()->user()->role === 'admin')
    
    <button onclick="toggleChat()"
        class="fixed bottom-6 right-6 w-14 h-14 bg-black hover:bg-gray-800 dark:bg-slate-700 dark:hover:bg-slate-600 text-white rounded-full shadow-lg flex items-center justify-center transition-all duration-300 z-50 group"
        id="chat-toggle-btn">
        <i class="fas fa-robot text-xl group-hover:scale-110 transition-transform" id="chat-icon"></i>
    </button>

    <div id="chat-modal"
        class="fixed bottom-24 right-6 w-96 h-[500px] bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-gray-200 dark:border-slate-700 z-50 flex flex-col transition-all duration-300 ease-out {{ $isOpen ? 'scale-100 opacity-100' : 'scale-95 opacity-0 pointer-events-none' }}"
        style="transform-origin: bottom right;">

        <div class="bg-black dark:bg-slate-950 text-white p-4 rounded-t-2xl flex items-center justify-between border-b border-gray-800 dark:border-slate-800">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 bg-gradient-to-r from-gray-800 to-gray-600 dark:from-slate-700 dark:to-slate-600 rounded-full flex items-center justify-center animate-pulse">
                    <i class="fas fa-robot text-white text-sm"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-white">Toko Kendali AI</h3>
                    <p class="text-xs text-gray-300 dark:text-slate-400">Asisten Penjualan</p>
                </div>
            </div>
            <button onclick="toggleChat()" class="text-gray-300 hover:text-white transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="flex-1 p-4 overflow-y-auto bg-gray-50 dark:bg-slate-800/50" id="chat-messages">
            <div class="space-y-4" id="messages-container">
                @foreach ($messages as $index => $message)
                    <div class="flex {{ $message['type'] === 'user' ? 'justify-end' : 'justify-start' }} message-item animate-slide-in">
                        <div class="max-w-[85%]">
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
                                        ? 'bg-black dark:bg-slate-700 text-white rounded-tr-none' 
                                        : 'bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 rounded-tl-none' 
                                    }} rounded-2xl px-4 py-3 shadow-sm">
                                    
                                    @if ($message['type'] === 'bot')
                                        <div class="text-sm markdown-content dark:text-slate-200">
                                            {!! \Illuminate\Support\Str::markdown($message['content']) !!}
                                        </div>
                                    @else
                                        <p class="text-sm whitespace-pre-line">{{ $message['content'] }}</p>
                                    @endif
                                    
                                    <p class="text-[10px] {{ $message['type'] === 'user' ? 'text-gray-400' : 'text-gray-400 dark:text-slate-500' }} mt-1 text-right">
                                        {{ $message['time'] }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                @if ($isTyping)
                    <div class="flex justify-start message-item animate-slide-in" id="typing-indicator">
                        <div class="flex items-end space-x-2">
                            <div class="w-6 h-6 bg-gradient-to-r from-gray-800 to-gray-600 dark:from-slate-700 dark:to-slate-600 rounded-full flex items-center justify-center">
                                <i class="fas fa-robot text-white text-xs"></i>
                            </div>
                            <div class="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-2xl rounded-tl-none px-4 py-3 shadow-sm typing-bubble">
                                <div class="flex space-x-1 typing-dots">
                                    <div class="w-2 h-2 bg-gray-400 dark:bg-slate-500 rounded-full dot-1"></div>
                                    <div class="w-2 h-2 bg-gray-400 dark:bg-slate-500 rounded-full dot-2"></div>
                                    <div class="w-2 h-2 bg-gray-400 dark:bg-slate-500 rounded-full dot-3"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="p-4 border-t border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-900 rounded-b-2xl">
            <form wire:submit.prevent="sendMessage" class="flex space-x-2" id="chat-form">
                <input type="text" 
                    wire:model="message" 
                    placeholder="Tanyakan tentang penjualan..."
                    autocomplete="off"
                    autocapitalize="off"
                    autocorrect="off"
                    spellcheck="false"
                    class="flex-1 border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-full px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-black dark:focus:ring-slate-500 focus:border-transparent transition-all placeholder-slate-400 dark:placeholder-slate-500"
                    {{ $isLoading ? 'disabled' : '' }} 
                    id="chat-input">
                
                <button type="submit"
                    class="bg-black hover:bg-gray-800 dark:bg-slate-700 dark:hover:bg-slate-600 text-white rounded-full w-12 h-12 flex items-center justify-center transition-all disabled:opacity-50 disabled:cursor-not-allowed hover:scale-105 active:scale-95"
                    {{ $isLoading ? 'disabled' : '' }} id="send-button">
                    <i class="fas fa-paper-plane transition-transform" id="send-icon"></i>
                </button>
            </form>
            <p class="text-xs text-gray-500 dark:text-slate-500 text-center mt-2">
                Tanya tentang penjualan, produk, atau transaksi
            </p>
        </div>
    </div>

    @if ($isOpen)
        <div class="fixed inset-0 bg-black/20 dark:bg-black/60 z-40 backdrop-fade-in backdrop-blur-sm" 
             onclick="toggleChat()"
             id="chat-backdrop">
        </div>
    @endif
    @endif
</div>