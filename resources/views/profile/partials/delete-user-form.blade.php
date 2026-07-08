<section class="field-group">
    <header class="profile-section-header">
        <div>
        <h2>
            {{ __('Delete Account') }}
        </h2>

        <p class="subtle-text" style="margin-top: .35rem;">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
        </div>
        <span class="card-icon"><i data-lucide="triangle-alert"></i></span>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >{{ __('Delete Account') }}</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="field-group" style="padding: 1.25rem;">
            @csrf
            @method('delete')

            <h2 class="text-xl font-bold">
                {{ __('Are you sure you want to delete your account?') }}
            </h2>

            <p class="subtle-text" style="margin-top: 0;">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
            </p>

            <div class="field-group">
                <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-full"
                    placeholder="{{ __('Password') }}"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="form-actions" style="justify-content: flex-end;">
                <x-secondary-button x-on:click="$dispatch('close')">
                    <i data-lucide="x"></i>
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button>
                    <i data-lucide="trash-2"></i>
                    {{ __('Delete Account') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
