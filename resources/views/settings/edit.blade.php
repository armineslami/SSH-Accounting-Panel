<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Settings') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="px-0 sm:px-8">
            <div class="flex ms-4 sm:ms-0 me-4 sm:me-0 mb-4">
                @if (session('status') === 'settings-updated')
                    <span
                        x-data="{ show: true }"
                        x-show="show"
                        x-transition
                        x-init="setTimeout(() => show = false, 10000)"
                        class="text-sm text-green-600 dark:text-green-400 me-4"
                    >{{ __('Settings Successfully Updated.') }}</span>
                @endif
            </div>

            <div class="p-8 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <header>
                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        {{ __('Update Settings') }}
                    </h2>

                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{ __("Change the default settings of the panel.") }}
                    </p>
                </header>

                <p class="text-base font-medium mt-12 text-gray-900 dark:text-gray-100">{{ __("Inbound Defaults") }}</p>

                <form method="post" action="{{ route('settings.update') }}" class="mt-6 space-y-6">
                    @csrf
                    @method('patch')

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        <div>
                            <x-input-label for="inbound_traffic_limit" :value="__('*Inbound Traffic Limit (GB, Blank = ∞)')" />
                            <x-text-input id="inbound_traffic_limit" name="inbound_traffic_limit" type="text" class="mt-1 block w-full" :value="old('inbound_traffic_limit', $settings->inbound_traffic_limit)" autofocus/>
                            <x-input-error class="mt-2" :messages="$errors->get('inbound_traffic_limit')" />
                        </div>

                        <div>
                            <x-input-label for="inbound_max_login" :value="__('*Inbound Max Login')" />
                            <x-text-input id="inbound_max_login" name="inbound_max_login" type="text" class="mt-1 block w-full" :value="old('inbound_max_login', $settings->inbound_max_login)" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('inbound_max_login')" />
                        </div>

                        <div>
                            <x-input-label for="inbound_active_days" :value="__('*Inbound Active Days (Blank = ∞)')" />
                            <x-text-input id="inbound_active_days" name="inbound_active_days" type="text" class="mt-1 block w-full" :value="old('inbound_active_days', $settings->inbound_active_days)" autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('inbound_active_days')" />
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="ms-auto">
                            <x-primary-button>{{ __('Update') }}</x-primary-button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
