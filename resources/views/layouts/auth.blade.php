<!doctype html>
<html lang="ar" dir="rtl">
  <!-- [Head] start -->
  <head>
    <title>@yield('title', 'وزارة الصحة - إدارة الصيدلة')</title>
    <!-- [Meta] -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta
      name="description"
      content="وزارة الصحة - إدارة الصيدلة"
    />
    <meta
      name="keywords"
      content="وزارة الصحة, إدارة الصيدلة, الأدوية, الصيدليات"
    />
    <meta name="author" content="Safe Tech" />

    <!-- [Favicon] icon -->
    <link rel="icon" href="{{ asset('logo-primary.png') }}" type="image/png" />
    
    <!-- [Font] Almarai from Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Almarai:wght@300;400;700;800&display=swap" rel="stylesheet">
    
    <!-- [Font] Family -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/inter/inter.css') }}" id="main-font-link" />
    <!-- [phosphor Icons] https://phosphoricons.com/ -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/phosphor/duotone/style.css') }}" />
    <!-- [Tabler Icons] https://tablericons.com -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}" />
    <!-- [Feather Icons] https://feathericons.com -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/feather.css') }}" />
    <!-- [Font Awesome Icons] https://fontawesome.com/icons -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/fontawesome.css') }}" />
    <!-- [Material Icons] https://fonts.google.com/icons -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/material.css') }}" />
    <!-- [Template CSS Files] -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="main-style-link" />
    <script src="{{ asset('assets/js/tech-stack.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('assets/css/style-preset.css') }}" />
    
    <style>
      body {
        font-family: 'Almarai', sans-serif;
        background-color: #f8f9fa;
        min-height: 100vh;
      }

      /* Islamic Geometric Pattern Background */
      .auth-main {
        position: relative;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
      }

      .auth-main::before {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        opacity: 0.04;
        pointer-events: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='80' height='80' viewBox='0 0 80 80'%3E%3Cg fill='%231a5f4a' fill-opacity='1'%3E%3Cpath d='M40 0L20 20L0 0h10l10 10L30 0h10zm0 80L20 60L0 80h10l10-10 10 10h10zm40-80L60 20 80 0H70L60 10 50 0h10zM80 80L60 60l20-20v10L70 60l10 10v10zM40 40l-10-10-10 10 10 10 10-10zm0 0l10-10 10 10-10 10-10-10zM20 20L10 30l10 10 10-10-10-10zm40 0l-10 10 10 10 10-10-10-10zM20 60l10-10-10-10-10 10 10 10zm40 0l10-10-10-10-10 10 10 10z'/%3E%3C/g%3E%3C/svg%3E");
      }

      /* Decorative corner ornaments */

      .corner-ornament-bl {
        position: fixed;
        bottom: 20px;
        left: 20px;
        width: 120px;
        height: 120px;
        opacity: 0.08;
        pointer-events: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Cpath fill='%231a5f4a' d='M50 0C22.4 0 0 22.4 0 50s22.4 50 50 50 50-22.4 50-50S77.6 0 50 0zm0 90c-22.1 0-40-17.9-40-40s17.9-40 40-40 40 17.9 40 40-17.9 40-40 40z'/%3E%3Cpath fill='%231a5f4a' d='M50 15c-19.3 0-35 15.7-35 35s15.7 35 35 35 35-15.7 35-35-15.7-35-35-35zm0 60c-13.8 0-25-11.2-25-25s11.2-25 25-25 25 11.2 25 25-11.2 25-25 25z'/%3E%3Cpath fill='%231a5f4a' d='M50 30c-11 0-20 9-20 20s9 20 20 20 20-9 20-20-9-20-20-20zm0 30c-5.5 0-10-4.5-10-10s4.5-10 10-10 10 4.5 10 10-4.5 10-10 10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-size: contain;
        transform: rotate(180deg);
      }

      .auth-wrapper.v3 {
        background: transparent;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
        width: 100%;
        padding: 20px;
      }

      .auth-form {
        position: relative;
        z-index: 1;
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
      }

      .spinner-border-sm {
        width: 1rem;
        height: 1rem;
        border-width: 0.15em;
      }

      button[type='submit']:disabled {
        cursor: not-allowed;
        opacity: 0.7;
      }

      @keyframes spin {
        to { transform: rotate(360deg); }
      }

      /* Toast Notifications */
      .toast-container {
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: 10px;
        max-width: 400px;
        width: 90%;
      }

      .toast {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 18px;
        border-radius: 8px;
        background: #ffffff;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        animation: slideDown 0.3s ease-out;
        font-family: 'Almarai', sans-serif;
      }

      .toast.success {
        border-right: 4px solid #16a34a;
      }

      .toast.error {
        border-right: 4px solid #dc2626;
      }

      .toast.info {
        border-right: 4px solid #2563eb;
      }

      .toast-icon {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
      }

      .toast.success .toast-icon {
        background: #dcfce7;
        color: #16a34a;
      }

      .toast.error .toast-icon {
        background: #fee2e2;
        color: #dc2626;
      }

      .toast.info .toast-icon {
        background: #dbeafe;
        color: #2563eb;
      }

      .toast-message {
        flex: 1;
        font-size: 0.9rem;
        color: #374151;
      }

      .toast-close {
        background: none;
        border: none;
        color: #9ca3af;
        cursor: pointer;
        padding: 4px;
        font-size: 1.1rem;
        transition: color 0.2s;
      }

      .toast-close:hover {
        color: #6b7280;
      }

      @keyframes slideDown {
        from {
          opacity: 0;
          transform: translateY(-20px);
        }
        to {
          opacity: 1;
          transform: translateY(0);
        }
      }

      @keyframes slideUp {
        from {
          opacity: 1;
          transform: translateY(0);
        }
        to {
          opacity: 0;
          transform: translateY(-20px);
        }
      }

      .toast.hiding {
        animation: slideUp 0.3s ease-out forwards;
      }

      /* Auth Header */
      .auth-header {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        background: #ffffff;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        z-index: 1000;
        padding: 12px 0;
      }

      .auth-header-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
      }

      .auth-header-logo {
        display: flex;
        align-items: center;
        gap: 12px;
        text-decoration: none;
        transition: all 0.2s ease;
      }

      .auth-header-logo:hover {
        opacity: 0.8;
      }

      .auth-header-logo img {
        height: 60px;
        width: auto;
      }

      .auth-header-actions {
        display: flex;
        align-items: center;
        gap: 15px;
      }

      .user-info {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 15px;
        background: #f3f4f6;
        border-radius: 6px;
        font-size: 0.9rem;
        color: #374151;
        text-decoration: none;
        transition: all 0.2s ease;
      }

      .user-info:hover {
        background: #e5e7eb;
        transform: translateY(-1px);
      }

      .user-info i {
        font-size: 1.1rem;
        color: #1a5f4a;
      }

      .logout-btn {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        background: #dc2626;
        color: #ffffff;
        border: none;
        border-radius: 6px;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        font-family: 'Almarai', sans-serif;
      }

      .logout-btn:hover {
        background: #b91c1c;
        transform: translateY(-1px);
      }

      .logout-btn i {
        font-size: 1.1rem;
      }

      @media (max-width: 768px) {
        .user-info span {
          display: none;
        }

        .logout-btn {
          padding: 8px 12px;
          font-size: 0.85rem;
        }

        .auth-header {
          padding: 8px 0;
        }

        .auth-header-logo img {
          height: 45px;
        }
      }
    </style>

    <!-- [Custom CSS] -->
    @stack('styles')
  </head>
  <!-- [Head] end -->
  <!-- [Body] Start -->

  <body data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-layout="vertical" data-pc-direction="rtl" data-pc-theme_contrast="" data-pc-theme="light">
    <!-- [ Pre-loader ] start -->
    <div class="loader-bg">
      <div class="loader-track">
        <div class="loader-fill"></div>
      </div>
    </div>
    <!-- [ Pre-loader ] End -->

    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- Header with Logout -->
    @auth('representative')
    @if(!request()->routeIs('login', 'register', 'verify-login-otp'))
    <header class="auth-header">
      <div class="auth-header-container">
        <a href="{{ route('representative.dashboard') }}" class="auth-header-logo">
          <img src="{{ asset('logo-v.png') }}" alt="وزارة الصحة - إدارة الصيدلة">
        </a>
        <div class="auth-header-actions">
          <a href="{{ route('representative.settings') }}" class="user-info">
            <i class="ti ti-user"></i>
            <span>{{ auth('representative')->user()->name }}</span>
          </a>
          <form action="{{ route('representative.logout') }}" method="POST" style="display: inline;">
            @csrf
            <button type="submit" class="logout-btn">
              <i class="ti ti-logout"></i>
              تسجيل الخروج
            </button>
          </form>
        </div>
      </div>
    </header>
    @endif
    @endauth

    <div class="auth-main" @auth('representative') @if(!request()->routeIs('login', 'register', 'verify-login-otp')) style="padding-top: 70px;" @endif @endauth>
      <div class="auth-wrapper v3">
        <div class="auth-form">
          @yield('content')
        </div>
      </div>
    </div>
    <!-- [ Main Content ] end -->
    
    <!-- Required Js -->
    <script src="{{ asset('assets/js/plugins/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/fonts/custom-font.js') }}"></script>
    <script src="{{ asset('assets/js/pcoded.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/feather.min.js') }}"></script>
    
    
    <script>
      layout_change('light');
    </script>
      
    <script>
      change_box_container('false');
    </script>
     
    <script>
      layout_caption_change('true');
    </script>
     
    <script>
      layout_rtl_change('false');
    </script>
     
    <script>
      preset_change('preset-1');
    </script>
     
    <script>
      main_layout_change('vertical');
    </script>

    <!-- Toast & Loading Functions -->
    <script>
      // Toast Notification System
      function showToast(message, type = 'info', duration = 4000) {
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;

        const icons = {
          success: 'ti-check',
          error: 'ti-x',
          info: 'ti-info-circle'
        };

        toast.innerHTML = `
          <div class="toast-icon">
            <i class="ti ${icons[type] || icons.info}"></i>
          </div>
          <span class="toast-message">${message}</span>
          <button class="toast-close" onclick="closeToast(this)">
            <i class="ti ti-x"></i>
          </button>
        `;

        container.appendChild(toast);

        // Auto remove after duration
        setTimeout(() => {
          if (toast.parentNode) {
            toast.classList.add('hiding');
            setTimeout(() => toast.remove(), 300);
          }
        }, duration);
      }

      function closeToast(btn) {
        const toast = btn.closest('.toast');
        toast.classList.add('hiding');
        setTimeout(() => toast.remove(), 300);
      }

      document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('form').forEach(form => {
          form.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector("button[type='submit']");

            if (submitBtn && !submitBtn.classList.contains('no-loading')) {
              submitBtn.disabled = true;

              const originalContent = submitBtn.innerHTML;
              submitBtn.setAttribute('data-original-content', originalContent);

              submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>جاري المعالجة...';
            }
          });
        });
      });
    </script>

    <!-- [Custom JS] -->
    @stack('scripts')
  </body>
  <!-- [Body] end -->
</html>