@extends('layouts.auth')

@section('title', __('auth.create_password'))

@section('content')
<div class="login-container">
    <!-- Logo Section -->
    <div class="logo-section">
        <a href="{{ route('login') }}">
            <img src="{{ asset('logo-v.png') }}" alt="{{ __('general.site_title') }}" />
        </a>
    </div>

 
    <!-- Welcome Text -->
    <div class="welcome-section">
        <h2>{{ __('auth.verification_success') }}</h2>
        <p>{{ __('auth.create_password_for_account') }}</p>
    </div>

    

    <!-- Password Form -->
    <form method="POST" action="{{ route('set-password.submit') }}" class="login-form" dir="rtl">
        @csrf

        <div class="form-group">
            <label for="password">{{ __('auth.password') }}</label>
            <div class="input-wrapper">
                <i class="ti ti-lock"></i>
                <input
                    type="password"
                    class="@error('password') is-invalid @enderror"
                    id="password"
                    name="password"
                    placeholder="{{ __('auth.enter_strong_password') }}"
                    required
                    autofocus
                />
                <button type="button" class="toggle-password" data-target="password">
                    <i class="ti ti-eye"></i>
                </button>
            </div>
            @error('password')
                <div class="error-message">{{ $message }}</div>
            @enderror

            <!-- Password Strength -->
            <div class="password-strength" id="passwordStrength">
                <div class="strength-bar">
                    <div class="strength-fill" id="strengthFill"></div>
                </div>
                <span class="strength-text" id="strengthText">{{ __('auth.enter_password') }}</span>
            </div>

            <div class="password-requirements">
                <div class="requirement" id="req-length">
                    <i class="ti ti-circle"></i>
                    <span>{{ __('auth.min_8_chars') }}</span>
                </div>
                <div class="requirement" id="req-upper">
                    <i class="ti ti-circle"></i>
                    <span>{{ __('auth.at_least_one_uppercase') }}</span>
                </div>
                <div class="requirement" id="req-number">
                    <i class="ti ti-circle"></i>
                    <span>{{ __('auth.at_least_one_number') }}</span>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="password_confirmation">{{ __('auth.confirm_password') }}</label>
            <div class="input-wrapper">
                <i class="ti ti-lock-check"></i>
                <input
                    type="password"
                    class=""
                    id="password_confirmation"
                    name="password_confirmation"
                    placeholder="{{ __('auth.re_enter_password') }}"
                    required
                />
                <button type="button" class="toggle-password" data-target="password_confirmation">
                    <i class="ti ti-eye"></i>
                </button>
            </div>
            <div class="match-status" id="matchStatus"></div>
        </div>

        <button type="submit" class="submit-btn" id="submitBtn">
            <span>{{ __('auth.create_account_btn') }}</span>
            <i class="ti ti-arrow-left"></i>
        </button>
    </form>

    <!-- Footer -->
    <div class="login-footer">
        <p>{{ __('auth.copyright', ['year' => date('Y')]) }}</p>
    </div>
</div>
@endsection

