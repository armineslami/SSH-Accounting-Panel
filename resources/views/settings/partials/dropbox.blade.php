<section
    id="dropbox"
    class="p-8 mt-8 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Dropbox') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Link your dropbox account to schedule daily backup.") }}
        </p>
    </header>

    <form method="post" action="{{ is_null($settings->dropbox_token) ? route('settings.dropbox.link') :  route('settings.dropbox.unlink') }}" class="space-y-6">
        @csrf
        @method('patch')
        <div class="">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                <div>
                    <x-input-label for="dropbox_client_id" :value="__('*Client ID')"/>
                    <x-text-input id="dropbox_client_id" name="dropbox_client_id" type="text"
                                  class="mt-1 block w-full"
                                  :value="old('dropbox_client_id', $settings->dropbox_client_id)"
                                  required
                                  autofocus/>
                    <x-input-error id="dropbox_client_id_error" class="mt-2" :messages="$errors->get('dropbox_client_id')"/>
                </div>

                <div>
                    <x-input-label for="dropbox_client_secret" :value="__('*Client Secret')"/>
                    <x-text-input id="dropbox_client_secret" name="dropbox_client_secret" type="text"
                                  class="mt-1 block w-full"
                                  :value="old('dropbox_client_secret', $settings->dropbox_client_secret)"
                                  required
                                  autofocus/>
                    <x-input-error id="dropbox_client_secret_error" class="mt-2" :messages="$errors->get('dropbox_client_secret')"/>
                </div>

            </div>

            <div class="mt-4 text-justify text-sm text-gray-900 dark:text-gray-100">
                <p class="mb-2">⚠️ To enable dropbox auto daily backup, first install SSL certificate and then visit
                    <a class="text-indigo-500" href="https://www.dropbox.com/developers/apps/create" target="_blank">
                        Dropbox Developers Panel
                    </a>
                    to create a new app for yourself and enter its id and secret keys here.
                    Also make sure to add the following configs to your dropbox app:
                </p>
                <ul>
                    <li><b>Permission:</b> files.content.write</li>
                    <li><b>Redirect URIs:</b> {{ route('settings.dropbox.callback') }}</li>
                </ul>
            </div>

            <div class="flex items-center gap-4 mt-8">
                <div class="ms-auto">
                    @if (!is_null($settings->dropbox_token))
                        <x-danger-button>{{ __('Unlink') }}</x-danger-button>
                    @else
                        <x-primary-button>{{ __('Link') }}</x-primary-button>
                    @endif
                </div>
            </div>
        </div>
    </form>
</section>
<script>
    const dropBoxIdErrorElement = document.getElementById('dropbox_client_id_error');
    const dropBoxSecretErrorElement = document.getElementById('dropbox_client_secret_error');
    if (dropBoxIdErrorElement || dropBoxSecretErrorElement) {
        const dropbox = document.getElementById('dropbox');
        dropbox.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
</script>
