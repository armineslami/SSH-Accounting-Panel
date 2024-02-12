<x-app-layout>
    <x-slot name="header">
        <div class="inline-block">
            <a href="{{ route('servers.index') }}" class="flex flex-row">
            <span class="me-2 sm:hidden">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-gray-900 dark:text-gray-100">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                </svg>
            </span>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                    {{ __('Servers') }}
                </h2>
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="px-0 sm:px-8">
            <div class="flex ms-4 sm:ms-0 me-4 sm:me-0 mb-4">
                @if (session('status') === 'server-updated')
                    <span
                        x-data="{ show: true }"
                        x-show="show"
                        x-transition
                        x-init="setTimeout(() => show = false, 10000)"
                        class="text-sm text-green-600 dark:text-green-400"
                    >{{ __('Server Successfully Updated.') }}</span>
                @endif
            </div>

            <div class="p-8 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <header>
                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        {{ __('Update Server') }}
                    </h2>

                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{ __("Change server settings.") }}
                    </p>
                </header>

                <form method="post" action="{{ route('servers.update', $server->id) }}" class="mt-6 space-y-6">
                    @csrf
                    @method('patch')

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        <div>
                            <x-input-label for="name" :value="__('*Name')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $server->name)" required autofocus/>
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>

                        <div>
                            <x-input-label for="username" :value="__('*Username')" />
                            <x-text-input id="username" name="username" type="text" class="mt-1 block w-full text-gray-400 dark:text-gray-700" :value="old('username', $server->username)" required readonly/>
                            <x-input-error class="mt-2" :messages="$errors->get('username')" />
                        </div>

                        <div>
                            <x-input-label for="address" :value="__('*Address (IP V4)')" />
                            <x-text-input id="address" name="address" type="text" class="mt-1 block w-full  text-gray-300 dark:text-gray-700" :value="old('address', $server->address)" required readonly/>
                            <x-input-error class="mt-2" :messages="$errors->get('address')" />
                        </div>

                        <div>
                            <x-input-label for="port" :value="__('*Port')" />
                            <x-text-input id="port" name="port" type="number" class="mt-1 block w-full" :value="old('port', $server->port)" required autofocus/>
                            <x-input-error class="mt-2" :messages="$errors->get('port')" />
                        </div>

                        <div>
                            <x-input-label for="udp_port" :value="__('*UDP Port')"/>
                            <x-text-input id="udp_port" name="udp_port" type="number" class="mt-1 block w-full  text-gray-300 dark:text-gray-700"
                                          :value="old('udp_port', 7300)" readonly autofocus/>
                            <x-input-error class="mt-2" :messages="$errors->get('udp_port')"/>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="ms-auto">
                            <x-danger-button class="me-4" x-data=""
                                x-on:click.prevent="$dispatch('open-modal', 'confirm-server-deletion')"
                            >{{ __('Delete Server') }}</x-danger-button>
                            <x-primary-button>{{ __('Update') }}</x-primary-button>
                        </div>
                    </div>
                </form>

                <x-modal name="confirm-server-deletion" focusable>
                    <form method="post" action="{{ route('servers.destroy', $server->id) }}" class="p-6">
                        @csrf
                        @method('delete')

                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            {{ __('Server Deletion') }}
                        </h2>

                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ __('Are you sure you want to delete this server?') }}
                            Use force delete if the server is not reachable.
                        </p>

                        <div class="mt-6 flex justify-end">
                            <label for="force_delete" class="inline-flex items-center me-4">
                                <input id="force_delete" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" name="force_delete">
                                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Force Delete') }}</span>
                            </label>

                            <x-secondary-button x-on:click="$dispatch('close')">
                                {{ __('Cancel') }}
                            </x-secondary-button>

                            <x-danger-button class="ms-3">
                                {{ __('Delete Server') }}
                            </x-danger-button>
                        </div>
                    </form>
                </x-modal>

                <x-terminal name="terminal" :token="session('terminal_session_token') ?? null" :show="!is_null(session('terminal_session_token'))" focusable/>
            </div>
        </div>
    </div>
</x-app-layout>
