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
                @if (session('status') === 'settings-not-updated')
                    <span
                        x-data="{ show: true }"
                        x-show="show"
                        x-transition
                        class="text-sm text-red-600 dark:text-red-400 me-4"
                    >{{ __('Failed to update the settings') . (session('message') ? ' : ' .session('message') : '.') }}</span>
                @endif
            </div>

            @include("settings.partials.inbound")

            @include("settings.partials.telegram")
        </div>
    </div>
</x-app-layout>
