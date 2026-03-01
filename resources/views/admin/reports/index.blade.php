@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="row g-4">
        <div class="col-lg-4 col-md-6">
            <div class="card h-100 border-primary shadow-sm hover-card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0">
                            <div class="avtar avtar-lg bg-light-primary">
                                <i class="ti ti-building fs-1 text-primary"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-0">تقرير الشركات المحلية</h5>
                            <small class="text-muted">تقرير شامل عن جميع الشركات المسجلة</small>
                        </div>
                    </div>

                    <p class="text-muted mb-4">
                        يعرض هذا التقرير معلومات مفصلة عن الشركات المحلية المسجلة في النظام مع إحصائيات حسب الحالة
                    </p>

                    <div class="d-grid">
                        <a href="{{ route('admin.reports.local-companies') }}" class="btn btn-primary">
                            <i class="ti ti-eye me-1"></i>
                            عرض التقرير
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="card h-100 border-success shadow-sm hover-card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0">
                            <div class="avtar avtar-lg bg-light-success">
                                <i class="ti ti-pill fs-1 text-success"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-0">تقرير الأصناف الدوائية</h5>
                            <small class="text-muted">تقرير شامل عن جميع الأصناف المسجلة</small>
                        </div>
                    </div>

                    <p class="text-muted mb-4">
                        يعرض هذا التقرير معلومات مفصلة عن الأصناف الدوائية المسجلة مع إحصائيات حسب الحالة
                    </p>

                    <div class="d-grid">
                        <a href="{{ route('admin.reports.pharmaceutical-products') }}" class="btn btn-success">
                            <i class="ti ti-eye me-1"></i>
                            عرض التقرير
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="card h-100 border-warning shadow-sm hover-card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0">
                            <div class="avtar avtar-lg bg-light-warning">
                                <i class="ti ti-receipt fs-1 text-warning"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-0">تقرير الفواتير</h5>
                            <small class="text-muted">تقرير شامل عن جميع الفواتير</small>
                        </div>
                    </div>

                    <p class="text-muted mb-4">
                        يعرض هذا التقرير معلومات مفصلة عن الفواتير الصادرة وإحصائيات الإيرادات
                    </p>

                    <div class="d-grid">
                        <a href="{{ route('admin.reports.invoices') }}" class="btn btn-warning">
                            <i class="ti ti-eye me-1"></i>
                            عرض التقرير
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.hover-card {
    transition: all 0.3s ease;
}

.hover-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important;
}

.avtar {
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
}

.avtar-lg {
    width: 60px;
    height: 60px;
}
</style>
@endsection
