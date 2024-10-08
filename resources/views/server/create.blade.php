<x-app-layout>
    <x-slot name="header">
        <div class="inline-block">
            <a href="javascript:history.back()" class="flex flex-row">
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
{{--            <div class="flex ms-4 sm:ms-0 me-4 sm:me-0 mb-4">--}}
{{--                @if (session('status') === 'server-created')--}}
{{--                    <span--}}
{{--                        x-data="{ show: true }"--}}
{{--                        x-show="show"--}}
{{--                        x-transition--}}
{{--                        x-init="setTimeout(() => show = false, 10000)"--}}
{{--                        class="text-sm text-green-600 dark:text-green-400 me-4"--}}
{{--                    >{{ __('Server Successfully Created.') }}</span>--}}
{{--                @endif--}}
{{--                @if (session('status') === 'server-not-created')--}}
{{--                    <span--}}
{{--                        x-data="{ show: true }"--}}
{{--                        x-show="show"--}}
{{--                        x-transition--}}
{{--                        class="text-sm text-red-600 dark:text-red-400 me-4"--}}
{{--                    >{{ __('Failed to create new server') . (session('message') ? ' : ' .session('message') : '.') }}</span>--}}
{{--                @endif--}}
{{--            </div>--}}

            <div class="p-8 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <header>
                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        {{ __('Create Server') }}
                    </h2>

                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{ __("Create a new server so you can add an inbounds into it.") }}
                    </p>

                    <p class="mt-4 text-justify text-gray-900 dark:text-gray-100">
                        {{ "If the server you are creating is not the same server of this app, these information will be
used to copy the public key of this app to your remote server. Therefore the password of your server will not be stored on the database." }}
                    </p>
                </header>

                {{-- <form id="send-verification" method="post" action="{{ route('verification.send') }}">
                    @csrf
                </form> --}}

                <form method="post" action="{{ route('servers.store') }}" class="mt-6 space-y-6">
                    @csrf
                    @method('post')

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        <div>
                            <x-input-label for="name" :value="__('*Name')"/>
                            <x-text-input id="name" name="name" placeholder="name" type="text"
                                          class="mt-1 block w-full"
                                          :value="old('name')" required autofocus/>
                            <x-input-error class="mt-2" :messages="$errors->get('name')"/>
                        </div>

                        <div>
                            <x-input-label for="username" :value="__('*Username (Only root)')"/>
                            <x-text-input id="username" name="username" placeholder="username" type="text"
                                          class="mt-1 block w-full text-gray-400 dark:text-gray-700"
                                          :value="old('username', 'root')" required readonly/>
                            <x-input-error class="mt-2" :messages="$errors->get('username')"/>
                        </div>

                        <div>
                            <x-input-label for="password" :value="__('*Password')"/>
                            <x-text-input id="password" name="password" placeholder="password" type="password"
                                          class="mt-1 block w-full"
                                          :value="old('password')" required autofocus/>
                            <x-input-error class="mt-2" :messages="$errors->get('password')"/>
                        </div>

                        <div>
                            <x-input-label for="address" :value="__('*Address (IP V4)')"/>
                            <x-text-input id="address" name="address" placeholder="ipv4 e.g. 1.1.1.1" type="text" class="mt-1 block w-full"
                                          :value="old('address')" required autofocus/>
                            <x-input-error class="mt-2" :messages="$errors->get('address')"/>
                        </div>

                        <div>
                            <x-input-label for="port" :value="__('*Port')"/>
                            <x-text-input id="port" name="port" placeholder="port e.g. 22" type="number"
                                          class="mt-1 block w-full"
                                          :value="old('port', 22)" required autofocus/>
                            <x-input-error class="mt-2" :messages="$errors->get('port')"/>
                        </div>

                        <div>
                            <x-input-label for="udp_port" :value="__('*UDP Port')"/>
                            <x-text-input id="udp_port" name="udp_port" placeholder="udp port e.g. 7300" type="number"
                                          class="mt-1 block w-full"
                                          :value="old('udp_port', 7300)" required autofocus/>
                            <x-input-error class="mt-2" :messages="$errors->get('udp_port')"/>
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
</x-app-layout>
