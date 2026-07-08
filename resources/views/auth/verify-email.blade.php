<x-guest-layout>
    <div class="subtle-text" style="margin-top: 0; margin-bottom: 1rem;">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="session-status" style="margin-bottom: 1rem;">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <div class="auth-actions" style="justify-content: space-between;">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    <i data-lucide="mail-check"></i>
                    {{ __('Resend Verification Email') }}
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="secondary-button">
                <i data-lucide="log-out"></i>
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
