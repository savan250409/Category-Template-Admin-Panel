@extends('partials.layout')
@section('title', 'Admin Dashboard')
@section('container')

    <div class="container my-5">
        <h1 class="h3 mb-4 text-dark fw-bold">
            <i class="bi bi-speedometer2 me-2"></i>Dashboard
        </h1>

        <div class="row g-4">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @forelse($categories as $category)
                <div class="col-md-4 col-lg-3">
                    <a href="{{ $category['redirect_url'] }}" class="text-decoration-none">
                        <div class="card stat-card shadow-lg rounded-4 p-4 text-center category-card">
                            <div class="stat-title fw-semibold mb-2">
                                <i class="bi bi-folder-fill me-1"></i> {{ $category['category_name'] }}
                            </div>
                            <div class="stat-count fs-3 fw-bold mb-2">
                                {{ $category['subcategories_count'] }}
                            </div>
                            <div class="stat-label text-muted">Subcategories</div>
                            <div class="stat-link mt-3">
                                View Subcategories <i class="bi bi-arrow-right"></i>
                            </div>
                        </div>
                    </a>
                </div>
            @empty
                <p class="text-muted">No categories found.</p>
            @endforelse

        </div>
    </div>

    <style>
        .stat-card {
            border-radius: 20px;
            padding: 30px 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            color: white;
            position: relative;
            overflow: hidden;
        }

        .stat-card::after {
            content: '';
            position: absolute;
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            top: -30px;
            right: -30px;
            z-index: 0;
        }

        .category-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .stat-title i {
            font-size: 1.3rem;
        }

        .stat-count {
            font-size: 2.5rem;
            font-weight: bold;
        }

        .stat-label {
            font-size: 0.95rem;
        }

        .stat-link {
            font-size: 0.9rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.85);
        }

        a.category-card:hover .stat-link {
            color: #fff;
        }

        a {
            color: inherit;
        }
    </style>

@endsection
