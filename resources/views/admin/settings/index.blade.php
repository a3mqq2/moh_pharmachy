@extends('layouts.app')

@section('title', 'إعدادات النظام')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
<li class="breadcrumb-item active">إعدادات النظام</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <form action="{{ route('admin.app-settings.update') }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Foreign Company Settings -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="ti ti-building"></i>
                        إعدادات الشركات الأجنبية
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
                                    <span class="input-group-text">سنة</span>
                                @else
                                    <span class="input-group-text">د.ل</span>
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

            <!-- Local Company Settings -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="ti ti-building-community"></i>
                        إعدادات الشركات المحلية
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
                                    <span class="input-group-text">سنة</span>
                                @else
                                    <span class="input-group-text">د.ل</span>
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

            <!-- Save Button -->
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">حفظ الإعدادات</h6>
                            <p class="text-muted mb-0">
                                <i class="ti ti-info-circle"></i>
                                سيتم تطبيق التغييرات على جميع الفواتير الجديدة
                            </p>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy"></i>
                            حفظ التغييرات
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
                    معلومات الفواتير التلقائية
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-0">
                    <h6 class="alert-heading">
                        <i class="ti ti-info-circle"></i>
                        الفواتير السنوية التلقائية
                    </h6>
                    <p class="mb-2">
                        يتم إنشاء الفواتير السنوية تلقائياً في أول كل شهر للشركات التي مر عليها سنة من التفعيل.
                    </p>
                    <hr>
                    <p class="mb-2"><strong>لتشغيل المهمة يدوياً:</strong></p>
                    <code class="d-block bg-dark text-white p-2 rounded">
                        php artisan invoices:generate-annual
                    </code>
                    <p class="mt-2 mb-2"><strong>للتجربة بدون إنشاء فواتير فعلية:</strong></p>
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
                    معلومات تجديد الشركات
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning mb-0">
                    <h6 class="alert-heading">
                        <i class="ti ti-info-circle"></i>
                        الفحص اليومي للشركات المنتهية
                    </h6>
                    <p class="mb-2">
                        يتم فحص جميع الشركات المفعلة يومياً، وعند انتهاء صلاحية أي شركة:
                    </p>
                    <ul class="mb-2">
                        <li>يتم تحويل حالة الشركة إلى "منتهية الصلاحية"</li>
                        <li>يتم إنشاء فاتورة تجديد تلقائياً</li>
                        <li>عند سداد الفاتورة، ترجع الشركة إلى "مفعلة"</li>
                    </ul>
                    <hr>
                    <p class="mb-2"><strong>لتشغيل المهمة يدوياً:</strong></p>
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
            title: 'تم بنجاح',
            text: '{{ session('success') }}',
            confirmButtonText: 'حسناً',
            confirmButtonColor: '#1a5f4a'
        });
    @endif
</script>
@endpush
