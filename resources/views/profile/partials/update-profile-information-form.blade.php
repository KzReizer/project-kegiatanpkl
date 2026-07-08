<section>
    <header class="profile-section-header">
        <div>
        <h2>
            {{ __('Profile Information') }}
        </h2>

        <p class="subtle-text" style="margin-top: .35rem;">
            {{ __("Update your account's profile information and email address.") }}
        </p>
        </div>
        <span class="card-icon"><i data-lucide="user-round"></i></span>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="field-group">
        @csrf
        @method('patch')

        <div class="field-group">
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div class="field-group">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="subtle-text" style="margin-top: .5rem;">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="text-link">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="inline-status" style="margin-top: .5rem;">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="form-actions">
            <x-primary-button>
                <i data-lucide="save"></i>
                {{ __('Save') }}
            </x-primary-button>

            @if (session('status') === 'profile-updated')
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
