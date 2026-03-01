@extends('layouts.auth')

@section('title', 'تسجيل ممثل شركة')

@section('content')
<div class="login-container">
    <!-- Logo Section -->
    <div class="logo-section">
        <a href="{{ route('login') }}">
            <img src="{{ asset('logo-v.png') }}" alt="وزارة الصحة - إدارة الصيدلة" />
        </a>
        <div class="ministry-name">
            <h1>بوابة الشركات</h1>
        </div>
    </div>

    <!-- Divider -->
    <div class="elegant-divider">
        <span></span>
    </div>

    <!-- Welcome Text -->
    <div class="welcome-section">
        <h2>تسجيل حساب جديد</h2>
        <p>أدخل بياناتك للتسجيل كممثل شركة</p>
    </div>

    

    <!-- Register Form -->
    <form method="POST" action="{{ route('register.submit') }}" class="login-form" dir="rtl">
        @csrf

        <div class="form-group">
            <label for="name">الاسم الكامل</label>
            <div class="input-wrapper">
                <i class="ti ti-user"></i>
                <input
                    type="text"
                    class="@error('name') is-invalid @enderror"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    placeholder="أدخل اسمك الكامل"
                    required
                    autofocus
                />
            </div>
            @error('name')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="job_title">المسمى الوظيفي</label>
            <div class="input-wrapper">
                <i class="ti ti-briefcase"></i>
                <input
                    type="text"
                    class="@error('job_title') is-invalid @enderror"
                    id="job_title"
                    name="job_title"
                    value="{{ old('job_title') }}"
                    placeholder="مثال: مدير تسويق، مندوب مبيعات"
                    required
                />
            </div>
            @error('job_title')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="phone">رقم الهاتف</label>
            <div class="input-wrapper">
                <i class="ti ti-phone"></i>
                <input
                    type="tel"
                    class="@error('phone') is-invalid @enderror"
                    id="phone"
                    name="phone"
                    value="{{ old('phone') }}"
                    placeholder="09XXXXXXXX"
                    required
                    dir="ltr"
                    style="text-align: right;"
                />
            </div>
            @error('phone')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

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
                    placeholder="example@company.com"
                    required
                    dir="ltr"
                    style="text-align: right;"
                />
            </div>
            @error('email')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="submit-btn">
            <span>إرسال رمز التحقق</span>
            <i class="ti ti-arrow-left"></i>
        </button>
    </form>

    <!-- Login Link -->
    <div class="auth-links">
        <p>لديك حساب بالفعل؟ <a href="{{ route('login') }}">تسجيل الدخول</a></p>
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
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideInRight {
        from { opacity: 0; transform: translateX(-20px); }
        to { opacity: 1; transform: translateX(0); }
    }

    @keyframes scaleIn {
        from { opacity: 0; transform: scale(0.9); }
        to { opacity: 1; transform: scale(1); }
    }

    @keyframes expandWidth {
        from { width: 0; }
        to { width: 60px; }
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
        padding: 40px 50px;
        border: 1px solid #e8e8e8;
        width: 100%;
        animation: fadeInUp 0.6s ease-out;
    }

    .logo-section {
        text-align: center;
        margin-bottom: 20px;
        animation: scaleIn 0.5s ease-out 0.1s both;
    }

    .logo-section img {
        max-width: 300px;
        height: auto;
        margin-bottom: 15px;
        transition: transform 0.3s ease;
    }

    .ministry-name h1 {
        font-size: 1.6rem;
        font-weight: 700;
        color: #1a5f4a;
        margin: 0;
    }

    .elegant-divider {
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 20px 0;
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
    }

    .elegant-divider span::before { right: -12px; }
    .elegant-divider span::after { left: -12px; }

    .welcome-section {
        text-align: center;
        margin-bottom: 25px;
    }

    .welcome-section h2 {
        font-size: 1.2rem;
        font-weight: 600;
        color: #2d3748;
        margin: 0 0 8px 0;
    }

    .welcome-section p {
        font-size: 0.85rem;
        color: #718096;
        margin: 0;
    }

    .form-group {
        margin-bottom: 18px;
        animation: slideInRight 0.5s ease-out both;
    }

    .form-group:nth-child(1) { animation-delay: 0.3s; }
    .form-group:nth-child(2) { animation-delay: 0.4s; }
    .form-group:nth-child(3) { animation-delay: 0.5s; }
    .form-group:nth-child(4) { animation-delay: 0.6s; }

    .form-group label {
        display: block;
        font-size: 0.85rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 6px;
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
        padding: 12px 44px 12px 14px;
        border: 1px solid #d1d5db;
        border-radius: 4px;
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

    .input-wrapper input.is-invalid {
        border-color: #dc2626;
    }

    .error-message {
        color: #dc2626;
        font-size: 0.8rem;
        margin-top: 4px;
    }

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
        margin-top: 10px;
    }

    .submit-btn:hover {
        background: #155a43;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(26, 95, 74, 0.3);
    }

    .submit-btn i {
        transition: transform 0.3s ease;
    }

    .submit-btn:hover i {
        transform: translateX(-4px);
    }

    .auth-links {
        text-align: center;
        margin-top: 20px;
    }

    .auth-links p {
        color: #6b7280;
        font-size: 0.9rem;
        margin: 0;
    }

    .auth-links a {
        color: #1a5f4a;
        font-weight: 600;
        text-decoration: none;
        transition: color 0.2s ease;
    }

    .auth-links a:hover {
        color: #155a43;
        text-decoration: underline;
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
        color: #6b7280;
    }

    .login-footer .sub {
        font-size: 0.75rem;
        color: #9ca3af;
        margin-top: 4px;
    }

    @media (max-width: 480px) {
        .login-container {
            padding: 30px 24px;
        }

        .logo-section img {
            max-width: 140px;
        }
    }
</style>
@endpush
