<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Servers') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="px-0 sm:px-8">
            <div class="flex ms-4 sm:ms-0 me-4 sm:me-0 mb-4">
                @if (session('status') === 'server-deleted')
                    <span
                        x-data="{ show: true }"
                        x-show="show"
                        x-transition
                        x-init="setTimeout(() => show = false, 10000)"
                        class="my-auto text-sm text-green-600 dark:text-green-400 me-4"
                    >{{ __('Server Successfully Deleted.') }}</span>
                @endif
                <a class="ms-auto" href="{{route('servers.create')}}" >
                    <x-primary-button>
                        {{ __('Create Server') }}
                    </x-primary-button>
                </a>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 overflow-x-auto">
                    @if ($servers->count() > 0)
                        <table class="border-collapse table-auto w-full text-sm">
                            <thead>
                            <tr>
                                <th class="border-b dark:border-slate-600 font-medium p-4 pl-8 pt-0 pb-3 text-slate-400 dark:text-slate-200 text-left">#</th>
                                <th class="border-b dark:border-slate-600 font-medium p-4 pl-8 pt-0 pb-3 text-slate-400 dark:text-slate-200 text-left sticky -left-8 z-10 bg-white dark:bg-gray-800 md:bg-transparent">{{ __('Name') }}</th>
                                <th class="border-b dark:border-slate-600 font-medium p-4 pl-8 pt-0 pb-3 text-slate-400 dark:text-slate-200 text-left">{{ __('Username') }}</th>
                                <th class="border-b dark:border-slate-600 font-medium p-4 pl-8 pt-0 pb-3 text-slate-400 dark:text-slate-200 text-left">{{ __('Address') }}</th>
                                <th class="border-b dark:border-slate-600 font-medium p-4 pl-8 pt-0 pb-3 text-slate-400 dark:text-slate-200 text-left">{{ __('Port') }}</th>
                                <th class="border-b dark:border-slate-600 font-medium p-4 pl-8 pt-0 pb-3 text-slate-400 dark:text-slate-200 text-left">{{ __('UDP Port') }}</th>
                            </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-slate-800">
                            @foreach ($servers as $server)
                                <tr class="md:hover:bg-gray-100 md:dark:hover:bg-gray-700 cursor-pointer" onclick="location.href='{{route('servers.index', ['id' => $server->id])}}'">
                                    <td class="border-b border-slate-100 dark:border-slate-700 p-4 pl-8 text-slate-500 dark:text-slate-400">{{ $loop->index + 1 + ($servers->currentPage() - 1) * $servers->perPage() }}</td>
                                    <td class="border-b border-slate-100 dark:border-slate-700 p-4 pl-8 text-slate-500 dark:text-slate-400 sticky -left-8 z-10 bg-white dark:bg-gray-800 md:bg-transparent">{{ $server->name }}</td>
                                    <td class="border-b border-slate-100 dark:border-slate-700 p-4 pl-8 text-slate-500 dark:text-slate-400">{{ $server->username }}</td>
                                    <td class="border-b border-slate-100 dark:border-slate-700 p-4 pl-8 text-slate-500 dark:text-slate-400">{{ $server->address }}</td>
                                    <td class="border-b border-slate-100 dark:border-slate-700 p-4 pl-8 text-slate-500 dark:text-slate-400">{{ $server->port }}</td>
                                    <td class="border-b border-slate-100 dark:border-slate-700 p-4 pl-8 text-slate-500 dark:text-slate-400">{{ $server->udp_port }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        <div class="table-nav mt-4 text-slate-400 dark:text-slate-200">
                            {{ $servers->links() }}
                        </div>
                    @else
                        <div class="p-8 text-center text-slate-400 dark:text-slate-200">
                            <h3 class="py-4 text-lg font-bold">No Servers</h3>
                            <p>You should create one using <b class="text-indigo-400 dark:text-indigo-600">{{ __('Create Server') }}</b> button</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
