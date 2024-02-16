<section
    id="pusher"
    class="p-8 mt-8 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Pusher') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Set the default settings of your pusher account.") }}
        </p>
    </header>

    <form method="post" action="{{ route('settings.updatePusher') }}" class="space-y-6">
        @csrf
        @method('patch')

        <div class="">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                <div class="lg:col-span-1">
                    <x-input-label for="pusher_id" :value="__('ID')"/>
                    <x-text-input id="pusher_id" name="pusher_id" placeholder="pusher id" type="text"
                                  class="mt-1 block w-full"
                                  :value="old('pusher_id', $settings->pusher_id)" />
                    <x-input-error id="pusher_id_error" class="mt-2" :messages="$errors->get('pusher_id')"/>
                </div>
                <div class="lg:col-span-1">
                    <x-input-label for="pusher_key" :value="__('Key')"/>
                    <x-text-input id="pusher_key" name="pusher_key" placeholder="pusher key" type="text"
                                  class="mt-1 block w-full"
                                  :value="old('pusher_key', $settings->pusher_key)" />
                    <x-input-error id="pusher_key_error" class="mt-2" :messages="$errors->get('pusher_key')"/>
                </div>
                <div class="lg:col-span-1">
                    <x-input-label for="pusher_secret" :value="__('Secret')"/>
                    <x-text-input id="pusher_secret" name="pusher_secret" placeholder="pusher secret" type="text"
                                  class="mt-1 block w-full"
                                  :value="old('pusher_secret', $settings->pusher_secret)" />
                    <x-input-error id="pusher_secret_error" class="mt-2" :messages="$errors->get('pusher_secret')"/>
                </div>
                <div class="lg:col-span-1">
                    <x-input-label for="pusher_cluster" :value="__('Cluster')"/>
                    <x-text-input id="pusher_cluster" name="pusher_cluster" placeholder="pusher cluster" type="text"
                                  class="mt-1 block w-full"
                                  :value="old('pusher_cluster', $settings->pusher_cluster)" />
                    <x-input-error id="pusher_cluster_error" class="mt-2" :messages="$errors->get('pusher_cluster')"/>
                </div>
                <div class="lg:col-span-1">
                    <x-input-label for="pusher_port" :value="__('Port')"/>
                    <x-text-input id="pusher_port" name="pusher_port" placeholder="pusher port" type="number"
                                  class="mt-1 block w-full"
                                  :value="old('pusher_port', $settings->pusher_port)" />
                    <x-input-error id="pusher_port_error" class="mt-2" :messages="$errors->get('pusher_port')"/>
                </div>
            </div>

            <div class="mt-4 text-sm text-gray-900 dark:text-gray-100">
                <p class="mb-2 text-justify">⚠️ To receive push notifications, set the pusher configs. If you don't have
                    an account, visit
                    <a class="text-indigo-500" href="https://pusher.com/" target="_blank">
                        Pusher official website
                    </a>
                    to create a new one.
                </p>
            </div>


            <div class="flex items-center gap-4 mt-8">
                <div class="ms-auto">
                    <x-primary-button>{{ __('Update') }}</x-primary-button>
                </div>
            </div>
        </div>
    </form>
</section>
<script>
    const idErrorElement = document.getElementById('pusher_id_error');
    const keyElement = document.getElementById('pusher_key_error');
    const secretElement = document.getElementById('pusher_secret_error');
    const clusterElement = document.getElementById('pusher_cluster_error');
    const portElement = document.getElementById('pusher_port_error');
    if (idErrorElement || keyElement || secretElement || clusterElement || portElement) {
        const pusher = document.getElementById('pusher');
        pusher.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
</script>
