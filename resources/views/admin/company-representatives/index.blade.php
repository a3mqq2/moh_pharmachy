@extends('layouts.app')

@section('title', 'ممثلي الشركات')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">ممثلي الشركات</li>
@endsection

@section('content')

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <h5 class="mb-0"><i class="fas fa-id-card me-2"></i>ممثلي الشركات</h5>
                <span class="badge bg-secondary">{{ $representatives->total() }} ممثل</span>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filtersCollapse">
                    <i class="fas fa-filter me-1"></i> الفلاتر
                </button>
            </div>
        </div>
    </div>
    <div class="collapse {{ request()->hasAny(['search', 'verified', 'has_companies']) ? 'show' : '' }}" id="filtersCollapse">
        <div class="card-body border-top bg-light pt-3">
            <form method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">بحث</label>
                        <input type="text" name="search" class="form-control" placeholder="الاسم، البريد الإلكتروني، الهاتف..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">حالة التوثيق</label>
                        <select name="verified" class="form-select">
                            <option value="">الكل</option>
                            <option value="1" {{ request('verified') === '1' ? 'selected' : '' }}>موثق</option>
                            <option value="0" {{ request('verified') === '0' ? 'selected' : '' }}>غير موثق</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">الشركات</label>
                        <select name="has_companies" class="form-select">
                            <option value="">الكل</option>
                            <option value="local" {{ request('has_companies') === 'local' ? 'selected' : '' }}>لديه شركات محلية</option>
                            <option value="foreign" {{ request('has_companies') === 'foreign' ? 'selected' : '' }}>لديه شركات أجنبية</option>
                            <option value="both" {{ request('has_companies') === 'both' ? 'selected' : '' }}>لديه النوعين</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex gap-2 justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i> بحث
                            </button>
                            @if(request()->hasAny(['search', 'verified', 'has_companies']))
                                <a href="{{ route('admin.company-representatives.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i> مسح الفلاتر
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-bordered mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الاسم</th>
                        <th>المسمى الوظيفي</th>
                        <th>البريد الإلكتروني</th>
                        <th>الهاتف</th>
                        <th>الشركات المحلية</th>
                        <th>الشركات الأجنبية</th>
                        <th>الحالة</th>
                        <th>تاريخ التسجيل</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($representatives as $rep)
                    <tr onclick="window.location='{{ route('admin.company-representatives.show', $rep->id) }}'" style="cursor: pointer;">
                        <td>
                            <span class="badge bg-dark">{{ $loop->iteration + ($representatives->currentPage() - 1) * $representatives->perPage() }}</span>
                        </td>
                        <td>
                            <strong>{{ $rep->name }}</strong>
                        </td>
                        <td>{{ $rep->job_title ?? '-' }}</td>
                        <td>
                            <a href="mailto:{{ $rep->email }}" onclick="event.stopPropagation();">{{ $rep->email }}</a>
                        </td>
                        <td>
                            <div class="d-flex gap-2 align-items-center">
                                @if($rep->phone)
                                <a href="tel:{{ $rep->phone }}" class="text-decoration-none" onclick="event.stopPropagation();" title="اتصال">
                                    <i class="fas fa-phone text-primary"></i>
                                </a>
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $rep->phone) }}" target="_blank" class="text-decoration-none" onclick="event.stopPropagation();" title="واتساب">
                                    <i class="fab fa-whatsapp text-success"></i>
                                </a>
                                <small class="text-muted" dir="ltr">{{ $rep->phone }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if($rep->companies_count > 0)
                                <span class="badge bg-info">{{ $rep->companies_count }}</span>
                            @else
                                <span class="text-muted">0</span>
                            @endif
                        </td>
                        <td>
                            @if($rep->foreign_companies_count > 0)
                                <span class="badge bg-warning">{{ $rep->foreign_companies_count }}</span>
                            @else
                                <span class="text-muted">0</span>
                            @endif
                        </td>
                        <td>
                            @if($rep->is_verified)
                                <span class="badge bg-success">موثق</span>
                            @else
                                <span class="badge bg-danger">غير موثق</span>
                            @endif
                        </td>
                        <td>
                            <small>{{ $rep->created_at->format('Y-m-d') }}</small><br>
                            <small class="text-muted">{{ $rep->created_at->format('h:i A') }}</small>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-id-card fs-1 d-block mb-2"></i>
                                لا يوجد ممثلين مسجلين
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($representatives->hasPages())
    <div class="card-footer">
        <div class="d-flex justify-content-center">
            {{ $representatives->withQueryString()->links() }}
        </div>
    </div>
    @endif
</div>

@endsection
