<x-guest-layout>
    <div class="subtle-text" style="margin-top: 0; margin-bottom: 1rem;">
        {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
    </div>

    <form class="field-group" method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <!-- Password -->
        <div class="field-group">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="auth-actions">
            <x-primary-button>
                <i data-lucide="shield-check"></i>
                {{ __('Confirm') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
