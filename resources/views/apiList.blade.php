@extends('partials.layout')
@section('title', 'API Documentation')
@section('container')

    <div class="container mt-4" style="padding: 0 2rem">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="api-header">API Documentation</h2>
        </div>

        <div class="tab-content">
            <div id="mobile" class="tab-pane fade show active">
                <!-- AI Baby Image Module Divider -->
                <div class="version-divider mb-4">
                    <hr class="divider-line">
                    <div class="version-badge ai-badge" style="background: #27ae60;">AI Baby Image Module</div>
                    <hr class="divider-line">
                </div>

                <div class="row g-4">
                    <!-- API 1: Get Languages -->
                    <div class="col-md-6 d-flex">
                        <div class="api-box w-100">
                            <h5>1. Get All Categories</h5>
                            <p><span class="param-label">Method:</span> GET</p>
                            <p><span class="param-label">URL (v1):</span><br>
                                <span class="url-text">{{ url('/api/getAllCategories') }}</span><br>
                                <span class="param-label">URL (v2):</span><br>
                                <span class="url-text">{{ url('/api/v2/getAllCategories') }}</span><br>
                                <span class="param-label">URL (v3):</span><br>
                                <span class="url-text">{{ url('/api/v3/getAllCategories') }}</span>
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

                    <!-- API 3: Get Sub Categories By Category ID -->
                    <div class="col-md-6 d-flex">
                        <div class="api-box w-100">
                            <h5>3. Get Sub Categories By Category ID</h5>
                            <p><span class="param-label">Method:</span> POST</p>
                            <p><span class="param-label">URL:</span><br>
                                <span class="url-text">{{ url('/api/getSubcategoriesByCategoryid') }}</span>
                            </p>
                            <p><span class="param-label">Parameters:</span><br>
                                <code>sub_category_id</code> (required) e.g. <code>1</code>
                            </p>
                            <div class="api-details">
                                <h6>Description:</h6>
                                <p>Retrieves subcategory details and images based on the provided sub_category_id.</p>
                            </div>
                        </div>
                    </div>

                    <!-- API 4: Get Trending -->
                    <div class="col-md-6 d-flex">
                        <div class="api-box w-100">
                            <h5>4. Get Trending Sub Categories</h5>
                            <p><span class="param-label">Method:</span> GET</p>
                            <p><span class="param-label">URL:</span><br>
                                <span class="url-text">{{ url('/api/trending') }}</span>
                            </p>
                            <div class="api-details">
                                <h6>Description:</h6>
                                <p>Retrieves a list of trending subcategories.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- AI Module Divider -->
                <div class="version-divider">
                    <hr class="divider-line">
                    <div class="version-badge ai-badge">NGD Module</div>
                    <hr class="divider-line">
                </div>

                <!-- NGD Module -->
                <div class="row g-4 mt-4">
                    <!-- API 19: Get AI Categories -->
                    <div class="col-md-6 d-flex">
                        <div class="api-box w-100">
                            <h5>19. Get AI Categories</h5>
                            <p><span class="param-label">Method:</span> GET</p>
                            <p><span class="param-label">URL:</span><br>
                                <span class="url-text">{{ url('/api/v1/ngd/getAiCategories') }}</span>
                            </p>
                            <div class="api-details">
                                <h6>Description:</h6>
                                <p>Retrieves a list of AI categories available in the app.</p>
                            </div>
                        </div>
                    </div>

                    <!-- API 20: Get AI Images by Category ID -->
                    <div class="col-md-6 d-flex">
                        <div class="api-box w-100">
                            <h5>20. Get AI Images by Category ID</h5>
                            <p><span class="param-label">Method:</span> POST</p>
                            <p><span class="param-label">URL:</span><br>
                                <span class="url-text">{{ url('/api/v1/ngd/getAiImageByCategoryId') }}</span>
                            </p>
                            <p><span class="param-label">Parameters:</span><br>
                                <code>category_id</code> (required) e.g. <code>5</code>
                            </p>
                            <div class="api-details">
                                <h6>Description:</h6>
                                <p>Returns all AI-generated images for the specified category ID.</p>
                            </div>
                        </div>
                    </div>

                    <!-- API 21: Get AI Video Categories -->
                    <div class="col-md-6 d-flex">
                        <div class="api-box w-100">
                            <h5>21. Get AI Video Categories</h5>
                            <p><span class="param-label">Method:</span> GET</p>
                            <p><span class="param-label">URL:</span><br>
                                <span class="url-text">{{ url('/api/v1/ngd/getAiVideoCategories') }}</span>
                            </p>
                            <div class="api-details">
                                <h6>Description:</h6>
                                <p>Retrieves a list of AI Video categories available in the app.</p>
                            </div>
                        </div>
                    </div>

                    <!-- API 22: Get AI Videos by Category ID -->
                    <div class="col-md-6 d-flex">
                        <div class="api-box w-100">
                            <h5>22. Get AI Videos by Category ID</h5>
                            <p><span class="param-label">Method:</span> POST</p>
                            <p><span class="param-label">URL:</span><br>
                                <span class="url-text">{{ url('/api/v1/ngd/getAiVideoByCategoryId') }}</span>
                            </p>
                            <p><span class="param-label">Parameters:</span><br>
                                <code>category_id</code> (required) e.g. <code>5</code>
                            </p>
                            <div class="api-details">
                                <h6>Description:</h6>
                                <p>Returns all AI-generated videos and their thumbnails for the specified category ID.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- AI Baby Video Module Divider -->
                <div class="version-divider mt-5">
                    <hr class="divider-line">
                    <div class="version-badge ai-badge" style="background: #e67e22;">AI Baby Video Module</div>
                    <hr class="divider-line">
                </div>

                <!-- AI Baby Video Module -->
                <div class="row g-4 mt-2 mb-4">
                    <!-- API: Get AI Baby Video Categories -->
                    <div class="col-md-6 d-flex">
                        <div class="api-box w-100">
                            <h5>Get AI Baby Video Categories</h5>
                            <p><span class="param-label">Method:</span> GET</p>
                            <p><span class="param-label">URL:</span><br>
                                <span class="url-text">{{ url('/api/video/getAllCategories') }}</span>
                            </p>
                            <div class="api-details">
                                <h6>Description:</h6>
                                <p>Retrieves a list of AI Baby Video categories available in the app.</p>
                            </div>
                        </div>
                    </div>

                    <!-- API: Get AI Baby Video Items by Category -->
                    <div class="col-md-6 d-flex">
                        <div class="api-box w-100">
                            <h5>Get AI Baby Videos by Category</h5>
                            <p><span class="param-label">Method:</span> POST</p>
                            <p><span class="param-label">URL:</span><br>
                                <span class="url-text">{{ url('/api/video/getSubcategoriesByCategoryid') }}</span>
                            </p>
                            <p><span class="param-label">Parameters:</span><br>
                                <code>sub_category_id</code> (required) e.g. <code>1</code>
                            </p>
                            <div class="api-details">
                                <h6>Description:</h6>
                                <p>Returns all AI Baby Videos for the specified sub_category_id.</p>
                            </div>
                        </div>
                    </div>

                    <!-- API: Get Trending AI Baby Videos -->
                    <div class="col-md-6 d-flex">
                        <div class="api-box w-100">
                            <h5>Get Trending AI Baby Video Categories</h5>
                            <p><span class="param-label">Method:</span> GET</p>
                            <p><span class="param-label">URL:</span><br>
                                <span class="url-text">{{ url('/api/video/trending') }}</span>
                            </p>
                            <div class="api-details">
                                <h6>Description:</h6>
                                <p>Retrieves a list of trending AI Baby Video categories.</p>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>

                <!-- Filter AI Image Module Divider -->
                <div class="version-divider mt-5">
                    <hr class="divider-line">
                    <div class="version-badge ai-badge" style="background: #3498db;">Filter AI Image Module</div>
                    <hr class="divider-line">
                </div>

                <!-- Filter AI Image Module -->
                <div class="row g-4 mt-2 mb-4">
                    <!-- API: Get Filter AI Categories -->
                    <div class="col-md-6 d-flex">
                        <div class="api-box w-100">
                            <h5>Get Filter AI Categories</h5>
                            <p><span class="param-label">Method:</span> GET</p>
                            <p><span class="param-label">URL:</span><br>
                                <span class="url-text">{{ url('/api/v1/filter/getFilterAiCategories') }}</span>
                            </p>
                            <div class="api-details">
                                <h6>Description:</h6>
                                <p>Retrieves a list of Filter AI categories available in the app.</p>
                            </div>
                        </div>
                    </div>

                    <!-- API: Get Filter AI Images by Category ID -->
                    <div class="col-md-6 d-flex">
                        <div class="api-box w-100">
                            <h5>Get Filter AI Images by Category ID</h5>
                            <p><span class="param-label">Method:</span> POST</p>
                            <p><span class="param-label">URL:</span><br>
                                <span class="url-text">{{ url('/api/v1/filter/getFilterAiImageByCategoryId') }}</span>
                            </p>
                            <p><span class="param-label">Parameters:</span><br>
                                <code>category_id</code> (required) e.g. <code>1</code>
                            </p>
                            <div class="api-details">
                                <h6>Description:</h6>
                                <p>Returns all Filter AI images for the specified category ID.</p>
                            </div>
                        </div>
                    </div>
                <!-- Top Slider Module Divider -->
                <div class="version-divider mt-5">
                    <hr class="divider-line">
                    <div class="version-badge ai-badge" style="background: #f1c40f;">Top Slider Module</div>
                    <hr class="divider-line">
                </div>

                <!-- Top Slider Module -->
                <div class="row g-4 mt-2 mb-4">
                    <!-- API: Get Top Slider Data -->
                    <div class="col-md-6 d-flex">
                        <div class="api-box w-100">
                            <h5>Get Top Slider Data</h5>
                            <p><span class="param-label">Method:</span> GET</p>
                            <p><span class="param-label">URL:</span><br>
                                <span class="url-text">{{ url('/api/v1/top-slider/getTopSlider') }}</span>
                            </p>
                            <div class="api-details">
                                <h6>Description:</h6>
                                <p>Retrieves all Top Slider categories and their associated items (images/videos).</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lips Sync Module Divider -->
                <div class="version-divider mt-5">
                    <hr class="divider-line">
                    <div class="version-badge ai-badge" style="background: #e67e22;">Lips Sync Module</div>
                    <hr class="divider-line">
                </div>

                <!-- Lips Sync Module -->
                <div class="row g-4 mt-2 mb-4">
                    <!-- API 35: Get Lips Sync Categories -->
                    <div class="col-md-6 d-flex">
                        <div class="api-box w-100">
                            <h5>35. Get Lips Sync Categories</h5>
                            <p><span class="param-label">Method:</span> GET</p>
                            <p><span class="param-label">URL:</span><br>
                                <span class="url-text">{{ url('/api/v1/asm003/getLipsSyncCategories') }}</span>
                            </p>
                            <div class="api-details">
                                <h6>Description:</h6>
                                <p>Lists all Lips Sync categories.</p>
                            </div>
                        </div>
                    </div>

                    <!-- API 36: Get Lips Sync By Category ID -->
                    <div class="col-md-6 d-flex">
                        <div class="api-box w-100">
                            <h5>36. Get Lips Sync By Category ID</h5>
                            <p><span class="param-label">Method:</span> POST</p>
                            <p><span class="param-label">URL:</span><br>
                                <span class="url-text">{{ url('/api/v1/asm003/getLipsSyncByCategoryId') }}</span>
                            </p>
                            <p><span class="param-label">Parameters:</span><br>
                                <code>category_id</code> (required) e.g. <code>1</code>
                            </p>
                            <div class="api-details">
                                <h6>Description:</h6>
                                <p>Fetches all Lips Sync items for a specific category.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dynamic Photo Frame Module Divider -->
                <div class="version-divider mt-5">
                    <hr class="divider-line">
                    <div class="version-badge ai-badge" style="background: #9b59b6;">Dynamic Photo Frame Module</div>
                    <hr class="divider-line">
                </div>

                <!-- Dynamic Photo Frame Module -->
                <div class="row g-4 mt-2 mb-4">
                    <!-- API 37: Get Dynamic Photo Frame Categories -->
                    <div class="col-md-6 d-flex">
                        <div class="api-box w-100">
                            <h5>37. Get Dynamic Photo Frame Categories</h5>
                            <p><span class="param-label">Method:</span> GET</p>
                            <p><span class="param-label">URL:</span><br>
                                <span class="url-text">{{ url('/api/get_dynamic_photo_frame_category') }}</span>
                            </p>
                            <p><span class="param-label">Headers:</span><br>
                                <code>Authorization: Bearer YOUR_API_TOKEN</code>
                            </p>
                            <div class="api-details">
                                <h6>Description:</h6>
                                <p>Fetches all dynamic photo frame categories in descending order.</p>
                            </div>
                        </div>
                    </div>

                    <!-- API 38: Get Dynamic Photo Frames By Category -->
                    <div class="col-md-6 d-flex">
                        <div class="api-box w-100">
                            <h5>38. Get Dynamic Photo Frames By Category</h5>
                            <p><span class="param-label">Method:</span> POST</p>
                            <p><span class="param-label">URL:</span><br>
                                <span class="url-text">{{ url('/api/get_dynamic_photo_frame_by_category_id') }}</span>
                            </p>
                            <p><span class="param-label">Parameters:</span><br>
                                <code>category_id</code> (required) e.g. <code>1</code>
                            </p>
                            <p><span class="param-label">Headers:</span><br>
                                <code>Authorization: Bearer YOUR_API_TOKEN</code>
                            </p>
                            <div class="api-details">
                                <h6>Description:</h6>
                                <p>Fetches all dynamic photo frames for a specific category in descending order. Returns zip file URL, input count, and thumbnail.</p>
                            </div>
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