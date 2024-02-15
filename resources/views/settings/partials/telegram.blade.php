<section
    id="telegram"
    class="p-8 mt-8 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Telegram') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Set the default settings of your telegram bot.") }}
        </p>
    </header>

    <form method="post" action="{{ route('settings.updateTelegram') }}" class="space-y-6">
        @csrf
        @method('patch')

        <div class="">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                <div class="lg:col-span-2">
                    <x-input-label for="bot_token" :value="__('Token')"/>
                    <x-text-input id="bot_token" name="bot_token" placeholder="token" type="text"
                                  class="mt-1 block w-full"
                                  :value="old('bot_token', $settings->bot_token)" autofocus/>
                    <x-input-error id="bot_token_error" class="mt-2" :messages="$errors->get('bot_token')"/>
                </div>

                <div class="lg:col-span-1">
                    <x-input-label for="bot_port" :value="__('Port')"/>
                    <x-select-input id="bot_port" name="bot_port" class="mt-1 block w-full">
                        <option
                            value="" {{ $settings->bot_port === '' || ('bot_port') === '' ? 'selected' : '' }}>
                            -
                        </option>
                        <option
                            value="443" {{ $settings->bot_port === '443' || old('bot_port') === '443' ? 'selected' : '' }}>
                            433
                        </option>
                        <option
                            value="8443" {{ $settings->bot_port === '8443' || old('bot_port') === '8443' ? 'selected' : '' }}>
                            8443
                        </option>
                        <option
                            value="80" {{ $settings->bot_port === '80' || old('bot_port') === '80' ? 'selected' : '' }}>
                            80
                        </option>
                        <option
                            value="88" {{ $settings->bot_port === '88' || old('bot_port') === '88' ? 'selected' : '' }}>
                            88
                        </option>
                    </x-select-input>
                    <x-input-error id="bot_port_error" class="mt-2" :messages="$errors->get('bot_port')"/>
                </div>
            </div>

            <div class="mt-4 text-justify text-sm text-gray-900 dark:text-gray-100">
                <p class="mb-2">⚠️ To run the bot there are a couple of things to do first:</p>
                <p class="mb-1">1- A domain must be set for the panel.</p>
                <p class="mb-1">2- SSL certificate must be activated for your domain.</p>
                <p class="mb-1">
                    3- Only the above ports are support by Telegram. By default, when you activate SSL
                    certificate,
                    no matter what port you have chosen, the panel must be running on port 80 too. But if
                    not,
                    then make sure the panel is running on the port that you chose above.
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
    const botTokenErrorElement = document.getElementById('bot_token_error');
    const botPortErrorElement = document.getElementById('bot_port_error');
    if (botTokenErrorElement || botPortErrorElement) {
        const telegram = document.getElementById('telegram');
        telegram.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
</script>
