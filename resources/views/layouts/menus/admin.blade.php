<li class="pc-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
  <a href="{{ route('admin.dashboard') }}" class="pc-link">
      <span class="pc-micon">
          <svg class="pc-icon">
              <use xlink:href="#custom-status-up"></use>
          </svg>
      </span>
      <span class="pc-mtext">الصفحة الرئيسية</span>
  </a>
</li>

@php
  $usersActive = request()->routeIs('admin.users.*') || request()->is('admin/users*');
@endphp

<li class="pc-item pc-hasmenu {{ $usersActive ? 'active open pc-trigger' : '' }}">
  <a href="#!" class="pc-link">
    <span class="pc-micon">
      <svg class="pc-icon">
        <use xlink:href="#custom-profile-2user-outline"></use>
      </svg>
    </span>
    <span class="pc-mtext"> المستخدمين </span>
    <span class="pc-arrow">
        <i class="fa fa-chevron-left"></i>
    </span>
  </a>

  <ul class="pc-submenu list-unstyled">
    <li class="pc-item {{ request()->routeIs('admin.users.create') ? 'active' : '' }}" >
      <a class="pc-link" href="{{route('admin.users.create')}}">إضافة مستخدم جديد</a>
    </li>
    <li class="pc-item {{ request()->routeIs('admin.users.index') ? 'active' : '' }}" >
      <a class="pc-link" href="{{route('admin.users.index')}}">عرض جميع المستخدمين</a>
    </li>
  </ul>
</li>

@php
  $localCompaniesActive = request()->routeIs('admin.local-companies.*') || request()->is('admin/local-companies*');
  $isDistributorFilter = request()->query('company_type') == 'distributor';
  $isSupplierFilter = request()->query('company_type') == 'supplier';
@endphp

<li class="pc-item pc-hasmenu {{ $localCompaniesActive ? 'active open pc-trigger' : '' }}">
  <a href="#!" class="pc-link">
    <span class="pc-micon">
      <i class="ti ti-building-skyscraper"></i>
    </span>
    <span class="pc-mtext">الشركات المحلية</span>
    <span class="pc-arrow">
        <i class="fa fa-chevron-left"></i>
    </span>
  </a>

  <ul class="pc-submenu list-unstyled">
    <li class="pc-item {{ request()->routeIs('admin.local-companies.create') ? 'active' : '' }}">
      <a class="pc-link" href="{{ route('admin.local-companies.create') }}">إضافة شركة جديدة</a>
    </li>
    <li class="pc-item {{ request()->routeIs('admin.local-companies.index') && $isDistributorFilter ? 'active' : '' }}">
      <a class="pc-link" href="{{ route('admin.local-companies.index', ['company_type' => 'distributor']) }}">
        <i class="ti ti-truck-delivery me-1"></i>الشركات الموزعة
      </a>
    </li>
    <li class="pc-item {{ request()->routeIs('admin.local-companies.index') && $isSupplierFilter ? 'active' : '' }}">
      <a class="pc-link" href="{{ route('admin.local-companies.index', ['company_type' => 'supplier']) }}">
        <i class="ti ti-package me-1"></i>الشركات الموردة
      </a>
    </li>
    <li class="pc-item {{ request()->routeIs('admin.local-companies.index') && !$isDistributorFilter && !$isSupplierFilter ? 'active' : '' }}">
      <a class="pc-link" href="{{ route('admin.local-companies.index') }}">عرض جميع الشركات</a>
    </li>
  </ul>
</li>

@php
  $foreignCompaniesActive = request()->routeIs('admin.foreign-companies.*') || request()->is('admin/foreign-companies*') || request()->routeIs('admin.foreign-company-invoices.*');
@endphp

<li class="pc-item pc-hasmenu {{ $foreignCompaniesActive ? 'active open pc-trigger' : '' }}">
  <a href="#!" class="pc-link">
    <span class="pc-micon">
      <i class="ti ti-world"></i>
    </span>
    <span class="pc-mtext">الشركات الأجنبية</span>
    <span class="pc-arrow">
        <i class="fa fa-chevron-left"></i>
    </span>
  </a>

  <ul class="pc-submenu list-unstyled">
    <li class="pc-item {{ request()->routeIs('admin.foreign-companies.index') ? 'active' : '' }}">
      <a class="pc-link" href="{{ route('admin.foreign-companies.index') }}">عرض جميع الشركات</a>
    </li>
    <li class="pc-item {{ request()->routeIs('admin.foreign-company-invoices.index') ? 'active' : '' }}">
      <a class="pc-link" href="{{ route('admin.foreign-company-invoices.index') }}">
        <i class="ti ti-file-invoice me-1"></i>الفواتير
      </a>
    </li>
  </ul>
