@extends('layouts.auth')

@section('title', 'استكمال البيانات التفصيلية')

@section('content')
<div class="dashboard-container">
    <div class="page-header">
        <div class="page-header-content">
            <a href="{{ route('representative.pharmaceutical-products.show', $pharmaceuticalProduct) }}" class="back-to-home">
                <i class="ti ti-arrow-right"></i>
            </a>
            <div>
                <h1>استكمال البيانات التفصيلية</h1>
                <p>{{ $pharmaceuticalProduct->product_name }}</p>
            </div>
        </div>
    </div>

    

    <div class="alert alert-info mb-4">
        <i class="ti ti-info-circle me-2"></i>
        <strong>تنبيه:</strong> يرجى إدخال جميع البيانات التفصيلية المطلوبة بدقة. سيتم مراجعة هذه البيانات من قبل الإدارة قبل الموافقة النهائية.
    </div>

    <form action="{{ route('representative.pharmaceutical-products.update-details', $pharmaceuticalProduct) }}" method="POST" class="product-form" id="detailsForm">
        @csrf

        <div class="form-section">
            <h3>المعلومات الأساسية</h3>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="trade_name">الاسم التجاري (Trade Name) <span class="required">*</span></label>
                        <input type="text" id="trade_name" name="trade_name"
                               class="form-control @error('trade_name') is-invalid @enderror"
                               value="{{ old('trade_name', $pharmaceuticalProduct->trade_name) }}" required>
                        @error('trade_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="origin">البلد المنشأ (Origin) <span class="required">*</span></label>
                        <input type="text" id="origin" name="origin"
                               class="form-control @error('origin') is-invalid @enderror"
                               value="{{ old('origin', $pharmaceuticalProduct->origin) }}"
                               placeholder="مثال: India, China, USA" required>
                        @error('origin')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3>معلومات التعبئة والتغليف</h3>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="unit">الوحدة (Unit) <span class="required">*</span></label>
                        <select id="unit" name="unit" class="form-control @error('unit') is-invalid @enderror" required>
                            <option value="">اختر الوحدة</option>
                            <option value="Tablet(s)" {{ old('unit', $pharmaceuticalProduct->unit) == 'Tablet(s)' ? 'selected' : '' }}>Tablet(s) - أقراص</option>
                            <option value="Capsule(s)" {{ old('unit', $pharmaceuticalProduct->unit) == 'Capsule(s)' ? 'selected' : '' }}>Capsule(s) - كبسولات</option>
                            <option value="Vial(s)" {{ old('unit', $pharmaceuticalProduct->unit) == 'Vial(s)' ? 'selected' : '' }}>Vial(s) - قوارير</option>
                            <option value="Ampoule(s)" {{ old('unit', $pharmaceuticalProduct->unit) == 'Ampoule(s)' ? 'selected' : '' }}>Ampoule(s) - أمبولات</option>
                            <option value="Bottle(s)" {{ old('unit', $pharmaceuticalProduct->unit) == 'Bottle(s)' ? 'selected' : '' }}>Bottle(s) - زجاجات</option>
                        </select>
                        @error('unit')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="packaging">نوع التعبئة (Packaging) <span class="required">*</span></label>
                        <input type="text" id="packaging" name="packaging"
                               class="form-control @error('packaging') is-invalid @enderror"
                               value="{{ old('packaging', $pharmaceuticalProduct->packaging) }}"
                               placeholder="مثال: Blister pack, Bottle, Box of 30 tablets" required>
                        @error('packaging')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="quantity">كمية العبوة (Quantity) <span class="required">*</span></label>
                        <input type="number" id="quantity" name="quantity"
                               class="form-control @error('quantity') is-invalid @enderror"
                               value="{{ old('quantity', $pharmaceuticalProduct->quantity) }}"
                               min="1" placeholder="مثال: 30, 60, 100" required>
                        @error('quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="unit_price">سعر الوحدة (Unit Price) - اختياري</label>
                        <input type="number" id="unit_price" name="unit_price"
                               class="form-control @error('unit_price') is-invalid @enderror"
                               value="{{ old('unit_price', $pharmaceuticalProduct->unit_price) }}"
                               step="0.01" min="0" placeholder="مثال: 5.50">
                        @error('unit_price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">سعر الوحدة الواحدة (اختياري - لأغراض لاحقة)</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3>الصلاحية والتخزين</h3>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="shelf_life_months">مدة الصلاحية (Shelf Life) <span class="required">*</span></label>
                        <input type="number" id="shelf_life_months" name="shelf_life_months"
                               class="form-control @error('shelf_life_months') is-invalid @enderror"
                               value="{{ old('shelf_life_months', $pharmaceuticalProduct->shelf_life_months) }}"
                               min="1" placeholder="بالأشهر - مثال: 24, 36" required>
                        @error('shelf_life_months')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">مدة الصلاحية بالأشهر (مثال: 24 = سنتان، 36 = 3 سنوات)</small>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="storage_conditions">ظروف التخزين (Storage Conditions) <span class="required">*</span></label>
                        <select id="storage_conditions" name="storage_conditions" class="form-control @error('storage_conditions') is-invalid @enderror" required>
                            <option value="">اختر ظروف التخزين</option>
                            <option value="Store below 25°C" {{ old('storage_conditions', $pharmaceuticalProduct->storage_conditions) == 'Store below 25°C' ? 'selected' : '' }}>Store below 25°C</option>
                            <option value="Store below 30°C" {{ old('storage_conditions', $pharmaceuticalProduct->storage_conditions) == 'Store below 30°C' ? 'selected' : '' }}>Store below 30°C</option>
                            <option value="Store below 0°C" {{ old('storage_conditions', $pharmaceuticalProduct->storage_conditions) == 'Store below 0°C' ? 'selected' : '' }}>Store below 0°C (Refrigerated)</option>
                            <option value="Store at room temperature" {{ old('storage_conditions', $pharmaceuticalProduct->storage_conditions) == 'Store at room temperature' ? 'selected' : '' }}>Store at room temperature</option>
                        </select>
                        @error('storage_conditions')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3>معلومات إضافية</h3>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="free_sale">نوع البيع (Free Sale) <span class="required">*</span></label>
                        <select id="free_sale" name="free_sale" class="form-control @error('free_sale') is-invalid @enderror" required>
                            <option value="">اختر نوع البيع</option>
                            <option value="Free Sale" {{ old('free_sale', $pharmaceuticalProduct->free_sale) == 'Free Sale' ? 'selected' : '' }}>Free Sale</option>
                            <option value="For Export Only" {{ old('free_sale', $pharmaceuticalProduct->free_sale) == 'For Export Only' ? 'selected' : '' }}>For Export Only</option>
                        </select>
                        @error('free_sale')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">حسب شهادة FSC/CPP</small>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="samples">العينات (Samples) <span class="required">*</span></label>
                        <select id="samples" name="samples" class="form-control @error('samples') is-invalid @enderror" required>
                            <option value="">اختر حالة العينات</option>
                            <option value="Samples Provided" {{ old('samples', $pharmaceuticalProduct->samples) == 'Samples Provided' ? 'selected' : '' }}>Samples Provided</option>
                            <option value="No Samples Provided" {{ old('samples', $pharmaceuticalProduct->samples) == 'No Samples Provided' ? 'selected' : '' }}>No Samples Provided</option>
                        </select>
                        @error('samples')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="pharmacopeal_ref">المرجع الدستوري (Pharmacopeal Reference) <span class="required">*</span></label>
                        <select id="pharmacopeal_ref" name="pharmacopeal_ref" class="form-control @error('pharmacopeal_ref') is-invalid @enderror" required>
                            <option value="">اختر المرجع</option>
                            <option value="BP" {{ old('pharmacopeal_ref', $pharmaceuticalProduct->pharmacopeal_ref) == 'BP' ? 'selected' : '' }}>BP (British Pharmacopoeia)</option>
                            <option value="USP" {{ old('pharmacopeal_ref', $pharmaceuticalProduct->pharmacopeal_ref) == 'USP' ? 'selected' : '' }}>USP (US Pharmacopoeia)</option>
                            <option value="EP" {{ old('pharmacopeal_ref', $pharmaceuticalProduct->pharmacopeal_ref) == 'EP' ? 'selected' : '' }}>EP (European Pharmacopoeia)</option>
                            <option value="IP" {{ old('pharmacopeal_ref', $pharmaceuticalProduct->pharmacopeal_ref) == 'IP' ? 'selected' : '' }}>IP (Indian Pharmacopoeia)</option>
                            <option value="JP" {{ old('pharmacopeal_ref', $pharmaceuticalProduct->pharmacopeal_ref) == 'JP' ? 'selected' : '' }}>JP (Japanese Pharmacopoeia)</option>
                        </select>
                        @error('pharmacopeal_ref')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">يجب أن يتطابق مع DMF وشهادة التحليل</small>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="item_classification">تصنيف الصنف (Item Classification) <span class="required">*</span></label>
                        <select id="item_classification" name="item_classification" class="form-control @error('item_classification') is-invalid @enderror" required>
                            <option value="">اختر التصنيف</option>
                            <option value="Requested Item" {{ old('item_classification', $pharmaceuticalProduct->item_classification) == 'Requested Item' ? 'selected' : '' }}>Requested Item - صنف مطلوب تسجيله</option>
                            <option value="Alternative Item" {{ old('item_classification', $pharmaceuticalProduct->item_classification) == 'Alternative Item' ? 'selected' : '' }}>Alternative Item - صنف بديل</option>
                            <option value="Optional Item" {{ old('item_classification', $pharmaceuticalProduct->item_classification) == 'Optional Item' ? 'selected' : '' }}>Optional Item - صنف اختياري</option>
                        </select>
                        @error('item_classification')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="ti ti-device-floppy me-1"></i>
                حفظ البيانات
            </button>
            <a href="{{ route('representative.pharmaceutical-products.show', $pharmaceuticalProduct) }}" class="btn btn-secondary">
                <i class="ti ti-x me-1"></i>
                إلغاء
            </a>
        </div>
    </form>

    @if($pharmaceuticalProduct->hasCompleteDetailedInfo())
    <div class="mt-4">
        <form action="{{ route('representative.pharmaceutical-products.submit-details', $pharmaceuticalProduct) }}" method="POST" id="submitDetailsForm">
            @csrf
            <button type="submit" class="btn btn-success btn-lg w-100">
                <i class="ti ti-send me-1"></i>
                إرسال البيانات للمراجعة النهائية
            </button>
        </form>
    </div>
    @else
    <div class="alert alert-warning mt-4">
        <i class="ti ti-alert-triangle me-2"></i>
        يرجى استكمال جميع البيانات التفصيلية قبل الإرسال للمراجعة النهائية.
    </div>
    @endif
</div>
@endsection

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Almarai:wght@300;400;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    * {
        font-family: 'Almarai', sans-serif !important;
    }

    .dashboard-container {
        background: #ffffff;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        padding: 30px;
        max-width: 1200px;
        margin: 0 auto;
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

    .alert {
        padding: 15px 20px;
        border-radius: 8px;
        border: 1px solid;
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }

    .alert-info {
        background: #dbeafe;
        color: #1e40af;
        border-color: #93c5fd;
    }

    .alert-warning {
        background: #fef3c7;
        color: #92400e;
        border-color: #fbbf24;
    }

    .product-form {
        margin-top: 20px;
    }

    .form-section {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 25px;
        margin-bottom: 25px;
    }

    .form-section h3 {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1a5f4a;
        margin: 0 0 20px 0;
        padding-bottom: 12px;
        border-bottom: 2px solid #e5e7eb;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        font-size: 0.9rem;
        font-weight: 500;
        color: #374151;
        margin-bottom: 8px;
    }

    .form-group .required {
        color: #dc2626;
        font-weight: 700;
    }

    .form-control {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 0.9rem;
        transition: all 0.2s ease;
        background: #ffffff;
    }

    .form-control:focus {
        outline: none;
        border-color: #1a5f4a;
        box-shadow: 0 0 0 3px rgba(26, 95, 74, 0.1);
    }

    .form-control.is-invalid {
        border-color: #dc2626;
    }

    .invalid-feedback {
        color: #dc2626;
        font-size: 0.875rem;
        margin-top: 5px;
        display: block;
    }

    .form-text {
        display: block;
        margin-top: 6px;
        font-size: 0.8125rem;
    }

    .text-muted {
        color: #6b7280;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        padding: 25px;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        margin-top: 30px;
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
    }

    .btn-primary {
        background: #1a5f4a;
        color: #ffffff;
    }

    .btn-primary:hover {
        background: #164538;
    }

    .btn-secondary {
        background: #6b7280;
        color: #ffffff;
    }

    .btn-secondary:hover {
        background: #4b5563;
    }

    .btn-success {
        background: #10b981;
        color: #ffffff;
    }

    .btn-success:hover {
        background: #059669;
    }

    .btn-lg {
        padding: 14px 28px;
        font-size: 1rem;
    }

    .w-100 {
        width: 100%;
    }

    .mt-4 {
        margin-top: 1.5rem;
    }

    .mb-4 {
        margin-bottom: 1.5rem;
    }

    .me-1 {
        margin-right: 0.25rem;
    }

    .me-2 {
        margin-right: 0.5rem;
    }

    @media (max-width: 768px) {
        .dashboard-container {
            padding: 20px 15px;
            border-radius: 0;
        }

        .page-header {
            flex-direction: column;
            gap: 15px;
            align-items: flex-start;
        }

        .page-header-content h1 {
            font-size: 1.25rem;
        }

        .form-section {
            padding: 20px 15px;
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.getElementById('submitDetailsForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;

        Swal.fire({
            title: 'إرسال للمراجعة النهائية',
            html: '<p>هل أنت متأكد من إرسال البيانات للمراجعة النهائية؟</p><p class="text-danger mt-2"><strong>لن تتمكن من التعديل بعد الإرسال.</strong></p>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'نعم، إرسال',
            cancelButtonText: 'إلغاء'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
</script>
@endpush
