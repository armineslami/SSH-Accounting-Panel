<section
    id="backup"
    class="p-8 mt-8 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Backup') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Create a backup and download it manually.") }}
        </p>
    </header>

    <form method="post" action="{{route('settings.backup.download') }}" class="space-y-6">
        @csrf
        @method('patch')
        <div class="">
            <p class="mt-4 text-justify text-gray-900 dark:text-gray-100">
                If you don't want to link your dropbox account or need to download the backup manually,
                use the button below to download the latest backup.
            </p>

            <div class="flex items-center gap-4 mt-6">
                <div class="ms-auto">
                    <x-primary-button>{{ __('Download') }}</x-primary-button>
                </div>
            </div>
        </div>
    </form>
</section>
