<x-app-layout>
    <x-slot name="header">
        <div class="grid gap-2 grid-cols-1 md:grid-cols-2 lg:grid-cols-2">
            <h2 class="my-auto font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Inbounds') }}
            </h2>
            <form class="relative my-auto mt-2 md:mt-0" method="get" action="{{ route('inbounds.search') }}" >
                @csrf
                @method('get')
                <x-text-input class="w-full text-sm md:text-md {{ !empty($query) ? 'pe-16' : 'pe-8' }}" name="query" type="text" placeholder="Search by username, server name or ip" :value="isset($query) ? $query : null" />
                @if(!empty($query))
                    <span class="absolute right-10 h-full cursor-pointer" onclick="window.location.href = '/inbounds'">
                        <svg class="w-4 h-4 text-gray-800 dark:text-white mt-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6m0 12L6 6"/>
                        </svg>
                    </span>
                @endif
                <button
                    type="submit"
                    class="absolute right-0 p-2 h-full cursor-pointer rounded-e me-1">
                    <svg class="w-4 h-4 text-gray-800 dark:text-white mt-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </button>
            </form>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="px-0 sm:px-8">
            <div class="flex ms-4 sm:ms-0 me-4 sm:me-0 mb-4">
                @if (session('status') === 'inbound-deleted')
                    <span
                        x-data="{ show: true }"
                        x-show="show"
                        x-transition
                        x-init="setTimeout(() => show = false, 10000)"
                        class="my-auto text-sm text-green-600 dark:text-green-400 me-4"
                    >{{ __('Inbound Successfully Deleted.') }}</span>
                @endif
                <a class="ms-auto" href="{{route('inbounds.create')}}" >
                    <x-primary-button>
                        {{ __('Create Inbound') }}
                    </x-primary-button>
                </a>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 overflow-x-auto">
                    @if (isset($inbounds))
                        @if ($inbounds->count() > 0)
                            <table class="border-collapse table-auto w-full text-sm">
                                <thead>
                                <tr>
                                    <th class="border-b dark:border-slate-600 font-medium p-4 pl-8 pt-0 pb-3 text-slate-400 dark:text-slate-200 text-left">#</th>
                                    <th class="border-b dark:border-slate-600 font-medium p-4 pl-8 pt-0 pb-3 text-slate-400 dark:text-slate-200 text-left sticky -left-8 z-10 bg-white dark:bg-gray-800 md:bg-transparent">{{ __('Username') }}</th>
                                    <th class="border-b dark:border-slate-600 font-medium p-4 pl-8 pt-0 pb-3 text-slate-400 dark:text-slate-200 text-left">{{ __('Server') }}</th>
                                    <th class="border-b dark:border-slate-600 font-medium p-4 pl-8 pt-0 pb-3 text-slate-400 dark:text-slate-200 text-left">{{ __('Active') }}</th>
                                    <th class="border-b dark:border-slate-600 font-medium p-4 pl-8 pt-0 pb-3 text-slate-400 dark:text-slate-200 text-left">{{ __('Traffic Limit (GB)') }}</th>
                                    <th class="border-b dark:border-slate-600 font-medium p-4 pl-8 pt-0 pb-3 text-slate-400 dark:text-slate-200 text-left">{{ __('Remaining Traffic (GB)') }}</th>
                                    <th class="border-b dark:border-slate-600 font-medium p-4 pl-8 pt-0 pb-3 text-slate-400 dark:text-slate-200 text-left">{{ __('Max Logins') }}</th>
                                    <th class="border-b dark:border-slate-600 font-medium p-4 pl-8 pt-0 pb-3 text-slate-400 dark:text-slate-200 text-left">{{ __('Expires At') }}</th>
                                    <th class="border-b dark:border-slate-600 font-medium p-4 pl-8 pt-0 pb-3 text-slate-400 dark:text-slate-200 text-left">{{ __('Created At') }}</th>
                                </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-slate-800">
                                @foreach ($inbounds as $inbound)
                                    <tr class="md:hover:bg-gray-100 md:dark:hover:bg-gray-700 cursor-pointer" onclick="location.href='{{route('inbounds.index', ['id' => $inbound->id])}}'">
                                        <td class="border-b border-slate-100 dark:border-slate-700 p-4 pl-8 text-slate-500 dark:text-slate-400">{{ $loop->index + 1 + ($inbounds->currentPage() - 1) * $inbounds->perPage() }}</td>
                                        <td class="border-b border-slate-100 dark:border-slate-700 p-4 pl-8 text-slate-500 dark:text-slate-400 sticky -left-8 z-10 bg-white dark:bg-gray-800 md:bg-transparent">{{ $inbound->username }}</td>
                                        <td class="border-b border-slate-100 dark:border-slate-700 p-4 pl-8 text-slate-500 dark:text-slate-400">{{ is_null($inbound->server_ip) ? "-" : $inbound->server->name }}</td>
                                        <td class="border-b border-slate-100 dark:border-slate-700 p-4 pl-8 {{ $inbound->is_active == 1 ? 'text-green-500 dark:green-slate-400' : 'text-red-500 dark:red-slate-400' }} ">{{ $inbound->is_active == 1 ? __("YES") : __("NO") }}</td>
                                        <td class="border-b border-slate-100 dark:border-slate-700 p-4 pl-8 text-slate-500 dark:text-slate-400">{{ is_null($inbound->traffic_limit) ? "∞" : $inbound->traffic_limit }}</td>
                                        <td class="border-b border-slate-100 dark:border-slate-700 p-4 pl-8 text-slate-500 dark:text-slate-400">{{ is_null($inbound->remaining_traffic) ? "∞" : $inbound->remaining_traffic }}</td>
                                        <td class="border-b border-slate-100 dark:border-slate-700 p-4 pl-8 text-slate-500 dark:text-slate-400">{{ $inbound->max_login }}</td>
                                        <td class="border-b border-slate-100 dark:border-slate-700 p-4 pl-8 text-slate-500 dark:text-slate-400">{{ is_null($inbound->expires_at) ? "∞" : $inbound->expires_at }}</td>
                                        <td class="border-b border-slate-100 dark:border-slate-700 p-4 pl-8 text-slate-500 dark:text-slate-400">{{ $inbound->updated_at }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                            <div class="table-nav mt-4 text-slate-400 dark:text-slate-200">
                                {{ $inbounds->links() }}
                            </div>
                        @else
                            <div class="p-8 text-center text-slate-400 dark:text-slate-200">
                                <h3 class="py-4 text-lg font-bold">No Inbounds</h3>
                                <p>You should create one using <b class="text-indigo-400 dark:text-indigo-600">{{ __('Create Inbound') }}</b> button</p>
                            </div>
                        @endif
                    @endif

                    @if (isset($search_result))
                            @if ($search_result->count() > 0)
                                <table class="border-collapse table-auto w-full text-sm">
                                    <thead>
                                    <tr>
                                        <th class="border-b dark:border-slate-600 font-medium p-4 pl-8 pt-0 pb-3 text-slate-400 dark:text-slate-200 text-left">#</th>
                                        <th class="border-b dark:border-slate-600 font-medium p-4 pl-8 pt-0 pb-3 text-slate-400 dark:text-slate-200 text-left sticky -left-8 z-10 bg-white dark:bg-slate-800">{{ __('Username') }}</th>
                                        <th class="border-b dark:border-slate-600 font-medium p-4 pl-8 pt-0 pb-3 text-slate-400 dark:text-slate-200 text-left">{{ __('Server') }}</th>
                                        <th class="border-b dark:border-slate-600 font-medium p-4 pl-8 pt-0 pb-3 text-slate-400 dark:text-slate-200 text-left">{{ __('Active') }}</th>
                                        <th class="border-b dark:border-slate-600 font-medium p-4 pl-8 pt-0 pb-3 text-slate-400 dark:text-slate-200 text-left">{{ __('Traffic Limit (GB)') }}</th>
                                        <th class="border-b dark:border-slate-600 font-medium p-4 pl-8 pt-0 pb-3 text-slate-400 dark:text-slate-200 text-left">{{ __('Remaining Traffic (GB)') }}</th>
                                        <th class="border-b dark:border-slate-600 font-medium p-4 pl-8 pt-0 pb-3 text-slate-400 dark:text-slate-200 text-left">{{ __('Max Logins') }}</th>
                                        <th class="border-b dark:border-slate-600 font-medium p-4 pl-8 pt-0 pb-3 text-slate-400 dark:text-slate-200 text-left">{{ __('Expires At') }}</th>
                                        <th class="border-b dark:border-slate-600 font-medium p-4 pl-8 pt-0 pb-3 text-slate-400 dark:text-slate-200 text-left">{{ __('Created At') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-slate-800">
                                    @foreach ($search_result as $inbound)
                                        <tr class="md:hover:bg-gray-100 md:dark:hover:bg-gray-700 cursor-pointer" onclick="location.href='{{route('inbounds.index', ['id' => $inbound->id])}}'">
                                            <td class="border-b border-slate-100 dark:border-slate-700 p-4 pl-8 text-slate-500 dark:text-slate-400">{{ $loop->index + 1 + ($search_result->currentPage() - 1) * $search_result->perPage() }}</td>
                                            <td class="border-b border-slate-100 dark:border-slate-700 p-4 pl-8 text-slate-500 dark:text-slate-400 sticky -left-8 z-10 bg-white dark:bg-gray-800 md:bg-transparent">{{ $inbound->username }}</td>
                                            <td class="border-b border-slate-100 dark:border-slate-700 p-4 pl-8 text-slate-500 dark:text-slate-400">{{ is_null($inbound->server_ip) ? "-" : $inbound->server->name }}</td>
                                            <td class="border-b border-slate-100 dark:border-slate-700 p-4 pl-8 {{ $inbound->is_active == 1 ? 'text-green-500 dark:green-slate-400' : 'text-red-500 dark:red-slate-400' }} ">{{ $inbound->is_active == 1 ? __("YES") : __("NO") }}</td>
                                            <td class="border-b border-slate-100 dark:border-slate-700 p-4 pl-8 text-slate-500 dark:text-slate-400">{{ is_null($inbound->traffic_limit) ? "∞" : $inbound->traffic_limit }}</td>
                                            <td class="border-b border-slate-100 dark:border-slate-700 p-4 pl-8 text-slate-500 dark:text-slate-400">{{ is_null($inbound->remaining_traffic) ? "∞" : $inbound->remaining_traffic }}</td>
                                            <td class="border-b border-slate-100 dark:border-slate-700 p-4 pl-8 text-slate-500 dark:text-slate-400">{{ $inbound->max_login }}</td>
                                            <td class="border-b border-slate-100 dark:border-slate-700 p-4 pl-8 text-slate-500 dark:text-slate-400">{{ is_null($inbound->expires_at) ? "∞" : $inbound->expires_at }}</td>
                                            <td class="border-b border-slate-100 dark:border-slate-700 p-4 pl-8 text-slate-500 dark:text-slate-400">{{ $inbound->updated_at }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                                <div class="table-nav mt-4 text-slate-400 dark:text-slate-200">
                                    {{ $search_result->links() }}
                                </div>
                            @else
                                <div class="p-8 text-center text-slate-400 dark:text-slate-200">
                                    <h3 class="py-4 text-lg font-bold">No Result</h3>
                                    <p>No inbound found using given query.</p>
                                </div>
                            @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
