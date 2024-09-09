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
                    <div class="flex justify-between">
                        <div>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                {{ __('Update Server') }}
                            </h2>

                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {{ __("Change server settings.") }}
                            </p>
                        </div>

                        <a class="ms-auto" href="{{route('servers.create')}}">
                            <x-secondary-button class="hidden sm:block">
                                {{ __('Create Server') }}
                            </x-secondary-button>
                            <x-secondary-button class="block sm:hidden">
                                {{ __('New') }}
                            </x-secondary-button>
                        </a>
                    </div>
                </header>

                <form method="post" action="{{ route('servers.update', $server->id) }}" class="mt-6 space-y-6">
                    @csrf
                    @method('patch')

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        <div>
                            <x-input-label for="name" :value="__('*Name')" />
                            <x-text-input id="name" name="name" placeholder="name" type="text"
                                          class="mt-1 block w-full" :value="old('name', $server->name)" required autofocus/>
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>

                        <div>
                            <x-input-label for="username" :value="__('*Username')" />
                            <x-text-input id="username" name="username" placeholder="username" type="text"
                                          class="mt-1 block w-full text-gray-400 dark:text-gray-700" :value="old('username', $server->username)" required readonly/>
                            <x-input-error class="mt-2" :messages="$errors->get('username')" />
                        </div>

                        <div>
                            <x-input-label for="address" :value="__('*Address (IP V4)')" />
                            <x-text-input id="address" name="address" placeholder="ipv4 e.g. 1.1.1.1" type="text"
                                          class="mt-1 block w-full  text-gray-300 dark:text-gray-700" :value="old('address', $server->address)" required readonly/>
                            <x-input-error class="mt-2" :messages="$errors->get('address')" />
                        </div>

                        <div>
                            <x-input-label for="port" :value="__('*Port')" />
                            <x-text-input id="port" name="port" placeholder="port e.g. 22" type="number"
                                          class="mt-1 block w-full" :value="old('port', $server->port)" required autofocus/>
                            <x-input-error class="mt-2" :messages="$errors->get('port')" />
                        </div>

                        <div>
                            <x-input-label for="udp_port" :value="__('*UDP Port')"/>
                            <x-text-input id="udp_port" name="udp_port" placeholder="udp port e.g. 7300" type="number"
                                          class="mt-1 block w-full  text-gray-300 dark:text-gray-700"
                                          :value="old('udp_port', 7300)" readonly autofocus/>
                            <x-input-error class="mt-2" :messages="$errors->get('udp_port')"/>
                        </div>
                    </div>

                    @if(isset($server->outline_api_url))
                        <div>
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                                {{ __('Outline API Address') }}
                            </label>
                            <div class="relative grid grid-cols-12 bg-gray-100 dark:bg-gray-900 border-l-4 border-indigo-500 dark:border-indigo-600 rounded m-1 p-3">
                                <p class="col-span-10 md:col-span-11 truncate text-gray-900 dark:text-gray-300">
                                    {{ $server->outline_api_url }}
                                </p>
                                <span
                                    class="flex items-center justify-center cursor-pointer col-span-2 md:col-span-1"
                                    x-data
                                    x-on:click="copy('{{ $server->outline_api_url }}')">
                                <span class="text-2xs uppercase text-gray-900 dark:text-gray-100 select-none">
                                    {{ __('Copy') }}
                                </span>
                            </span>
                            </div>
                        </div>
                    @endif

                    <div class="flex items-center gap-4">
                        <div class="ms-auto">
                            <x-danger-button x-data=""
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

    <script>
        function copy(text) {
            if (window.clipboardData && window.clipboardData.setData) {
                // Internet Explorer-specific code path to prevent textarea being shown while dialog is visible.
                return window.clipboardData.setData("Text", text);

            }
            else if (document.queryCommandSupported && document.queryCommandSupported("copy")) {
                var textarea = document.createElement("textarea");
                textarea.textContent = text;
                textarea.style.position = "fixed";  // Prevent scrolling to bottom of page in Microsoft Edge.
                document.body.appendChild(textarea);
                textarea.select();
                try {
                    return document.execCommand("copy");  // Security exception may be thrown by some browsers.
                }
                catch (ex) {
                    console.warn("Copy to clipboard failed.", ex);
                    // return prompt("Copy to clipboard: Ctrl+C, Enter", text);
                }
                finally {
                    document.body.removeChild(textarea);
                }
            }
        }
    </script>
</x-app-layout>
