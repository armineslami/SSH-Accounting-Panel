<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="px-0 sm:px-8">
            <div class="grid grid-cols-1 gap-0 md:grid-cols-2 md:gap-2 lg:grid-cols-4 lg:gap-4 mb-4">
                <a href="{{ route('inbounds.index') }}">
                    <div class="bg-indigo-500 dark:bg-indigo-600 overflow-hidden shadow-sm sm:rounded-lg p-6 mb-4 md:mb-0">
                        <div class="grid grid-cols-3 gap-2">
                            <div class="col-span-2">
                                <span class="font-bold text-2xl text-gray-100 dark:text-gray-100">{{ $inboundCount }}</span>
                                <p class="font-light text-sm text-gray-200 dark:text-gray-300">{{ __("Inbounds") }}</p>
                            </div>
                            <div class="ml-auto col-span-1">
                                <svg class="w-10 h-10 text-gray-200 dark:text-gray-200" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                                    <path fill-rule="evenodd" d="M8 4a4 4 0 1 0 0 8 4 4 0 0 0 0-8Zm-2 9a4 4 0 0 0-4 4v1c0 1.1.9 2 2 2h8a2 2 0 0 0 2-2v-1a4 4 0 0 0-4-4H6Zm7.3-2a6 6 0 0 0 0-6A4 4 0 0 1 20 8a4 4 0 0 1-6.7 3Zm2.2 9a4 4 0 0 0 .5-2v-1a6 6 0 0 0-1.5-4H18a4 4 0 0 1 4 4v1a2 2 0 0 1-2 2h-4.5Z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </a>
                <a href="{{ route('servers.index') }}">
                    <div class="bg-rose-500 dark:bg-rose-600 overflow-hidden shadow-sm sm:rounded-lg p-6 mb-4 md:mb-0">
                        <div class="grid grid-cols-3 gap-2">
                            <div class="col-span-2">
                                <span class="font-bold text-2xl text-gray-100 dark:text-gray-100">{{ $serverCount }}</span>
                                <p class="font-light text-sm text-gray-200 dark:text-gray-300">{{ __("Servers") }}</p>
                            </div>
                            <div class="ml-auto col-span-1">
                                <svg class="w-10 h-10 text-gray-200 dark:text-gray-200" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                                    <path fill-rule="evenodd" d="M2 5a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm14 1a1 1 0 11-2 0 1 1 0 012 0zM2 13a2 2 0 012-2h12a2 2 0 012 2v2a2 2 0 01-2 2H4a2 2 0 01-2-2v-2zm14 1a1 1 0 11-2 0 1 1 0 012 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </a>
                <div class="relative has-tooltip cursor-pointer bg-orange-500 dark:bg-orange-600 overflow-hidden shadow-sm sm:rounded-lg p-6 mb-4 md:mb-0">
                    <div class="grid grid-cols-3 gap-2">
                        <div class="col-span-2">
                            <span id="upTime" class="font-bold text-2xl text-gray-100 dark:text-gray-100">-</span>
                            <p class="font-light text-sm text-gray-200 dark:text-gray-300">{{ __("Uptime") }}</p>
                        </div>
                        <div class="ml-auto col-span1">
                            <svg class="w-10 h-10 text-gray-200 dark:text-gray-200" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                                <path fill-rule="evenodd" d="M2 12a10 10 0 1 1 20 0 10 10 0 0 1-20 0Zm11-4a1 1 0 1 0-2 0v4c0 .3.1.5.3.7l3 3a1 1 0 0 0 1.4-1.4L13 11.6V8Z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                    <div id="upTimeFull" class='tooltip bg-white text-xl font-bold text-gray-900 opacity-95 p-4'>-</div>
                </div>
                <div class="bg-teal-500 dark:bg-teal-600 overflow-hidden shadow-sm sm:rounded-lg p-6 mb-1 md:mb-0">
                    <div class="grid grid-cols-3 gap-2">
                        <div class="col-span-2">
                            <span class="font-bold text-2xl text-gray-100 dark:text-gray-100">{{ config("app.version") }}</span>
                            <p class="font-light text-sm text-gray-200 dark:text-gray-300">{{ __("Version") }}</p>
                        </div>
                        <div class="ml-auto col-span-1">
                            <svg class="w-10 h-10 text-gray-200 dark:text-gray-200" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                                <path fill-rule="evenodd" d="M2 12a10 10 0 1 1 20 0 10 10 0 0 1-20 0Zm9.4-5.5a1 1 0 1 0 0 2 1 1 0 1 0 0-2ZM10 10a1 1 0 1 0 0 2h1v3h-1a1 1 0 1 0 0 2h4a1 1 0 1 0 0-2h-1v-4c0-.6-.4-1-1-1h-2Z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="">
                    <div class="grid grid-cols-2 gap-2 lg:grid-cols-4 lg:gap-4">
                        <div class="p-4">
                            <div id="cpuProgressBar"></div>
                            <p class="pt-12 font-medium text-gray-900 dark:text-gray-100 text-center">CPU</p>
                        </div>
                        <div class="p-4">
                            <div id="memoryProgressBar"></div>
                            <p class="pt-12 font-medium text-gray-900 dark:text-gray-100 text-center">Memory: <span id="memoryUsage">0</span> / <span id="memory">0</span></p>
                        </div>
                        <div class="p-4">
                            <div id="swapProgressBar"></div>
                            <p class="pt-12 font-medium text-gray-900 dark:text-gray-100 text-center">Swap: <span id="swapUsage">0</span> / <span id="swap">0</span></p>
                        </div>
                        <div class="p-4">
                            <div id="diskProgressBar"></div>
                            <p class="pt-12 font-medium text-gray-900 dark:text-gray-100 text-center">Disk: <span id="diskUsage">0</span> / <span id="disk">0</span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
        <script src="{{ asset('js/dashboard.js?v2') }}"></script>
    @endpush
</x-app-layout>


