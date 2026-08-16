<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? (\App\Models\Setting::first()?->shop_name ?? 'POS Cafe') }}</title>
    
    {{-- 1. CDN Tailwind & Config untuk Dark Mode --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class', // Wajib untuk fitur Dark Mode
            theme: {
                extend: {
                    colors: {
                        slate: {
                            850: '#151f32',
                        }
                    }
                }
            }
        }
    </script>

    {{-- 2. Library Pendukung --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    {{-- Alpine.js (Wajib ada defer) --}}
    {{-- <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script> --}}

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        * { font-family: 'Inter', sans-serif; }

        /* Style Scrollbar Chat */
        #chat-messages { scrollbar-width: thin; scrollbar-color: #cbd5e1 #f1f5f9; scroll-behavior: smooth; }
        #chat-messages::-webkit-scrollbar { width: 6px; }
        #chat-messages::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 3px; }
        #chat-messages::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; transition: background 0.3s ease; }
        #chat-messages::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* Animasi Chat */
        @keyframes slideIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .animate-slide-in { animation: slideIn 0.3s ease-out; }
        .typing-dots .dot-1 { animation: typingDot 1.4s infinite; }
        .typing-dots .dot-2 { animation: typingDot 1.4s infinite 0.2s; }
        .typing-dots .dot-3 { animation: typingDot 1.4s infinite 0.4s; }
        @keyframes typingDot { 0%, 60%, 100% { transform: translateY(0); opacity: 0.4; } 30% { transform: translateY(-5px); opacity: 1; } }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        .backdrop-fade-in { animation: fadeIn 0.15s ease-out; }
        
        /* Markdown Styles untuk Chatbot */
        .markdown-content h1, .markdown-content h2, .markdown-content h3 { font-weight: 600; margin: 0.5rem 0; color: #1f2937; }
        .markdown-content strong { font-weight: 700; color: #111827; }
        .markdown-content ul { list-style-type: disc; margin-left: 1.2rem; }
        .markdown-content ol { list-style-type: decimal; margin-left: 1.2rem; }
        .markdown-content p { margin-bottom: 0.5rem; }
        .dark .markdown-content h1, .dark .markdown-content h2, .dark .markdown-content h3 { color: #e2e8f0; }
        .dark .markdown-content strong { color: #f8fafc; }
    </style>
    
    @livewireStyles
</head>

{{-- 
    LOGIKA UTAMA (GABUNGAN SIDEBAR & DARK MODE):
    1. sidebarOpen: Mengatur menu di HP/Laptop.
    2. darkMode: Mengatur tema gelap/terang.
--}}
<body class="bg-slate-50 text-slate-900 antialiased transition-colors duration-300 dark:bg-slate-900 dark:text-slate-100"
      x-data="{ 
          sidebarOpen: window.innerWidth >= 1024,
          darkMode: localStorage.getItem('darkMode') === 'true',
          
          toggleTheme() {
              this.darkMode = !this.darkMode;
              localStorage.setItem('darkMode', this.darkMode);
              if (this.darkMode) {
                  document.documentElement.classList.add('dark');
              } else {
                  document.documentElement.classList.remove('dark');
              }
          }
      }"
      x-init="
          // Listener Resize Layar
          window.addEventListener('resize', () => { sidebarOpen = window.innerWidth >= 1024 });
          
          // Init Dark Mode saat load
          if (darkMode) document.documentElement.classList.add('dark');
          
          // Watcher perubahan Dark Mode
          $watch('darkMode', val => val ? document.documentElement.classList.add('dark') : document.documentElement.classList.remove('dark'));
      ">

    <div id="app-layout">
        {{ $slot }}
    </div>

    {{-- 3. AI Chatbot (Hanya dimuat jika user adalah ADMIN) --}}
    @auth
        @if(auth()->user()->role === 'admin')
            <livewire:ai-chat />
        @endif
    @endauth

    {{-- Backdrop Mobile untuk Sidebar --}}
    <div x-show="sidebarOpen" 
         @click="sidebarOpen = false"
         x-transition.opacity
         class="fixed inset-0 z-40 bg-black/50 lg:hidden backdrop-blur-sm">
    </div>

    @livewireScripts
    @stack('scripts')
    
    <script>
        // --- SCRIPT AI CHATBOT ---

        // Toggle Icon Animation
        function toggleChat() {
            Livewire.dispatch('toggleChat');
            const icon = document.getElementById('chat-icon');
            if (icon) {
                icon.style.transform = 'rotate(360deg)';
                setTimeout(() => { icon.style.transform = 'rotate(0deg)'; }, 200);
            }
        }

        // Scroll ke Bawah Otomatis
        function scrollToBottom() {
            const chatMessages = document.getElementById('chat-messages');
            if (chatMessages) {
                setTimeout(() => {
                    chatMessages.scrollTo({ top: chatMessages.scrollHeight, behavior: 'smooth' });
                }, 50);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // PENTING: Cek apakah tombol chat ada. Jika Kasir login, tombol ini gak ada, jadi stop script di sini.
            if (!document.getElementById('chat-toggle-btn')) {
                return;
            }

            scrollToBottom();

            // Listeners dari Livewire
            Livewire.on('scroll-to-bottom', () => { scrollToBottom(); });
            
            Livewire.on('start-typing-animation', (event) => {
                simulateTypingAnimation(event.message);
            });

            // Animasi tombol kirim
            const form = document.getElementById('chat-form');
            const sendIcon = document.getElementById('send-icon');
            if (form) {
                form.addEventListener('submit', function(e) {
                    if (sendIcon) {
                        sendIcon.style.transform = 'translateY(-2px)';
                        setTimeout(() => { sendIcon.style.transform = 'translateY(0)'; }, 200);
                    }
                });
            }
        });

        // Efek Mengetik (Typing Effect)
        function simulateTypingAnimation(message) {
            const messagesContainer = document.getElementById('messages-container');
            const typingIndicator = document.getElementById('typing-indicator');

            if (!messagesContainer) return;
            if (typingIndicator) typingIndicator.remove();

            const messageElement = document.createElement('div');
            messageElement.className = 'flex justify-start message-item';
            messageElement.innerHTML = `
                <div class="max-w-[80%]">
                    <div class="flex items-end space-x-2">
                        <div class="w-6 h-6 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-robot text-white text-xs"></i>
                        </div>
                        <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-2xl px-4 py-3 shadow-sm typing-message">
                            <div class="text-sm markdown-content typing-text dark:text-slate-200"></div>
                        </div>
                    </div>
                </div>
            `;

            messagesContainer.appendChild(messageElement);
            scrollToBottom();

            const typingText = messageElement.querySelector('.typing-text');
            let index = 0;
            const typingSpeed = 10;

            function typeCharacter() {
                if (index < message.length) {
                    const partialMessage = message.substring(0, index + 1);
                    typingText.innerHTML = marked.parse(partialMessage);
                    index++;
                    scrollToBottom();
                    setTimeout(typeCharacter, typingSpeed);
                } else {
                    typingText.innerHTML = marked.parse(message);
                    setTimeout(() => {
                        Livewire.dispatch('add-bot-message-from-js', { message: message });
                    }, 500);
                }
            }
            setTimeout(typeCharacter, 500);
        }

        // Hook Livewire Update
        Livewire.hook('morph.updated', ({ el, component }) => {
            scrollToBottom();
        });

        // Global SweetAlert Toast Listener
        document.addEventListener('livewire:init', () => {
            Livewire.on('show-toast', (event) => {
                const data = Array.isArray(event) ? event[0] : event;
                const type = data.type; 
                const message = data.message;
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    background: document.documentElement.classList.contains('dark') ? '#0f172a' : '#fff',
                    color: document.documentElement.classList.contains('dark') ? '#fff' : '#0f172a',
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                });
                Toast.fire({ icon: type, title: message });
            });
        });
    </script>
</body>
</html>