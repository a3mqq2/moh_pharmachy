@extends('layouts.auth')

@section('title', __('auth.verify_otp'))

@section('content')
<div class="login-container otp-container">
    <div class="logo-section">
        <a href="{{ route('login') }}">
            <img src="{{ asset('logo-v.png') }}" alt="{{ __('general.site_title') }}" />
        </a>
    </div>

    <div class="welcome-section">
        <h2>{{ __('auth.verify_password_reset_otp') }}</h2>
        <p>{{ __('auth.enter_code_sent_to') }}</p>
        <p class="email-display">{{ $email }}</p>
    </div>

    @if(session('error'))
        <div class="error-alert">
            <i class="ti ti-alert-circle"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.verify-password-otp.submit') }}" class="otp-form" id="otpForm">
        @csrf
        <input type="hidden" name="otp" id="otpHidden" />

        <div class="otp-inputs">
            <input type="text" maxlength="1" class="otp-input" data-index="0" autofocus inputmode="numeric" pattern="[0-9]" />
            <input type="text" maxlength="1" class="otp-input" data-index="1" inputmode="numeric" pattern="[0-9]" />
            <input type="text" maxlength="1" class="otp-input" data-index="2" inputmode="numeric" pattern="[0-9]" />
            <input type="text" maxlength="1" class="otp-input" data-index="3" inputmode="numeric" pattern="[0-9]" />
            <input type="text" maxlength="1" class="otp-input" data-index="4" inputmode="numeric" pattern="[0-9]" />
            <input type="text" maxlength="1" class="otp-input" data-index="5" inputmode="numeric" pattern="[0-9]" />
        </div>

        <button type="submit" class="submit-btn" id="verifyBtn" disabled>
            <span>{{ __('auth.verify_and_continue') }}</span>
            <i class="ti ti-arrow-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }}"></i>
        </button>
    </form>

    <div class="resend-section">
        <p>{{ __('auth.code_not_received') }}</p>
        <button type="button" class="resend-btn" id="resendBtn" disabled>
            {{ __('auth.resend') }} <span id="countdown">(60)</span>
        </button>
    </div>

    <div class="timer-section">
        <i class="ti ti-clock"></i>
        <span>{{ __('auth.code_valid_for') }} <strong>10 {{ __('auth.minutes') }}</strong></span>
    </div>

    <div style="text-align: center; margin-top: 15px;">
        <a href="{{ route('login') }}" style="color: #6b7280; font-size: 0.9rem; text-decoration: none;">
            <i class="ti ti-arrow-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}" style="margin-inline-end: 5px;"></i>
            {{ __('auth.back_to_login') }}
        </a>
    </div>

    <div class="login-footer">
        <p>{{ __('auth.copyright', ['year' => date('Y')]) }}</p>
    </div>
</div>
@endsection

