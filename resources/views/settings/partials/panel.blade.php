<section class="p-8 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('App') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Set the default settings of the App.") }}
        </p>
    </header>

    <form method="post" action="{{ route('settings.updateApp') }}" class="space-y-6">
        @csrf
        @method('patch')
        <div class="">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                <div>
                    <x-input-label for="app_inbound_bandwidth_check_interval" :value="__('*Inbound Bandwidth Check Interval')" />
                    <x-select-input id="app_inbound_bandwidth_check_interval"
                                    name="app_inbound_bandwidth_check_interval"
                                    class="mt-1 block w-full" >
                        <option
                            value="30"
                            {{ old('app_inbound_bandwidth_check_interval') === 30 || (!old('app_inbound_bandwidth_check_interval') && $settings->app_inbound_bandwidth_check_interval === 30) ? 'selected' : '' }}>
                            Every 30 Minutes
                        </option>
                        <option
                            value="60"
                            {{ old('app_inbound_bandwidth_check_interval') === 60 || (!old('app_inbound_bandwidth_check_interval') && $settings->app_inbound_bandwidth_check_interval === 60) ? 'selected' : '' }}>
                            Every 1 Hour
                        </option>
                        <option
                            value="360"
                            {{ old('app_inbound_bandwidth_check_interval') === 360 || (!old('app_inbound_bandwidth_check_interval') && $settings->app_inbound_bandwidth_check_interval === 360) ? 'selected' : '' }}>
                            Every 6 Hours
                        </option>
                        <option
                            value="1440"
                            {{ old('app_inbound_bandwidth_check_interval') === 1440 || (!old('app_inbound_bandwidth_check_interval') && $settings->app_inbound_bandwidth_check_interval === 1440) ? 'selected' : '' }}>
                            Every Day
                        </option>
                    </x-select-input>
                    <x-input-error class="mt-2" :messages="$errors->get('app_inbound_bandwidth_check_interval')" />
                </div>

                <div>
                    <x-input-label for="app_update_check_interval" :value="__('*App Update Check Interval')" />
                    <x-select-input id="app_update_check_interval"
                                    name="app_update_check_interval"
                                    class="mt-1 block w-full">
                        <option
                            value="day"
                            {{ old('app_update_check_interval') === 'day' || (!old('app_update_check_interval') && $settings->app_update_check_interval === 'day') ? 'selected' : '' }}>
                            Every Day
                        </option>
                        <option
                            value="week"
                            {{ old('app_update_check_interval') === 'week' || (!old('app_update_check_interval') && $settings->app_update_check_interval === 'week') ? 'selected' : '' }}>
                            Every Week
                        </option>
                        <option
                            value="month"
                            {{ old('app_update_check_interval') === 'month' || (!old('app_update_check_interval') && $settings->app_update_check_interval === 'month') ? 'selected' : '' }}>
                            Every Month
                        </option>
                        <option
                            value="never"
                            {{ old('app_update_check_interval') === 'never' || (!old('app_update_check_interval') && $settings->app_update_check_interval === 'never') ? 'selected' : '' }}>
                            Never
                        </option>
                    </x-select-input>
                    <x-input-error class="mt-2" :messages="$errors->get('app_update_check_interval')" />
                </div>

                <div>
                    <x-input-label for="app_paginate_number" :value="__('*Pagination Count')"/>
                    <x-text-input id="app_paginate_number" name="app_paginate_number" placeholder="paginate number e.g. 20" type="number"
                                  class="mt-1 block w-full"
                                  :value="old('app_paginate_number', $settings->app_paginate_number)" required />
                    <x-input-error class="mt-2" :messages="$errors->get('app_paginate_number')"/>
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
