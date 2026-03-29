@extends('layouts.auth')

@section('title', __('products.pharmaceutical_products'))

@section('content')
<div class="dashboard-container">
    <div class="page-header">
        <div class="page-header-content">
            <a href="{{ route('representative.dashboard') }}" class="back-to-home">
                <i class="ti ti-arrow-right"></i>
            </a>
            <div>
                <h1>{{ __('products.pharmaceutical_products') }}</h1>
                <p>{{ __('products.all_products_desc') }}</p>
            </div>
        </div>
        @if($activeForeignCompaniesCount > 0)
            <a href="{{ route('representative.pharmaceutical-products.create') }}" class="btn btn-primary">
                <i class="ti ti-plus"></i>
                {{ __('products.register_new') }}
            </a>
        @endif
    </div>

    @if($activeForeignCompaniesCount == 0)
        <div class="empty-state">
            <h2>{{ __('products.not_available_now') }}</h2>
            <p>{{ __('products.must_have_foreign') }}</p>
            <a href="{{ route('representative.foreign-companies.index') }}" class="primary-btn">
                <i class="ti ti-world"></i>
                {{ __('products.manage_foreign') }}
            </a>
        </div>
    @elseif($products->count() > 0)
        <div class="products-table desktop-only">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('products.trade_name') }}</th>
                        <th>{{ __('products.scientific_name') }}</th>
                        <th>{{ __('products.dosage_form') }}</th>
                        <th>{{ __('products.concentration_short') }}</th>
                        <th>{{ __('products.foreign_company') }}</th>
                        <th>{{ __('products.usage_method') }}</th>
                        <th>{{ __('general.registration_date') }}</th>
                        <th>{{ __('general.status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $index => $product)
                    <tr class="clickable-row" data-href="{{ route('representative.pharmaceutical-products.show', $product) }}">
                        <td>{{ $products->firstItem() + $index }}</td>
                        <td>
                            <strong>{{ $product->product_name }}</strong>
                            @if($product->registration_number)
                                <br><small style="color: #6b7280;">{{ $product->registration_number }}</small>
                            @endif
                        </td>
                        <td>{{ $product->scientific_name }}</td>
                        <td>{{ $product->pharmaceutical_form }}</td>
                        <td>{{ $product->concentration }}</td>
                        <td>{{ $product->foreignCompany->company_name }}</td>
                        <td>{{ $product->usage_methods_text }}</td>
                        <td>{{ $product->created_at->format('Y-m-d') }}</td>
                        <td>
                            <span class="badge {{ $product->status_badge_class }}">
                                {{ $product->status_name }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="products-cards mobile-only">
            @foreach($products as $product)
            <div class="product-card clickable-card" data-href="{{ route('representative.pharmaceutical-products.show', $product) }}">
                <div class="card-header">
                    <div class="product-name">
                        <i class="ti ti-pill"></i>
                        <h3>{{ $product->product_name }}</h3>
                    </div>
                    <span class="badge {{ $product->status_badge_class }}">
                        {{ $product->status_name }}
                    </span>
                </div>
                <div class="card-body">
                    @if($product->registration_number)
                    <div class="info-row">
                        <span class="label">{{ __('general.registration_number') }}:</span>
                        <span class="value">{{ $product->registration_number }}</span>
                    </div>
                    @endif
                    <div class="info-row">
                        <span class="label">{{ __('products.dosage_form') }}:</span>
                        <span class="value">{{ $product->pharmaceutical_form }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">{{ __('products.concentration_short') }}:</span>
                        <span class="value">{{ $product->concentration }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">{{ __('products.foreign_company') }}:</span>
                        <span class="value">{{ $product->foreignCompany->company_name }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">{{ __('products.usage_method') }}:</span>
                        <span class="value">{{ $product->usage_methods_text }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">{{ __('general.registration_date') }}:</span>
                        <span class="value">{{ $product->created_at->format('Y-m-d') }}</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        @if($products->hasPages())
        <div class="pagination-wrapper">
            {{ $products->links() }}
        </div>
        @endif
    @else
        <div class="empty-state">
            <h2>{{ __('products.no_products') }}</h2>
            <p>{{ __('products.no_products_yet') }}</p>
            <a href="{{ route('representative.pharmaceutical-products.create') }}" class="primary-btn">
                <i class="ti ti-plus"></i>
                {{ __('products.register_new') }}
            </a>
        </div>
    @endif
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
        max-width: 1600px;
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

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border-radius: 6px;
        font-size: 0.9rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .btn-primary {
        background: #1a5f4a;
        color: #ffffff;
        border: none;
    }

    .btn-primary:hover {
        background: #164538;
    }

    .products-table {
        overflow-x: auto;
        margin-bottom: 20px;
    }

    .products-table table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
    }

    .products-table thead {
        background: #f9fafb;
    }

    .products-table th {
        padding: 12px 16px;
        text-align: right;
        font-size: 0.875rem;
        font-weight: 600;
        color: #374151;
        border-bottom: 1px solid #e5e7eb;
    }

    .products-table td {
        padding: 12px 16px;
        font-size: 0.875rem;
        color: #1f2937;
        border-bottom: 1px solid #e5e7eb;
    }

    .products-table tbody tr:last-child td {
        border-bottom: none;
    }

    .clickable-row {
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .clickable-row:hover {
        background: #f0fdf4 !important;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(26, 95, 74, 0.1);
    }

    .badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .badge-info {
        background: #dbeafe;
        color: #1e40af;
    }

    .badge-warning {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-success {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-danger {
        background: #fee2e2;
        color: #991b1b;
    }

    .badge-secondary {
        background: #e5e7eb;
        color: #374151;
    }

    .desktop-only {
        display: block !important;
    }

    .mobile-only {
        display: none !important;
    }

    .products-table.desktop-only {
        display: block !important;
    }

    .products-cards.mobile-only {
        display: none !important;
    }

    .products-cards {
        display: grid;
        grid-template-columns: 1fr;
        gap: 15px;
        margin-bottom: 20px;
    }

    .product-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        overflow: hidden;
    }

    .clickable-card {
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .clickable-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(26, 95, 74, 0.15);
        border-color: #1a5f4a;
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px;
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
    }

    .product-name {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .product-name i {
        font-size: 1.5rem;
        color: #1a5f4a;
    }

    .product-name h3 {
        font-size: 1rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
    }

    .card-body {
        padding: 15px;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
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

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: #f9fafb;
        border: 1px dashed #d1d5db;
        border-radius: 8px;
    }

    .empty-state h2 {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0 0 10px 0;
    }

    .empty-state p {
        font-size: 0.95rem;
        color: #6b7280;
        margin: 0 0 25px 0;
    }

    .primary-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        background: #1a5f4a;
        color: #ffffff;
        border: none;
        border-radius: 6px;
        font-size: 0.95rem;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .primary-btn:hover {
        background: #164538;
    }

    .pagination-wrapper {
        margin-top: 20px;
    }

    @media (max-width: 992px) {
        .desktop-only {
            display: none !important;
        }

        .mobile-only {
            display: block !important;
        }

        .products-table.desktop-only {
            display: none !important;
        }

        .products-cards.mobile-only {
            display: grid !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.querySelectorAll('.clickable-row').forEach(row => {
        row.addEventListener('click', function() {
            window.location.href = this.dataset.href;
        });
    });

    document.querySelectorAll('.clickable-card').forEach(card => {
        card.addEventListener('click', function() {
            window.location.href = this.dataset.href;
        });
    });
</script>
@endpush
