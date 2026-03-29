@php
  $menuCounts = Cache::remember('admin_menu_counts', 300, function () {
      return [
          'pending_review' => \App\Models\PharmaceuticalProduct::where('status', 'pending_review')->count(),
          'pending_payment' => \App\Models\PharmaceuticalProduct::where('status', 'pending_payment')->count(),
          'pending_foreign' => \App\Models\ForeignCompany::where('status', 'pending')->count(),
          'pending_local' => \App\Models\LocalCompany::where('status', 'pending')->count(),
      ];
  });
  $pendingReviewCount = $menuCounts['pending_review'];
  $pendingPaymentCount = $menuCounts['pending_payment'];
  $pendingForeignCount = $menuCounts['pending_foreign'];
  $pendingLocalCount = $menuCounts['pending_local'];
  $unreadNotifications = auth()->user()->unreadNotifications()->count();
  $totalPending = $pendingReviewCount + $pendingPaymentCount;

  $localCompaniesActive = request()->routeIs('admin.local-companies.*') || request()->is('admin/local-companies*');
  $foreignCompaniesActive = request()->routeIs('admin.foreign-companies.*') || request()->is('admin/foreign-companies*');
  $representativesActive = request()->routeIs('admin.company-representatives.*');
  $pharmaceuticalProductsActive = request()->routeIs('admin.pharmaceutical-products.*') || request()->is('admin/pharmaceutical-products*');

  $invoicesActive = request()->routeIs('admin.invoices.*');
  $reportsActive = request()->routeIs('admin.reports.*');
  $financeGroupActive = $invoicesActive || $reportsActive;

  $pendingUpdateRequests = \App\Models\DocumentUpdateRequest::where('status', 'pending')->count();
  $documentCenterActive = request()->routeIs('admin.document-center.*');
  $announcementsActive = request()->routeIs('admin.announcements.*');

  $usersActive = request()->routeIs('admin.users.*') || request()->is('admin/users*');
  $departmentsActive = request()->routeIs('admin.departments.*') || request()->is('admin/departments*');
  $settingsActive = request()->routeIs('admin.app-settings.*');
  $notificationsActive = request()->routeIs('admin.notifications.*');
  $systemGroupActive = $usersActive || $departmentsActive || $settingsActive || $notificationsActive;
@endphp

<li class="pc-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
  <a href="{{ route('admin.dashboard') }}" class="pc-link">
    <span class="pc-micon"><i class="ti ti-dashboard"></i></span>
    <span class="pc-mtext">{{ __('menu.home') }}</span>
  </a>
</li>

@can('view_local_companies')
<li class="pc-item pc-hasmenu {{ $localCompaniesActive ? 'active open pc-trigger' : '' }}">
  <a href="#!" class="pc-link">
    <span class="pc-micon"><i class="ti ti-building-skyscraper"></i></span>
    <span class="pc-mtext">{{ __('menu.local_companies') }}</span>
    @if($pendingLocalCount > 0)
      <span class="pc-badge">{{ $pendingLocalCount }}</span>
    @endif
    <span class="pc-arrow"><i class="fa fa-chevron-left"></i></span>
  </a>
  <ul class="pc-submenu list-unstyled">
    <li class="pc-item {{ request()->routeIs('admin.local-companies.index') && !request()->has('company_type') ? 'active' : '' }}">
      <a class="pc-link" href="{{ route('admin.local-companies.index') }}"><i class="ti ti-list me-1"></i>{{ __('menu.all_companies') }}</a>
    </li>
    <li class="pc-item {{ request()->query('company_type') == 'distributor' ? 'active' : '' }}">
      <a class="pc-link" href="{{ route('admin.local-companies.index', ['company_type' => 'distributor']) }}"><i class="ti ti-truck-delivery me-1"></i>{{ __('menu.distributors') }}</a>
    </li>
    <li class="pc-item {{ request()->query('company_type') == 'supplier' ? 'active' : '' }}">
      <a class="pc-link" href="{{ route('admin.local-companies.index', ['company_type' => 'supplier']) }}"><i class="ti ti-package me-1"></i>{{ __('menu.suppliers') }}</a>
    </li>
    @can('create_local_company')
    <li class="pc-item {{ request()->routeIs('admin.local-companies.create') ? 'active' : '' }}">
      <a class="pc-link" href="{{ route('admin.local-companies.create') }}"><i class="ti ti-plus me-1"></i>{{ __('menu.add_company') }}</a>
    </li>
    @endcan
  </ul>
</li>
@endcan

