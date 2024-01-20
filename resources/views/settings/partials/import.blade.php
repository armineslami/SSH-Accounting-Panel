<section
    id="import"
    class="p-8 mt-8 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Import') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Import a .zip backup file to transfer your data into this panel.") }}
        </p>
    </header>

    <form method="post" enctype="multipart/form-data" action="{{route('settings.backup.import') }}" class="space-y-6">
        @csrf
        @method('post')
        <div class="">
            <div class="w-full">
                <div>
                    <x-file-input name="backup_file" accept=".zip"  />
                    <x-input-error id="backup_file_error" class="mt-2" :messages="$errors->get('backup_file')" />
                </div>
            </div>

            <div class="flex items-center gap-4 mt-8">
                <div class="ms-auto">
                    <x-primary-button>{{ __('Import') }}</x-primary-button>
                </div>
            </div>
        </div>
    </form>
</section>
<script>
    const importFileErrorElement = document.getElementById('backup_file_error');
    if (importFileErrorElement) {
        const imp = document.getElementById('import');
        imp.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
</script>
