<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
  <!-- [Head] start -->
  <head>
    <title>@yield('title', __('general.site_title'))</title>
    <!-- [Meta] -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta
      name="description"
      content="{{ __('general.site_title') }}"
    />
    <meta
      name="keywords"
      content="{{ __('general.site_title') }}"
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

      .grecaptcha-badge {
        visibility: hidden;
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

  <body data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-layout="vertical" data-pc-direction="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}" data-pc-theme_contrast="" data-pc-theme="light">
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
          <img src="{{ asset('logo-v.png') }}" alt="{{ __('general.site_title') }}">
        </a>
        <div class="auth-header-actions">
          <a href="{{ route('lang.switch', app()->getLocale() == 'ar' ? 'en' : 'ar') }}" class="user-info">
            <i class="ti ti-language"></i>
            <span>{{ app()->getLocale() == 'ar' ? 'English' : 'العربية' }}</span>
          </a>
          <a href="{{ route('representative.settings') }}" class="user-info">
            <i class="ti ti-user"></i>
            <span>{{ auth('representative')->user()->name }}</span>
          </a>
          <form action="{{ route('representative.logout') }}" method="POST" style="display: inline;">
            @csrf
            <button type="submit" class="logout-btn">
              <i class="ti ti-logout"></i>
              {{ __('general.logout') }}
            </button>
          </form>
        </div>
      </div>
    </header>
    @endif
    @endauth

    @guest('representative')
    <div style="position: fixed; top: 15px; {{ app()->getLocale() == 'ar' ? 'left' : 'right' }}: 15px; z-index: 1001;">
      <a href="{{ route('lang.switch', app()->getLocale() == 'ar' ? 'en' : 'ar') }}" class="btn btn-light btn-sm shadow-sm" style="border-radius: 20px; padding: 6px 16px; font-size: 0.85rem;">
        <i class="ti ti-language me-1"></i>{{ app()->getLocale() == 'ar' ? 'English' : 'العربية' }}
      </a>
    </div>
    @endguest

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
      layout_rtl_change('{{ app()->getLocale() == "ar" ? "true" : "false" }}');
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
        @if(session('success'))
          showToast(@json(session('success')), 'success', 5000);
        @endif
        @if(session('error'))
          showToast(@json(session('error')), 'error', 6000);
        @endif
        @if(session('info'))
          showToast(@json(session('info')), 'info', 5000);
        @endif
        @if($errors->any())
          @foreach($errors->all() as $error)
            showToast(@json($error), 'error', 6000);
          @endforeach
        @endif

        document.querySelectorAll('form').forEach(form => {
          form.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector("button[type='submit']");

            if (submitBtn && !submitBtn.classList.contains('no-loading')) {
              submitBtn.disabled = true;

              const originalContent = submitBtn.innerHTML;
              submitBtn.setAttribute('data-original-content', originalContent);

              submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>{{ __("general.processing") }}';
            }
          });
        });
      });
    </script>

    <style>
    .docx-content { font-family: 'Almarai', 'Segoe UI', sans-serif; line-height: 1.8; color: #1f2937; max-width: 800px; margin: 0 auto; }
    .docx-content p { margin-bottom: 0.75em; }
    .docx-content table { width: 100%; border-collapse: collapse; margin: 1em 0; }
    .docx-content table td, .docx-content table th { border: 1px solid #d1d5db; padding: 8px 12px; }
    .docx-content img { max-width: 100%; height: auto; }
    .docx-content h1, .docx-content h2, .docx-content h3, .docx-content h4 { color: #111827; margin-top: 1em; margin-bottom: 0.5em; }
    .docx-content ul, .docx-content ol { padding-inline-start: 2em; margin-bottom: 0.75em; }
    #docViewerWord { scrollbar-width: thin; }
    .docx-preview-wrapper { padding: 20px; background: #fff; }
    .docx-preview-wrapper .docx-wrapper { background: #fff !important; padding: 0 !important; }
    .docx-preview-wrapper .docx-wrapper > section.docx { box-shadow: none !important; padding: 30px 40px !important; margin: 0 auto !important; }
    .doc-not-supported { padding: 40px 20px; }
    </style>

    <!-- Document Viewer Modal -->
    <div class="modal fade" id="docViewerModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow" style="height: 90vh;">
                <div class="modal-header py-2 bg-dark text-white">
                    <h6 class="modal-title" id="docViewerTitle"><i class="ti ti-file me-2"></i>{{ __('general.view_document') }}</h6>
                    <div class="d-flex align-items-center gap-2">
                        <a href="#" id="docViewerDownload" class="btn btn-sm btn-outline-light" download>
                            <i class="ti ti-download me-1"></i>{{ __('general.download') }}
                        </a>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                </div>
                <div class="modal-body p-0 position-relative" style="overflow: hidden;">
                    <div id="docViewerLoading" class="position-absolute top-50 start-50 translate-middle text-center d-none">
                        <div class="spinner-border text-primary mb-2" role="status"></div>
                        <p class="text-muted">{{ __('general.loading_document') }}</p>
                    </div>
                    <iframe id="docViewerFrame" src="" style="width: 100%; height: 100%; border: none;" class="d-none"></iframe>
                    <div id="docViewerImage" class="d-none h-100 w-100 d-flex align-items-center justify-content-center overflow-auto bg-dark">
                        <img src="" alt="" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                    </div>
                    <div id="docViewerWord" class="d-none h-100 w-100" style="overflow-y: auto; background: #fff; padding: 30px 40px;">
                    </div>
                    <div id="docViewerUnsupported" class="d-none position-absolute top-50 start-50 translate-middle text-center">
                        <i class="ti ti-file-off f-48 text-muted d-block mb-3"></i>
                        <h6 class="text-muted mb-2">{{ __('general.unsupported_file') }}</h6>
                        <p class="text-muted f-13 mb-3">{{ __('general.download_to_view') }}</p>
                        <a href="#" id="docViewerFallbackDownload" class="btn btn-primary">
                            <i class="ti ti-download me-1"></i>{{ __('general.download_file') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/mammoth@1.8.0/mammoth.browser.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/docx-preview@0.3.3/dist/docx-preview.min.js"></script>
    <script>
    function openDocViewer(fileUrl, fileName, downloadUrl, mimeType) {
        var modal = document.getElementById('docViewerModal');
        var frame = document.getElementById('docViewerFrame');
        var imgContainer = document.getElementById('docViewerImage');
        var wordContainer = document.getElementById('docViewerWord');
        var unsupported = document.getElementById('docViewerUnsupported');
        var loading = document.getElementById('docViewerLoading');
        var title = document.getElementById('docViewerTitle');
        var downloadBtn = document.getElementById('docViewerDownload');
        var fallbackBtn = document.getElementById('docViewerFallbackDownload');

        frame.classList.add('d-none');
        frame.src = '';
        imgContainer.classList.add('d-none');
        wordContainer.classList.add('d-none');
        wordContainer.innerHTML = '';
        unsupported.classList.add('d-none');
        loading.classList.remove('d-none');

        title.innerHTML = '<i class="ti ti-file me-2"></i>' + (fileName || '{{ __("general.view_document") }}');
        downloadBtn.href = downloadUrl || fileUrl;
        fallbackBtn.href = downloadUrl || fileUrl;

        var mimeMap = {
            'application/pdf': 'pdf',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document': 'docx',
            'application/msword': 'doc',
            'application/vnd.ms-excel': 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': 'xlsx',
            'image/jpeg': 'jpg', 'image/png': 'png', 'image/gif': 'gif',
            'image/webp': 'webp', 'image/bmp': 'bmp', 'image/svg+xml': 'svg'
        };
        var imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'];
        var knownExts = ['pdf','doc','docx','xls','xlsx','jpg','jpeg','png','gif','webp','bmp','svg'];

        var ext = '';
        if (mimeType && mimeMap[mimeType]) {
            ext = mimeMap[mimeType];
        }
        if (!ext && fileName && fileName.indexOf('.') !== -1) {
            var fExt = fileName.split('.').pop().toLowerCase();
            if (knownExts.indexOf(fExt) !== -1) ext = fExt;
        }
        if (!ext && fileUrl) {
            var uExt = fileUrl.split('?')[0].split('#')[0].split('.').pop().toLowerCase();
            if (knownExts.indexOf(uExt) !== -1) ext = uExt;
        }

        var bsModal = new bootstrap.Modal(modal);
        bsModal.show();

        function renderDocx(fetchUrl) {
            fetch(fetchUrl, { credentials: 'same-origin' })
                .then(function(r) {
                    if (!r.ok) throw new Error('HTTP ' + r.status);
                    return r.arrayBuffer();
                })
                .then(function(buffer) {
                    loading.classList.add('d-none');
                    wordContainer.innerHTML = '';
                    wordContainer.classList.remove('d-none');
                    if (typeof docx !== 'undefined' && docx.renderAsync) {
                        return docx.renderAsync(buffer, wordContainer, null, {
                            className: 'docx-preview-wrapper',
                            inWrapper: true,
                            ignoreWidth: false,
                            ignoreHeight: true,
                            renderHeaders: true,
                            renderFooters: true,
                            renderFootnotes: true
                        });
                    } else {
                        return mammoth.convertToHtml({ arrayBuffer: buffer }).then(function(result) {
                            wordContainer.innerHTML = '<div class="docx-content" dir="auto">' + result.value + '</div>';
                        });
                    }
                })
                .catch(function(err) {
                    console.error('DOCX preview error:', err);
                    loading.classList.add('d-none');
                    unsupported.classList.remove('d-none');
                });
        }

        function showDocDownload() {
            loading.classList.add('d-none');
            wordContainer.innerHTML = '<div class="doc-not-supported text-center py-5"><i class="ti ti-file-type-doc" style="font-size: 64px; color: #2b579a;"></i><h5 class="mt-3 mb-2">{{ __("general.doc_format_old") }}</h5><p class="text-muted mb-3">{{ __("general.doc_convert_hint") }}</p><a href="' + (downloadUrl || fileUrl) + '" class="btn btn-primary"><i class="ti ti-download me-2"></i>{{ __("general.download_file") }}</a></div>';
            wordContainer.classList.remove('d-none');
        }

        if (ext === 'pdf') {
            frame.onload = function() { loading.classList.add('d-none'); };
            frame.src = fileUrl;
            frame.classList.remove('d-none');
            loading.classList.add('d-none');
        } else if (imageExts.indexOf(ext) !== -1) {
            var img = imgContainer.querySelector('img');
            img.onload = function() { loading.classList.add('d-none'); };
            img.src = fileUrl;
            imgContainer.classList.remove('d-none');
            imgContainer.classList.add('d-flex');
            loading.classList.add('d-none');
        } else if (ext === 'docx') {
            renderDocx(downloadUrl || fileUrl);
        } else if (ext === 'doc') {
            showDocDownload();
        } else if (ext === 'xls' || ext === 'xlsx') {
            loading.classList.add('d-none');
            frame.src = 'https://view.officeapps.live.com/op/embed.aspx?src=' + encodeURIComponent(window.location.origin + fileUrl);
            frame.classList.remove('d-none');
            frame.onerror = function() {
                frame.classList.add('d-none');
                unsupported.classList.remove('d-none');
            };
        } else if (!ext) {
            var headUrl = downloadUrl || fileUrl;
            fetch(headUrl, { method: 'HEAD', credentials: 'same-origin' })
                .then(function(r) {
                    var ct = r.headers.get('content-type') || '';
                    if (ct.indexOf('pdf') !== -1) {
                        frame.onload = function() { loading.classList.add('d-none'); };
                        frame.src = fileUrl;
                        frame.classList.remove('d-none');
                        loading.classList.add('d-none');
                    } else if (ct.indexOf('image/') !== -1) {
                        var img = imgContainer.querySelector('img');
                        img.onload = function() { loading.classList.add('d-none'); };
                        img.src = fileUrl;
                        imgContainer.classList.remove('d-none');
                        imgContainer.classList.add('d-flex');
                        loading.classList.add('d-none');
                    } else if (ct.indexOf('wordprocessingml') !== -1 || ct.indexOf('officedocument.word') !== -1) {
                        renderDocx(headUrl);
                    } else if (ct.indexOf('msword') !== -1) {
                        showDocDownload();
                    } else {
                        loading.classList.add('d-none');
                        unsupported.classList.remove('d-none');
                    }
                })
                .catch(function() {
                    loading.classList.add('d-none');
                    unsupported.classList.remove('d-none');
                });
        } else {
            loading.classList.add('d-none');
            unsupported.classList.remove('d-none');
        }

        modal.addEventListener('hidden.bs.modal', function cleanup() {
            frame.src = '';
            imgContainer.querySelector('img').src = '';
            wordContainer.innerHTML = '';
            frame.classList.add('d-none');
            imgContainer.classList.add('d-none');
            wordContainer.classList.add('d-none');
            unsupported.classList.add('d-none');
            modal.removeEventListener('hidden.bs.modal', cleanup);
        }, { once: true });
    }

    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.btn-doc-preview');
        if (btn) {
            e.preventDefault();
            openDocViewer(btn.dataset.fileUrl, btn.dataset.fileName, btn.dataset.downloadUrl, btn.dataset.mimeType || '');
        }
    });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    function confirmDelete(formId) {
        Swal.fire({
            title: '{{ __("general.confirm_delete") }}',
            text: '{{ __("general.delete_confirm_msg") }}',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '{{ __("general.yes_delete") }}',
            cancelButtonText: '{{ __("general.cancel") }}'
        }).then(function(result) {
            if (result.isConfirmed) {
                document.getElementById(formId).submit();
            }
        });
    }
    </script>

    <!-- [Custom JS] -->
    @stack('scripts')
  </body>
  <!-- [Body] end -->
</html>