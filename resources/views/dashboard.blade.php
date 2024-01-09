<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="px-0 sm:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="max-w-5xl mx-auto">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="p-4">
                            <div id="cpuProgressBar"></div>
                            <p class="py-12 text-gray-900 dark:text-gray-100 text-center">CPU</p>
                        </div>
                        <div class="p-4">
                            <div id="memoryProgressBar"></div>
                            <p class="py-12 text-gray-900 dark:text-gray-100 text-center">Memory: <span id="memoryUsage">0</span> / <span id="memory">0</span></p>
                        </div>
                        <div class="p-4">
                            <div id="swapProgressBar"></div>
                            <p class="py-12 text-gray-900 dark:text-gray-100 text-center">Swap: <span id="swapUsage">0</span> / <span id="swap">0</span></p>
                        </div>
                        <div class="p-4">
                            <div id="diskProgressBar"></div>
                            <p class="py-12 text-gray-900 dark:text-gray-100 text-center">Disk: <span id="diskUsage">0</span> / <span id="disk">0</span></p>
                        </div>
                    </div>
                </div>
                <div class="p-6 flex flex-wrap">
                    <span class="w-full sm:w-auto mr-2 mb-2 p-2 text-gray-100 bg-indigo-400 dark:bg-indigo-600 rounded">
                        <span class="font-bold">Uptime: </span><span class="font-light" id="upTime">-</span>
                    </span>
                    <span class="w-full sm:w-auto mr-2 mb-2 p-2 text-gray-100 bg-rose-400 dark:bg-rose-600 rounded">
                        <span class="font-bold">Inbounds: </span><span class="font-light">{{ $inboundCount }}</span>
                    </span>
                    <span class="w-full sm:w-auto mr-2 mb-2 p-2 text-gray-100 bg-orange-400 dark:bg-orange-600 rounded">
                        <span class="font-bold">Version: </span><span class="font-light">{{ config("app.version") }}</span>
                    </span>
                  </div>
            </div>
        </div>
    </div>
    @push('scripts')
        <script src="{{ asset('js/dashboard.js') }}"></script>
    @endpush
</x-app-layout>