@push('styles')
<style>
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        20%, 60% { transform: translateX(-5px); }
        40%, 80% { transform: translateX(5px); }
    }

    .otp-container {
        background: #ffffff; border-radius: 12px; box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        padding: 50px 40px; border: 1px solid #e8e8e8; width: 100%; text-align: center;
    }

    .logo-section { margin-bottom: 20px; }
    .logo-section img { max-width: 200px; height: auto; }
    .welcome-section { margin-bottom: 30px; }
    .welcome-section h2 { font-size: 1.4rem; font-weight: 700; color: #2d3748; margin: 0 0 10px 0; }
    .welcome-section p { font-size: 0.9rem; color: #718096; margin: 0; }
    .email-display { color: #1a5f4a !important; font-weight: 600; direction: ltr; margin-top: 5px !important; }

    .error-alert {
        display: flex; align-items: center; gap: 10px; padding: 12px 16px;
        border-radius: 4px; font-size: 0.9rem; margin-bottom: 20px;
        background: #fef2f2; border: 1px solid #fecaca; color: #dc2626;
    }

    .otp-form { margin-bottom: 25px; }
    .otp-inputs { display: flex; justify-content: center; gap: 10px; margin-bottom: 25px; direction: ltr; }

    .otp-input {
        width: 52px; height: 60px; border: 2px solid #e2e8f0; border-radius: 12px;
        font-size: 1.8rem; font-weight: 700; text-align: center; color: #1a5f4a;
        background: #f8fafc; transition: all 0.3s ease; font-family: 'Courier New', monospace;
    }
    .otp-input:focus { outline: none; border-color: #1a5f4a; background: #ffffff; box-shadow: 0 0 0 4px rgba(26, 95, 74, 0.15); transform: scale(1.05); }
    .otp-input.filled { border-color: #1a5f4a; background: #f0fdf4; }
    .otp-input.error { border-color: #dc2626; animation: shake 0.4s ease-in-out; }

    .submit-btn {
        width: 100%; padding: 14px 24px; background: #1a5f4a; color: #ffffff;
        border: none; border-radius: 8px; font-size: 1rem; font-weight: 600;
        font-family: 'Changa', 'Almarai', sans-serif; cursor: pointer;
        display: flex; align-items: center; justify-content: center; gap: 10px; transition: all 0.3s ease;
    }
    .submit-btn:disabled { background: #9ca3af; cursor: not-allowed; }
    .submit-btn:not(:disabled):hover { background: #155a43; transform: translateY(-2px); box-shadow: 0 4px 15px rgba(26, 95, 74, 0.3); }

    .resend-section { margin-bottom: 20px; }
    .resend-section p { color: #6b7280; font-size: 0.9rem; margin: 0 0 8px 0; }
    .resend-btn { background: none; border: none; color: #1a5f4a; font-weight: 600; font-size: 0.95rem; cursor: pointer; font-family: 'Changa', 'Almarai', sans-serif; }
    .resend-btn:disabled { color: #9ca3af; cursor: not-allowed; }
    .resend-btn:not(:disabled):hover { color: #155a43; text-decoration: underline; }

    .timer-section { display: flex; align-items: center; justify-content: center; gap: 8px; padding: 12px; background: #fef3c7; border-radius: 8px; margin-bottom: 20px; }
    .timer-section i { color: #d97706; font-size: 1.1rem; }
    .timer-section span { color: #92400e; font-size: 0.85rem; }

    .login-footer { padding-top: 20px; border-top: 1px solid #e5e7eb; }
    .login-footer p { margin: 0; font-size: 0.8rem; color: #9ca3af; }

    @media (max-width: 480px) {
        .otp-container { padding: 35px 25px; }
        .otp-input { width: 45px; height: 52px; font-size: 1.5rem; }
        .otp-inputs { gap: 6px; }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('.otp-input');
    const form = document.getElementById('otpForm');
    const otpHidden = document.getElementById('otpHidden');
    const verifyBtn = document.getElementById('verifyBtn');
    const resendBtn = document.getElementById('resendBtn');
    const countdown = document.getElementById('countdown');

    function updateOtpValue() {
        let otp = '';
        inputs.forEach(input => { otp += input.value; });
        otpHidden.value = otp;
    }

    inputs.forEach((input, index) => {
        input.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length == 1) {
                this.classList.add('filled');
                if (index < inputs.length - 1) inputs[index + 1].focus();
            } else {
                this.classList.remove('filled');
            }
            updateOtpValue();
            checkComplete();
        });

        input.addEventListener('keydown', function(e) {
            if (e.key == 'Backspace' && !this.value && index > 0) {
                inputs[index - 1].focus();
                inputs[index - 1].value = '';
                inputs[index - 1].classList.remove('filled');
                updateOtpValue();
            }
        });

        input.addEventListener('paste', function(e) {
            e.preventDefault();
            const digits = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '').slice(0, 6);
            digits.split('').forEach((digit, i) => {
                if (inputs[i]) { inputs[i].value = digit; inputs[i].classList.add('filled'); }
            });
            if (digits.length > 0) inputs[Math.min(digits.length - 1, inputs.length - 1)].focus();
            updateOtpValue();
            checkComplete();
        });

        input.addEventListener('focus', function() { this.select(); });
    });

    function checkComplete() {
        let allFilled = true;
        inputs.forEach(input => { if (!input.value) allFilled = false; });
        verifyBtn.disabled = !allFilled;
    }

    let timeLeft = 60;
    const timer = setInterval(() => {
        timeLeft--;
        countdown.textContent = `(${timeLeft})`;
        if (timeLeft <= 0) { clearInterval(timer); resendBtn.disabled = false; countdown.textContent = ''; }
    }, 1000);

    resendBtn.addEventListener('click', function() {
        if (this.disabled) return;
        const originalText = this.innerHTML;
        this.innerHTML = '<span style="display:inline-block;width:14px;height:14px;border:2px solid #1a5f4a;border-top-color:transparent;border-radius:50%;animation:spin 0.8s linear infinite;"></span>';
        this.disabled = true;

        fetch('{{ route("admin.resend-password-otp") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({})
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                timeLeft = 60;
                this.innerHTML = '{{ __("auth.resend") }} <span id="countdown">(60)</span>';
                const nc = document.getElementById('countdown');
                const nt = setInterval(() => {
                    timeLeft--;
                    nc.textContent = `(${timeLeft})`;
                    if (timeLeft <= 0) { clearInterval(nt); resendBtn.disabled = false; nc.textContent = ''; }
                }, 1000);
                inputs.forEach(input => { input.value = ''; input.classList.remove('filled'); });
                otpHidden.value = '';
                verifyBtn.disabled = true;
                inputs[0].focus();
            } else {
                this.innerHTML = originalText;
                this.disabled = false;
            }
        })
        .catch(() => { this.innerHTML = originalText; this.disabled = false; });
    });
});
</script>
@endpush
