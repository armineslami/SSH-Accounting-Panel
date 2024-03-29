<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Update Password') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')
        <div class="grid gap-3 grid-cols-1 md:grid-cols-1 lg:grid-cols-3">
            <div>
                <x-input-label for="update_password_current_password" :value="__('Current Password')"/>
                <x-text-input id="update_password_current_password" name="current_password"
                              placeholder="current password" type="password"
                              class="mt-1 block w-full" autocomplete="current-password" required/>
                <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2"/>
            </div>

            <div>
                <x-input-label for="update_password_password" :value="__('New Password')"/>
                <x-text-input id="update_password_password" name="password"
                              placeholder="new password" type="password"
                              class="mt-1 block w-full"
                              autocomplete="new-password" required/>
                <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2"/>
            </div>

            <div>
                <x-input-label for="update_password_password_confirmation" :value="__('Confirm Password')"/>
                <x-text-input id="update_password_password_confirmation" name="password_confirmation"
                              placeholder="new password" type="password"
                              class="mt-1 block w-full" autocomplete="new-password" required/>
                <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2"/>
            </div>

        </div>
        <div class="flex items-center gap-4">
            <div class="ms-auto">
                <x-primary-button>{{ __('Update') }}</x-primary-button>
            </div>
        </div>
    </form>
</section>
