<div>
    @if ($paginator->hasPages())
        <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between">
            {{-- Tampilan Mobile --}}
            <div class="flex justify-between flex-1 sm:hidden">
                <span>
                    @if ($paginator->onFirstPage())
                        <span
                            class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-slate-500 bg-white border border-slate-300 cursor-default leading-5 rounded-md">
                            Previous
                        </span>
                    @else
                        {{-- PERBAIKAN 1: Tambah getPageName() --}}
                        <button wire:click="previousPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled"
                            class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 leading-5 rounded-md hover:text-slate-500 focus:outline-none focus:ring ring-slate-300 focus:border-blue-300 active:bg-slate-100 active:text-slate-700 transition ease-in-out duration-150">
                            Previous
                        </button>
                    @endif
                </span>

                <span>
                    @if ($paginator->hasMorePages())
                        {{-- PERBAIKAN 2: Tambah getPageName() --}}
                        <button wire:click="nextPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled"
                            class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-slate-700 bg-white border border-slate-300 leading-5 rounded-md hover:text-slate-500 focus:outline-none focus:ring ring-slate-300 focus:border-blue-300 active:bg-slate-100 active:text-slate-700 transition ease-in-out duration-150">
                            Next
                        </button>
                    @else
                        <span
                            class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-slate-500 bg-white border border-slate-300 cursor-default leading-5 rounded-md">
                            Next
                        </span>
                    @endif
                </span>
            </div>

            {{-- Tampilan Desktop --}}
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-slate-700 leading-5">
                        Showing
                        <span class="font-medium">{{ $paginator->firstItem() }}</span>
                        to
                        <span class="font-medium">{{ $paginator->lastItem() }}</span>
                        of
                        <span class="font-medium">{{ $paginator->total() }}</span>
                        results
                    </p>
                </div>

                <div>
                    <span class="relative z-0 inline-flex rounded-md shadow-sm">
                        {{-- Tombol Previous Desktop --}}
                        <span>
                            @if ($paginator->onFirstPage())
                                <span aria-disabled="true" aria-label="Previous">
                                    <span
                                        class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-slate-500 bg-white border border-slate-300 cursor-default rounded-l-md leading-5"
                                        aria-hidden="true">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                </span>
                            @else
                                {{-- PERBAIKAN 3: Tambah getPageName() --}}
                                <button wire:click="previousPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled"
                                    class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-slate-500 bg-white border border-slate-300 rounded-l-md leading-5 hover:text-slate-400 focus:z-10 focus:outline-none focus:ring ring-slate-300 focus:border-blue-300 active:bg-slate-100 active:text-slate-500 transition ease-in-out duration-150"
                                    aria-label="Previous">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            @endif
                        </span>

                        {{-- Nomor Halaman --}}
                        @foreach ($elements as $element)
                            @if (is_string($element))
                                <span aria-disabled="true">
                                    <span
                                        class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-slate-700 bg-white border border-slate-300 cursor-default leading-5">{{ $element }}</span>
                                </span>
                            @endif

                            @if (is_array($element))
                                @foreach ($element as $page => $url)
                                    <span wire:key="paginator-{{ $paginator->getPageName() }}-page{{ $page }}">
                                        @if ($page == $paginator->currentPage())
                                            <span aria-current="page">
                                                <span
                                                    class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-white bg-slate-900 border border-slate-300 cursor-default leading-5">{{ $page }}</span>
                                            </span>
                                        @else
                                            {{-- PERBAIKAN 4: Tambah getPageName() di parameter kedua gotoPage --}}
                                            <button wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')"
                                                class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-slate-700 bg-white border border-slate-300 leading-5 hover:text-slate-500 focus:z-10 focus:outline-none focus:ring ring-slate-300 focus:border-blue-300 active:bg-slate-100 active:text-slate-700 transition ease-in-out duration-150"
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
                                {{-- PERBAIKAN 5: Tambah getPageName() --}}
                                <button wire:click="nextPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled"
                                    class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-slate-500 bg-white border border-slate-300 rounded-r-md leading-5 hover:text-slate-400 focus:z-10 focus:outline-none focus:ring ring-slate-300 focus:border-blue-300 active:bg-slate-100 active:text-slate-500 transition ease-in-out duration-150"
                                    aria-label="Next">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            @else
                                <span aria-disabled="true" aria-label="Next">
                                    <span
                                        class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-slate-500 bg-white border border-slate-300 cursor-default rounded-r-md leading-5"
                                        aria-hidden="true">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                                clip-rule="evenodd" />
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