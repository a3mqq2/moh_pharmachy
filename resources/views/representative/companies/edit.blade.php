@extends('layouts.auth')

@section('title', 'تعديل بيانات الشركة')

@section('content')
<div class="company-form-container">
    <div class="form-header">
        <h1>تعديل بيانات الشركة</h1>
        <p>قم بتحديث البيانات المطلوبة</p>

        @if($company->status == 'rejected' && $company->rejection_reason)
        <div class="rejection-alert">
            <i class="ti ti-alert-circle"></i>
            <div>
                <strong>سبب الرفض:</strong>
                <p>{{ $company->rejection_reason }}</p>
            </div>
        </div>
        @endif
    </div>

    <!-- Progress Steps -->
    <div class="steps-progress">
        <div class="step active" data-step="1">
            <div class="step-number">1</div>
            <div class="step-label">نوع الشركة</div>
        </div>
        <div class="step-line"></div>
        <div class="step" data-step="2">
            <div class="step-number">2</div>
            <div class="step-label">بيانات الشركة</div>
        </div>
        <div class="step-line"></div>
        <div class="step" data-step="3">
            <div class="step-number">3</div>
            <div class="step-label">بيانات الترخيص</div>
        </div>
        <div class="step-line"></div>
        <div class="step" data-step="4">
            <div class="step-number">4</div>
            <div class="step-label">بيانات المدير</div>
        </div>
    </div>

    <form action="{{ route('representative.companies.update', $company) }}" method="POST" id="companyForm">
        @csrf
        @method('PUT')

        <!-- Step 1: Company Type Selection -->
        <div class="form-step active" data-step="1">
            <div class="company-type-selection">
                <h2 class="selection-title">اختر نوع الشركة</h2>
                <p class="selection-subtitle">حدد نوع شركتك للمتابعة</p>

                <div class="type-cards">
                    <label class="type-card" for="type_distributor">
                        <input type="radio" name="company_type" id="type_distributor" value="distributor" {{ old('company_type', $company->company_type) == 'distributor' ? 'checked' : '' }} required>
                        <div class="type-card-content">
                            <div class="type-icon">
                                <i class="ti ti-truck-delivery"></i>
                            </div>
                            <h3>شركة موزعة</h3>
                            <p>شركة تقوم بتوزيع المنتجات الطبية</p>
                        </div>
                    </label>

                    <label class="type-card" for="type_supplier">
                        <input type="radio" name="company_type" id="type_supplier" value="supplier" {{ old('company_type', $company->company_type) == 'supplier' ? 'checked' : '' }} required>
                        <div class="type-card-content">
                            <div class="type-icon">
                                <i class="ti ti-package"></i>
                            </div>
                            <h3>شركة موردة</h3>
                            <p>شركة تقوم بتوريد المنتجات الطبية</p>
                        </div>
                    </label>
                </div>
                @error('company_type')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Step 2: Company Information -->
        <div class="form-step" data-step="2">
            <h2 class="step-title">بيانات الشركة الأساسية</h2>

            <div class="form-row">
                <div class="form-group full-width">
                    <label for="company_name">اسم الشركة <span class="required">*</span></label>
                    <input type="text" name="company_name" id="company_name" class="form-control" value="{{ old('company_name', $company->company_name) }}" required>
                    @error('company_name')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="pre-registration-section">
                <div class="checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_pre_registered" id="is_pre_registered" value="1" {{ old('is_pre_registered', $company->is_pre_registered) ? 'checked' : '' }}>
                        <span>الشركة مسجلة مسبقاً قبل وجود النظام</span>
                    </label>
                </div>

                <div id="preRegistrationFields" class="pre-registration-fields" style="display: {{ old('is_pre_registered', $company->is_pre_registered) ? 'block' : 'none' }};">
                    <div class="alert-info-box">
                        <i class="ti ti-info-circle"></i>
                        <div>
                            <strong>ملاحظة هامة</strong>
                            <p>يرجى إدخال رقم القيد وسنة التسجيل الخاصة بالشركة المسجلة مسبقاً. سيتم التحقق من هذه البيانات من قبل الإدارة.</p>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="pre_registration_number">رقم القيد السابق <span class="required">*</span></label>
                            <input type="text" name="pre_registration_number" id="pre_registration_number" class="form-control" value="{{ old('pre_registration_number', $company->pre_registration_number) }}" placeholder="مثال: 2024/15">
                            @error('pre_registration_number')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="pre_registration_year">سنة التسجيل <span class="required">*</span></label>
                            <input type="number" name="pre_registration_year" id="pre_registration_year" class="form-control" value="{{ old('pre_registration_year', $company->pre_registration_year) }}" placeholder="مثال: 2024" min="1990" max="{{ date('Y') }}">
                            @error('pre_registration_year')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="city">المدينة <span class="required">*</span></label>
                    <input type="text" name="city" id="city" class="form-control" value="{{ old('city', $company->city) }}" required>
                    @error('city')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="street">الشارع</label>
                    <input type="text" name="street" id="street" class="form-control" value="{{ old('street', $company->street) }}">
                    @error('street')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="company_address">العنوان التفصيلي</label>
                <textarea name="company_address" id="company_address" class="form-control" rows="3">{{ old('company_address', $company->company_address) }}</textarea>
                @error('company_address')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="phone">رقم الهاتف <span class="required">*</span></label>
                    <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone', $company->phone) }}" required>
                    @error('phone')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="mobile">رقم الجوال</label>
                    <input type="text" name="mobile" id="mobile" class="form-control" value="{{ old('mobile', $company->mobile) }}">
                    @error('mobile')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="email">البريد الإلكتروني <span class="required">*</span></label>
                <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $company->email) }}" required>
                @error('email')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Step 3: License Information -->
        <div class="form-step" data-step="3">
            <h2 class="step-title">بيانات الترخيص والتسجيل</h2>

            <div class="form-row">
                <div class="form-group">
                    <label for="license_type">نوع الترخيص <span class="required">*</span></label>
                    <select name="license_type" id="license_type" class="form-control" required>
                        <option value="">اختر نوع الترخيص</option>
                        <option value="company" {{ old('license_type', $company->license_type) == 'company' ? 'selected' : '' }}>شركة</option>
                        <option value="partnership" {{ old('license_type', $company->license_type) == 'partnership' ? 'selected' : '' }}>تشاركية</option>
                        <option value="authorized_agent" {{ old('license_type', $company->license_type) == 'authorized_agent' ? 'selected' : '' }}>وكيل معتمد</option>
                    </select>
                    @error('license_type')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="license_specialty">تخصص الترخيص <span class="required">*</span></label>
                    <select name="license_specialty" id="license_specialty" class="form-control" required>
                        <option value="">اختر التخصص</option>
                        <option value="medicines" {{ old('license_specialty', $company->license_specialty) == 'medicines' ? 'selected' : '' }}>أدوية</option>
                        <option value="medical_supplies" {{ old('license_specialty', $company->license_specialty) == 'medical_supplies' ? 'selected' : '' }}>مستلزمات طبية</option>
                        <option value="medical_equipment" {{ old('license_specialty', $company->license_specialty) == 'medical_equipment' ? 'selected' : '' }}>معدات طبية</option>
                    </select>
                    @error('license_specialty')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="license_number">رقم الترخيص</label>
                    <input type="text" name="license_number" id="license_number" class="form-control" value="{{ old('license_number', $company->license_number) }}">
                    @error('license_number')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="license_issuer">جهة إصدار الترخيص</label>
                    <input type="text" name="license_issuer" id="license_issuer" class="form-control" value="{{ old('license_issuer', $company->license_issuer) }}">
                    @error('license_issuer')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="registration_date">تاريخ التسجيل</label>
                    <input type="date" name="registration_date" id="registration_date" class="form-control" value="{{ old('registration_date', $company->registration_date) }}">
                    @error('registration_date')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="chamber_of_commerce_number">رقم السجل التجاري</label>
                    <input type="text" name="chamber_of_commerce_number" id="chamber_of_commerce_number" class="form-control" value="{{ old('chamber_of_commerce_number', $company->chamber_of_commerce_number) }}">
                    @error('chamber_of_commerce_number')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="food_drug_registration_number">رقم التسجيل في هيئة الغذاء والدواء</label>
                <input type="text" name="food_drug_registration_number" id="food_drug_registration_number" class="form-control" value="{{ old('food_drug_registration_number', $company->food_drug_registration_number) }}">
                @error('food_drug_registration_number')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Step 4: Manager Information -->
        <div class="form-step" data-step="4">
            <h2 class="step-title">بيانات المدير المسؤول</h2>

            <div class="form-row">
                <div class="form-group">
                    <label for="manager_name">اسم المدير <span class="required">*</span></label>
                    <input type="text" name="manager_name" id="manager_name" class="form-control" value="{{ old('manager_name', $company->manager_name) }}" required>
                    @error('manager_name')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="manager_position">المنصب الوظيفي</label>
                    <input type="text" name="manager_position" id="manager_position" class="form-control" value="{{ old('manager_position', $company->manager_position) }}">
                    @error('manager_position')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="manager_phone">رقم هاتف المدير <span class="required">*</span></label>
                    <input type="text" name="manager_phone" id="manager_phone" class="form-control" value="{{ old('manager_phone', $company->manager_phone) }}" required>
                    @error('manager_phone')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="manager_email">البريد الإلكتروني للمدير</label>
                    <input type="email" name="manager_email" id="manager_email" class="form-control" value="{{ old('manager_email', $company->manager_email) }}">
                    @error('manager_email')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="info-box">
                <i class="ti ti-info-circle"></i>
                <div>
                    <strong>ملاحظة:</strong> بعد إتمام التسجيل، سيتم مراجعة البيانات من قبل الإدارة. سيتم إشعارك بحالة الطلب عبر البريد الإلكتروني.
                </div>
            </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="form-navigation">
            <button type="button" class="btn btn-secondary" id="prevBtn" style="display: none;">
                <i class="ti ti-arrow-right"></i>
                السابق
            </button>
            <button type="button" class="btn btn-primary" id="nextBtn">
                التالي
                <i class="ti ti-arrow-left"></i>
            </button>
            <button type="submit" class="btn btn-success" id="submitBtn" style="display: none;">
                <i class="ti ti-check"></i>
                تسجيل الشركة
            </button>
            <a href="{{ route('representative.dashboard') }}" class="btn btn-outline">إلغاء</a>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
    .auth-form {
        width: 100%;
        max-width: 1100px;
        padding: 0 20px;
    }

    .company-form-container {
        background: #ffffff;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        padding: 40px;
    }

    .form-header {
        text-align: center;
        margin-bottom: 40px;
    }

    .form-header h1 {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1a5f4a;
        margin: 0 0 8px 0;
    }

    .form-header p {
        font-size: 0.95rem;
        color: #6b7280;
        margin: 0;
    }

    .rejection-alert {
        background: #fef2f2;
        border: 2px solid #fecaca;
        border-radius: 8px;
        padding: 16px;
        margin: 20px 0;
        display: flex;
        gap: 12px;
        align-items: flex-start;
    }

    .rejection-alert i {
        font-size: 1.5rem;
        color: #dc2626;
        flex-shrink: 0;
        margin-top: 2px;
    }

    .rejection-alert strong {
        font-size: 0.95rem;
        color: #991b1b;
        display: block;
        margin-bottom: 6px;
    }

    .rejection-alert p {
        font-size: 0.9rem;
        color: #7f1d1d;
        margin: 0;
        line-height: 1.6;
    }

    /* Steps Progress */
    .steps-progress {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 50px;
        padding: 0 20px;
    }

    .step {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        flex: 0 0 auto;
    }

    .step-number {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: #e5e7eb;
        color: #6b7280;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.125rem;
        transition: all 0.3s;
        border: 3px solid transparent;
    }

    .step.active .step-number {
        background: #1a5f4a;
        color: white;
        border-color: #1a5f4a;
        box-shadow: 0 0 0 4px rgba(26, 95, 74, 0.1);
    }

    .step.completed .step-number {
        background: #10b981;
        color: white;
    }

    .step-label {
        font-size: 0.875rem;
        color: #6b7280;
        font-weight: 500;
        text-align: center;
    }

    .step.active .step-label {
        color: #1a5f4a;
        font-weight: 700;
    }

    .step-line {
        flex: 1;
        height: 3px;
        background: #e5e7eb;
        margin: 0 15px;
    }

    /* Company Type Selection */
    .company-type-selection {
        text-align: center;
        padding: 40px 20px;
    }

    .selection-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1f2937;
        margin: 0 0 10px 0;
    }

    .selection-subtitle {
        font-size: 1rem;
        color: #6b7280;
        margin: 0 0 40px 0;
    }

    .type-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 25px;
        max-width: 700px;
        margin: 0 auto;
    }

    .type-card {
        cursor: pointer;
        position: relative;
    }

    .type-card input[type="radio"] {
        position: absolute;
        opacity: 0;
    }

    .type-card-content {
        padding: 40px 30px;
        border: 3px solid #e5e7eb;
        border-radius: 12px;
        background: #ffffff;
        transition: all 0.3s ease;
    }

    .type-card:hover .type-card-content {
        border-color: #1a5f4a;
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(26, 95, 74, 0.15);
    }

    .type-card input[type="radio"]:checked + .type-card-content {
        border-color: #1a5f4a;
        background: #f0fdf4;
        box-shadow: 0 10px 30px rgba(26, 95, 74, 0.2);
    }

    .type-icon {
        width: 80px;
        height: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
    }

    .type-icon i {
        font-size: 3.5rem;
        color: #1a5f4a;
    }

    .type-card input[type="radio"]:checked + .type-card-content .type-icon i {
        color: #1a5f4a;
    }

    .type-card-content h3 {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1f2937;
        margin: 0 0 10px 0;
    }

    .type-card-content p {
        font-size: 0.9rem;
        color: #6b7280;
        margin: 0;
    }

    /* Form Steps */
    .form-step {
        display: none;
        min-height: 400px;
    }

    .form-step.active {
        display: block;
        animation: fadeIn 0.3s;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .step-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1f2937;
        margin: 0 0 30px 0;
        padding-bottom: 15px;
        border-bottom: 2px solid #e5e7eb;
    }

    /* Form Styles */
    .form-row {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 25px;
        margin-bottom: 20px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group.full-width {
        grid-column: 1 / -1;
    }

    .form-group label {
        display: block;
        font-size: 0.9rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
    }

    .required {
        color: #dc2626;
    }

    .form-control {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #d1d5db;
        border-radius: 8px;
        font-size: 0.95rem;
        font-family: 'Almarai', sans-serif;
        transition: all 0.2s;
        background: #ffffff;
    }

    .form-control:focus {
        outline: none;
        border-color: #1a5f4a;
        box-shadow: 0 0 0 3px rgba(26, 95, 74, 0.1);
    }

    textarea.form-control {
        resize: vertical;
    }

    .error-message {
        display: block;
        color: #dc2626;
        font-size: 0.8rem;
        margin-top: 6px;
    }

    /* Info Box */
    .info-box {
        display: flex;
        gap: 15px;
        padding: 20px;
        background: #eff6ff;
        border: 1px solid #3b82f6;
        border-radius: 8px;
        margin-top: 30px;
    }

    .info-box i {
        font-size: 1.5rem;
        color: #3b82f6;
        flex-shrink: 0;
    }

    .info-box div {
        font-size: 0.9rem;
        color: #1e40af;
        line-height: 1.6;
    }

    /* Navigation */
    .form-navigation {
        display: flex;
        gap: 15px;
        justify-content: flex-end;
        margin-top: 40px;
        padding-top: 30px;
        border-top: 2px solid #e5e7eb;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 28px;
        border-radius: 8px;
        font-size: 0.95rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
        font-family: 'Almarai', sans-serif;
    }

    .btn-primary {
        background: #1a5f4a;
        color: white;
    }

    .btn-primary:hover {
        background: #164538;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(26, 95, 74, 0.3);
    }

    .btn-secondary {
        background: #6b7280;
        color: white;
    }

    .btn-secondary:hover {
        background: #4b5563;
    }

    .btn-success {
        background: #16a34a;
        color: white;
    }

    .btn-success:hover {
        background: #15803d;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(22, 163, 74, 0.3);
    }

    .btn-outline {
        background: white;
        color: #374151;
        border: 2px solid #d1d5db;
        text-decoration: none;
    }

    .btn-outline:hover {
        background: #f9fafb;
        border-color: #9ca3af;
    }

    @media (max-width: 768px) {
        .company-form-container {
            padding: 25px 20px;
        }

        .steps-progress {
            overflow-x: auto;
            padding: 0 10px;
        }

        .step-label {
            font-size: 0.75rem;
        }

        .step-number {
            width: 40px;
            height: 40px;
            font-size: 1rem;
        }

        .type-cards {
            grid-template-columns: 1fr;
        }

        .form-row {
            grid-template-columns: 1fr;
        }

        .form-navigation {
            flex-wrap: wrap;
        }

        .btn {
            flex: 1 1 auto;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Show validation errors on page load if any
    @if($errors->any())
        Swal.fire({
            icon: 'error',
            title: 'خطأ في البيانات',
            html: '<ul style="text-align: right; list-style: none; padding: 0;">' +
                @foreach($errors->all() as $error)
                    '<li style="margin: 8px 0;">• {{ $error }}</li>' +
                @endforeach
            '</ul>',
            confirmButtonText: 'حسناً',
            confirmButtonColor: '#1a5f4a',
            iconColor: '#ef4444'
        });
    @endif

    let currentStep = 1;
    const totalSteps = 4;

    function showStep(step) {
        document.querySelectorAll('.form-step').forEach(el => {
            el.classList.remove('active');
        });

        document.querySelector(`.form-step[data-step="${step}"]`).classList.add('active');

        document.querySelectorAll('.step').forEach((el, index) => {
            const stepNum = index + 1;
            if (stepNum < step) {
                el.classList.add('completed');
                el.classList.remove('active');
            } else if (stepNum == step) {
                el.classList.add('active');
                el.classList.remove('completed');
            } else {
                el.classList.remove('active', 'completed');
            }
        });

        document.getElementById('prevBtn').style.display = step == 1 ? 'none' : 'inline-flex';
        document.getElementById('nextBtn').style.display = step == totalSteps ? 'none' : 'inline-flex';
        document.getElementById('submitBtn').style.display = step == totalSteps ? 'inline-flex' : 'none';

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function validateStep(step) {
        const currentStepEl = document.querySelector(`.form-step[data-step="${step}"]`);
        const requiredInputs = currentStepEl.querySelectorAll('[required]');
        let isValid = true;
        let missingFields = [];

        requiredInputs.forEach(input => {
            if (input.type == 'radio') {
                const radioGroup = currentStepEl.querySelectorAll(`[name="${input.name}"]`);
                const isChecked = Array.from(radioGroup).some(radio => radio.checked);
                if (!isChecked) {
                    isValid = false;
                    missingFields.push('نوع الشركة');
                }
            } else if (!input.value.trim()) {
                input.style.borderColor = '#dc2626';
                isValid = false;
                const label = currentStepEl.querySelector(`label[for="${input.id}"]`);
                if (label) {
                    missingFields.push(label.textContent.replace('*', '').trim());
                }
            } else {
                input.style.borderColor = '#d1d5db';
            }
        });

        return { isValid, missingFields };
    }

    document.getElementById('nextBtn').addEventListener('click', function() {
        const validation = validateStep(currentStep);
        if (validation.isValid) {
            currentStep++;
            showStep(currentStep);
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'حقول مطلوبة',
                text: 'يرجى ملء جميع الحقول المطلوبة للمتابعة',
                confirmButtonText: 'حسناً',
                confirmButtonColor: '#1a5f4a',
                iconColor: '#f59e0b'
            });
        }
    });

    document.getElementById('prevBtn').addEventListener('click', function() {
        currentStep--;
        showStep(currentStep);
    });

    // Form submission confirmation
    document.getElementById('companyForm').addEventListener('submit', function(e) {
        e.preventDefault();

        Swal.fire({
            title: 'تأكيد التسجيل',
            text: 'هل أنت متأكد من صحة جميع البيانات المدخلة؟',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#1a5f4a',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'نعم، تسجيل الشركة',
            cancelButtonText: 'مراجعة البيانات',
            iconColor: '#1a5f4a'
        }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
        });
    });

    showStep(1);

    document.getElementById('is_pre_registered').addEventListener('change', function() {
        const preRegFields = document.getElementById('preRegistrationFields');
        const preRegNumber = document.getElementById('pre_registration_number');
        const preRegYear = document.getElementById('pre_registration_year');

        if (this.checked) {
            preRegFields.style.display = 'block';
            preRegNumber.required = true;
            preRegYear.required = true;
        } else {
            preRegFields.style.display = 'none';
            preRegNumber.required = false;
            preRegYear.required = false;
            preRegNumber.value = '';
            preRegYear.value = '';
        }
    });
</script>
@endpush
