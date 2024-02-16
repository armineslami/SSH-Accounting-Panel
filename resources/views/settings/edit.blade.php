<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-900 dark:text-gray-100 leading-tight">
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
                    >{{ __('Settings successfully updated.') }}</span>
                @elseif (session('status') === 'settings-not-updated')
                    <span
                        x-data="{ show: true }"
                        x-show="show"
                        x-transition
                        class="text-sm text-red-600 dark:text-red-400 me-4"
                    >{{ __('Failed to update the settings') . (session('message') ? ' : ' .session('message') : '.') }}</span>
                @elseif (session('status') === 'telegram-updated')
                    <span
                        x-data="{ show: true }"
                        x-show="show"
                        x-transition
                        x-init="setTimeout(() => show = false, 10000)"
                        class="text-sm text-green-600 dark:text-green-400 me-4"
                    >{{ __('Telegram settings successfully updated.') }}</span>
                @elseif (session('status') === 'telegram-not-updated')
                    <span
                        x-data="{ show: true }"
                        x-show="show"
                        x-transition
                        class="text-sm text-red-600 dark:text-red-400 me-4"
                    >{{ __('Failed to update the telegram settings') . (session('message') ? ' : ' .session('message') : '.') }}</span>
                @elseif (session('status') === 'dropbox-linked')
                    <span
                        x-data="{ show: true }"
                        x-show="show"
                        x-transition
                        x-init="setTimeout(() => show = false, 10000)"
                        class="text-sm text-green-600 dark:text-green-400 me-4"
                    >{{ __('Dropbox successfully linked.') }}</span>
                @elseif (session('status') === 'dropbox-not-linked')
                    <span
                        x-data="{ show: true }"
                        x-show="show"
                        x-transition
                        class="text-sm text-red-600 dark:text-red-400 me-4"
                    >{{ __('Failed to link to the dropbox') . (session('message') ? ' : ' .session('message') : '.') }}</span>
                @elseif (session('status') === 'dropbox-unlinked')
                    <span
                        x-data="{ show: true }"
                        x-show="show"
                        x-transition
                        x-init="setTimeout(() => show = false, 10000)"
                        class="text-sm text-green-600 dark:text-green-400 me-4"
                    >{{ __('Dropbox successfully unlinked.') }}</span>
                @elseif (session('status') === 'dropbox-not-unlinked')
                    <span
                        x-data="{ show: true }"
                        x-show="show"
                        x-transition
                        class="text-sm text-red-600 dark:text-red-400 me-4"
                    >{{ __('Failed to unlink the dropbox access') . (session('message') ? ' : ' .session('message') : '.') }}</span>
                @elseif (session('status') === 'backup-imported')
                    <span
                        x-data="{ show: true }"
                        x-show="show"
                        x-transition
                        x-init="setTimeout(() => show = false, 10000)"
                        class="text-sm text-green-600 dark:text-green-400 me-4"
                    >{{ __('Backup successfully imported.') }}</span>
                @elseif (session('status') === 'backup-not-imported')
                    <span
                        x-data="{ show: true }"
                        x-show="show"
                        x-transition
                        class="text-sm text-red-600 dark:text-red-400 me-4"
                    >{{ __('Failed to import the backup') . (session('message') ? ' : ' .session('message') : '.') }}</span>
                @endif
            </div>

            @include("settings.partials.panel")

            @include("settings.partials.inbound")

            @include("settings.partials.telegram")

            @include("settings.partials.dropbox")

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-0 md:gap-4">
                @include("settings.partials.backup")

                @include("settings.partials.import")
            </div>
        </div>
    </div>
</x-app-layout>
