@extends('layouts.auth')

@section('title', 'إعدادات الحساب')

@section('content')
<div class="dashboard-container">
    <div class="page-header">
        <div class="page-header-content">
            <a href="{{ route('representative.dashboard') }}" class="back-to-home">
                <i class="ti ti-arrow-right"></i>
            </a>
            <div>
                <h1>إعدادات الحساب</h1>
                <p>إدارة المعلومات الشخصية وكلمة المرور</p>
            </div>
        </div>
    </div>

    

    <div class="settings-container">
        <!-- Personal Information Section -->
        <div class="settings-card">
            <div class="settings-card-header">
                <h3><i class="ti ti-user"></i> المعلومات الشخصية</h3>
            </div>
            <div class="settings-card-body">
                <form action="{{ route('representative.settings.update-profile') }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <div class="form-row">
                        <div class="form-group">
                            <label>الاسم الكامل <span class="required">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $representative->name) }}" required>
                            @error('name')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>المسمى الوظيفي <span class="required">*</span></label>
                            <input type="text" name="job_title" class="form-control" value="{{ old('job_title', $representative->job_title) }}" required>
                            @error('job_title')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>رقم الهاتف <span class="required">*</span></label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $representative->phone) }}" required>
                            @error('phone')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>البريد الإلكتروني</label>
                            <input type="email" class="form-control" value="{{ $representative->email }}" disabled>
                            <small>لا يمكن تغيير البريد الإلكتروني</small>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy"></i>
                            حفظ التغييرات
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Password Change Section -->
        <div class="settings-card">
            <div class="settings-card-header">
                <h3><i class="ti ti-lock"></i> تغيير كلمة المرور</h3>
            </div>
            <div class="settings-card-body">
                <form action="{{ route('representative.settings.update-password') }}" method="POST" id="passwordForm">
                    @csrf
                    @method('PATCH')

                    <div class="form-group">
                        <label>كلمة المرور الحالية <span class="required">*</span></label>
                        <div class="password-input-wrapper">
                            <input type="password" name="current_password" id="current_password" class="form-control" required>
                            <button type="button" class="toggle-password" onclick="togglePassword('current_password')">
                                <i class="ti ti-eye" id="current_password_icon"></i>
                            </button>
                        </div>
                        @error('current_password')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>كلمة المرور الجديدة <span class="required">*</span></label>
                            <div class="password-input-wrapper">
                                <input type="password" name="new_password" id="new_password" class="form-control" required>
                                <button type="button" class="toggle-password" onclick="togglePassword('new_password')">
                                    <i class="ti ti-eye" id="new_password_icon"></i>
                                </button>
                            </div>
                            @error('new_password')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                            <small>يجب أن تكون 8 أحرف على الأقل</small>
                        </div>

                        <div class="form-group">
                            <label>تأكيد كلمة المرور الجديدة <span class="required">*</span></label>
                            <div class="password-input-wrapper">
                                <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="form-control" required>
                                <button type="button" class="toggle-password" onclick="togglePassword('new_password_confirmation')">
                                    <i class="ti ti-eye" id="new_password_confirmation_icon"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="password-strength">
                        <div class="strength-label">قوة كلمة المرور:</div>
                        <div class="strength-bar">
                            <div class="strength-bar-fill" id="strengthBar"></div>
                        </div>
                        <div class="strength-text" id="strengthText">غير محددة</div>
                    </div>

                    <div class="alert-info">
                        <i class="ti ti-info-circle"></i>
                        <div>
                            <strong>نصائح لكلمة مرور قوية:</strong>
                            <ul>
                                <li>استخدم 8 أحرف على الأقل</li>
                                <li>اجمع بين الأحرف الكبيرة والصغيرة</li>
                                <li>أضف أرقاماً ورموزاً خاصة</li>
                                <li>تجنب المعلومات الشخصية الواضحة</li>
                            </ul>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-key"></i>
                            تغيير كلمة المرور
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection

