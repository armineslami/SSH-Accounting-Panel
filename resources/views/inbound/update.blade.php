<x-app-layout>
    <x-slot name="header">
        <div class="inline-block">
            <a href="{{ route('inbounds.index') }}" class="flex flex-row">
            <span class="me-2 sm:hidden">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-gray-900 dark:text-gray-100">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                </svg>
            </span>
                <h2 class="font-semibold text-xl text-gray-900 dark:text-gray-100 leading-tight">
                    {{ __('Inbounds') }}
                </h2>
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="px-0 sm:px-8">
{{--            <div class="flex ms-4 sm:ms-0 me-4 sm:me-0 mb-4">--}}
{{--                @if (session('status') === 'inbound-updated')--}}
{{--                    <span--}}
{{--                        x-data="{ show: true }"--}}
{{--                        x-show="show"--}}
{{--                        x-transition--}}
{{--                        x-init="setTimeout(() => show = false, 10000)"--}}
{{--                        class="text-sm text-green-600 dark:text-green-400"--}}
{{--                    >{{ __('Inbound Successfully Updated.') }}</span>--}}
{{--                @endif--}}
{{--                @if (session('status') === 'inbound-not-updated')--}}
{{--                    <span--}}
{{--                        x-data="{ show: true }"--}}
{{--                        x-show="show"--}}
{{--                        x-transition--}}
{{--                        class="text-sm text-red-600 dark:text-red-400 me-4"--}}
{{--                    >{{ __('Failed to update the inbound') . (session('message') ? ' : ' .session('message') : '.') }}</span>--}}
{{--                @endif--}}
{{--                @if (session('status') === 'inbound-not-deleted')--}}
{{--                    <span--}}
{{--                        x-data="{ show: true }"--}}
{{--                        x-show="show"--}}
{{--                        x-transition--}}
{{--                        class="text-sm text-red-600 dark:text-red-400 me-4"--}}
{{--                    >{{ __('Failed to delete the inbound') . (session('message') ? ' : ' .session('message') : '.') }}</span>--}}
{{--                @endif--}}
{{--            </div>--}}

            <div class="p-8 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <header>
                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        {{ __('Update Inbound') }}
                    </h2>

                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{ __("Change inbound settings.") }}
                    </p>
                </header>

                <form method="post" action="{{ route('inbounds.update', $inbound->id) }}" class="mt-6 space-y-6">
                    @csrf
                    @method('patch')

                    <div class="grid gap-4 grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        <div>
                            <x-input-label for="username" :value="__('*Username')"/>
                            <x-text-input id="username" name="username" placeholder="username" type="text" class="mt-1 block w-full text-gray-400 dark:text-gray-700"
                                          :value="old('username', $inbound->username)" required readonly/>
                            <x-input-error class="mt-2" :messages="$errors->get('username')"/>
                        </div>

                        <div class="relative">
                            <x-input-label for="user_password" :value="__('*Password')"/>
                            <x-text-input id="user_password" name="user_password" placeholder="password" type="text" class="mt-1 block w-full"
                                          :value="old('user_password', $inbound->password)" autofocus/>
                            <span
                                class="absolute right-3 top-8 cursor-pointer"
                                x-data
                                x-on:click="generate(8, 'user_password')">
                                <span class="text-2xs uppercase text-gray-900 dark:text-gray-100 select-none">
                                    {{ __('Generate') }}
                                </span>
                            </span>
                            <x-input-error class="mt-2" :messages="$errors->get('user_password')"/>
                        </div>

                        <div>
                            <x-input-label for="is_active" :value="__('*Active')"/>
                            <x-select-input id="is_active" name="is_active" class="mt-1 block w-full">
                                <option
                                    value="1" {{old('is_active') === '1' || $inbound->is_active == '1' ? 'selected' : '' }}>
                                    Yes
                                </option>
                                <option
                                    value="0" {{old('is_active') === '0' || $inbound->is_active == '0' ? 'selected' : '' }}>
                                    No
                                </option>
                            </x-select-input>
                            <x-input-error class="mt-2" :messages="$errors->get('is_active')"/>
                        </div>

                        <div>
                            <x-input-label for="max_login" :value="__('*Max Login')"/>
                            <x-text-input id="max_login" name="max_login" placeholder="max login e.g. 1" type="text"
                                          class="mt-1 block w-full"
                                          :value="old('max_login', $inbound->max_login)" required autofocus/>
                            <x-input-error class="mt-2" :messages="$errors->get('max_login')"/>
                        </div>

                        <div>
                            <x-input-label for="traffic_limit" :value="__('*Traffic Limit (GB, Blank = ∞)')"/>
                            <x-text-input id="traffic_limit" name="traffic_limit" placeholder="traffic limit e.g. 10"
                                          type="text" class="mt-1 block w-full"
                                          :value="old('traffic_limit', $inbound->traffic_limit)" autofocus/>
                            <x-input-error class="mt-2" :messages="$errors->get('traffic_limit')"/>
                        </div>

                        <div>
                            <x-input-label for="remaining_traffic" :value="__('*Remaining Traffic (GB, Blank = ∞)')"/>
                            <x-text-input id="remaining_traffic" name="remaining_traffic"
                                          placeholder="remaining traffic e.g. 2"
                                          type="text"
                                          class="mt-1 block w-full"
                                          :value="old('remaining_traffic', $inbound->remaining_traffic)" autofocus/>
                            <x-input-error class="mt-2" :messages="$errors->get('remaining_traffic')"/>
                        </div>

                        <div>
                            <x-input-label for="active_days" :value="__('*Remaining Days (Blank = ∞)')"/>
                            <x-text-input id="active_days" name="active_days" placeholder="active days e.g. 30"
                                          type="text" class="mt-1 block w-full"
                                          :value="old('active_days', $inbound->active_days)" autofocus/>
                            <x-input-error class="mt-2" :messages="$errors->get('active_days')"/>
                        </div>

                        <div>
                            <x-input-label for="server_ip" :value="__('*Server IP')"/>
                            <x-select-input id="server_ip" name="server_ip" class="mt-1 block w-full">
                                @if(!$servers->contains('address', $inbound->server_ip) || is_null($inbound->server_ip))
                                    <option
                                        value="" {{ old('server_ip') === null || (!old('server_ip') && $inbound->server_ip === null) ? 'selected' : '' }} >
                                        -
                                    </option>
                                @endif
                                @foreach ($servers as $server)
                                    <option
                                        value="{{$server->address}}" {{ old('server_ip') === $server->address || (!old('server_ip') && $inbound->server_ip === $server->address) ? 'selected' : '' }} >
                                        {{$server->name}} : {{$server->address}}
                                    </option>
                                @endforeach
                            </x-select-input>
                            <x-input-error class="mt-2" :messages="$errors->get('server_ip')"/>
                            @foreach ($servers as $server)
                                @if($inbound->server_ip == $server->address)
                                    <span id="current_server_address" class="hidden">{{$server->address}}</span>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <div id="delete_from_old_server_container" class="hidden">
                        <x-input-label for="delete_from_old_server" :value="__('Before adding this inbound
                                into the new server, it can be deleted from the current server. Do you want to?')"/>
                        <x-select-input id="delete_from_old_server" name="delete_from_old_server" class="mt-1 me-auto"
                                        disabled>
                            <option
                                value="0" {{old('delete_from_old_server') === '0' ? 'selected' : '' }}>
                                No
                            </option>
                            <option
                                value="1" {{old('delete_from_old_server') === '1' ? 'selected' : '' }}>
                                Yes
                            </option>
                        </x-select-input>
                        <x-input-error class="mt-2" :messages="$errors->get('delete_from_old_server')"/>
                    </div>

                    <div x-data="{ show: {{ is_null(old('outline')) && is_null($inbound->outline) ? 'false' : 'true'  }} }">
                        <x-input-label :value="__('More Protocols')" />
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ __("You can add more vpn configs for the user alongside the default SSH config:") }}
                        </p>

                        <label for="outline" class="inline-flex items-center mt-4">
                            <input
                                x-model="show"
                                class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800"
                                id="outline" name="outline" type="checkbox">
                            <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Outline') }}</span>
                            <x-input-error class="mt-2" :messages="$errors->get('outline')" />
                        </label>

                        <div id="outline_warning" x-show="show" class="mt-2 text-sm text-gray-900 dark:text-gray-100">
                            <p class="text-justify">
                                ⚠️ Max Login won't work for Outline connections.
                            </p>
                        </div>
                    </div>

                    @if(!is_null($inbound->outline))
                        <div>
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                                {{ __('Outline Access Key') }}
                            </label>
                            <div class="relative grid grid-cols-12 bg-gray-100 dark:bg-gray-900 border-l-4 border-indigo-500 dark:border-indigo-600 rounded m-1 p-3">
                                <p class="col-span-11 truncate text-gray-900 dark:text-gray-300">
                                    {{ $inbound->outline->key }}
                                </p>
                                <span
                                    class="flex items-center justify-center cursor-pointer col-span-1"
                                    x-data
                                    x-on:click="copy('{{ $inbound->outline->key }}')">
                                <span class="text-2xs uppercase text-gray-900 dark:text-gray-100 select-none">
                                    {{ __('Copy') }}
                                </span>
                            </span>
                            </div>
                        </div>
                    @endif

                    <div class="flex items-center gap-4">
                        <div class="ms-auto">
                            <x-danger-button class="me-4" x-data=""
                                             x-on:click.prevent="$dispatch('open-modal', 'confirm-inbound-deletion')"
                            >{{ __('Delete Inbound') }}</x-danger-button>
                            <x-primary-button>{{ __('Update') }}</x-primary-button>
                        </div>
                    </div>
                </form>

                <x-modal name="confirm-inbound-deletion" focusable>
                    <form method="post" action="{{ route('inbounds.destroy', $inbound->id) }}" class="p-6">
                        @csrf
                        @method('delete')

                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            {{ __('Inbound Deletion') }}
                        </h2>

                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ __('Are you sure you want to delete this inbound?') }}
                            Use force delete if you want to delete the inbound without connecting to the server
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
                                {{ __('Delete Inbound') }}
                            </x-danger-button>
                        </div>
                    </form>
                </x-modal>

                <x-terminal name="terminal" :token="session('terminal_session_token') ?? null" :show="!is_null(session('terminal_session_token'))" focusable/>
            </div>
        </div>
    </div>

    <script>
        addEventListener("load", (event) => {
            const serverAddressElement = document.getElementById("server_ip");
            const selectInput = document.getElementById("delete_from_old_server_container");
            const selectedServerAddress = serverAddressElement.value
            const currentServerAddressElement = document.getElementById("current_server_address");
            const currentServerAddress = currentServerAddressElement ? currentServerAddressElement.textContent : null;

            if (currentServerAddress && selectedServerAddress !== currentServerAddress) {
                selectInput.classList.remove('hidden');
                selectInput.classList.add('visible');
            }

            serverAddressElement.addEventListener("change", function () {
                const selectedAddress = this.value;

                if (currentServerAddress && selectedAddress !== currentServerAddress) {
                    selectInput.classList.remove('hidden');
                    selectInput.classList.add('visible');
                } else {
                    selectInput.classList.remove('visible');
                    selectInput.classList.add('hidden');
                }
            });
        });

        function generate(length, input) {
            let result = '';
            const characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
            for (let i = 0; i < length; i++) {
                const randomIndex = Math.floor(Math.random() * characters.length);
                result += characters[randomIndex];
            }
            /**
             * Since linux does not accept a username starts with number,
             * check if the string starts with a number, regenerate it
             */
            if (/^\d/.test(result)) {
                return generate(length, input);
            }
            const targetInput = document.getElementById(input);
            targetInput.value = result;
        }

        function toggleVisibility(elementId) {
            const element = document.getElementById(elementId);
            console.log(element);
            element.classList.toggle('hidden');
        }

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
