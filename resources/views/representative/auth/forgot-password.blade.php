@extends('layouts.auth')

@section('title', 'نسيت كلمة المرور')

@section('content')
<div class="login-container">
    <!-- Logo Section -->
    <div class="logo-section">
        <a href="{{ route('login') }}">
            <img src="{{ asset('logo-v.png') }}" alt="وزارة الصحة - إدارة الصيدلة" />
        </a>
    </div>

    <!-- Welcome Text -->
    <div class="welcome-section">
        <h2>استعادة كلمة المرور</h2>
        <p>أدخل بريدك الإلكتروني لإرسال رمز التحقق</p>
    </div>

    

    <!-- Forgot Password Form -->
    <form method="POST" action="{{ route('forgot-password.submit') }}" dir="rtl" id="forgotPasswordForm">
        @csrf
        <input type="hidden" name="recaptcha_token" id="recaptcha_token">

        <div class="form-group">
            <label for="email">البريد الإلكتروني</label>
            <div class="input-wrapper">
                <i class="ti ti-mail"></i>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control @error('email') is-invalid @enderror"
                    placeholder="أدخل بريدك الإلكتروني"
                    value="{{ old('email') }}"
                    required
                    autofocus
                />
            </div>
            @error('email')
                <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="submit-btn">
            <span>إرسال رمز التحقق</span>
            <i class="ti ti-send"></i>
        </button>
    </form>

    <!-- Back to login -->
    <div style="text-align: center; margin-top: 20px;">
        <a href="{{ route('login') }}" style="color: #6b7280; font-size: 0.9rem; text-decoration: none;">
            <i class="ti ti-arrow-right" style="margin-left: 5px;"></i>
            العودة لتسجيل الدخول
        </a>
    </div>

    <!-- Footer -->
    <div class="login-footer">
        <p>© {{ date('Y') }} وزارة الصحة - إدارة الصيدلة</p>
    </div>
</div>
@endsection

@push('scripts')
@if(config('services.recaptcha.site_key'))
<script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
@endif
<script>
document.addEventListener('DOMContentLoaded', function() {
    var forgotForm = document.getElementById('forgotPasswordForm');
    if (forgotForm) {
        forgotForm.addEventListener('submit', function(e) {
            var siteKey = '{{ config("services.recaptcha.site_key") }}';
            if (siteKey && typeof grecaptcha !== 'undefined') {
                e.preventDefault();
                grecaptcha.ready(function() {
                    grecaptcha.execute(siteKey, {action: 'forgot_password'}).then(function(token) {
                        document.getElementById('recaptcha_token').value = token;
                        forgotForm.submit();
                    });
                });
            }
        });
    }
});
</script>
@endpush

@push('styles')
<style>
    .login-container {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        padding: 50px 40px;
        border: 1px solid #e8e8e8;
        width: 100%;
        max-width: 480px;
        text-align: center;
    }

    .logo-section {
        margin-bottom: 20px;
    }

    .logo-section img {
        max-width: 300px;
        height: auto;
    }

    .welcome-section {
        margin-bottom: 30px;
    }

    .welcome-section h2 {
        font-size: 1.4rem;
        font-weight: 700;
        color: #2d3748;
        margin: 0 0 10px 0;
    }

    .welcome-section p {
        font-size: 0.9rem;
        color: #718096;
        margin: 0;
    }

    .auth-form {
        text-align: right;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: #2d3748;
        font-weight: 600;
        font-size: 0.95rem;
    }

    .input-wrapper {
        position: relative;
    }

    .input-wrapper i {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        font-size: 1.2rem;
    }

    .form-control {
        width: 100%;
        padding: 12px 45px 12px 15px;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        font-family: 'Cairo', 'Almarai', sans-serif;
    }

    .form-control:focus {
        outline: none;
        border-color: #1a5f4a;
        box-shadow: 0 0 0 4px rgba(26, 95, 74, 0.1);
    }

    .form-control.is-invalid {
        border-color: #dc2626;
    }

    .error-message {
        display: block;
        color: #dc2626;
        font-size: 0.85rem;
        margin-top: 5px;
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
        font-family: 'Cairo', 'Almarai', sans-serif;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: all 0.3s ease;
        margin-top: 25px;
    }

    .submit-btn:hover {
        background: #155a43;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(26, 95, 74, 0.3);
    }

    .login-footer {
        padding-top: 20px;
        border-top: 1px solid #e5e7eb;
        margin-top: 25px;
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
