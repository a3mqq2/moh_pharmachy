@extends('layouts.app')

@section('title', __('companies.edit_company') . ': ' . $localCompany->company_name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('general.home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.local-companies.index') }}">{{ __('companies.local_companies') }}</a></li>
    <li class="breadcrumb-item active">{{ __('general.edit') }}</li>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
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

    .input-group-icon .form-control {
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
</style>
@endpush

@section('content')


<form action="{{ route('admin.local-companies.update', $localCompany) }}" method="POST" id="companyForm">
    @csrf
    @method('PUT')

    <div class="card form-section-card">
        <div class="wizard-steps">
            <div class="wizard-step active" data-step="1">
                <div class="step-number">1</div>
                <span class="step-title">{{ __('companies.company_data') }}</span>
            </div>
            <div class="wizard-step" data-step="2">
                <div class="step-number">2</div>
                <span class="step-title">{{ __('companies.license_type') }}</span>
            </div>
            <div class="wizard-step" data-step="3">
                <div class="step-number">3</div>
                <span class="step-title">{{ __('companies.official_licenses') }}</span>
            </div>
            <div class="wizard-step" data-step="4">
                <div class="step-number">4</div>
                <span class="step-title">{{ __('companies.manager_data') }}</span>
            </div>
        </div>

        <div class="wizard-content active" id="step-1">
            <div class="step-header">
                <h5><i class="ti ti-building me-2"></i>{{ __('companies.basic_data') }}</h5>
            </div>
            <div class="step-body">
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label class="form-label">{{ __('companies.company_name') }} <span class="required-asterisk">*</span></label>
                        <div class="input-group-icon">
                            <input type="text" name="company_name" class="form-control @error('company_name') is-invalid @enderror" value="{{ old('company_name', $localCompany->company_name) }}" placeholder="{{ __('companies.company_name') }}" required>
                            <i class="ti ti-building-skyscraper input-icon"></i>
                        </div>
                        @error('company_name')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-4">
                        <label class="form-label">{{ __('companies.company_classification') }} <span class="required-asterisk">*</span></label>
                        <select name="company_type" class="form-select @error('company_type') is-invalid @enderror" required>
                            @foreach($companyTypes as $key => $value)
                                <option value="{{ $key }}" {{ old('company_type', $localCompany->company_type) == $key ? 'selected' : '' }}>{{ $value }}</option>
                            @endforeach
                        </select>
                        @error('company_type')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-4">
                        <label class="form-label">{{ __('general.email') }} <span class="required-asterisk">*</span></label>
                        <div class="input-group-icon">
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $localCompany->email) }}" placeholder="example@company.com" required>
                            <i class="ti ti-mail input-icon"></i>
                        </div>
                        @error('email')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-4">
                        <label class="form-label">{{ __('general.address') }}</label>
                        <textarea name="company_address" class="form-control @error('company_address') is-invalid @enderror" rows="2" placeholder="{{ __('companies.detailed_address') }}">{{ old('company_address', $localCompany->company_address) }}</textarea>
                        @error('company_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 mb-4">
                        <label class="form-label"><i class="ti ti-map-pin me-1"></i>{{ __('companies.map_location') }}</label>
                        <div id="map" style="height: 350px; border-radius: 8px; border: 1px solid #d1d5db; margin-bottom: 8px;"></div>
                        <small class="text-muted">{{ __('companies.click_map') }}</small>
                        <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude', $localCompany->latitude) }}">
                        <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude', $localCompany->longitude) }}">
                    </div>

                    <div class="col-md-6 mb-4">
                        <label class="form-label">{{ __('general.street') }}</label>
                        <input type="text" name="street" class="form-control @error('street') is-invalid @enderror" value="{{ old('street', $localCompany->street) }}" placeholder="{{ __('companies.street_name') }}">
                        @error('street')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-4">
                        <label class="form-label">{{ __('general.city') }} <span class="required-asterisk">*</span></label>
                        <input type="text" name="city" class="form-control @error('city') is-invalid @enderror" value="{{ old('city', $localCompany->city) }}" placeholder="{{ __('companies.city_example') }}" required>
                        @error('city')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-4">
                        <label class="form-label">{{ __('general.phone') }} <span class="required-asterisk">*</span></label>
                        <div class="input-group-icon">
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $localCompany->phone) }}" placeholder="{{ __('companies.phone_landline') }}" required>
                            <i class="ti ti-phone input-icon"></i>
                        </div>
                        @error('phone')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-4">
                        <label class="form-label">{{ __('general.mobile') }}</label>
                        <div class="input-group-icon">
                            <input type="text" name="mobile" class="form-control @error('mobile') is-invalid @enderror" value="{{ old('mobile', $localCompany->mobile) }}" placeholder="{{ __('companies.phone_mobile') }}">
                            <i class="ti ti-device-mobile input-icon"></i>
                        </div>
                        @error('mobile')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="wizard-nav">
                <span class="step-indicator">{{ __('general.step_of', ['step' => 1, 'total' => 4]) }}</span>
                <div>
                    <a href="{{ route('admin.local-companies.show', $localCompany) }}" class="btn btn-wizard btn-wizard-prev me-2">
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
                <h5><i class="ti ti-license me-2"></i>{{ __('companies.license_type_and_status') }}</h5>
            </div>
            <div class="step-body">
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label class="form-label">{{ __('companies.license_type') }} <span class="required-asterisk">*</span></label>
                        <select name="license_type" class="form-select @error('license_type') is-invalid @enderror" required>
                            @foreach($licenseTypes as $key => $value)
                                <option value="{{ $key }}" {{ old('license_type', $localCompany->license_type) == $key ? 'selected' : '' }}>{{ $value }}</option>
                            @endforeach
                        </select>
                        @error('license_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-4">
                        <label class="form-label">{{ __('companies.license_specialty') }} <span class="required-asterisk">*</span></label>
                        <select name="license_specialty" class="form-select @error('license_specialty') is-invalid @enderror" required>
                            @foreach($licenseSpecialties as $key => $value)
                                <option value="{{ $key }}" {{ old('license_specialty', $localCompany->license_specialty) == $key ? 'selected' : '' }}>{{ $value }}</option>
                            @endforeach
                        </select>
                        @error('license_specialty')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-4">
                        <label class="form-label">{{ __('general.status') }} <span class="required-asterisk">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            @foreach($statuses as $key => $value)
                                <option value="{{ $key }}" {{ old('status', $localCompany->status) == $key ? 'selected' : '' }}>{{ $value }}</option>
                            @endforeach
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-4">
                        <label class="form-label">{{ __('general.registration_number') }}</label>
                        @if($localCompany->registration_number && in_array($localCompany->status, ['approved', 'active', 'expired']))
                            <input type="text" class="form-control" value="{{ $localCompany->registration_number }}" readonly disabled>
                            <small class="text-muted">{{ __('companies.reg_number_locked') }}</small>
                        @else
                            <input type="text" name="registration_number" class="form-control @error('registration_number') is-invalid @enderror" value="{{ old('registration_number', $localCompany->registration_number) }}" placeholder="{{ __('companies.reg_number_auto') }}">
                            @error('registration_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">{{ __('companies.reg_number_auto_note') }}</small>
                        @endif
                    </div>
                </div>

                <div class="alert alert-light border mt-3">
                    <div class="d-flex align-items-start">
                        <i class="ti ti-info-circle text-primary me-2 fs-5"></i>
                        <div>
                            {{ __('companies.license_specialty_note') }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="wizard-nav">
                <span class="step-indicator">{{ __('general.step_of', ['step' => 2, 'total' => 4]) }}</span>
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
                <h5><i class="ti ti-file-certificate me-2"></i>{{ __('companies.official_license_data') }}</h5>
            </div>
            <div class="step-body">
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label class="form-label">{{ __('companies.license_number') }}</label>
                        <input type="text" name="license_number" class="form-control @error('license_number') is-invalid @enderror" value="{{ old('license_number', $localCompany->license_number) }}" placeholder="{{ __('companies.license_number') }}">
                        @error('license_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-4">
                        <label class="form-label">{{ __('companies.issuing_place') }}</label>
                        <input type="text" name="license_issuer" class="form-control @error('license_issuer') is-invalid @enderror" value="{{ old('license_issuer', $localCompany->license_issuer) }}" placeholder="{{ __('companies.issuing_place_example') }}">
                        @error('license_issuer')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-4">
                        <label class="form-label">{{ __('companies.food_drug_control_reg') }} <span class="text-danger">*</span></label>
                        <input type="text" name="food_drug_registration_number" class="form-control @error('food_drug_registration_number') is-invalid @enderror" value="{{ old('food_drug_registration_number', $localCompany->food_drug_registration_number) }}" placeholder="{{ __('general.registration_number') }}" required>
                        @error('food_drug_registration_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-4">
                        <label class="form-label">{{ __('companies.chamber_commerce_reg') }}</label>
                        <input type="text" name="chamber_of_commerce_number" class="form-control @error('chamber_of_commerce_number') is-invalid @enderror" value="{{ old('chamber_of_commerce_number', $localCompany->chamber_of_commerce_number) }}" placeholder="{{ __('companies.commercial_reg') }}">
                        @error('chamber_of_commerce_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="alert alert-light border mt-3">
                    <div class="d-flex align-items-start">
                        <i class="ti ti-info-circle text-primary me-2 fs-5"></i>
                        <div>
                            {{ __('companies.optional_fields_edit_note') }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="wizard-nav">
                <span class="step-indicator">{{ __('general.step_of', ['step' => 3, 'total' => 4]) }}</span>
                <div>
                    <button type="button" class="btn btn-wizard btn-wizard-prev me-2" onclick="prevStep(3)">
                        <i class="ti ti-arrow-right me-1"></i> {{ __('general.previous') }}
                    </button>
                    <button type="button" class="btn btn-wizard btn-wizard-next" onclick="nextStep(3)">
                        {{ __('general.next') }} <i class="ti ti-arrow-left ms-1"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="wizard-content" id="step-4">
            <div class="step-header">
                <h5><i class="ti ti-user-check me-2"></i>{{ __('companies.manager_data') }}</h5>
            </div>
            <div class="step-body">
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label class="form-label">{{ __('companies.manager_name') }} <span class="required-asterisk">*</span></label>
                        <div class="input-group-icon">
                            <input type="text" name="manager_name" class="form-control @error('manager_name') is-invalid @enderror" value="{{ old('manager_name', $localCompany->manager_name) }}" placeholder="{{ __('companies.manager_full_name') }}" required>
                            <i class="ti ti-user input-icon"></i>
                        </div>
                        @error('manager_name')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-4">
                        <label class="form-label">{{ __('companies.manager_position') }}</label>
                        <input type="text" name="manager_position" class="form-control @error('manager_position') is-invalid @enderror" value="{{ old('manager_position', $localCompany->manager_position) }}" placeholder="{{ __('companies.manager_position_example') }}">
                        @error('manager_position')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-4">
                        <label class="form-label">{{ __('companies.manager_phone') }} <span class="required-asterisk">*</span></label>
                        <div class="input-group-icon">
                            <input type="text" name="manager_phone" class="form-control @error('manager_phone') is-invalid @enderror" value="{{ old('manager_phone', $localCompany->manager_phone) }}" placeholder="{{ __('companies.phone_mobile') }}" required>
                            <i class="ti ti-phone input-icon"></i>
                        </div>
                        @error('manager_phone')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-4">
                        <label class="form-label">{{ __('general.email') }}</label>
                        <div class="input-group-icon">
                            <input type="email" name="manager_email" class="form-control @error('manager_email') is-invalid @enderror" value="{{ old('manager_email', $localCompany->manager_email) }}" placeholder="manager@company.com">
                            <i class="ti ti-mail input-icon"></i>
                        </div>
                        @error('manager_email')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">{{ __('companies.rejection_reason_field') }}</label>
                    <textarea name="rejection_reason" class="form-control @error('rejection_reason') is-invalid @enderror" rows="3" placeholder="{{ __('companies.rejection_reason_optional') }}">{{ old('rejection_reason', $localCompany->rejection_reason) }}</textarea>
                    @error('rejection_reason')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="alert alert-warning border mt-3">
                    <div class="d-flex align-items-start">
                        <i class="ti ti-alert-triangle text-warning me-2 fs-5"></i>
                        <div>
                            {{ __('companies.review_before_save') }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="wizard-nav">
                <span class="step-indicator">{{ __('general.step_of', ['step' => 4, 'total' => 4]) }}</span>
                <div>
                    <button type="button" class="btn btn-wizard btn-wizard-prev me-2" onclick="prevStep(4)">
                        <i class="ti ti-arrow-right me-1"></i> {{ __('general.previous') }}
                    </button>
                    <button type="submit" class="btn btn-wizard btn-wizard-submit">
                        <i class="ti ti-check me-1"></i> {{ __('companies.save_edits') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
    let currentStep = 1;
    const totalSteps = 4;

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
</script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var savedLat = document.getElementById('latitude').value;
        var savedLng = document.getElementById('longitude').value;
        var center = (savedLat && savedLng) ? [parseFloat(savedLat), parseFloat(savedLng)] : [32.9022, 13.1800];
        var zoom = (savedLat && savedLng) ? 15 : 12;

        var map = L.map('map').setView(center, zoom);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        var marker = null;
        if (savedLat && savedLng) {
            marker = L.marker([parseFloat(savedLat), parseFloat(savedLng)]).addTo(map);
        }

        map.on('click', function(e) {
            if (marker) map.removeLayer(marker);
            marker = L.marker(e.latlng).addTo(map);
            document.getElementById('latitude').value = e.latlng.lat.toFixed(7);
            document.getElementById('longitude').value = e.latlng.lng.toFixed(7);
        });

        document.querySelectorAll('.wizard-step, .btn-next, .btn-prev').forEach(function(el) {
            el.addEventListener('click', function() {
                setTimeout(function() { map.invalidateSize(); }, 300);
            });
        });

        var observer = new MutationObserver(function() {
            setTimeout(function() { map.invalidateSize(); }, 300);
        });
        var step1 = document.getElementById('step-1');
        if (step1) observer.observe(step1, { attributes: true, attributeFilter: ['class', 'style'] });
    });
</script>
@endpush