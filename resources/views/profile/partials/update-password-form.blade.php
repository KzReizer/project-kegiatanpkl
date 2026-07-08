<section>
    <header class="profile-section-header">
        <div>
        <h2>
            {{ __('Update Password') }}
        </h2>

        <p class="subtle-text" style="margin-top: .35rem;">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
        </div>
        <span class="card-icon"><i data-lucide="lock-keyhole"></i></span>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="field-group">
        @csrf
        @method('put')

        <div class="field-group">
            <x-input-label for="update_password_current_password" :value="__('Current Password')" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div class="field-group">
            <x-input-label for="update_password_password" :value="__('New Password')" />
            <x-text-input id="update_password_password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div class="field-group">
            <x-input-label for="update_password_password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="form-actions">
            <x-primary-button>
                <i data-lucide="save"></i>
                {{ __('Save') }}
            </x-primary-button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="inline-status"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