@push('styles')
<style>
    .auth-form {
        width: 100%;
        max-width: 900px;
        padding: 0 20px;
    }

    .dashboard-container {
        background: #ffffff;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        padding: 30px;
        width: 100%;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid #e5e7eb;
    }

    .page-header-content {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .back-to-home {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        color: #1a5f4a;
        text-decoration: none;
        transition: all 0.2s ease;
        font-size: 1.2rem;
    }

    .back-to-home:hover {
        background: #1a5f4a;
        color: white;
        border-color: #1a5f4a;
    }

    .page-header h1 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1a5f4a;
        margin: 0 0 5px 0;
    }

    .page-header p {
        font-size: 0.9rem;
        color: #6b7280;
        margin: 0;
    }

    /* Settings Container */
    .settings-container {
        display: flex;
        flex-direction: column;
        gap: 25px;
    }

    .settings-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        overflow: hidden;
    }

    .settings-card-header {
        background: #f9fafb;
        padding: 20px;
        border-bottom: 1px solid #e5e7eb;
    }

    .settings-card-header h3 {
        font-size: 1rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .settings-card-header i {
        color: #1a5f4a;
        font-size: 1.125rem;
    }

    .settings-card-body {
        padding: 25px;
    }

    /* Form Styles */
    .form-row {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 20px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        margin-bottom: 6px;
    }

    .form-group .required {
        color: #dc2626;
    }

    .form-control {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 0.875rem;
        font-family: 'Almarai', sans-serif;
        transition: all 0.2s ease;
    }

    .form-control:focus {
        outline: none;
        border-color: #1a5f4a;
        box-shadow: 0 0 0 3px rgba(26, 95, 74, 0.1);
    }

    .form-control:disabled {
        background: #f9fafb;
        color: #9ca3af;
        cursor: not-allowed;
    }

    .form-group small {
        display: block;
        font-size: 0.75rem;
        color: #6b7280;
        margin-top: 5px;
    }

    .error-message {
        display: block;
        color: #dc2626;
        font-size: 0.75rem;
        margin-top: 5px;
    }

    /* Password Input */
    .password-input-wrapper {
        position: relative;
    }

    .password-input-wrapper .form-control {
        padding-left: 45px;
    }

    .toggle-password {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #6b7280;
        cursor: pointer;
        padding: 5px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .toggle-password:hover {
        color: #1a5f4a;
    }

    .toggle-password i {
        font-size: 1.125rem;
    }

    /* Password Strength */
    .password-strength {
        margin: 15px 0 20px 0;
    }

    .strength-label {
        font-size: 0.875rem;
        color: #6b7280;
        margin-bottom: 8px;
    }

    .strength-bar {
        height: 8px;
        background: #e5e7eb;
        border-radius: 4px;
        overflow: hidden;
        margin-bottom: 5px;
    }

    .strength-bar-fill {
        height: 100%;
        width: 0;
        transition: all 0.3s ease;
        border-radius: 4px;
    }

    .strength-text {
        font-size: 0.75rem;
        font-weight: 600;
        color: #6b7280;
    }

    .strength-bar-fill.weak {
        width: 33%;
        background: #ef4444;
    }

    .strength-bar-fill.medium {
        width: 66%;
        background: #f59e0b;
    }

    .strength-bar-fill.strong {
        width: 100%;
        background: #10b981;
    }

    .strength-text.weak {
        color: #ef4444;
    }

    .strength-text.medium {
        color: #f59e0b;
    }

    .strength-text.strong {
        color: #10b981;
    }

    /* Alert Info */
    .alert-info {
        display: flex;
        gap: 12px;
        padding: 15px;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 6px;
        margin-bottom: 20px;
    }

    .alert-info i {
        font-size: 1.25rem;
        color: #3b82f6;
        flex-shrink: 0;
        margin-top: 2px;
    }

    .alert-info strong {
        font-size: 0.875rem;
        color: #1e40af;
        display: block;
        margin-bottom: 8px;
    }

    .alert-info ul {
        margin: 0;
        padding-right: 20px;
        font-size: 0.875rem;
        color: #1e3a8a;
    }

    .alert-info li {
        margin-bottom: 4px;
    }

    /* Form Actions */
    .form-actions {
        display: flex;
        justify-content: flex-end;
        padding-top: 15px;
        border-top: 1px solid #e5e7eb;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
        text-decoration: none;
        font-family: 'Almarai', sans-serif;
    }

    .btn-primary {
        background: #1a5f4a;
        color: white;
    }

    .btn-primary:hover {
        background: #164538;
    }

    /* Info Grid */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0;
    }

    .info-item {
        display: flex;
        flex-direction: column;
        gap: 5px;
        padding: 15px;
        border-right: 1px solid #f3f4f6;
        border-bottom: 1px solid #f3f4f6;
    }

    .info-item:nth-child(2n) {
        border-left: 1px solid #f3f4f6;
    }

    .info-label {
        font-size: 0.875rem;
        color: #6b7280;
        font-weight: 500;
    }

    .info-value {
        font-size: 0.95rem;
        color: #1f2937;
        font-weight: 600;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .badge-success {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-warning {
        background: #fef3c7;
        color: #92400e;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .dashboard-container {
            padding: 20px;
        }

        .form-row {
            grid-template-columns: 1fr;
        }

        .info-grid {
            grid-template-columns: 1fr;
        }

        .info-item:nth-child(2n) {
            border-left: none;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Toggle password visibility
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const icon = document.getElementById(fieldId + '_icon');

        if (field.type == 'password') {
            field.type = 'text';
            icon.classList.remove('ti-eye');
            icon.classList.add('ti-eye-off');
        } else {
            field.type = 'password';
            icon.classList.remove('ti-eye-off');
            icon.classList.add('ti-eye');
        }
    }
    window.togglePassword = togglePassword;

    // Password strength checker
    const newPasswordInput = document.getElementById('new_password');
    if (newPasswordInput) {
        newPasswordInput.addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');

            let strength = 0;
            let strengthLabel = 'ضعيفة';
            let strengthClass = 'weak';

            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;

            strengthBar.className = 'strength-bar-fill';
            strengthText.className = 'strength-text';

            if (strength == 0) {
                strengthLabel = 'غير محددة';
                strengthClass = '';
            } else if (strength <= 2) {
                strengthLabel = 'ضعيفة';
                strengthClass = 'weak';
            } else if (strength == 3) {
                strengthLabel = 'متوسطة';
                strengthClass = 'medium';
            } else {
                strengthLabel = 'قوية';
                strengthClass = 'strong';
            }

            if (strengthClass) {
                strengthBar.classList.add(strengthClass);
                strengthText.classList.add(strengthClass);
            }
            strengthText.textContent = strengthLabel;
        });
    }

    // Form submission confirmation for password change
    document.getElementById('passwordForm').addEventListener('submit', function(e) {
        e.preventDefault();

        Swal.fire({
            title: 'تأكيد تغيير كلمة المرور',
            text: 'هل أنت متأكد من تغيير كلمة المرور؟',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#1a5f4a',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'نعم، غير كلمة المرور',
            cancelButtonText: 'إلغاء',
            iconColor: '#1a5f4a'
        }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
        });
    });

    // Success/Error messages
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'تم بنجاح',
            text: '{{ session('success') }}',
            confirmButtonText: 'حسناً',
            confirmButtonColor: '#1a5f4a',
            iconColor: '#10b981',
            timer: 3000,
            timerProgressBar: true
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'خطأ',
            text: '{{ session('error') }}',
            confirmButtonText: 'حسناً',
            confirmButtonColor: '#1a5f4a',
            iconColor: '#ef4444'
        });
    @endif

    @if($errors->any())
        Swal.fire({
            icon: 'error',
            title: 'خطأ في البيانات',
            html: '<ul style="text-align: right; list-style: none; padding: 0;">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>',
            confirmButtonText: 'حسناً',
            confirmButtonColor: '#1a5f4a',
            iconColor: '#ef4444'
        });
    @endif
</script>
@endpush
