@extends('layouts.auth')

@section('title', 'تسجيل الدخول')

@section('content')
<div class="login-container">
    <!-- Logo Section -->
    <div class="logo-section">
        <a href="{{ route('login') }}">
            <img src="{{ asset('logo-v.png') }}" alt="وزارة الصحة - إدارة الصيدلة" />
        </a>
        <div class="ministry-name">
            <h1>إدارة الصيدلة</h1>
        </div>
    </div>

    <!-- Divider -->
    <div class="elegant-divider">
        <span></span>
    </div>

    <!-- Welcome Text -->
    <div class="welcome-section">
        <h2>تسجيل الدخول</h2>
        <p>يرجى إدخال بيانات الاعتماد للوصول إلى النظام</p>
    </div>

    

    <!-- Login Form -->
    <form method="POST" action="{{ route('login.submit') }}" class="login-form" dir="rtl">
        @csrf

        <div class="form-group">
            <label for="email">البريد الإلكتروني</label>
            <div class="input-wrapper">
                <i class="ti ti-mail"></i>
                <input
                    type="email"
                    class="@error('email') is-invalid @enderror"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="example@moh.gov.ly"
                    required
                    autofocus
                />
            </div>
            @error('email')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">كلمة المرور</label>
            <div class="input-wrapper">
                <i class="ti ti-lock"></i>
                <input
                    type="password"
                    class="@error('password') is-invalid @enderror"
                    id="password"
                    name="password"
                    placeholder="••••••••"
                    required
                />
                <button type="button" class="toggle-password" id="togglePassword">
                    <i class="ti ti-eye" id="eyeIcon"></i>
                </button>
            </div>
            @error('password')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <div class="remember-section">
            <label class="custom-checkbox">
                <input
                    type="checkbox"
                    id="remember"
                    name="remember"
                    {{ old('remember') ? 'checked' : '' }}
                />
                <span class="checkmark"></span>
                <span class="label-text">تذكرني</span>
            </label>
        </div>

        <button type="submit" class="submit-btn">
            <span>تسجيل الدخول</span>
            <i class="ti ti-arrow-left"></i>
        </button>
    </form>

    <div class="register-section">
        <p>ليس لديك حساب؟ <a href="{{ route('register') }}">أنشئ حساباً جديداً</a></p>
    </div>

    <!-- Footer -->
    <div class="login-footer">
        <p>© {{ date('Y') }} وزارة الصحة - إدارة الصيدلة</p>
        <p class="sub">جميع الحقوق محفوظة</p>
    </div>
</div>

@endsection

