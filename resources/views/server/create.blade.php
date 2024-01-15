<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Servers') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="px-0 sm:px-8">
            <div class="flex ms-4 sm:ms-0 me-4 sm:me-0 mb-4">
                @if (session('status') === 'server-created')
                    <span
                        x-data="{ show: true }"
                        x-show="show"
                        x-transition
                        x-init="setTimeout(() => show = false, 10000)"
                        class="text-sm text-green-600 dark:text-green-400 me-4"
                    >{{ __('Server Successfully Created.') }}</span>
                @endif
                @if (session('status') === 'server-not-created')
                    <span
                        x-data="{ show: true }"
                        x-show="show"
                        x-transition
                        class="text-sm text-red-600 dark:text-red-400 me-4"
                    >{{ __('Failed to create new server') . (session('message') ? ' : ' .session('message') : '.') }}</span>
                @endif
            </div>

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
used to establish a SSH connection to the remote server. When you click the 'create' button,
the public key of this app will be copied to your remote server in order to perform future operations like creating inbound,
without requiring you to enter the password again. This way the password of your server will not be stored on the database.
Then a folder named " }}<b>{{ "'ssh-accounting-panel'" }}</b> {{ "will be copied to your server root directory which includes
multiple files required by this panel." }}
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
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                                          :value="old('name')" required autofocus/>
                            <x-input-error class="mt-2" :messages="$errors->get('name')"/>
                        </div>

                        <div>
                            <x-input-label for="username" :value="__('*Username (Only root)')"/>
                            <x-text-input id="username" name="username" type="text" class="mt-1 block w-full"
                                          :value="old('username', 'root')" required readonly/>
                            <x-input-error class="mt-2" :messages="$errors->get('username')"/>
                        </div>

                        <div>
                            <x-input-label for="password" :value="__('*Password')"/>
                            <x-text-input id="password" name="password" type="password" class="mt-1 block w-full"
                                          :value="old('password')" required autofocus/>
                            <x-input-error class="mt-2" :messages="$errors->get('password')"/>
                        </div>

                        <div>
                            <x-input-label for="address" :value="__('*Address (IP V4)')"/>
                            <x-text-input id="address" name="address" type="text" class="mt-1 block w-full"
                                          :value="old('address')" required autofocus/>
                            <x-input-error class="mt-2" :messages="$errors->get('address')"/>
                        </div>

                        <div>
                            <x-input-label for="port" :value="__('*Port')"/>
                            <x-text-input id="port" name="port" type="number" class="mt-1 block w-full"
                                          :value="old('port', 22)" required autofocus/>
                            <x-input-error class="mt-2" :messages="$errors->get('port')"/>
                        </div>

                        <div>
                            <x-input-label for="udp_port" :value="__('*UDP Port')"/>
                            <x-text-input id="udp_port" name="udp_port" type="number" class="mt-1 block w-full"
                                          :value="old('udp_port', 7300)" required autofocus/>
                            <x-input-error class="mt-2" :messages="$errors->get('udp_port')"/>
                        </div>
                    </div>

                    <p class="text-justify text-sm text-gray-900 dark:text-gray-100">
                        {{ "⚠️ Adding a server may take some time to finish and if you want to make this process shorter,
you can update packages list and then install the following packages on your server manually:" }}
                    </p>
                    <span class="text-sm font-bold text-gray-900 dark:text-gray-100">
                        {{ "nethogs golang bc coreutils cmake git" }}
                    </span>

                    <div class="flex items-center gap-4">
                        <div class="ms-auto">
                            <x-primary-button>{{ __('Create') }}</x-primary-button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
