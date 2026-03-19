@extends('layouts.auth')

@section('title', 'تعديل بيانات الشركة الأجنبية')

@section('content')
<div class="dashboard-container">
    <div class="page-header">
        <div class="page-header-content">
            <a href="{{ route('representative.foreign-companies.show', $company) }}" class="back-to-home">
                <i class="ti ti-arrow-right"></i>
            </a>
            <div>
                <h1>تعديل بيانات الشركة الأجنبية</h1>
                <p>قم بتعديل البيانات المطلوبة للشركة: {{ $company->company_name }}</p>
            </div>
        </div>
    </div>

    

    <div class="form-container">
        <!-- Steps Indicator -->
        <div class="steps-indicator">
            <div class="step active" data-step="1">
                <div class="step-number">1</div>
                <div class="step-title">الشركة المحلية</div>
            </div>
            <div class="step-line"></div>
            <div class="step" data-step="2">
                <div class="step-number">2</div>
                <div class="step-title">معلومات الشركة</div>
            </div>
            <div class="step-line"></div>
            <div class="step" data-step="3">
                <div class="step-number">3</div>
                <div class="step-title">المنتجات والتسجيل</div>
            </div>
            <div class="step-line"></div>
            <div class="step" data-step="4">
                <div class="step-number">4</div>
                <div class="step-title">المراجعة والتأكيد</div>
            </div>
        </div>

        <form action="{{ route('representative.foreign-companies.update', $company) }}" method="POST" id="foreignCompanyForm">
            @csrf
            @method('PUT')

            <!-- Step 1: الشركة المحلية (الوكيل) -->
            <div class="step-content active" data-step="1">
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="ti ti-building"></i>
                        الشركة المحلية (الوكيل)
                    </h3>
                    <div class="form-group">
                        <label for="local_company_id" class="required">اختر الشركة المحلية الموردة</label>
                        <select name="local_company_id" id="local_company_id" class="form-control select2" required>
                            <option value="">-- اختر الشركة --</option>
                            @foreach($localCompanies as $localComp)
                                <option value="{{ $localComp->id }}" {{ (old('local_company_id') ?? $company->local_company_id) == $localComp->id ? 'selected' : '' }}>
                                    {{ $localComp->company_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('local_company_id')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                        <small class="help-text">يجب أن تكون الشركة المحلية من نوع "مورد" ومفعلة</small>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="section-title">
                        <i class="ti ti-history"></i>
                        التسجيل المسبق
                    </h3>
                    <div class="checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_pre_registered" id="is_pre_registered" value="1" {{ (old('is_pre_registered') ?? $company->is_pre_registered) ? 'checked' : '' }}>
                            <span>الشركة مسجلة من قبل</span>
                        </label>
                    </div>

                    @php
                        $preRegParts = $company->pre_registration_number ? explode('-', $company->pre_registration_number) : [null, null];
                        $preRegYear = old('pre_registration_year') ?? ($preRegParts[0] ?? '');
                        $preRegSeq = old('pre_registration_sequence') ?? ($preRegParts[1] ?? '');
                    @endphp

                    <div id="preRegistrationFields" class="pre-registration-fields" style="display: {{ (old('is_pre_registered') ?? $company->is_pre_registered) ? 'block' : 'none' }};">
                        <div class="alert-info-box">
                            <i class="ti ti-info-circle"></i>
                            <div>
                                <strong>ملاحظة هامة</strong>
                                <p>يرجى إدخال رقم القيد وسنة التسجيل الخاصة بالشركة المسجلة مسبقاً. سيتم التحقق من هذه البيانات من قبل الإدارة.</p>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="pre_registration_year">سنة التسجيل <span class="text-danger">*</span></label>
                                <input type="number" name="pre_registration_year" id="pre_registration_year" class="form-control" value="{{ $preRegYear }}" placeholder="مثال: 2024" min="1990" max="{{ date('Y') }}">
                                @error('pre_registration_year')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="pre_registration_sequence">الرقم التسلسلي <span class="text-danger">*</span></label>
                                <input type="number" name="pre_registration_sequence" id="pre_registration_sequence" class="form-control" value="{{ $preRegSeq }}" placeholder="مثال: 15" min="1">
                                @error('pre_registration_sequence')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group">
                            <small>رقم القيد: <strong id="preRegPreview">-</strong></small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 2: معلومات الشركة الأساسية -->
            <div class="step-content" data-step="2">
                <div class="form-section">
                <h3 class="section-title">
                    <i class="ti ti-info-circle"></i>
                    معلومات الشركة الأساسية
                </h3>

                <div class="form-row">
                    <div class="form-group">
                        <label for="company_name" class="required">اسم الشركة الأجنبية</label>
                        <input type="text" name="company_name" id="company_name" class="form-control"
                               value="{{ old('company_name') ?? $company->company_name }}" required>
                        @error('company_name')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="country" class="required">الدولة</label>
                        <select name="country" id="country" class="form-control select2-tags" required>
                            <option value="">-- اختر أو اكتب اسم الدولة --</option>
                            @foreach($countries as $country)
                                <option value="{{ $country }}" {{ (old('country') ?? $company->country) == $country ? 'selected' : '' }}>
                                    {{ $country }}
                                </option>
                            @endforeach
                        </select>
                        @error('country')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                        <small class="help-text">يمكنك الاختيار من القائمة أو كتابة اسم الدولة</small>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="entity_type" class="required">نوع الكيان</label>
                        <select name="entity_type" id="entity_type" class="form-control" required>
                            <option value="">-- اختر نوع الكيان --</option>
                            <option value="company" {{ (old('entity_type') ?? $company->entity_type) == 'company' ? 'selected' : '' }}>شركة</option>
                            <option value="factory" {{ (old('entity_type') ?? $company->entity_type) == 'factory' ? 'selected' : '' }}>مصنع</option>
                        </select>
                        @error('entity_type')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="activity_type" class="required">نوع النشاط</label>
                        <select name="activity_type" id="activity_type" class="form-control" required>
                            <option value="">-- اختر نوع النشاط --</option>
                            <option value="medicines" {{ (old('activity_type') ?? $company->activity_type) == 'medicines' ? 'selected' : '' }}>أدوية</option>
                            <option value="medical_supplies" {{ (old('activity_type') ?? $company->activity_type) == 'medical_supplies' ? 'selected' : '' }}>مستلزمات طبية</option>
                            <option value="both" {{ (old('activity_type') ?? $company->activity_type) == 'both' ? 'selected' : '' }}>أدوية ومستلزمات طبية</option>
                        </select>
                        @error('activity_type')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="email" class="required">البريد الإلكتروني للشركة</label>
                    <input type="email" name="email" id="email" class="form-control"
                           value="{{ old('email') ?? $company->email }}" required>
                    @error('email')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="address" class="required">عنوان الشركة</label>
                    <textarea name="address" id="address" class="form-control" rows="3" required>{{ old('address') ?? $company->address }}</textarea>
                    @error('address')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                </div>
            </div>

            <!-- Step 3: معلومات المنتجات والتسجيل -->
            <div class="step-content" data-step="3">
                <div class="form-section">
                <h3 class="section-title">
                    <i class="ti ti-package"></i>
                    معلومات المنتجات والتسجيل
                </h3>

                <div class="form-group">
                    <label for="products_count" class="required">عدد المنتجات المراد تسجيلها</label>
                    <input type="number" name="products_count" id="products_count" class="form-control"
                           value="{{ old('products_count') ?? $company->products_count }}" min="1" required>
                    @error('products_count')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="registered_countries">الدول المسجلة بها الشركة أو المصنع</label>
                    <select name="registered_countries[]" id="registered_countries" class="form-control select2-tags-multiple" multiple>
                        @foreach($countries as $country)
                            <option value="{{ $country }}"
                                {{ (is_array(old('registered_countries')) ? in_array($country, old('registered_countries')) : (is_array($company->registered_countries) && in_array($country, $company->registered_countries))) ? 'selected' : '' }}>
                                {{ $country }}
                            </option>
                        @endforeach
                    </select>
                    @error('registered_countries')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                    <small class="help-text">يمكنك الاختيار من القائمة أو كتابة أسماء الدول (يمكنك اختيار أو كتابة أكثر من دولة)</small>
                </div>
                </div>
            </div>

            <!-- Step 4: المراجعة والتأكيد -->
            <div class="step-content" data-step="4">
                <!-- ملاحظات إلزامية -->
                <div class="alert alert-info">
                <h4><i class="ti ti-info-circle"></i> ملاحظات إلزامية:</h4>
                <ul>
                    <li>يجب أن تكون جميع الشهادات موثقة من قبل الجهات الصحية في بلد المنشأ</li>
                    <li>يجب أن تكون جميع الشهادات موثقة من قبل السفارة الليبية أو ممثلها</li>
                    <li>يجب ترجمة المستندات باللغات الأجنبية ترجمة قانونية معتمدة</li>
                    <li>يجب تقديم جميع المستندات من خلال شركة استيراد محلية مسجلة</li>
                </ul>
                </div>

                <div class="review-section">
                    <h3 class="section-title">
                        <i class="ti ti-check-circle"></i>
                        مراجعة البيانات المدخلة
                    </h3>
                    <div class="review-content">
                        <p class="text-muted">يرجى مراجعة جميع البيانات قبل الحفظ. بعد حفظ التعديلات، تأكد من رفع جميع المستندات المطلوبة لإعادة التقديم.</p>
                    </div>
                </div>
            </div>

            <!-- أزرار التنقل بين الخطوات -->
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" id="prevBtn" style="display:none;">
                    <i class="ti ti-arrow-right"></i>
                    السابق
                </button>
                <div style="flex-grow: 1;"></div>
                <a href="{{ route('representative.foreign-companies.index') }}" class="btn btn-secondary">
                    <i class="ti ti-x"></i>
                    إلغاء
                </a>
                <button type="button" class="btn btn-primary" id="nextBtn">
                    التالي
                    <i class="ti ti-arrow-left"></i>
                </button>
                <button type="submit" class="btn btn-primary" id="submitBtn" style="display:none;">
                    <i class="ti ti-check"></i>
                    حفظ التعديلات
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .auth-form {
        width: 100%;
        max-width: 1000px;
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
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f3f4f6;
        border-radius: 8px;
        color: #374151;
        text-decoration: none;
        transition: all 0.2s;
    }

    .back-to-home:hover {
        background: #e5e7eb;
    }

    .page-header h1 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1a5f4a;
        margin: 0 0 5px 0;
    }

    .page-header p {
        font-size: 0.875rem;
        color: #6b7280;
        margin: 0;
    }

    .form-container {
        max-width: 900px;
        margin: 0 auto;
    }

    /* Steps Indicator */
    .steps-indicator {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 40px;
        padding: 20px;
        background: #f9fafb;
        border-radius: 8px;
    }

    .step {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        position: relative;
    }

    .step-number {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #e5e7eb;
        color: #6b7280;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    .step.active .step-number {
        background: #1a5f4a;
        color: #ffffff;
    }

    .step.completed .step-number {
        background: #10b981;
        color: #ffffff;
    }

    .step-title {
        font-size: 0.75rem;
        font-weight: 600;
        color: #6b7280;
        text-align: center;
        transition: all 0.3s ease;
    }

    .step.active .step-title {
        color: #1a5f4a;
    }

    .step.completed .step-title {
        color: #10b981;
    }

    .step-line {
        width: 60px;
        height: 2px;
        background: #e5e7eb;
        margin: 0 10px;
        transition: all 0.3s ease;
    }

    .step.completed + .step-line {
        background: #10b981;
    }

    /* Step Content */
    .step-content {
        display: none;
    }

    .step-content.active {
        display: block;
        animation: fadeIn 0.3s ease;
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

    .review-section {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 25px;
        margin-top: 20px;
    }

    .review-content {
        padding: 15px 0;
    }

    .text-muted {
        color: #6b7280;
        font-size: 0.875rem;
        line-height: 1.6;
    }

    .form-section {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 25px;
        margin-bottom: 25px;
    }

    .section-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1a5f4a;
        margin: 0 0 20px 0;
        padding-bottom: 10px;
        border-bottom: 2px solid #e5e7eb;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-title i {
        font-size: 1.25rem;
    }

    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        font-size: 0.875rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
    }

    .form-group label.required::after {
        content: ' *';
        color: #ef4444;
    }

    .form-control {
        width: 100%;
        padding: 10px 14px;
        font-size: 0.875rem;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        background: #ffffff;
        transition: all 0.2s;
        font-family: 'Almarai', sans-serif;
    }

    .form-control:focus {
        outline: none;
        border-color: #1a5f4a;
        box-shadow: 0 0 0 3px rgba(26, 95, 74, 0.1);
    }

    textarea.form-control {
        resize: vertical;
        min-height: 80px;
    }

    .error-message {
        display: block;
        color: #ef4444;
        font-size: 0.75rem;
        margin-top: 5px;
    }

    .help-text {
        display: block;
        color: #6b7280;
        font-size: 0.75rem;
        margin-top: 5px;
    }

    .alert {
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 25px;
    }

    .alert-info {
        background: #dbeafe;
        border: 1px solid #93c5fd;
        color: #1e40af;
    }

    .alert h4 {
        font-size: 1rem;
        font-weight: 600;
        margin: 0 0 15px 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .alert ul {
        margin: 0;
        padding-right: 20px;
    }

    .alert li {
        margin-bottom: 8px;
        font-size: 0.875rem;
    }

    .alert li:last-child {
        margin-bottom: 0;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 15px;
        padding-top: 20px;
        border-top: 1px solid #e5e7eb;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        font-size: 0.9rem;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s ease;
        font-family: 'Almarai', sans-serif;
    }

    .btn-primary {
        background: #1a5f4a;
        color: #ffffff;
    }

    .btn-primary:hover {
        background: #164538;
    }

    .btn-secondary {
        background: #ffffff;
        color: #374151;
        border: 1px solid #d1d5db;
    }

    .btn-secondary:hover {
        background: #f9fafb;
    }

    /* Pre-registration */
    .checkbox-group {
        margin-bottom: 15px;
    }

    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        font-size: 0.9rem;
        font-weight: 600;
        color: #374151;
    }

    .checkbox-label input[type="checkbox"] {
        width: 18px;
        height: 18px;
        accent-color: #1a5f4a;
    }

    .pre-registration-fields {
        margin-top: 15px;
        padding: 15px;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
    }

    .alert-info-box {
        display: flex;
        gap: 10px;
        padding: 12px 15px;
        background: #dbeafe;
        border: 1px solid #93c5fd;
        border-radius: 6px;
        margin-bottom: 15px;
        color: #1e40af;
        font-size: 0.825rem;
        align-items: flex-start;
    }

    .alert-info-box i {
        font-size: 1.2rem;
        margin-top: 2px;
    }

    .alert-info-box strong {
        display: block;
        margin-bottom: 3px;
    }

    .alert-info-box p {
        margin: 0;
        line-height: 1.5;
    }

    .text-danger {
        color: #ef4444;
    }

    /* Select2 Customization */
    .select2-container {
        width: 100% !important;
    }

    .select2-container--default .select2-selection--single,
    .select2-container--default .select2-selection--multiple {
        border: 1px solid #d1d5db;
        border-radius: 6px;
        min-height: 46px;
        padding: 4px 8px;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px;
        padding-right: 14px;
        font-size: 0.9rem;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 44px;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__rendered {
        padding: 2px 8px;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #1a5f4a;
        border: 1px solid #164538;
        color: #ffffff;
        padding: 4px 10px;
        font-size: 0.875rem;
        margin: 3px 5px 3px 0;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: #ffffff;
        margin-left: 5px;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        color: #fef3c7;
    }

    .select2-dropdown {
        border: 1px solid #d1d5db;
        border-radius: 6px;
    }

    .select2-container--default .select2-results__option {
        padding: 10px 14px;
        font-size: 0.9rem;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #1a5f4a;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }

        .form-actions {
            flex-direction: column;
        }

        .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    console.log('Script loaded');
    console.log('jQuery available:', typeof jQuery != 'undefined');

    // Wait for DOM to be ready
    (function() {
        'use strict';

        // Function to initialize everything
        function initializeForm() {
            console.log('Initializing form...');

            // Check if jQuery is available
            if (typeof jQuery == 'undefined') {
                console.error('jQuery is not loaded!');
                return;
            }

            const $ = jQuery;

            // Initialize regular Select2
            $('.select2').select2({
                dir: "rtl",
                language: {
                    noResults: function() {
                        return "لا توجد نتائج";
                    },
                    searching: function() {
                        return "جاري البحث...";
                    }
                }
            });

            // Initialize Select2 with tags for single select (country)
            $('.select2-tags').select2({
                dir: "rtl",
                tags: true,
                createTag: function (params) {
                    var term = $.trim(params.term);
                    if (term == '') {
                        return null;
                    }
                    return {
                        id: term,
                        text: term,
                        newTag: true
                    };
                },
                language: {
                    noResults: function() {
                        return "لا توجد نتائج - يمكنك كتابة اسم الدولة";
                    },
                    searching: function() {
                        return "جاري البحث...";
                    }
                },
                placeholder: "اختر أو اكتب اسم الدولة"
            });

            // Initialize Select2 with tags for multiple select (registered countries)
            $('.select2-tags-multiple').select2({
                dir: "rtl",
                tags: true,
                tokenSeparators: [','],
                createTag: function (params) {
                    var term = $.trim(params.term);
                    if (term == '') {
                        return null;
                    }
                    return {
                        id: term,
                        text: term,
                        newTag: true
                    };
                },
                language: {
                    noResults: function() {
                        return "لا توجد نتائج - يمكنك كتابة اسم الدولة";
                    },
                    searching: function() {
                        return "جاري البحث...";
                    }
                },
                placeholder: "اختر أو اكتب أسماء الدول"
            });

            console.log('Select2 initialized');

            // Multi-step form functionality
            let currentStep = 1;
            const totalSteps = 4;

            function showStep(step) {
                console.log('Showing step:', step);

                // Hide all steps
                $('.step-content').removeClass('active');
                $('.step').removeClass('active completed');

                // Show current step
                $(`.step-content[data-step="${step}"]`).addClass('active');
                $(`.step[data-step="${step}"]`).addClass('active');

                // Mark previous steps as completed
                for (let i = 1; i < step; i++) {
                    $(`.step[data-step="${i}"]`).addClass('completed');
                }

                // Update buttons
                const prevBtn = $('#prevBtn');
                const nextBtn = $('#nextBtn');
                const submitBtn = $('#submitBtn');

                console.log('Buttons found:', {
                    prev: prevBtn.length,
                    next: nextBtn.length,
                    submit: submitBtn.length
                });

                if (step == 1) {
                    prevBtn.hide();
                } else {
                    prevBtn.show();
                }

                if (step == totalSteps) {
                    nextBtn.hide();
                    submitBtn.show();
                } else {
                    nextBtn.show();
                    submitBtn.hide();
                }

                // Scroll to top
                $('html, body').animate({ scrollTop: 0 }, 300);
            }

            // Attach click handlers
            const nextBtn = document.getElementById('nextBtn');
            const prevBtn = document.getElementById('prevBtn');

            console.log('Button elements:', { nextBtn, prevBtn });

            if (nextBtn) {
                nextBtn.addEventListener('click', function() {
                    console.log('Next button clicked! Current step:', currentStep);
                    if (currentStep < totalSteps) {
                        currentStep++;
                        showStep(currentStep);
                    }
                });
                console.log('Next button click handler attached');
            } else {
                console.error('Next button not found!');
            }

            if (prevBtn) {
                prevBtn.addEventListener('click', function() {
                    console.log('Previous button clicked! Current step:', currentStep);
                    if (currentStep > 1) {
                        currentStep--;
                        showStep(currentStep);
                    }
                });
                console.log('Previous button click handler attached');
            } else {
                console.error('Previous button not found!');
            }

            // Pre-registration toggle
            var preRegCheckbox = document.getElementById('is_pre_registered');
            var preRegFields = document.getElementById('preRegistrationFields');
            var preRegYear = document.getElementById('pre_registration_year');
            var preRegSeq = document.getElementById('pre_registration_sequence');
            var preRegPreview = document.getElementById('preRegPreview');

            if (preRegCheckbox) {
                preRegCheckbox.addEventListener('change', function() {
                    preRegFields.style.display = this.checked ? 'block' : 'none';
                    if (!this.checked) {
                        preRegYear.value = '';
                        preRegSeq.value = '';
                        preRegPreview.textContent = '-';
                        preRegYear.removeAttribute('required');
                        preRegSeq.removeAttribute('required');
                    } else {
                        preRegYear.setAttribute('required', 'required');
                        preRegSeq.setAttribute('required', 'required');
                    }
                });
            }

            function updatePreRegPreview() {
                var year = preRegYear ? preRegYear.value : '';
                var seq = preRegSeq ? preRegSeq.value : '';
                if (year && seq) {
                    preRegPreview.textContent = year + '-' + seq;
                } else {
                    preRegPreview.textContent = '-';
                }
            }

            if (preRegYear) preRegYear.addEventListener('input', updatePreRegPreview);
            if (preRegSeq) preRegSeq.addEventListener('input', updatePreRegPreview);
            updatePreRegPreview();

            // Initialize first step
            showStep(1);
            console.log('Form initialization complete');
        }

        // Wait for DOM and jQuery to be ready
        if (document.readyState == 'loading') {
            document.addEventListener('DOMContentLoaded', initializeForm);
        } else {
            // DOM is already loaded, check if jQuery is ready
            if (typeof jQuery != 'undefined') {
                initializeForm();
            } else {
                // Wait a bit for jQuery to load
                setTimeout(function() {
                    initializeForm();
                }, 100);
            }
        }
    })();
</script>
@endpush
