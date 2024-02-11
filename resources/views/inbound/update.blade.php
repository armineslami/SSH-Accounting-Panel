<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('inbounds.index') }}" class="flex flex-row">
            <span class="me-2 sm:hidden">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                </svg>
            </span>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Inbounds') }}
            </h2>
        </a>
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
                            <x-text-input id="username" name="username" type="text" class="mt-1 block w-full text-gray-400 dark:text-gray-700"
                                          :value="old('username', $inbound->username)" required readonly/>
                            <x-input-error class="mt-2" :messages="$errors->get('username')"/>
                        </div>

                        <div class="relative">
                            <x-input-label for="user_password" :value="__('*Password')"/>
                            <x-text-input id="user_password" name="user_password" type="text" class="mt-1 block w-full"
                                          :value="old('user_password', $inbound->password)" autofocus/>
                            <span
                                class="absolute right-3 top-8 cursor-pointer"
                                x-data
                                x-on:click="generate()">
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
                            <x-text-input id="max_login" name="max_login" type="text" class="mt-1 block w-full"
                                          :value="old('max_login', $inbound->max_login)" required autofocus/>
                            <x-input-error class="mt-2" :messages="$errors->get('max_login')"/>
                        </div>

                        <div>
                            <x-input-label for="traffic_limit" :value="__('*Traffic Limit (GB, Blank = ∞)')"/>
                            <x-text-input id="traffic_limit" name="traffic_limit" type="text" class="mt-1 block w-full"
                                          :value="old('traffic_limit', $inbound->traffic_limit)" autofocus/>
                            <x-input-error class="mt-2" :messages="$errors->get('traffic_limit')"/>
                        </div>

                        <div>
                            <x-input-label for="remaining_traffic" :value="__('*Remaining Traffic (GB, Blank = ∞)')"/>
                            <x-text-input id="remaining_traffic" name="remaining_traffic" type="text"
                                          class="mt-1 block w-full"
                                          :value="old('remaining_traffic', $inbound->remaining_traffic)" autofocus/>
                            <x-input-error class="mt-2" :messages="$errors->get('remaining_traffic')"/>
                        </div>

                        <div>
                            <x-input-label for="active_days" :value="__('*Remaining Days (Blank = ∞)')"/>
                            <x-text-input id="active_days" name="active_days" type="text" class="mt-1 block w-full"
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

        function generate() {
            const length = 8;
            let result = '';
            const characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
            const charactersLength = characters.length;
            let counter = 0;
            while (counter < length) {
                result += characters.charAt(Math.floor(Math.random() * charactersLength));
                counter += 1;
            }
            const passwordInput = document.getElementById("user_password");
            passwordInput.value = result;
        }
    </script>
</x-app-layout>