@can('view_foreign_companies')
<li class="pc-item pc-hasmenu {{ $foreignCompaniesActive ? 'active open pc-trigger' : '' }}">
  <a href="#!" class="pc-link">
    <span class="pc-micon"><i class="ti ti-world"></i></span>
    <span class="pc-mtext">{{ __('menu.foreign_companies') }}</span>
    @if($pendingForeignCount > 0)
      <span class="pc-badge">{{ $pendingForeignCount }}</span>
    @endif
    <span class="pc-arrow"><i class="fa fa-chevron-left"></i></span>
  </a>
  <ul class="pc-submenu list-unstyled">
    <li class="pc-item {{ request()->routeIs('admin.foreign-companies.index') ? 'active' : '' }}">
      <a class="pc-link" href="{{ route('admin.foreign-companies.index') }}"><i class="ti ti-list me-1"></i>{{ __('menu.all_companies') }}</a>
    </li>
    @can('create_foreign_company')
    <li class="pc-item {{ request()->routeIs('admin.foreign-companies.create') ? 'active' : '' }}">
      <a class="pc-link" href="{{ route('admin.foreign-companies.create') }}"><i class="ti ti-plus me-1"></i>{{ __('menu.add_foreign_company') }}</a>
    </li>
    @endcan
  </ul>
</li>
@endcan

@can('view_pharmaceutical_products')
<li class="pc-item pc-hasmenu {{ $pharmaceuticalProductsActive ? 'active open pc-trigger' : '' }}">
  <a href="#!" class="pc-link">
    <span class="pc-micon"><i class="ti ti-pill"></i></span>
    <span class="pc-mtext">{{ __('menu.pharmaceutical_products') }}</span>
    @if($totalPending > 0)
      <span class="pc-badge">{{ $totalPending }}</span>
    @endif
    <span class="pc-arrow"><i class="fa fa-chevron-left"></i></span>
  </a>
  <ul class="pc-submenu list-unstyled">
    <li class="pc-item {{ request()->routeIs('admin.pharmaceutical-products.index') && !request()->has('status') ? 'active' : '' }}">
      <a class="pc-link" href="{{ route('admin.pharmaceutical-products.index') }}"><i class="ti ti-list me-1"></i>{{ __('menu.all_products') }}</a>
    </li>
    <li class="pc-item {{ request('status') == 'pending_review' ? 'active' : '' }}">
      <a class="pc-link" href="{{ route('admin.pharmaceutical-products.index', ['status' => 'pending_review']) }}">
        <i class="ti ti-clock me-1"></i>{{ __('menu.pending_review') }}
        @if($pendingReviewCount > 0)
          <span class="badge bg-warning ms-1">{{ $pendingReviewCount }}</span>
        @endif
      </a>
    </li>
    <li class="pc-item {{ request('status') == 'pending_payment' ? 'active' : '' }}">
      <a class="pc-link" href="{{ route('admin.pharmaceutical-products.index', ['status' => 'pending_payment']) }}">
        <i class="ti ti-credit-card me-1"></i>{{ __('menu.pending_payment') }}
        @if($pendingPaymentCount > 0)
          <span class="badge bg-warning ms-1">{{ $pendingPaymentCount }}</span>
        @endif
      </a>
    </li>
    <li class="pc-item {{ request('status') == 'active' ? 'active' : '' }}">
      <a class="pc-link" href="{{ route('admin.pharmaceutical-products.index', ['status' => 'active']) }}"><i class="ti ti-circle-check me-1"></i>{{ __('menu.activated') }}</a>
    </li>
  </ul>
</li>
@endcan

@can('view_representatives')
<li class="pc-item {{ $representativesActive ? 'active' : '' }}">
  <a href="{{ route('admin.company-representatives.index') }}" class="pc-link">
    <span class="pc-micon"><i class="ti ti-id"></i></span>
    <span class="pc-mtext">{{ __('menu.company_representatives') }}</span>
  </a>
</li>
@endcan

@canany(['view_invoices', 'view_reports'])
<li class="pc-item pc-hasmenu {{ $financeGroupActive ? 'active open pc-trigger' : '' }}">
  <a href="#!" class="pc-link">
    <span class="pc-micon"><i class="ti ti-report-money"></i></span>
    <span class="pc-mtext">{{ __('menu.finance_reports') }}</span>
    <span class="pc-arrow"><i class="fa fa-chevron-left"></i></span>
  </a>
  <ul class="pc-submenu list-unstyled">
    @can('view_invoices')
    <li class="pc-item {{ $invoicesActive ? 'active' : '' }}">
      <a class="pc-link" href="{{ route('admin.invoices.index') }}"><i class="ti ti-file-invoice me-1"></i>{{ __('menu.invoices') }}</a>
    </li>
    @endcan
    @can('view_reports')
    <li class="pc-item {{ $reportsActive ? 'active' : '' }}">
      <a class="pc-link" href="{{ route('admin.reports.index') }}"><i class="ti ti-chart-bar me-1"></i>{{ __('menu.reports') }}</a>
    </li>
    @endcan
  </ul>
</li>
@endcanany

@can('view_announcements')
<li class="pc-item {{ $announcementsActive ? 'active' : '' }}">
  <a href="{{ route('admin.announcements.index') }}" class="pc-link">
    <span class="pc-micon"><i class="ti ti-speakerphone"></i></span>
    <span class="pc-mtext">{{ __('menu.announcements') }}</span>
  </a>
