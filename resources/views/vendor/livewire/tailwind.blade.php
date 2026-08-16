<div>
    @if ($paginator->hasPages())
        <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between">
            {{-- Tampilan Mobile --}}
            <div class="flex justify-between flex-1 sm:hidden">
                <span>
                    @if ($paginator->onFirstPage())
                        <span class="relative inline-flex items-center px-4 py-2 text-xs font-medium text-slate-400 dark:text-slate-500 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 cursor-not-allowed leading-5 rounded-lg">
                            Sebelumnya
                        </span>
                    @else
                        <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled"
                            class="relative inline-flex items-center px-4 py-2 text-xs font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 leading-5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 focus:outline-none transition ease-in-out duration-150">
                            Sebelumnya
                        </button>
                    @endif
                </span>

                <span>
                    @if ($paginator->hasMorePages())
                        <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled"
                            class="relative inline-flex items-center px-4 py-2 ml-3 text-xs font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 leading-5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 focus:outline-none transition ease-in-out duration-150">
                            Selanjutnya
                        </button>
                    @else
                        <span class="relative inline-flex items-center px-4 py-2 ml-3 text-xs font-medium text-slate-400 dark:text-slate-500 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 cursor-not-allowed leading-5 rounded-lg">
                            Selanjutnya
                        </span>
                    @endif
                </span>
            </div>

            {{-- Tampilan Desktop --}}
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between gap-4">
                <div>
                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-5">
                        Menampilkan
                        <span class="font-bold text-slate-900 dark:text-white">{{ $paginator->firstItem() ?? 0 }}</span>
                        sampai
                        <span class="font-bold text-slate-900 dark:text-white">{{ $paginator->lastItem() ?? 0 }}</span>
                        dari
                        <span class="font-bold text-slate-900 dark:text-white">{{ $paginator->total() }}</span>
                        data
                    </p>
                </div>

                <div>
                    <span class="relative z-0 inline-flex rounded-lg shadow-2xs space-x-0 border border-slate-200 dark:border-slate-700 overflow-hidden">
                        {{-- Tombol Previous Desktop --}}
                        <span>
                            @if ($paginator->onFirstPage())
                                <span aria-disabled="true" aria-label="Previous">
                                    <span class="relative inline-flex items-center px-3 py-2 text-xs font-medium text-slate-400 dark:text-slate-600 bg-slate-50 dark:bg-slate-800/80 cursor-not-allowed leading-5 h-full" aria-hidden="true">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                </span>
                            @else
                                <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled"
                                    class="relative inline-flex items-center px-3 py-2 text-xs font-medium text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 leading-5 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-white focus:z-10 focus:outline-none transition ease-in-out duration-150 h-full" aria-label="Previous">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            @endif
                        </span>

                        {{-- Nomor Halaman --}}
                        @foreach ($elements as $element)
                            @if (is_string($element))
                                <span aria-disabled="true">
                                    <span class="relative inline-flex items-center px-3 py-2 text-xs font-medium text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-800/80 cursor-default leading-5 border-l border-slate-200 dark:border-slate-700">{{ $element }}</span>
                                </span>
                            @endif

                            @if (is_array($element))
                                @foreach ($element as $page => $url)
                                    <span wire:key="paginator-{{ $paginator->getPageName() }}-page{{ $page }}">
                                        @if ($page == $paginator->currentPage())
                                            <span aria-current="page">
                                                <span class="relative inline-flex items-center px-3.5 py-2 text-xs font-bold text-white bg-slate-900 dark:bg-blue-600 cursor-default leading-5 border-l border-slate-200 dark:border-slate-700 shadow-inner">{{ $page }}</span>
                                            </span>
                                        @else
                                            <button type="button" wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')"
                                                class="relative inline-flex items-center px-3.5 py-2 text-xs font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 leading-5 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-white border-l border-slate-200 dark:border-slate-700 focus:z-10 focus:outline-none transition ease-in-out duration-150"
                                                aria-label="Go to page {{ $page }}">
                                                {{ $page }}
                                            </button>
                                        @endif
                                    </span>
                                @endforeach
                            @endif
                        @endforeach

                        {{-- Tombol Next Desktop --}}
                        <span>
                            @if ($paginator->hasMorePages())
                                <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled"
                                    class="relative inline-flex items-center px-3 py-2 text-xs font-medium text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 leading-5 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-white border-l border-slate-200 dark:border-slate-700 focus:z-10 focus:outline-none transition ease-in-out duration-150 h-full" aria-label="Next">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            @else
                                <span aria-disabled="true" aria-label="Next">
                                    <span class="relative inline-flex items-center px-3 py-2 text-xs font-medium text-slate-400 dark:text-slate-600 bg-slate-50 dark:bg-slate-800/80 cursor-not-allowed leading-5 border-l border-slate-200 dark:border-slate-700 h-full" aria-hidden="true">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                </span>
                            @endif
                        </span>
                    </span>
                </div>
            </div>
        </nav>
    @endif
</div>