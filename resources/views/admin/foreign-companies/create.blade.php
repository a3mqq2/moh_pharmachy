@extends('layouts.app')

@section('title', __('companies.register_foreign'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('general.home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.foreign-companies.index') }}">{{ __('companies.foreign_companies') }}</a></li>
    <li class="breadcrumb-item active">{{ __('companies.register_foreign') }}</li>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .wizard-steps {
        display: flex;
        justify-content: center;
        padding: 2rem 1rem;
        position: relative;
        background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
    }

    .wizard-steps::before {
        content: '';
        position: absolute;
        top: 50%;
        right: 15%;
        left: 15%;
        height: 3px;
        background: #e9ecef;
        z-index: 0;
        transform: translateY(-50%);
    }

    .wizard-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        z-index: 1;
        flex: 1;
        max-width: 200px;
        cursor: pointer;
    }

    .step-number {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: #e9ecef;
        color: #6c757d;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        font-weight: 700;
        margin-bottom: 0.75rem;
        transition: all 0.3s ease;
        border: 3px solid #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .wizard-step.active .step-number {
        background: linear-gradient(135deg, #1a5f4a 0%, #2d8a6e 100%);
        color: #fff;
        transform: scale(1.1);
    }

    .wizard-step.completed .step-number {
        background: linear-gradient(135deg, #1a5f4a 0%, #2d8a6e 100%);
        color: #fff;
    }

    .step-title {
        font-size: 0.85rem;
        color: #6c757d;
        font-weight: 600;
        text-align: center;
        transition: all 0.3s ease;
    }

    .wizard-step.active .step-title,
    .wizard-step.completed .step-title {
        color: #1a5f4a;
    }

    .wizard-content {
        display: none;
        animation: fadeIn 0.4s ease;
    }

    .wizard-content.active {
        display: block;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .form-section-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        overflow: hidden;
    }

    .step-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
        border-bottom: 2px solid #1a5f4a;
        padding: 1.25rem 1.5rem;
    }

    .step-header h5 {
        color: #1a5f4a;
        font-weight: 700;
        margin: 0;
    }

    .step-body {
        padding: 2rem;
    }

    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
    }

    .form-control, .form-select {
        border-radius: 10px;
        border: 2px solid #e9ecef;
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: #1a5f4a;
        box-shadow: 0 0 0 0.2rem rgba(26, 95, 74, 0.15);
    }

    .btn-wizard {
        padding: 0.75rem 2rem;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-wizard-next {
        background: linear-gradient(135deg, #1a5f4a 0%, #2d8a6e 100%);
        border: none;
        color: #fff;
    }

    .btn-wizard-next:hover {
        background: linear-gradient(135deg, #155a45 0%, #257a5f 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(26, 95, 74, 0.3);
        color: #fff;
    }

    .btn-wizard-prev {
        background: #f8f9fa;
        border: 2px solid #dee2e6;
        color: #495057;
    }

    .btn-wizard-prev:hover {
        background: #e9ecef;
        color: #495057;
    }

    .btn-wizard-submit {
        background: linear-gradient(135deg, #1a5f4a 0%, #2d8a6e 100%);
        border: none;
        color: #fff;
        padding: 0.875rem 2.5rem;
    }

    .btn-wizard-submit:hover {
        background: linear-gradient(135deg, #155a45 0%, #257a5f 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(26, 95, 74, 0.3);
        color: #fff;
    }

    .required-asterisk {
        color: #dc3545;
        font-weight: bold;
    }

    .input-group-icon {
        position: relative;
    }

    .input-group-icon .form-control,
    .input-group-icon .form-select {
        padding-right: 2.75rem;
    }

    .input-group-icon .input-icon {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
    }

    .wizard-nav {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem 2rem;
        background: #f8f9fa;
        border-top: 1px solid #e9ecef;
    }

    .step-indicator {
        font-size: 0.9rem;
        color: #6c757d;
    }

    .select2-container {
        width: 100% !important;
    }

    .select2-container--default .select2-selection--single,
    .select2-container--default .select2-selection--multiple {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        min-height: 48px;
        padding: 4px 8px;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px;
        padding-right: 14px;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 46px;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #1a5f4a;
        border: 1px solid #164538;
        color: #ffffff;
        padding: 4px 10px;
        border-radius: 6px;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: #ffffff;
        margin-left: 5px;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #1a5f4a;
    }
</style>
@endpush

@section('content')

<form action="{{ route('admin.foreign-companies.store') }}" method="POST" id="companyForm">
    @csrf

    <div class="card form-section-card">
        <div class="wizard-steps">
            <div class="wizard-step active" data-step="1">
                <div class="step-number">1</div>
                <span class="step-title">{{ __('companies.local_company') }}</span>
            </div>
            <div class="wizard-step" data-step="2">
                <div class="step-number">2</div>
                <span class="step-title">{{ __('companies.company_info') }}</span>
            </div>
            <div class="wizard-step" data-step="3">
                <div class="step-number">3</div>
                <span class="step-title">{{ __('companies.products_and_registration') }}</span>
            </div>
        </div>

        <div class="wizard-content active" id="step-1">
            <div class="step-header">
                <h5><i class="ti ti-building me-2"></i>{{ __('companies.local_company_agent') }}</h5>
            </div>
            <div class="step-body">
                <div class="row">
                    <div class="col-md-8 mb-4">
                        <label class="form-label">{{ __('companies.local_supplier') }} <span class="required-asterisk">*</span></label>
                        <select name="local_company_id" class="form-select select2" required>
                            <option value="">{{ __('companies.select_local_company') }}</option>
                            @foreach($localCompanies as $company)
                                <option value="{{ $company->id }}" {{ old('local_company_id') == $company->id ? 'selected' : '' }}>
                                    {{ $company->company_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('local_company_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="text-muted mt-1 d-block">{{ __('companies.supplier_only_note') }}</small>
                    </div>
                </div>

                @if($localCompanies->isEmpty())
                <div class="alert alert-warning border">
                    <div class="d-flex align-items-start">
                        <i class="ti ti-alert-triangle text-warning me-2 fs-5"></i>
                        <div>
                            {{ __('companies.no_supplier_companies') }}
                        </div>
                    </div>
                </div>
                @endif
            </div>
            <div class="wizard-nav">
                <span class="step-indicator">{{ __('general.step_of', ['step' => 1, 'total' => 3]) }}</span>
                <div>
                    <a href="{{ route('admin.foreign-companies.index') }}" class="btn btn-wizard btn-wizard-prev me-2">
                        <i class="ti ti-x me-1"></i> {{ __('general.cancel') }}
                    </a>
                    <button type="button" class="btn btn-wizard btn-wizard-next" onclick="nextStep(1)">
                        {{ __('general.next') }} <i class="ti ti-arrow-left ms-1"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="wizard-content" id="step-2">
            <div class="step-header">
                <h5><i class="ti ti-world me-2"></i>{{ __('companies.foreign_basic_info') }}</h5>
            </div>
            <div class="step-body">
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label class="form-label">{{ __('companies.foreign_company_name') }} <span class="required-asterisk">*</span></label>
                        <div class="input-group-icon">
                            <input type="text" name="company_name" class="form-control @error('company_name') is-invalid @enderror" value="{{ old('company_name') }}" placeholder="{{ __('companies.foreign_company_name') }}" required>
                            <i class="ti ti-building input-icon"></i>
                        </div>
                        @error('company_name')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-4">
                        <label class="form-label">{{ __('general.country') }} <span class="required-asterisk">*</span></label>
                        <select name="country" class="form-select select2-tags @error('country') is-invalid @enderror" required>
                            <option value="">{{ __('companies.select_or_type_country_placeholder') }}</option>
                            @foreach($countries as $country)
                                <option value="{{ $country }}" {{ old('country') == $country ? 'selected' : '' }}>{{ $country }}</option>
                            @endforeach
                        </select>
                        @error('country')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-4">
                        <label class="form-label">{{ __('companies.entity_type') }} <span class="required-asterisk">*</span></label>
                        <select name="entity_type" class="form-select @error('entity_type') is-invalid @enderror" required>
                            <option value="">{{ __('companies.select_entity_type') }}</option>
                            <option value="company" {{ old('entity_type') == 'company' ? 'selected' : '' }}>{{ __('companies.entity_company') }}</option>
                            <option value="factory" {{ old('entity_type') == 'factory' ? 'selected' : '' }}>{{ __('companies.entity_factory') }}</option>
                        </select>
                        @error('entity_type')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-4">
                        <label class="form-label">{{ __('companies.activity_type') }} <span class="required-asterisk">*</span></label>
                        <select name="activity_type" class="form-select @error('activity_type') is-invalid @enderror" required>
                            <option value="">{{ __('companies.select_activity_type') }}</option>
                            <option value="medicines" {{ old('activity_type') == 'medicines' ? 'selected' : '' }}>{{ __('companies.activity_medicines') }}</option>
                            <option value="medical_supplies" {{ old('activity_type') == 'medical_supplies' ? 'selected' : '' }}>{{ __('companies.activity_medical_supplies') }}</option>
                            <option value="both" {{ old('activity_type') == 'both' ? 'selected' : '' }}>{{ __('companies.activity_both') }}</option>
                        </select>
                        @error('activity_type')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-4">
                        <label class="form-label">{{ __('general.email') }} <span class="required-asterisk">*</span></label>
                        <div class="input-group-icon">
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="company@example.com" required>
                            <i class="ti ti-mail input-icon"></i>
                        </div>
                        @error('email')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-4">
                        <label class="form-label">{{ __('companies.product_count') }} <span class="required-asterisk">*</span></label>
                        <div class="input-group-icon">
                            <input type="number" name="products_count" class="form-control @error('products_count') is-invalid @enderror" value="{{ old('products_count', 1) }}" min="1" required>
                            <i class="ti ti-package input-icon"></i>
                        </div>
                        @error('products_count')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 mb-4">
                        <label class="form-label">{{ __('general.address') }} <span class="required-asterisk">*</span></label>
                        <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="3" placeholder="{{ __('companies.foreign_company_address') }}" required>{{ old('address') }}</textarea>
                        @error('address')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="wizard-nav">
                <span class="step-indicator">{{ __('general.step_of', ['step' => 2, 'total' => 3]) }}</span>
                <div>
                    <button type="button" class="btn btn-wizard btn-wizard-prev me-2" onclick="prevStep(2)">
                        <i class="ti ti-arrow-right me-1"></i> {{ __('general.previous') }}
                    </button>
                    <button type="button" class="btn btn-wizard btn-wizard-next" onclick="nextStep(2)">
                        {{ __('general.next') }} <i class="ti ti-arrow-left ms-1"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="wizard-content" id="step-3">
            <div class="step-header">
                <h5><i class="ti ti-world-check me-2"></i>{{ __('companies.registered_countries_review') }}</h5>
            </div>
            <div class="step-body">
                <div class="row">
                    <div class="col-12 mb-4">
                        <label class="form-label">{{ __('companies.registered_countries') }}</label>
                        <select name="registered_countries[]" class="form-select select2-tags-multiple" multiple>
                            @foreach($countries as $country)
                                <option value="{{ $country }}"
                                    {{ is_array(old('registered_countries')) && in_array($country, old('registered_countries')) ? 'selected' : '' }}>
                                    {{ $country }}
                                </option>
                            @endforeach
                        </select>
                        @error('registered_countries')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="text-muted mt-1 d-block">{{ __('companies.can_select_multi') }}</small>
                    </div>
                </div>

                <div class="alert alert-success border mt-3">
                    <div class="d-flex align-items-start">
                        <i class="ti ti-check-circle text-success me-2 fs-5"></i>
                        <div>
                            {{ __('companies.after_save_foreign_note') }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="wizard-nav">
                <span class="step-indicator">{{ __('general.step_of', ['step' => 3, 'total' => 3]) }}</span>
                <div>
                    <button type="button" class="btn btn-wizard btn-wizard-prev me-2" onclick="prevStep(3)">
                        <i class="ti ti-arrow-right me-1"></i> {{ __('general.previous') }}
                    </button>
                    <button type="submit" class="btn btn-wizard btn-wizard-submit">
                        <i class="ti ti-check me-1"></i> {{ __('companies.register_company') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    let currentStep = 1;
    const totalSteps = 3;

    function updateSteps() {
        document.querySelectorAll('.wizard-step').forEach((step, index) => {
            step.classList.remove('active', 'completed');
            if (index + 1 == currentStep) {
                step.classList.add('active');
            } else if (index + 1 < currentStep) {
                step.classList.add('completed');
            }
        });

        document.querySelectorAll('.wizard-content').forEach((content, index) => {
            content.classList.remove('active');
            if (index + 1 == currentStep) {
                content.classList.add('active');
            }
        });

        document.getElementById('companyForm').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function validateStep(step) {
        const stepContent = document.getElementById(`step-${step}`);
        const requiredFields = stepContent.querySelectorAll('[required]');
        let isValid = true;

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });

        if (!isValid) {
            const firstInvalid = stepContent.querySelector('.is-invalid');
            if (firstInvalid) {
                firstInvalid.focus();
            }
        }

        return isValid;
    }

    function nextStep(step) {
        if (validateStep(step)) {
            if (currentStep < totalSteps) {
                currentStep++;
                updateSteps();
            }
        }
    }

    function prevStep(step) {
        if (currentStep > 1) {
            currentStep--;
            updateSteps();
        }
    }

    document.querySelectorAll('.wizard-step').forEach(step => {
        step.addEventListener('click', function() {
            const stepNum = parseInt(this.dataset.step);
            if (stepNum < currentStep) {
                currentStep = stepNum;
                updateSteps();
            }
        });
    });

    document.getElementById('companyForm').addEventListener('submit', function(e) {
        for (let i = 1; i <= totalSteps; i++) {
            if (!validateStep(i)) {
                e.preventDefault();
                currentStep = i;
                updateSteps();
                return false;
            }
        }
    });

    $(document).ready(function() {
        $('.select2').select2({
            dir: "rtl",
            language: {
                noResults: function() { return "{{ __('general.no_results') }}"; },
                searching: function() { return "{{ __('general.searching') }}"; }
            }
        });

        $('.select2-tags').select2({
            dir: "rtl",
            tags: true,
            createTag: function(params) {
                var term = $.trim(params.term);
                if (term == '') return null;
                return { id: term, text: term, newTag: true };
            },
            language: {
                noResults: function() { return "{{ __('companies.no_results_type_country') }}"; },
                searching: function() { return "{{ __('general.searching') }}"; }
            },
            placeholder: "{{ __('companies.select_or_type_country') }}"
        });

        $('.select2-tags-multiple').select2({
            dir: "rtl",
            tags: true,
            tokenSeparators: [','],
            createTag: function(params) {
                var term = $.trim(params.term);
                if (term == '') return null;
                return { id: term, text: term, newTag: true };
            },
            language: {
                noResults: function() { return "{{ __('companies.no_results_type_country') }}"; },
                searching: function() { return "{{ __('general.searching') }}"; }
            },
            placeholder: "{{ __('companies.select_or_type_countries') }}"
        });
    });
</script>
@endpush