</li>
@endcan

@canany(['manage_admin_documents', 'view_company_archive', 'manage_shared_files'])
<li class="pc-item pc-hasmenu {{ $documentCenterActive ? 'active open pc-trigger' : '' }}">
  <a href="#!" class="pc-link">
    <span class="pc-micon"><i class="ti ti-folder"></i></span>
    <span class="pc-mtext">{{ __('menu.document_center') }}</span>
    <span class="pc-arrow"><i class="fa fa-chevron-left"></i></span>
  </a>
  <ul class="pc-submenu list-unstyled">
    @can('manage_admin_documents')
    <li class="pc-item {{ request()->routeIs('admin.document-center.admin-documents') ? 'active' : '' }}">
      <a class="pc-link" href="{{ route('admin.document-center.admin-documents') }}"><i class="ti ti-files me-1"></i>{{ __('menu.admin_documents') }}</a>
    </li>
    @endcan
    @can('view_company_archive')
    <li class="pc-item {{ request()->routeIs('admin.document-center.company-archive') ? 'active' : '' }}">
      <a class="pc-link" href="{{ route('admin.document-center.company-archive') }}"><i class="ti ti-archive me-1"></i>{{ __('menu.company_archive') }}</a>
    </li>
    @endcan
    @can('view_pharmaceutical_products')
    <li class="pc-item {{ request()->routeIs('admin.document-center.product-archive') ? 'active' : '' }}">
      <a class="pc-link" href="{{ route('admin.document-center.product-archive') }}"><i class="ti ti-pill me-1"></i>{{ __('menu.product_archive') }}</a>
    </li>
    @endcan
    @can('view_company_archive')
    <li class="pc-item {{ request()->routeIs('admin.document-center.update-requests') ? 'active' : '' }}">
      <a class="pc-link" href="{{ route('admin.document-center.update-requests') }}">
        <i class="ti ti-replace me-1"></i>{{ __('menu.update_requests') }}
        @if($pendingUpdateRequests > 0)
          <span class="badge bg-warning ms-1">{{ $pendingUpdateRequests }}</span>
        @endif
      </a>
    </li>
    @endcan
  </ul>
</li>
@endcanany

@canany(['view_users', 'manage_departments', 'manage_settings'])
<li class="pc-item pc-hasmenu {{ $systemGroupActive ? 'active open pc-trigger' : '' }}">
  <a href="#!" class="pc-link">
    <span class="pc-micon"><i class="ti ti-settings"></i></span>
    <span class="pc-mtext">{{ __('menu.admin_settings') }}</span>
    @if($unreadNotifications > 0)
      <span class="pc-badge">{{ $unreadNotifications }}</span>
    @endif
    <span class="pc-arrow"><i class="fa fa-chevron-left"></i></span>
  </a>
  <ul class="pc-submenu list-unstyled">
    @can('view_users')
    <li class="pc-item {{ $usersActive ? 'active' : '' }}">
      <a class="pc-link" href="{{ route('admin.users.index') }}"><i class="ti ti-users me-1"></i>{{ __('menu.users') }}</a>
    </li>
    @endcan
    @can('manage_departments')
    <li class="pc-item {{ $departmentsActive ? 'active' : '' }}">
      <a class="pc-link" href="{{ route('admin.departments.index') }}"><i class="ti ti-sitemap me-1"></i>{{ __('menu.org_structure') }}</a>
    </li>
    @endcan
    @can('manage_settings')
    <li class="pc-item {{ $settingsActive ? 'active' : '' }}">
      <a class="pc-link" href="{{ route('admin.app-settings.index') }}"><i class="ti ti-adjustments me-1"></i>{{ __('menu.system_settings') }}</a>
    </li>
    @endcan
    <li class="pc-item {{ $notificationsActive ? 'active' : '' }}">
      <a class="pc-link" href="{{ route('admin.notifications.index') }}">
        <i class="ti ti-bell me-1"></i>{{ __('menu.notifications') }}
        @if($unreadNotifications > 0)
          <span class="badge bg-danger ms-1">{{ $unreadNotifications }}</span>
        @endif
      </a>
    </li>
  </ul>
</li>
@endcanany

<li class="pc-item" style="margin-top: 10px; border-top: 1px solid rgba(0,0,0,0.1); padding-top: 10px;">
  <a href="#" class="pc-link" onclick="event.preventDefault(); document.getElementById('logout-form-sidebar').submit();">
    <span class="pc-micon"><i class="ti ti-logout text-danger"></i></span>
    <span class="pc-mtext text-danger">{{ __('menu.logout') }}</span>
  </a>
  <form id="logout-form-sidebar" action="{{ route('admin.logout') }}" method="POST" style="display: none;">
    @csrf
  </form>
</li>