@push('styles')
<style>
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes bounce {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
    }

    .auth-form {
        width: 100%;
        max-width: 480px;
        padding: 0 20px;
    }

    .login-container {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        padding: 50px 40px;
        border: 1px solid #e8e8e8;
        width: 100%;
        animation: fadeInUp 0.6s ease-out;
        text-align: center;
    }

    .logo-section {
        margin-bottom: 20px;
    }

    .logo-section img {
        max-width: 300px;
        height: auto;
    }

    .success-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 25px;
        animation: bounce 0.6s ease-out;
    }

    .success-icon i {
        font-size: 2.5rem;
        color: white;
    }

    .welcome-section {
        margin-bottom: 30px;
    }

    .welcome-section h2 {
        font-size: 1.4rem;
        font-weight: 700;
        color: #16a34a;
        margin: 0 0 10px 0;
    }

    .welcome-section p {
        font-size: 0.9rem;
        color: #718096;
        margin: 0;
    }

    .login-form {
        text-align: right;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        font-size: 0.85rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
    }

    .input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .input-wrapper i:first-child {
        position: absolute;
        right: 14px;
        color: #9ca3af;
        font-size: 1.1rem;
        pointer-events: none;
        transition: color 0.3s ease;
    }

    .input-wrapper input {
        width: 100%;
        padding: 14px 44px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 0.95rem;
        font-family: 'Almarai', sans-serif;
        color: #1f2937;
        background: #fafafa;
        transition: all 0.3s ease;
    }

    .input-wrapper input:focus {
        outline: none;
        border-color: #1a5f4a;
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(26, 95, 74, 0.1);
    }

    .input-wrapper:focus-within i:first-child {
        color: #1a5f4a;
    }

    .toggle-password {
        position: absolute;
        left: 1px;
        top: 1px;
        bottom: 1px;
        width: 44px;
        background: transparent;
        border: none;
        border-radius: 0 7px 7px 0;
        color: #6b7280;
        cursor: pointer;
        font-size: 1.2rem;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .toggle-password:hover {
        color: #1a5f4a;
        background: rgba(26, 95, 74, 0.05);
    }

    .error-message {
        color: #dc2626;
        font-size: 0.8rem;
        margin-top: 6px;
    }

    .password-strength {
        margin-top: 12px;
    }

    .strength-bar {
        height: 4px;
        background: #e5e7eb;
        border-radius: 2px;
        overflow: hidden;
        margin-bottom: 6px;
    }

    .strength-fill {
        height: 100%;
        width: 0;
        border-radius: 2px;
        transition: all 0.3s ease;
    }

    .strength-fill.weak { width: 33%; background: #dc2626; }
    .strength-fill.medium { width: 66%; background: #f59e0b; }
    .strength-fill.strong { width: 100%; background: #16a34a; }

    .strength-text {
        font-size: 0.75rem;
        color: #6b7280;
    }

    .strength-text.weak { color: #dc2626; }
    .strength-text.medium { color: #f59e0b; }
    .strength-text.strong { color: #16a34a; }

    .password-requirements {
        margin-top: 12px;
        text-align: right;
    }

    .requirement {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.8rem;
        color: #6b7280;
        margin-bottom: 4px;
        transition: color 0.2s ease;
    }

    .requirement i {
        font-size: 0.7rem;
        transition: all 0.2s ease;
    }

    .requirement.valid {
        color: #16a34a;
    }

    .requirement.valid i {
        color: #16a34a;
    }

    .requirement.valid i::before {
        content: "\eb7a"; /* ti-check icon */
    }

    .match-status {
        font-size: 0.8rem;
        margin-top: 6px;
        min-height: 20px;
    }

    .match-status.match {
        color: #16a34a;
    }

    .match-status.no-match {
        color: #dc2626;
    }

    .submit-btn {
        width: 100%;
        padding: 14px 24px;
        background: #1a5f4a;
        color: #ffffff;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
        font-family: 'Almarai', sans-serif;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: all 0.3s ease;
        margin-top: 10px;
    }

    .submit-btn:hover {
        background: #155a43;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(26, 95, 74, 0.3);
    }

    .submit-btn i {
        transition: transform 0.3s ease;
    }

    .submit-btn:hover i {
        transform: translateX(-4px);
    }

    .login-footer {
        text-align: center;
        margin-top: 25px;
        padding-top: 20px;
        border-top: 1px solid #e5e7eb;
    }

    .login-footer p {
        margin: 0;
        font-size: 0.8rem;
        color: #9ca3af;
    }

    @media (max-width: 480px) {
        .login-container {
            padding: 35px 25px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const password = document.getElementById('password');
    const confirm = document.getElementById('password_confirmation');
    const strengthFill = document.getElementById('strengthFill');
    const strengthText = document.getElementById('strengthText');
    const matchStatus = document.getElementById('matchStatus');

    const reqLength = document.getElementById('req-length');
    const reqUpper = document.getElementById('req-upper');
    const reqNumber = document.getElementById('req-number');

    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const icon = this.querySelector('i');

            if (input.type == 'password') {
                input.type = 'text';
                icon.classList.remove('ti-eye');
                icon.classList.add('ti-eye-off');
            } else {
                input.type = 'password';
                icon.classList.remove('ti-eye-off');
                icon.classList.add('ti-eye');
            }
        });
    });

    // Password strength checker
    password.addEventListener('input', function() {
        const value = this.value;
        let strength = 0;

        // Check length
        if (value.length >= 8) {
            strength++;
            reqLength.classList.add('valid');
        } else {
            reqLength.classList.remove('valid');
        }

        // Check uppercase
        if (/[A-Z]/.test(value)) {
            strength++;
            reqUpper.classList.add('valid');
        } else {
            reqUpper.classList.remove('valid');
        }

        // Check number
        if (/[0-9]/.test(value)) {
            strength++;
            reqNumber.classList.add('valid');
        } else {
            reqNumber.classList.remove('valid');
        }

        // Update strength indicator
        strengthFill.className = 'strength-fill';
        strengthText.className = 'strength-text';

        if (value.length == 0) {
            strengthText.textContent = '{{ __("auth.enter_password") }}';
        } else if (strength == 1) {
            strengthFill.classList.add('weak');
            strengthText.classList.add('weak');
            strengthText.textContent = '{{ __("auth.password_weak") }}';
        } else if (strength == 2) {
            strengthFill.classList.add('medium');
            strengthText.classList.add('medium');
            strengthText.textContent = '{{ __("auth.password_medium") }}';
        } else if (strength == 3) {
            strengthFill.classList.add('strong');
            strengthText.classList.add('strong');
            strengthText.textContent = '{{ __("auth.password_strong") }}';
        }

        checkMatch();
    });

    // Password match checker
    confirm.addEventListener('input', checkMatch);

    function checkMatch() {
        if (confirm.value.length == 0) {
            matchStatus.textContent = '';
            matchStatus.className = 'match-status';
            return;
        }

        if (password.value == confirm.value) {
            matchStatus.textContent = '{{ __("auth.passwords_match") }} ✓';
            matchStatus.className = 'match-status match';
        } else {
            matchStatus.textContent = '{{ __("auth.passwords_not_match") }}';
            matchStatus.className = 'match-status no-match';
        }
    }
});
</script>
@endpush