@push('styles')
<style>
    /* Animations */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(-20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes scaleIn {
        from {
            opacity: 0;
            transform: scale(0.9);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    @keyframes expandWidth {
        from {
            width: 0;
        }
        to {
            width: 60px;
        }
    }

    .auth-form {
        width: 100%;
        max-width: 520px;
        padding: 0 20px;
    }

    .login-container {
        background: #ffffff;
        border-radius: 4px;
        box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
        padding: 50px 50px;
        border: 1px solid #e8e8e8;
        width: 100%;
        animation: fadeInUp 0.6s ease-out;
    }

    /* Logo Section */
    .logo-section {
        text-align: center;
        margin-bottom: 24px;
        animation: scaleIn 0.5s ease-out 0.1s both;
    }

    .logo-section img {
        max-width: 200px;
        height: auto;
        margin-bottom: 20px;
        transition: transform 0.3s ease;
    }

    .logo-section img:hover {
        transform: scale(1.02);
    }

    .ministry-name h1 {
        font-size: 1.8rem;
        font-weight: 700;
        color: #1a5f4a;
        margin: 0;
        letter-spacing: -0.5px;
        animation: fadeIn 0.5s ease-out 0.2s both;
    }

    .ministry-name p {
        font-size: 1.2rem;
        color: #5a6a72;
        margin: 8px 0 0 0;
        font-weight: 500;
        animation: fadeIn 0.5s ease-out 0.3s both;
    }

    /* Elegant Divider */
    .elegant-divider {
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 24px 0;
    }

    .elegant-divider span {
        display: block;
        width: 60px;
        height: 2px;
        background: #1a5f4a;
        position: relative;
        animation: expandWidth 0.6s ease-out 0.4s both;
    }

    .elegant-divider span::before,
    .elegant-divider span::after {
        content: '';
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 6px;
        height: 6px;
        background: #1a5f4a;
        border-radius: 50%;
        animation: scaleIn 0.3s ease-out 0.8s both;
    }

    .elegant-divider span::before {
        right: -12px;
    }

    .elegant-divider span::after {
        left: -12px;
    }

    /* Welcome Section */
    .welcome-section {
        text-align: center;
        margin-bottom: 32px;
        animation: fadeInUp 0.5s ease-out 0.5s both;
    }

    .welcome-section h2 {
        font-size: 1.25rem;
        font-weight: 600;
        color: #2d3748;
        margin: 0 0 8px 0;
    }

    .welcome-section p {
        font-size: 0.9rem;
        color: #718096;
        margin: 0;
    }

    /* Form Styles */
    .login-form {
        direction: rtl;
    }

    .form-group {
        margin-bottom: 20px;
        animation: slideInRight 0.5s ease-out both;
    }

    .form-group:nth-child(1) {
        animation-delay: 0.6s;
    }

    .form-group:nth-child(2) {
        animation-delay: 0.7s;
    }

    .form-group label {
        display: block;
        font-size: 0.875rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
        transition: color 0.2s ease;
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
        transition: color 0.3s ease, transform 0.3s ease;
    }

    .input-wrapper input {
        width: 100%;
        padding: 14px 44px 14px 44px;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        font-size: 0.95rem;
        font-family: 'Almarai', sans-serif;
        color: #1f2937;
        background: #fafafa;
        transition: all 0.3s ease;
    }

    .input-wrapper input::placeholder {
        color: #9ca3af;
        transition: opacity 0.3s ease;
    }

    .input-wrapper input:focus {
        outline: none;
        border-color: #1a5f4a;
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(26, 95, 74, 0.1);
    }

    .input-wrapper input:focus::placeholder {
        opacity: 0.5;
    }

    .input-wrapper input:focus + i:first-child,
    .input-wrapper:focus-within i:first-child {
        color: #1a5f4a;
        transform: scale(1.1);
    }

    .input-wrapper input.is-invalid {
        border-color: #dc2626;
        animation: shake 0.4s ease-in-out;
    }

    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        20%, 60% { transform: translateX(-5px); }
        40%, 80% { transform: translateX(5px); }
    }

    .toggle-password {
        position: absolute;
        left: 1px;
        top: 1px;
        bottom: 1px;
        width: 44px;
        background: transparent;
        border: none;
        border-radius: 0 3px 3px 0;
        color: #6b7280;
        cursor: pointer;
        font-size: 1.2rem;
        transition: all 0.3s ease;
        z-index: 10;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .toggle-password:hover {
        color: #1a5f4a;
        background: rgba(26, 95, 74, 0.05);
    }

    .toggle-password:focus {
        outline: none;
        color: #1a5f4a;
    }

    .error-message {
        color: #dc2626;
        font-size: 0.8rem;
        margin-top: 6px;
        animation: fadeIn 0.3s ease-out;
    }

    /* Remember Checkbox */
    .remember-section {
        margin-bottom: 24px;
        animation: slideInRight 0.5s ease-out 0.8s both;
    }

    .custom-checkbox {
        display: flex;
        align-items: center;
        cursor: pointer;
        user-select: none;
    }

    .custom-checkbox input {
        position: absolute;
        opacity: 0;
        cursor: pointer;
        height: 0;
        width: 0;
    }

    .checkmark {
        height: 18px;
        width: 18px;
        background-color: #fafafa;
        border: 1px solid #d1d5db;
        border-radius: 3px;
        margin-left: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .custom-checkbox:hover .checkmark {
        border-color: #1a5f4a;
    }

    .custom-checkbox input:checked ~ .checkmark {
        background-color: #1a5f4a;
        border-color: #1a5f4a;
        transform: scale(1.05);
    }

    .checkmark::after {
        content: '';
        display: none;
        width: 5px;
        height: 9px;
        border: solid white;
        border-width: 0 2px 2px 0;
        transform: rotate(45deg);
        margin-bottom: 2px;
    }

    .custom-checkbox input:checked ~ .checkmark::after {
        display: block;
        animation: scaleIn 0.2s ease-out;
    }

    .label-text {
        font-size: 0.875rem;
        color: #6b7280;
        transition: color 0.2s ease;
    }

    .custom-checkbox:hover .label-text {
        color: #374151;
    }

    /* Submit Button */
    .submit-btn {
        width: 100%;
        padding: 14px 24px;
        background: #1a5f4a;
        color: #ffffff;
        border: none;
        border-radius: 4px;
        font-size: 1rem;
        font-weight: 600;
        font-family: 'Almarai', sans-serif;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: all 0.3s ease;
        animation: fadeInUp 0.5s ease-out 0.9s both;
        position: relative;
        overflow: hidden;
    }

    .submit-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
        transition: left 0.5s ease;
    }

    .submit-btn:hover {
        background: #155a43;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(26, 95, 74, 0.3);
    }

    .submit-btn:hover::before {
        left: 100%;
    }

    .submit-btn:active {
        transform: translateY(0);
        box-shadow: 0 2px 6px rgba(26, 95, 74, 0.2);
    }

    .submit-btn i {
        font-size: 1.1rem;
        transition: transform 0.3s ease;
    }

    .submit-btn:hover i {
        transform: translateX(-4px);
    }

    /* Register Section */
    .register-section {
        text-align: center;
        margin-top: 20px;
        animation: fadeIn 0.5s ease-out 1s both;
    }

    .register-section p {
        margin: 0;
        font-size: 0.9rem;
        color: #6b7280;
    }

    .register-section a {
        color: #1a5f4a;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
        border-bottom: 1px solid transparent;
        padding-bottom: 1px;
    }

    .register-section a:hover {
        color: #155a43;
        border-bottom-color: #155a43;
    }

    /* Footer */
    .login-footer {
        text-align: center;
        margin-top: 32px;
        padding-top: 24px;
        border-top: 1px solid #e5e7eb;
        animation: fadeIn 0.5s ease-out 1s both;
    }

    .login-footer p {
        margin: 0;
        font-size: 0.8rem;
        color: #6b7280;
    }

    .login-footer .sub {
        font-size: 0.75rem;
        color: #9ca3af;
        margin-top: 4px;
    }

    /* Responsive */
    @media (max-width: 480px) {
        .login-container {
            padding: 32px 24px;
            margin: 0 10px;
        }

        .logo-section img {
            max-width: 160px;
        }

        .ministry-name h1 {
            font-size: 1.5rem;
        }

        .ministry-name p {
            font-size: 1.1rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle Password Visibility
    const togglePassword = document.getElementById('togglePassword');
    const passwordField = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');

    if (togglePassword && passwordField && eyeIcon) {
        togglePassword.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            if (passwordField.type == 'password') {
                passwordField.type = 'text';
                eyeIcon.classList.remove('ti-eye');
                eyeIcon.classList.add('ti-eye-off');
            } else {
                passwordField.type = 'password';
                eyeIcon.classList.remove('ti-eye-off');
                eyeIcon.classList.add('ti-eye');
            }

            passwordField.focus();
        });
    }

    // Auto-dismiss alerts
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            if (typeof bootstrap != 'undefined' && bootstrap.Alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    });
});
</script>
@endpush
