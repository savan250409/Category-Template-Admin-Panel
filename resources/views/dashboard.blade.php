@extends('partials.layout')
@section('title', 'Admin Dashboard')
@section('container')

    <div class="dashboard-container container sidebar-closed" id="dashboardContainer">
        <div>
            <!-- Dashboard Header -->
            <div class="dashboard-header">
                <h1 class="h3 mb-0" style="font-weight: 600; color: #2c3e50;">
                    <i class="bi bi-speedometer2 me-2"></i>Dashboard
                </h1>
            </div>

            <!-- Stats Cards -->
            <div class="row g-4 mb-4">
                <!-- Total Languages -->
                <div class="col-md-3">
                    <div class="stat-card language p-4 text-center">
                        <div class="stat-icon">
                            <i class="bi bi-translate"></i>
                        </div>
                        {{-- <div class="stat-count">{{ $totalLanguages }}</div> --}}
                        <div class="stat-title">Total Languages</div>
                        <a href="{{ url('/languageDisplay') }}" class="stat-link">
                            View Details <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Total Categories -->
                <div class="col-md-3">
                    <div class="stat-card category p-4 text-center">
                        <div class="stat-icon">
                            <i class="bi bi-tags"></i>
                        </div>
                        {{-- <div class="stat-count">{{ $totalCategories }}</div> --}}
                        <div class="stat-title">Total Categories</div>
                        <a href="{{ url('/categoryDisplay') }}" class="stat-link">
                            View Details <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Total Frames -->
                <div class="col-md-3">
                    <div class="stat-card frames p-4 text-center">
                        <div class="stat-icon">
                            <i class="bi bi-images"></i>
                        </div>
                        {{-- <div class="stat-count">{{ $totalFrames }}</div> --}}
                        <div class="stat-title">Total Frames</div>
                        <a href="{{ url('/frameDisplay') }}" class="stat-link">
                            View Details <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Total Apps -->
                <div class="col-md-3">
                    <div class="stat-card apps p-4 text-center">
                        <div class="stat-icon">
                            <i class="bi bi-app-indicator" style="color: white"></i>
                        </div>
                        {{-- <div class="stat-count">{{ $totalApps }}</div> --}}
                        <div class="stat-title">Total Apps</div>
                        <a href="{{ url('/applist') }}" class="stat-link">
                            View Details <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Total AI Images -->
                <div class="col-md-3">
                    <div class="stat-card aiimages p-4 text-center">
                        <div class="stat-icon">
                            <i class="bi bi-image"></i>
                        </div>
                        {{-- <div class="stat-count">{{ $totalAIImages }}</div> --}}
                        <div class="stat-title">Total AI Images</div>
                        <a href="{{ url('/ai/images') }}" class="stat-link">
                            View Details <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Total Videos -->
                <div class="col-md-3">
                    <div class="stat-card videos p-4 text-center">
                        <div class="stat-icon">
                            <i class="bi bi-play-btn"></i>
                        </div>
                        {{-- <div class="stat-count">{{ $totalVideos }}</div> --}}
                        <div class="stat-title">Total Videos</div>
                        <a href="{{ url('/ai/videos/index') }}" class="stat-link">
                            View Details <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Toggle Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const dashboardContainer = document.getElementById('dashboardContainer');

            if (localStorage.getItem('sidebarClosed') === 'true') {
                dashboardContainer.classList.add('sidebar-closed');
            }

            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    dashboardContainer.classList.toggle('sidebar-closed');

                    const isClosed = dashboardContainer.classList.contains('sidebar-closed');
                    localStorage.setItem('sidebarClosed', isClosed);
                });
            }
        });
    </script>

    <!-- Custom Card Colors -->
    <style>
        .stat-card {
            border-radius: 15px;
            color: white;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .stat-count {
            font-size: 2rem;
            font-weight: bold;
        }

        .stat-link {
            color: white;
            font-weight: 500;
        }

        .language {
            background: linear-gradient(45deg, #6a11cb, #2575fc);
        }

        .category {
            background: linear-gradient(45deg, #ff416c, #ff4b2b);
        }

        .frames {
            background: linear-gradient(45deg, #11998e, #38ef7d);
        }

        .apps {
            background: linear-gradient(45deg, #ff8008, #ffc837);
        }

        .aiimages {
            background: linear-gradient(45deg, #8e2de2, #4a00e0);
        }

        .videos {
            background: linear-gradient(45deg, #00c6ff, #0072ff);
        }
    </style>
@endsection
