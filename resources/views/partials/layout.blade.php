<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <!-- Favicons -->
    <link href="{{ asset('NiceAdmin/images/icon/logo-2023 copy.png') }}" rel="icon" />

    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect" />
    <link
        href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i"
        rel="stylesheet" />
    <!-- Vendor CSS Files -->
    <link href="{{ asset('NiceAdmin/assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="{{ asset('NiceAdmin/assets/css/style.css') }}">

    <link href="{{ asset('NiceAdmin/images/icon/logo-2023 copy.png') }}" rel="icon" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        #sidebar-nav .nav-link {
            transition: all 0.2s ease-in-out;
        }

        #sidebar-nav .nav-link:hover {
            background: #3b3b4d !important;
            color: #fff !important;
        }

        #sidebar-nav .nav-link.active {
            background: #007bff !important;
            color: #fff !important;
        }

        #sidebar-nav ul.collapse .nav-link:hover {
            background: #2a2a3b !important;
        }

        .sidebar-nav .nav-link i {
            margin-right: 5px !important;
        }
    </style>

</head>

<body>
    <aside id="sidebar" class="sidebar">
        <div class="p-3 border-bottom">
            <a class="sidebar-brand d-flex align-items-center" href="{{ url('dashboard') }}">
                <span class="fw-bold fs-5 text-white">NGD Admin</span>
            </a>
        </div>

        <ul class="sidebar-nav flex-column p-2" id="sidebar-nav" style="background:#1e1e2d; min-height:100vh;">

            {{-- Dashboard --}}
            <li class="sidebar-header" style="padding: 1.5rem 0.5rem 0.375rem; font-size: .90rem; color: #ced4da;">
                Component
            </li>
            <li class="nav-item mb-1">
                <a class="nav-link d-flex align-items-center px-3 py-2 rounded-3
                {{ request()->is('dashboard') || request()->is('/') || request()->routeIs('dashboard') ? 'active bg-primary text-white' : 'text-light' }}"
                    href="{{ url('dashboard') }}">
                    <i class="bi bi-speedometer me-2"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            {{-- AI Image Module Header --}}
            <li class="sidebar-header" style="padding: 1.5rem 0.5rem 0.375rem; font-size: .90rem; color: #ced4da;">
                AI Image Module
            </li>

            {{-- AI Image Baby Photo (Static Categories) --}}
            @php
                $currentRoute = request()->route()->getName();
                $currentSubId = request()->route('id');
                $isSubRoute = str_starts_with($currentRoute ?? '', 'subcategories.');

                // Check if any static category route is active
                $isBabyPhotoActive = false;
                foreach ($categories as $cat) {
                    $subActive =
                        $isSubRoute && $currentRoute === 'subcategories.form' && request('category_name') === $cat;
                    $activeSub =
                        $isSubRoute && $currentRoute === 'subcategories.show'
                            ? collect($allSubs[$cat] ?? [])->first(fn($s) => $currentSubId == $s->id)
                            : null;
                    if ($subActive || $activeSub) {
                        $isBabyPhotoActive = true;
                        break;
                    }
                }

                // Check if baby photo setting is active
                $isBabyPhotoSettingActive = request()->routeIs('ai-image-baby-photo-setting.index');
                $isBabyPhotoActive = $isBabyPhotoActive || $isBabyPhotoSettingActive;
            @endphp

            <li class="nav-item mb-1">
                <a class="nav-link collapse-toggle d-flex align-items-center justify-content-between px-3 py-2 rounded-3 text-light
                {{ $isBabyPhotoActive ? 'bg-secondary' : '' }}"
                    href="javascript:void(0);" data-target="#babyPhotoCollapse" style="transition: all 0.2s;">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-image-fill me-2 text-info"></i>
                        <span class="fw-semibold">AI Image Baby Photo</span>
                    </div>
                    <i class="bi bi-chevron-down small chevron-icon"></i>
                </a>

                {{-- Static Categories Submenu --}}
                <ul id="babyPhotoCollapse" class="submenu-list nav flex-column ps-4 mt-2"
                    style="display: {{ $isBabyPhotoActive ? 'block' : 'none' }};">

                    {{-- AI Image Baby Photo Setting --}}
                    <li class="nav-item mb-1">
                        <a class="nav-link d-flex align-items-center px-2 py-1 rounded-2 {{ request()->routeIs('ai-image-baby-photo-setting.index') ? 'active bg-primary text-white' : 'text-light' }}"
                            href="{{ route('ai-image-baby-photo-setting.index') }}" style="transition: all 0.2s;">
                            <i class="bi bi-gear me-2"></i>
                            <span>AI Image Baby Photo Setting</span>
                        </a>
                    </li>

                    @foreach ($categories as $cat)
                        @php
                            $catId = 'cat-' . Str::slug($cat, '-');
                            $subActive =
                                $isSubRoute &&
                                $currentRoute === 'subcategories.form' &&
                                request('category_name') === $cat;
                            $activeSub =
                                $isSubRoute && $currentRoute === 'subcategories.show'
                                    ? collect($allSubs[$cat] ?? [])->first(fn($s) => $currentSubId == $s->id)
                                    : null;
                            $isOpen = $subActive || $activeSub;
                        @endphp

                        <li class="nav-item mb-1">
                            {{-- Category --}}
                            <a class="nav-link collapse-toggle d-flex align-items-center justify-content-between px-3 py-2 rounded-3 text-light
                                {{ $isOpen ? 'bg-secondary' : '' }}"
                                href="javascript:void(0);" data-target="#{{ $catId }}"
                                style="transition: all 0.2s;">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-folder-fill me-2 text-warning"></i>
                                    <span class="fw-semibold">{{ $cat }}</span>
                                </div>
                                <i class="bi bi-chevron-down small chevron-icon"></i>
                            </a>

                            {{-- Subcategories --}}
                            <ul id="{{ $catId }}" class="submenu-list nav flex-column ps-4 mt-2"
                                style="display: {{ $isOpen ? 'block' : 'none' }};">
                                {{-- Add Subcategory --}}
                                <li class="mb-1">
                                    <a href="{{ route('subcategories.form', ['category_name' => $cat]) }}"
                                        class="nav-link d-flex align-items-center px-2 py-1 rounded-2
                                            {{ $subActive ? 'active bg-primary text-white' : 'text-light' }}"
                                        style="transition: all 0.2s;">
                                        <i class="bi bi-plus-circle me-2"></i>
                                        <span>Add Subcategory</span>
                                    </a>
                                </li>

                                {{-- Existing Subcategories --}}
                                @foreach ($allSubs[$cat] ?? [] as $sub)
                                    <li class="mb-1">
                                        <a href="{{ route('subcategories.show', $sub->id) }}"
                                            class="nav-link d-flex align-items-center px-2 py-1 rounded-2
                                                {{ $isSubRoute && $currentRoute === 'subcategories.show' && $currentSubId == $sub->id
                                                    ? 'active bg-primary text-white'
                                                    : 'text-light' }}"
                                            style="transition: all 0.2s;">
                                            <i class="bi bi-circle me-2"></i>
                                            <span>{{ $sub->title }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @endforeach
                </ul>
            </li>

            {{-- AI Image NGD (NGD Module) --}}
            @php
                $isNGDActive = request()->is('ngendev/categories*') || request()->is('ngendev/images*');
                // Check if NGD setting is active
                $isNGDSettingActive = request()->routeIs('ai-image-ngd-setting.index');
                $isNGDActive = $isNGDActive || $isNGDSettingActive;
            @endphp

            <li class="nav-item mb-1">
                <a class="nav-link collapse-toggle d-flex align-items-center justify-content-between px-3 py-2 rounded-3 text-light
                {{ $isNGDActive ? 'bg-secondary' : '' }}"
                    href="javascript:void(0);" data-target="#ngdCollapse" style="transition: all 0.2s;">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-robot me-2 text-success"></i>
                        <span class="fw-semibold">AI Image NGD</span>
                    </div>
                    <i class="bi bi-chevron-down small chevron-icon"></i>
                </a>

                {{-- NGD Module Submenu --}}
                <ul id="ngdCollapse" class="submenu-list nav flex-column ps-4 mt-2"
                    style="display: {{ $isNGDActive ? 'block' : 'none' }};">

                    {{-- AI Image NGD Setting --}}
                    <li class="nav-item mb-1">
                        <a class="nav-link d-flex align-items-center px-2 py-1 rounded-2 {{ request()->routeIs('ai-image-ngd-setting.index') ? 'active bg-primary text-white' : 'text-light' }}"
                            href="{{ route('ai-image-ngd-setting.index') }}" style="transition: all 0.2s;">
                            <i class="bi bi-gear me-2"></i>
                            <span>AI Image NGD Setting</span>
                        </a>
                    </li>

                    <li class="nav-item mb-1">
                        <a class="nav-link d-flex align-items-center px-2 py-1 rounded-2 {{ request()->is('ngendev/categories*') ? 'active bg-primary text-white' : 'text-light' }}"
                            href="{{ route('ngendev.categories.index') }}" style="transition: all 0.2s;">
                            <i class="bi bi-tags me-2"></i>
                            <span>AI Category</span>
                        </a>
                    </li>

                    <li class="nav-item mb-1">
                        <a class="nav-link d-flex align-items-center px-2 py-1 rounded-2 {{ request()->is('ngendev/images*') ? 'active bg-primary text-white' : 'text-light' }}"
                            href="{{ route('ngendev.images.index') }}" style="transition: all 0.2s;">
                            <i class="bi bi-image me-2"></i>
                            <span>AI Image</span>
                        </a>
                    </li>
                </ul>
            </li>

            {{-- API URL --}}
            <li class="sidebar-header" style="padding: 1.5rem 0.5rem 0.375rem; font-size: .90rem; color: #ced4da;">
                API
            </li>
            <li class="nav-item mb-1">
                <a class="nav-link d-flex align-items-center px-3 py-2 rounded-3 {{ request()->is('apiList') ? 'active bg-primary text-white' : 'text-light' }}"
                    href="{{ url('apiList') }}" style="transition: all 0.2s;">
                    <img src="https://cdn-icons-png.flaticon.com/512/103/103093.png" alt="API Icon" width="18"
                        height="18" style="margin-right: 8px; filter: brightness(0) invert(1);">
                    <span>API URL</span>
                </a>
            </li>
        </ul>

        <style>
            #sidebar-nav .nav-link {
                transition: all 0.2s ease-in-out;
                cursor: pointer;
            }

            #sidebar-nav .nav-link:hover {
                background: #3b3b4d !important;
                color: #fff !important;
            }

            #sidebar-nav .nav-link.active {
                background: #007bff !important;
                color: #fff !important;
            }

            #sidebar-nav ul.submenu-list .nav-link:hover {
                background: #2a2a3b !important;
            }

            #sidebar-nav ul.submenu-list {
                transition: all 0.3s ease;
                overflow: hidden;
            }

            .chevron-icon {
                transition: transform 0.3s ease;
            }

            .collapsed .chevron-icon {
                transform: rotate(0deg);
            }

            .expanded .chevron-icon {
                transform: rotate(180deg);
            }
        </style>
    </aside>

    <!-- Header -->
    <header id="header" class="header fixed-top d-flex align-items-center">
        <div class="d-flex align-items-center justify-content-between toggle-wrapper">
            <div class="left">
                <a href="#" class="js-sidebar-toggle" id="sidebarToggle">
                    <i class="bi bi-list" style="font-size: 2.5rem; color: black"></i>
                </a>
            </div>
        </div>

        <a href="{{ route('clear.cache') }}" title="Clear Cache">
            <i class="fas fa-broom" style="font-size: 24px; color: #9aa5df; margin-left:20px;"></i>
        </a>

        <nav class="header-nav ms-auto">
            <ul class="d-flex align-items-center">
                <li class="nav-item d-block d-lg-none">
                    <a class="nav-link nav-icon search-bar-toggle" href="#">
                        <i class="bi bi-search"></i>
                    </a>
                </li>

                <li class="nav-item dropdown pe-3">
                    <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#"
                        data-bs-toggle="dropdown">
                        <img src="{{ asset('NiceAdmin/images/icon/logo-2023 copy.png') }}" alt="Profile"
                            class="rounded-circle" />
                        <span class="d-none d-md-block dropdown-toggle ps-2">NGD Admin</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                        <li class="dropdown-header">
                            <h6>NGD Admin</h6>
                            <span>Developer</span>
                        </li>
                        <li>
                            <hr class="dropdown-divider" />
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="{{ url('logout') }}"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="bi bi-box-arrow-right"></i>
                                <span>Log Out</span>
                            </a>
                            <form id="logout-form" action="{{ url('logout') }}" method="POST"
                                style="display: none;">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="main">
        @section('container')
        @show
    </main>

    <!-- Vendor JS Files -->
    <script src="{{ asset('NiceAdmin/assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('NiceAdmin/assets/js/main.js') }}"></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script src="NiceAdmin/assets/vendor/apexcharts/apexcharts.min.js"></script>
    <script src="NiceAdmin/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="NiceAdmin/assets/vendor/chart.js/chart.umd.js"></script>
    <script src="NiceAdmin/assets/vendor/echarts/echarts.min.js"></script>
    <script src="NiceAdmin/assets/vendor/quill/quill.js"></script>
    <script src="NiceAdmin/assets/vendor/simple-datatables/simple-datatables.js"></script>
    <script src="NiceAdmin/assets/vendor/tinymce/tinymce.min.js"></script>
    <script src="NiceAdmin/assets/vendor/php-email-form/validate.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>

    <!-- existing sidebar toggle state -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const sidebar = document.getElementById("sidebar");
            const header = document.getElementById("header");
            const main = document.querySelector(".main");
            const toggleBtn = document.getElementById("sidebarToggle");
            const isClosed = localStorage.getItem('sidebarClosed') === 'true';
            if (isClosed) {
                sidebar.classList.add("closed");
                main.classList.add("full");
            }

            toggleBtn.addEventListener("click", function(e) {
                e.preventDefault();
                sidebar.classList.toggle("closed");
                main.classList.toggle("full");
                localStorage.setItem('sidebarClosed', sidebar.classList.contains("closed"));
            });
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll('.collapse-toggle').forEach(function(toggle) {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();

                    const targetId = this.getAttribute('data-target');
                    const targetMenu = document.querySelector(targetId);

                    if (targetMenu) {
                        if (targetMenu.style.display === 'none' || targetMenu.style.display ===
                            '') {
                            targetMenu.style.display = 'block';
                            this.classList.remove('collapsed');
                            this.classList.add('expanded');
                        } else {
                            targetMenu.style.display = 'none';
                            this.classList.remove('expanded');
                            this.classList.add('collapsed');
                        }
                    }
                });

                const targetId = toggle.getAttribute('data-target');
                const targetMenu = document.querySelector(targetId);
                if (targetMenu && targetMenu.style.display === 'block') {
                    toggle.classList.add('expanded');
                } else {
                    toggle.classList.add('collapsed');
                }
            });
        });
    </script>

    <script>
        (function() {
            function normalizePath(href) {
                try {
                    const url = new URL(href, location.origin);
                    return url.pathname + url.search;
                } catch (e) {
                    return href;
                }
            }

            function restoreSidebarState() {
                const activePath = localStorage.getItem('sidebar_active_path');
                if (activePath) {
                    document.querySelectorAll('#sidebar-nav a.nav-link').forEach(a => {
                        const p = normalizePath(a.getAttribute('href') || '');
                        if (p === activePath) {
                            a.classList.add('active', 'bg-primary', 'text-white');
                        }
                    });
                }
            }

            function saveActiveLinkByElement(el) {
                const href = el.getAttribute('href') || '';
                const path = normalizePath(href);
                localStorage.setItem('sidebar_active_path', path);
            }

            document.addEventListener('DOMContentLoaded', function() {
                restoreSidebarState();

                document.querySelectorAll('#sidebar-nav a.nav-link:not(.collapse-toggle)').forEach(link => {
                    link.addEventListener('click', function(e) {
                        saveActiveLinkByElement(link);
                    });
                });
            });
        })();
    </script>

    @yield('scripts')
</body>

</html>
