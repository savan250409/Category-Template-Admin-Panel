@extends('partials.layout')
@section('title', 'API Documentation')
@section('container')

    <div class="container mt-4" style="padding: 0 2rem">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="api-header">API Documentation</h2>
        </div>

        <div class="tab-content">
            <div id="mobile" class="tab-pane fade show active">
                <div class="row g-4">
                    <!-- API 1: Get Languages -->
                    <div class="col-md-6 d-flex">
                        <div class="api-box w-100">
                            <h5>1. Get All Categories</h5>
                            <p><span class="param-label">Method:</span> GET</p>
                            <p><span class="param-label">URL:</span><br>
                                <span class="url-text">{{ url('/api/getAllCategories') }}</span>
                            </p>
                            <div class="api-details">
                                <h6>Description:</h6>
                                <p>Lists all available categories in the system.</p>
                            </div>
                        </div>
                    </div>

                    <!-- API 2: Get Categories -->
                    <div class="col-md-6 d-flex">
                        <div class="api-box w-100">
                            <h5>2. Get Sub Categories By Category</h5>
                            <p><span class="param-label">Method:</span> POST</p>
                            <p><span class="param-label">URL:</span><br>
                                <span class="url-text">{{ url('/api/getSubcategoriesByCategory') }}</span>
                            </p>
                            <p><span class="param-label">Parameters:</span><br>
                                <code>main_category</code> (required) </br>
                                <code>category_name</code> (required)
                            </p>
                            <div class="api-details">
                                <h6>Description:</h6>
                                <p>Retrieves all subcategories under a specific main category based on the provided
                                    main_category and category_name parameters.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .api-box {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            height: 100%;
        }

        .api-header {
            color: #2c3e50;
            font-weight: 600;
        }

        .param-label {
            font-weight: 600;
            color: #3498db;
        }

        .url-text {
            background: #e8f4fd;
            padding: 5px 10px;
            border-radius: 4px;
            font-family: monospace;
            word-break: break-all;
        }

        .api-details {
            margin-top: 15px;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }

        code {
            background: #f0f0f0;
            padding: 2px 4px;
            border-radius: 3px;
            font-family: monospace;
        }

        .version-divider {
            display: flex;
            align-items: center;
            margin: 2rem 0;
        }

        .divider-line {
            flex: 1;
            height: 1px;
            background-color: #ddd;
            border: none;
        }

        .version-badge {
            background: #3498db;
            color: white;
            padding: 0.25rem 1rem;
            border-radius: 20px;
            font-weight: bold;
            margin: 0 1rem;
        }

        .ai-badge {
            background: #8e44ad;
        }
    </style>
@endsection
