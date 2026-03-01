@extends('layouts.auth')

@section('title', 'إعادة تعيين كلمة المرور')

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
        <h2>إعادة تعيين كلمة المرور</h2>
        <p>أدخل كلمة المرور الجديدة لحسابك</p>
    </div>

    

    <!-- Reset Password Form -->
    <form method="POST" action="{{ route('reset-password.submit') }}"  dir="rtl">
        @csrf

        <div class="form-group">
            <label for="password">كلمة المرور الجديدة</label>
            <div class="input-wrapper">
                <i class="ti ti-lock"></i>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control @error('password') is-invalid @enderror"
                    placeholder="أدخل كلمة المرور الجديدة"
                    required
                    autofocus
                />
                <button type="button" class="toggle-password" onclick="togglePassword('password')">
                    <i class="ti ti-eye" id="password-eye"></i>
                </button>
            </div>
            @error('password')
                <span class="error-message">{{ $message }}</span>
            @enderror
            <small class="form-text">كلمة المرور يجب أن تكون 8 أحرف على الأقل</small>
        </div>

        <div class="form-group">
            <label for="password_confirmation">تأكيد كلمة المرور</label>
            <div class="input-wrapper">
                <i class="ti ti-lock"></i>
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    class="form-control"
                    placeholder="أعد إدخال كلمة المرور"
                    required
                />
                <button type="button" class="toggle-password" onclick="togglePassword('password_confirmation')">
                    <i class="ti ti-eye" id="password_confirmation-eye"></i>
                </button>
            </div>
        </div>

        <!-- Password Requirements -->
        <div class="password-requirements">
            <p style="margin: 0 0 8px 0; font-size: 0.85rem; color: #6b7280;">متطلبات كلمة المرور:</p>
            <ul style="margin: 0; padding-right: 20px; font-size: 0.85rem; color: #6b7280;">
                <li>8 أحرف على الأقل</li>
                <li>يجب أن تطابق كلمة المرور الجديدة</li>
            </ul>
        </div>

        <button type="submit" class="submit-btn">
            <span>تغيير كلمة المرور</span>
            <i class="ti ti-check"></i>
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

    .toggle-password {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #9ca3af;
        cursor: pointer;
        padding: 0;
        font-size: 1.2rem;
        transition: color 0.2s;
    }

    .toggle-password:hover {
        color: #6b7280;
    }

    .form-control {
        width: 100%;
        padding: 12px 45px;
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

    .form-text {
        display: block;
        color: #6b7280;
        font-size: 0.85rem;
        margin-top: 5px;
    }

    .password-requirements {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 12px 15px;
        margin-bottom: 20px;
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

@push('scripts')
<script>
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(inputId + '-eye');

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('ti-eye');
        icon.classList.add('ti-eye-off');
    } else {
        input.type = 'password';
        icon.classList.remove('ti-eye-off');
        icon.classList.add('ti-eye');
    }
}
</script>
@endpush
