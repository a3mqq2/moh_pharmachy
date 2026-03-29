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
                                <i class="fas fa-building fs-1 text-primary"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-0">{{ __('reports.local_companies_report') }}</h5>
                            <small class="text-muted">{{ __('reports.local_companies_report_desc') }}</small>
                        </div>
                    </div>

                    <p class="text-muted mb-4">
                        {{ __('reports.local_companies_report_detail') }}
                    </p>

                    <div class="d-grid">
                        <a href="{{ route('admin.reports.local-companies') }}" class="btn btn-primary">
                            <i class="fas fa-eye me-1"></i>
                            {{ __('reports.view_report') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="card h-100 border-info shadow-sm hover-card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0">
                            <div class="avtar avtar-lg bg-light-info">
                                <i class="fas fa-globe-americas fs-1 text-info"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-0">{{ __('reports.foreign_companies_report') }}</h5>
                            <small class="text-muted">{{ __('reports.foreign_companies_report_desc') }}</small>
                        </div>
                    </div>

                    <p class="text-muted mb-4">
                        {{ __('reports.foreign_companies_report_detail') }}
                    </p>

                    <div class="d-grid">
                        <a href="{{ route('admin.reports.foreign-companies') }}" class="btn btn-info">
                            <i class="fas fa-eye me-1"></i>
                            {{ __('reports.view_report') }}
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
                                <i class="fas fa-capsules fs-1 text-success"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-0">{{ __('reports.pharmaceutical_products_report') }}</h5>
                            <small class="text-muted">{{ __('reports.pharmaceutical_products_report_desc') }}</small>
                        </div>
                    </div>

                    <p class="text-muted mb-4">
                        {{ __('reports.pharmaceutical_products_report_detail') }}
                    </p>

                    <div class="d-grid">
                        <a href="{{ route('admin.reports.pharmaceutical-products') }}" class="btn btn-success">
                            <i class="fas fa-eye me-1"></i>
                            {{ __('reports.view_report') }}
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
                                <i class="fas fa-file-invoice-dollar fs-1 text-warning"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-0">{{ __('reports.invoices_report') }}</h5>
                            <small class="text-muted">{{ __('reports.invoices_report_desc') }}</small>
                        </div>
                    </div>

                    <p class="text-muted mb-4">
                        {{ __('reports.invoices_report_detail') }}
                    </p>

                    <div class="d-grid">
                        <a href="{{ route('admin.reports.invoices') }}" class="btn btn-warning">
                            <i class="fas fa-eye me-1"></i>
                            {{ __('reports.view_report') }}
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
