<section class="p-8 mt-8 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Inbound') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Set the default settings of inbounds.") }}
        </p>
    </header>

    <form method="post" action="{{ route('settings.updateInbound') }}" class="space-y-6">
        @csrf
        @method('patch')
        <div class="">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                <div>
                    <x-input-label for="inbound_traffic_limit"
                                   :value="__('*Traffic Limit (GB, Blank = ∞)')"/>
                    <x-text-input id="inbound_traffic_limit" name="inbound_traffic_limit" placeholder="traffic limit e.g. 100"
                                  type="text"
                                  class="mt-1 block w-full"
                                  :value="old('inbound_traffic_limit', $settings->inbound_traffic_limit)"
                                  autofocus/>
                    <x-input-error class="mt-2" :messages="$errors->get('inbound_traffic_limit')"/>
                </div>

                <div>
                    <x-input-label for="inbound_max_login" :value="__('*Max Login')"/>
                    <x-text-input id="inbound_max_login" name="inbound_max_login" placeholder="max login e.g. 1" type="text"
                                  class="mt-1 block w-full"
                                  :value="old('inbound_max_login', $settings->inbound_max_login)" required
                                  autofocus/>
                    <x-input-error class="mt-2" :messages="$errors->get('inbound_max_login')"/>
                </div>

                <div>
                    <x-input-label for="inbound_active_days" :value="__('*Active Days (Blank = ∞)')"/>
                    <x-text-input id="inbound_active_days" name="inbound_active_days" placeholder="active days e.g. 30"
                                  type="text"
                                  class="mt-1 block w-full"
                                  :value="old('inbound_active_days', $settings->inbound_active_days)"
                                  autofocus/>
                    <x-input-error class="mt-2" :messages="$errors->get('inbound_active_days')"/>
                </div>
            </div>

            <div class="flex items-center gap-4 mt-8">
                <div class="ms-auto">
                    <x-primary-button>{{ __('Update') }}</x-primary-button>
                </div>
            </div>
        </div>
    </form>
</section>
