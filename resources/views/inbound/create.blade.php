<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Inbounds') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="px-0 sm:px-8">
{{--            <div class="flex ms-4 sm:ms-0 me-4 sm:me-0 mb-4">--}}
{{--                @if (session('status') === 'inbound-created')--}}
{{--                    <span--}}
{{--                        x-data="{ show: true }"--}}
{{--                        x-show="show"--}}
{{--                        x-transition--}}
{{--                        x-init="setTimeout(() => show = false, 10000)"--}}
{{--                        class="text-sm text-green-600 dark:text-green-400 me-4"--}}
{{--                    >{{ __('Inbound Successfully Created.') }}</span>--}}
{{--                @endif--}}
{{--                @if (session('status') === 'inbound-not-created')--}}
{{--                    <span--}}
{{--                        x-data="{ show: true }"--}}
{{--                        x-show="show"--}}
{{--                        x-transition--}}
{{--                        x-init="setTimeout(() => show = false, 5000)"--}}
{{--                        class="text-sm text-red-600 dark:text-red-400 me-4"--}}
{{--                    >{{ __('Failed to create new inbound') . (session('message') ? ' : ' .session('message') : '.') }}</span>--}}
{{--                @endif--}}
{{--            </div>--}}

            <div class="p-8 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <header>
                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        {{ __('Create Inbound') }}
                    </h2>

                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{ __("Create a new inbound and add it to your server. The serve can be the same server that this panel is running on or another server.") }}
                    </p>
                </header>

                <form method="post" action="{{ route('inbounds.store') }}" class="mt-6 space-y-6">
                    @csrf
                    @method('post')

                    <div class="grid gap-4 grid-cols-1 md:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <x-input-label for="username" :value="__('*Username')" />
                            <x-text-input id="username" name="username" type="text" class="mt-1 block w-full" :value="old('username')" required autofocus/>
                            <x-input-error class="mt-2" :messages="$errors->get('username')" />
                        </div>

                        <div class="relative my-auto mt-2 md:mt-0">
                            <x-input-label for="user_password" :value="__('*Password')" />
                            <x-text-input id="user_password" name="user_password" type="text" class="mt-1 block w-full pe-20" :value="old('user_password')" required autofocus />
                            <span
                                class="absolute right-3 top-8 cursor-pointer"
                                x-data
                                x-on:click="generate()">
                                <span class="text-2xs uppercase text-gray-900 dark:text-gray-100 select-none">
                                    {{ __('Generate') }}
                                </span>
                            </span>
                            <x-input-error class="mt-2" :messages="$errors->get('user_password')" />
                        </div>

                        <div>
                            <x-input-label for="is_active" :value="__('*Active')" />
                            <x-select-input id="is_active" name="is_active" class="mt-1 block w-full">
                                <option value="1" {{ old('is_active') === '1' ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ old('is_active') === '0' ? 'selected' : '' }}>No</option>
                            </x-select-input>
                            <x-input-error class="mt-2" :messages="$errors->get('is_active')" />
                        </div>

                        <div>
                            <x-input-label for="traffic_limit" :value="__('*Traffic Limit (GB, Blank = ∞)')" />
                            <x-text-input id="traffic_limit" name="traffic_limit" type="text" class="mt-1 block w-full" :value="old('traffic_limit', $settings->inbound_traffic_limit)" autofocus/>
                            <x-input-error class="mt-2" :messages="$errors->get('traffic_limit')" />
                        </div>

                        <div>
                            <x-input-label for="max_login" :value="__('*Max Login')" />
                            <x-text-input id="max_login" name="max_login" type="text" class="mt-1 block w-full" :value="old('max_login', $settings->inbound_max_login)" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('max_login')" />
                        </div>

                        <div>
                            <x-input-label for="active_days" :value="__('*Active Days (Blank = ∞)')" />
                            <x-text-input id="active_days" name="active_days" type="text" class="mt-1 block w-full" :value="old('active_days', $settings->inbound_active_days)" autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('active_days')" />
                        </div>

                        <div>
                            <x-input-label for="server_ip" :value="__('*Server IP')" />
                            <x-select-input id="server_ip" name="server_ip" class="mt-1 block w-full">
                                @foreach ($servers as $server)
                                    <option value="{{$server->address}}" {{ old('server_ip') === $server->address ? 'selected' : '' }}>
                                        {{$server->name}} : {{$server->address}}
                                    </option>
                                @endforeach
                            </x-select-input>
                            <x-input-error class="mt-2" :messages="$errors->get('server_ip')" />
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="ms-auto">
                            <x-primary-button>{{ __('Create') }}</x-primary-button>
                        </div>
                    </div>
                </form>

                <x-terminal name="terminal" :token="session('terminal_session_token') ?? null" :show="!is_null(session('terminal_session_token'))" focusable/>
            </div>
        </div>
    </div>
    <script>
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
