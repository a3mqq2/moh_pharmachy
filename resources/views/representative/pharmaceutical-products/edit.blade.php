@extends('layouts.auth')

@section('title', 'تعديل بيانات الصنف الدوائي')

@section('content')
<div class="dashboard-container">
    <div class="page-header">
        <div class="page-header-content">
            <a href="{{ route('representative.pharmaceutical-products.show', $pharmaceuticalProduct) }}" class="back-to-home">
                <i class="ti ti-arrow-right"></i>
            </a>
            <div>
                <h1>تعديل بيانات الصنف الدوائي</h1>
                <p>تعديل بيانات: {{ $pharmaceuticalProduct->product_name }}</p>
            </div>
        </div>
    </div>



    <form action="{{ route('representative.pharmaceutical-products.update', $pharmaceuticalProduct) }}" method="POST" class="product-form">
        @csrf
        @method('PUT')

        <div class="form-section">
            <h3>معلومات الصنف الدوائي</h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="product_name">الاسم التجاري <span class="required">*</span></label>
                    <input type="text" id="product_name" name="product_name" class="form-control @error('product_name') is-invalid @enderror" value="{{ old('product_name', $pharmaceuticalProduct->product_name) }}" required>
                    @error('product_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="scientific_name">الاسم العلمي <span class="required">*</span></label>
                    <input type="text" id="scientific_name" name="scientific_name" class="form-control @error('scientific_name') is-invalid @enderror" value="{{ old('scientific_name', $pharmaceuticalProduct->scientific_name) }}" required>
                    @error('scientific_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="pharmaceutical_form">الشكل الصيدلاني <span class="required">*</span></label>
                    <input type="text" id="pharmaceutical_form" name="pharmaceutical_form" class="form-control @error('pharmaceutical_form') is-invalid @enderror" value="{{ old('pharmaceutical_form', $pharmaceuticalProduct->pharmaceutical_form) }}" placeholder="مثال: أقراص، كبسولات، شراب..." required>
                    @error('pharmaceutical_form')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="concentration">التركيز / العيار <span class="required">*</span></label>
                    <input type="text" id="concentration" name="concentration" class="form-control @error('concentration') is-invalid @enderror" value="{{ old('concentration', $pharmaceuticalProduct->concentration) }}" placeholder="مثال: 500 مجم، 10 مل..." required>
                    @error('concentration')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            @php
                $currentMethods = old('usage_methods', $pharmaceuticalProduct->usage_methods ?? []);
            @endphp

            <div class="form-group">
                <label>طريقة الاستعمال <span class="required">*</span></label>
                <div class="checkboxes-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="usage_methods[]" value="oral" {{ in_array('oral', $currentMethods) ? 'checked' : '' }}>
                        <span>فموي</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="usage_methods[]" value="injection" {{ in_array('injection', $currentMethods) ? 'checked' : '' }}>
                        <span>حقن</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="usage_methods[]" value="topical" {{ in_array('topical', $currentMethods) ? 'checked' : '' }}>
                        <span>موضعي</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="usage_methods[]" value="inhalation" {{ in_array('inhalation', $currentMethods) ? 'checked' : '' }}>
                        <span>استنشاق</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="usage_methods[]" value="other" id="usage_other" {{ in_array('other', $currentMethods) ? 'checked' : '' }}>
                        <span>أخرى</span>
                    </label>
                </div>
                @error('usage_methods')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group" id="other_usage_method_group" style="display: {{ in_array('other', $currentMethods) ? 'block' : 'none' }};">
                <label for="other_usage_method">حدد طريقة الاستعمال الأخرى</label>
                <input type="text" id="other_usage_method" name="other_usage_method" class="form-control @error('other_usage_method') is-invalid @enderror" value="{{ old('other_usage_method', $pharmaceuticalProduct->other_usage_method) }}" placeholder="اكتب طريقة الاستعمال الأخرى">
                @error('other_usage_method')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="form-section">
            <h3>التسجيل المسبق</h3>
            <div class="checkbox-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_pre_registered" id="is_pre_registered" value="1" {{ (old('is_pre_registered') ?? $pharmaceuticalProduct->is_pre_registered) ? 'checked' : '' }}>
                    <span>الصنف الدوائي مسجل من قبل</span>
                </label>
            </div>

            @php
                $preRegParts = $pharmaceuticalProduct->pre_registration_number ? explode('-', $pharmaceuticalProduct->pre_registration_number) : [null, null];
                $preRegYear = old('pre_registration_year') ?? ($preRegParts[0] ?? '');
                $preRegSeq = old('pre_registration_sequence') ?? ($preRegParts[1] ?? '');
            @endphp

            <div id="preRegistrationFields" class="pre-registration-fields" style="display: {{ (old('is_pre_registered') ?? $pharmaceuticalProduct->is_pre_registered) ? 'block' : 'none' }};">
                <div class="alert-info-box">
                    <i class="ti ti-info-circle"></i>
                    <div>
                        <strong>ملاحظة هامة</strong>
                        <p>يرجى إدخال رقم القيد وسنة التسجيل الخاصة بالصنف المسجل مسبقاً. سيتم التحقق من هذه البيانات من قبل الإدارة.</p>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="pre_registration_year">سنة التسجيل <span class="required">*</span></label>
                        <input type="number" name="pre_registration_year" id="pre_registration_year" class="form-control" value="{{ $preRegYear }}" placeholder="مثال: 2024" min="1990" max="{{ date('Y') }}">
                        @error('pre_registration_year')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="pre_registration_sequence">الرقم التسلسلي <span class="required">*</span></label>
                        <input type="number" name="pre_registration_sequence" id="pre_registration_sequence" class="form-control" value="{{ $preRegSeq }}" placeholder="مثال: 15" min="1">
                        @error('pre_registration_sequence')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="form-group">
                    <small>رقم القيد: <strong id="preRegPreview">-</strong></small>
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3>معلومات الشركة</h3>

            <div class="form-group">
                <label for="foreign_company_id">اسم الشركة <span class="required">*</span></label>
                <select id="foreign_company_id" name="foreign_company_id" class="form-control @error('foreign_company_id') is-invalid @enderror" required>
                    <option value="">اختر الشركة الأجنبية</option>
                    @foreach($foreignCompanies as $company)
                        <option value="{{ $company->id }}"
                            data-local-company="{{ $company->localCompany->company_name }}"
                            data-local-company-address="{{ $company->localCompany->commercial_address }}"
                            {{ (old('foreign_company_id') ?? $pharmaceuticalProduct->foreign_company_id) == $company->id ? 'selected' : '' }}>
                            {{ $company->company_name }} - {{ $company->country }}
                        </option>
                    @endforeach
                </select>
                @error('foreign_company_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div id="local_company_info" class="local-company-info" style="display: {{ (old('foreign_company_id') ?? $pharmaceuticalProduct->foreign_company_id) ? 'block' : 'none' }};">
                <div class="info-card">
                    <h4>بيانات الشركة المحلية التابعة</h4>
                    <div class="info-row">
                        <span class="label">اسم الشركة المحلية:</span>
                        <span class="value" id="local_company_name">-</span>
                    </div>
                    <div class="info-row">
                        <span class="label">العنوان التجاري:</span>
                        <span class="value" id="local_company_address">-</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('representative.pharmaceutical-products.show', $pharmaceuticalProduct) }}" class="btn btn-secondary">
                <i class="ti ti-x"></i>
                إلغاء
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="ti ti-check"></i>
                حفظ التعديلات
            </button>
        </div>
    </form>
</div>
@endsection

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Almarai:wght@300;400;700;800&display=swap" rel="stylesheet">
<style>
    * {
        font-family: 'Almarai', sans-serif !important;
    }

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
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        color: #374151;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .back-to-home:hover {
        background: #1a5f4a;
        color: #ffffff;
        border-color: #1a5f4a;
    }

    .page-header-content h1 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1a5f4a;
        margin: 0 0 5px 0;
    }

    .page-header-content p {
        font-size: 0.9rem;
        color: #6b7280;
        margin: 0;
    }

    .product-form {
        display: flex;
        flex-direction: column;
        gap: 25px;
    }

    .form-section {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 25px;
    }

    .form-section h3 {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0 0 20px 0;
        padding-bottom: 12px;
        border-bottom: 2px solid #e5e7eb;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group:last-child {
        margin-bottom: 0;
    }

    .form-group label {
        display: block;
        font-size: 0.9rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
    }

    .required {
        color: #ef4444;
    }

    .form-control {
        width: 100%;
        padding: 10px 15px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 0.9rem;
        font-family: 'Almarai', sans-serif;
        transition: all 0.2s ease;
    }

    .form-control:focus {
        outline: none;
        border-color: #1a5f4a;
        box-shadow: 0 0 0 3px rgba(26, 95, 74, 0.1);
    }

    .form-control.is-invalid {
        border-color: #ef4444;
    }

    .invalid-feedback {
        display: block;
        color: #ef4444;
        font-size: 0.875rem;
        margin-top: 5px;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .checkboxes-group {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
    }

    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        font-size: 0.9rem;
        color: #374151;
        user-select: none;
    }

    .checkbox-label input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: #1a5f4a;
    }

    .checkbox-group {
        margin-bottom: 15px;
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

    .local-company-info {
        margin-top: 15px;
    }

    .info-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 20px;
    }

    .info-card h4 {
        font-size: 1rem;
        font-weight: 600;
        color: #1a5f4a;
        margin: 0 0 15px 0;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #f3f4f6;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-row .label {
        font-size: 0.875rem;
        color: #6b7280;
        font-weight: 500;
    }

    .info-row .value {
        font-size: 0.875rem;
        color: #1f2937;
        font-weight: 600;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
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

    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }

        .checkboxes-group {
            flex-direction: column;
            gap: 10px;
        }

        .form-actions {
            flex-direction: column-reverse;
        }

        .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.getElementById('usage_other').addEventListener('change', function() {
        const otherGroup = document.getElementById('other_usage_method_group');
        const otherInput = document.getElementById('other_usage_method');

        if (this.checked) {
            otherGroup.style.display = 'block';
            otherInput.required = true;
        } else {
            otherGroup.style.display = 'none';
            otherInput.required = false;
            otherInput.value = '';
        }
    });

    document.getElementById('foreign_company_id').addEventListener('change', function() {
        const localCompanyInfo = document.getElementById('local_company_info');
        const selectedOption = this.options[this.selectedIndex];

        if (this.value) {
            const localCompanyName = selectedOption.getAttribute('data-local-company');
            const localCompanyAddress = selectedOption.getAttribute('data-local-company-address');

            document.getElementById('local_company_name').textContent = localCompanyName;
            document.getElementById('local_company_address').textContent = localCompanyAddress;

            localCompanyInfo.style.display = 'block';
        } else {
            localCompanyInfo.style.display = 'none';
        }
    });

    if (document.getElementById('foreign_company_id').value) {
        document.getElementById('foreign_company_id').dispatchEvent(new Event('change'));
    }

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
</script>
@endpush