</li>

@php
  $pharmaceuticalProductsActive = request()->routeIs('admin.pharmaceutical-products.*') || request()->is('admin/pharmaceutical-products*');
@endphp

<li class="pc-item pc-hasmenu {{ $pharmaceuticalProductsActive ? 'active open pc-trigger' : '' }}">
  <a href="#!" class="pc-link">
    <span class="pc-micon">
      <i class="ti ti-pill"></i>
    </span>
    <span class="pc-mtext">الأصناف الدوائية</span>
    <span class="pc-arrow">
        <i class="fa fa-chevron-left"></i>
    </span>
  </a>

  <ul class="pc-submenu list-unstyled">
    <li class="pc-item {{ request()->routeIs('admin.pharmaceutical-products.index') && !request()->has('status') ? 'active' : '' }}">
      <a class="pc-link" href="{{ route('admin.pharmaceutical-products.index') }}">عرض جميع الأصناف</a>
    </li>
    <li class="pc-item {{ request()->routeIs('admin.pharmaceutical-products.index') && request('status') == 'pending_review' ? 'active' : '' }}">
      <a class="pc-link" href="{{ route('admin.pharmaceutical-products.index', ['status' => 'pending_review']) }}">
        <i class="ti ti-clock me-1"></i>قيد المراجعة
        @php
          $pendingCount = \App\Models\PharmaceuticalProduct::where('status', 'pending_review')->count();
        @endphp
        @if($pendingCount > 0)
          <span class="badge bg-warning ms-2">{{ $pendingCount }}</span>
        @endif
      </a>
    </li>
    <li class="pc-item {{ request()->routeIs('admin.pharmaceutical-products.index') && request('status') == 'pending_payment' ? 'active' : '' }}">
      <a class="pc-link" href="{{ route('admin.pharmaceutical-products.index', ['status' => 'pending_payment']) }}">
        <i class="ti ti-file-invoice me-1"></i>قيد السداد
        @php
          $pendingPaymentCount = \App\Models\PharmaceuticalProduct::where('status', 'pending_payment')->count();
        @endphp
        @if($pendingPaymentCount > 0)
          <span class="badge bg-warning ms-2">{{ $pendingPaymentCount }}</span>
        @endif
      </a>
    </li>
    <li class="pc-item {{ request()->routeIs('admin.pharmaceutical-products.index') && request('status') == 'active' ? 'active' : '' }}">
      <a class="pc-link" href="{{ route('admin.pharmaceutical-products.index', ['status' => 'active']) }}">
        <i class="ti ti-check me-1"></i>المفعلة
      </a>
    </li>
  </ul>
</li>

<li class="pc-item {{ request()->routeIs('admin.invoices.*') ? 'active' : '' }}">
  <a href="{{ route('admin.invoices.index') }}" class="pc-link">
      <span class="pc-micon">
          <i class="ti ti-file-invoice"></i>
      </span>
      <span class="pc-mtext">الفواتير</span>
  </a>
</li>

<li class="pc-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
  <a href="{{ route('admin.reports.index') }}" class="pc-link">
      <span class="pc-micon">
          <i class="ti ti-file-analytics"></i>
      </span>
      <span class="pc-mtext">التقارير</span>
  </a>
</li>

@php
  $settingsActive = request()->routeIs('admin.app-settings.*') || request()->routeIs('admin.notifications.*');
@endphp

<li class="pc-item pc-hasmenu {{ $settingsActive ? 'active open pc-trigger' : '' }}">
  <a href="#!" class="pc-link">
    <span class="pc-micon">
      <i class="ti ti-settings"></i>
    </span>
    <span class="pc-mtext">الإعدادات</span>
    <span class="pc-arrow">
        <i class="fa fa-chevron-left"></i>
    </span>
  </a>

  <ul class="pc-submenu list-unstyled">
    <li class="pc-item {{ request()->routeIs('admin.app-settings.*') ? 'active' : '' }}">
      <a class="pc-link" href="{{ route('admin.app-settings.index') }}">
        <i class="ti ti-adjustments me-1"></i>إعدادات النظام
      </a>
    </li>
    <li class="pc-item {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
      <a class="pc-link" href="{{ route('admin.notifications.index') }}">
        <i class="ti ti-bell me-1"></i>الإشعارات
        @if(auth()->user()->unreadNotifications->count() > 0)
        <span class="badge bg-danger ms-2">{{ auth()->user()->unreadNotifications->count() }}</span>
        @endif
      </a>
    </li>
  </ul>
</li>
