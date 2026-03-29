@extends('layouts.app')

@section('title', __('settings.system_settings'))

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('dashboard.dashboard') }}</a></li>
<li class="breadcrumb-item active">{{ __('settings.system_settings') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <form action="{{ route('admin.app-settings.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="ti ti-building"></i>
                        {{ __('settings.foreign_company_settings') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($foreignCompanySettings as $setting)
                        <div class="col-md-6 mb-3">
                            <label for="{{ $setting->key }}" class="form-label">
                                {{ $setting->label }}
                            </label>
                            <div class="input-group">
                                <input
                                    type="number"
                                    class="form-control @error('settings.'.$setting->key) is-invalid @enderror"
                                    id="{{ $setting->key }}"
                                    name="settings[{{ $setting->key }}]"
                                    value="{{ old('settings.'.$setting->key, $setting->value) }}"
                                    min="0"
                                    step="{{ str_contains($setting->key, 'validity_years') ? '1' : '0.01' }}"
                                    required
                                >
                                @if(str_contains($setting->key, 'validity_years'))
                                    <span class="input-group-text">{{ __('settings.year') }}</span>
                                @else
                                    <span class="input-group-text">{{ __('general.currency') }}</span>
                                @endif
                                @error('settings.'.$setting->key)
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="text-muted">{{ $setting->description }}</small>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="ti ti-building-community"></i>
                        {{ __('settings.local_company_settings') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($localCompanySettings as $setting)
                        <div class="col-md-6 mb-3">
                            <label for="{{ $setting->key }}" class="form-label">
                                {{ $setting->label }}
                            </label>
                            <div class="input-group">
                                <input
                                    type="number"
                                    class="form-control @error('settings.'.$setting->key) is-invalid @enderror"
                                    id="{{ $setting->key }}"
                                    name="settings[{{ $setting->key }}]"
                                    value="{{ old('settings.'.$setting->key, $setting->value) }}"
                                    min="0"
                                    step="{{ str_contains($setting->key, 'validity_years') ? '1' : '0.01' }}"
                                    required
                                >
                                @if(str_contains($setting->key, 'validity_years'))
                                    <span class="input-group-text">{{ __('settings.year') }}</span>
                                @else
                                    <span class="input-group-text">{{ __('general.currency') }}</span>
                                @endif
                                @error('settings.'.$setting->key)
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="text-muted">{{ $setting->description }}</small>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">{{ __('settings.save_settings') }}</h6>
                            <p class="text-muted mb-0">
                                <i class="ti ti-info-circle"></i>
                                {{ __('settings.save_settings_info') }}
                            </p>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy"></i>
                            {{ __('general.save_changes') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card border-info">
            <div class="card-header bg-light-info">
                <h5 class="mb-0">
                    <i class="ti ti-clock"></i>
                    {{ __('settings.auto_invoices_info') }}
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-0">
                    <h6 class="alert-heading">
                        <i class="ti ti-info-circle"></i>
                        {{ __('settings.annual_auto_invoices') }}
                    </h6>
                    <p class="mb-2">
                        {{ __('settings.annual_invoices_desc') }}
                    </p>
                    <hr>
                    <p class="mb-2"><strong>{{ __('settings.run_manually') }}:</strong></p>
                    <code class="d-block bg-dark text-white p-2 rounded">
                        php artisan invoices:generate-annual
                    </code>
                    <p class="mt-2 mb-2"><strong>{{ __('settings.test_run') }}:</strong></p>
                    <code class="d-block bg-dark text-white p-2 rounded">
                        php artisan invoices:generate-annual --test
                    </code>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-warning">
            <div class="card-header bg-light-warning">
                <h5 class="mb-0">
                    <i class="ti ti-refresh"></i>
                    {{ __('settings.renewal_info') }}
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning mb-0">
                    <h6 class="alert-heading">
                        <i class="ti ti-info-circle"></i>
                        {{ __('settings.daily_expiry_check') }}
                    </h6>
                    <p class="mb-2">
                        {{ __('settings.daily_check_desc') }}
                    </p>
                    <ul class="mb-2">
                        <li>{{ __('settings.status_change_expired') }}</li>
                        <li>{{ __('settings.auto_renewal_invoice') }}</li>
                        <li>{{ __('settings.payment_reactivate') }}</li>
                    </ul>
                    <hr>
                    <p class="mb-2"><strong>{{ __('settings.run_manually') }}:</strong></p>
                    <code class="d-block bg-dark text-white p-2 rounded">
                        php artisan companies:check-expired
                    </code>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: '{{ __("general.success") }}',
            text: '{{ session('success') }}',
            confirmButtonText: '{{ __("general.ok") }}',
            confirmButtonColor: '#1a5f4a'
        });
    @endif
</script>
@endpush
