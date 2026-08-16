<header class="sticky top-0 z-30 flex items-center justify-between h-16 px-4 sm:px-6 bg-white border-b border-slate-200 dark:bg-slate-800 dark:border-slate-700 transition-colors duration-300">
    
    <div class="flex items-center space-x-3">
        {{-- TOMBOL BURGER MENU --}}
        <button @click.stop="sidebarOpen = !sidebarOpen"
            type="button"
            class="xl:hidden text-slate-600 hover:text-slate-900 p-2 rounded-lg hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-700 transition-colors focus:outline-none"
            aria-label="Toggle Sidebar">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
        {{-- TOMBOL TEMA --}}
        <button @click="toggleTheme()" 
                type="button"
                class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-700 transition-colors focus:outline-none"
                title="Ganti Tema">
            
           <svg x-show="darkMode" style="display: none;" class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path>
            </svg>

            <svg x-show="!darkMode" class="w-5 h-5 text-slate-600" fill="currentColor" viewBox="0 0 20 20">
                <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
            </svg>
        </button>

        {{-- JAM --}}
        <span class="hidden md:inline-block text-xs font-medium text-slate-600 bg-slate-100 px-3 py-1.5 rounded-lg dark:bg-slate-700 dark:text-slate-300 border border-transparent dark:border-slate-600">
            {{ now()->format('d M Y, H:i')}}
        </span>
    </div>
</header>