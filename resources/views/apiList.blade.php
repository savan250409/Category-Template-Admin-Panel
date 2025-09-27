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
                            <h5>1. Get Languages</h5>
                            <p><span class="param-label">Method:</span> GET</p>
                            <p><span class="param-label">URL:</span><br>
                                <span class="url-text">{{ url('/api/get-languages') }}</span>
                            </p>
                            <div class="api-details">
                                <h6>Description:</h6>
                                <p>Lists all supported languages</p>
                            </div>
                        </div>
                    </div>

                    <!-- API 2: Get Categories -->
                    <div class="col-md-6 d-flex">
                        <div class="api-box w-100">
                            <h5>2. Get Categories</h5>
                            <p><span class="param-label">Method:</span> POST</p>
                            <p><span class="param-label">URL:</span><br>
                                <span class="url-text">{{ url('/api/get-categories') }}</span>
                            </p>
                            <p><span class="param-label">Parameters:</span><br>
                                <code>language_name</code> (required)
                            </p>
                            <div class="api-details">
                                <h6>Description:</h6>
                                <p>Gets categories for a specific language</p>
                            </div>
                        </div>
                    </div>

                    <!-- API 3: Get App Details -->
                    <div class="col-md-6 d-flex">
                        <div class="api-box w-100">
                            <h5>3. Get App Details</h5>
                            <p><span class="param-label">Method:</span> POST</p>
                            <p><span class="param-label">URL:</span><br>
                                <span class="url-text">{{ url('/api/app-by-package') }}</span>
                            </p>
                            <p><span class="param-label">Parameters:</span><br>
                                <code>package_name</code> (required)
                            </p>
                            <div class="api-details">
                                <h6>Description:</h6>
                                <p>Returns app information with all content organized by language and category</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Version 1 Divider -->
                <div class="version-divider">
                    <hr class="divider-line">
                    <div class="version-badge">v1</div>
                    <hr class="divider-line">
                </div>

                <!-- New Version 1 APIs -->
                <div class="row g-4 mt-4">
                    <!-- API 4: Get Categories By Package -->
                    <div class="col-md-6 d-flex">
                        <div class="api-box w-100">
                            <h5>4. Get Categories By Package</h5>
                            <p><span class="param-label">Method:</span> POST</p>
                            <p><span class="param-label">URL:</span><br>
                                <span
                                    class="url-text">https://babyframeshub.3ftechnolabs.com/api/v1/getCategoriesByPackageName</span>
                            </p>
                            <p><span class="param-label">Parameters:</span><br>
                                <code>language_name</code> (required)<br>
                                <code>package_name</code> (required)
                            </p>
                            <div class="api-details">
                                <h6>Description:</h6>
                                <p>Gets all categories for a specific app package and language</p>
                            </div>
                        </div>
                    </div>

                    <!-- API 5: Get Random Images -->
                    <div class="col-md-6 d-flex">
                        <div class="api-box w-100">
                            <h5>5. Get Random Images</h5>
                            <p><span class="param-label">Method:</span> POST</p>
                            <p><span class="param-label">URL:</span><br>
                                <span
                                    class="url-text">https://babyframeshub.3ftechnolabs.com/api/v1/getRandomImageByPackageName</span>
                            </p>
                            <p><span class="param-label">Parameters:</span><br>
                                <code>language_name</code> (required)<br>
                                <code>package_name</code> (required)
                            </p>
                            <div class="api-details">
                                <h6>Description:</h6>
                                <p>Returns random images from all categories in an app package</p>
                            </div>
                        </div>
                    </div>

                    <!-- API 6: Get Images By Category -->
                    <div class="col-md-6 d-flex">
                        <div class="api-box w-100">
                            <h5>6. Get Images By Category</h5>
                            <p><span class="param-label">Method:</span> POST</p>
                            <p><span class="param-label">URL:</span><br>
                                <span
                                    class="url-text">https://babyframeshub.3ftechnolabs.com/api/v1/getImagesByCategoryId</span>
                            </p>
                            <p><span class="param-label">Parameters:</span><br>
                                <code>category_id</code> (required)<br>
                                <code>package_name</code> (required)
                            </p>
                            <div class="api-details">
                                <h6>Description:</h6>
                                <p>Gets all images for a specific category ID</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- AI Module Divider -->
                <div class="version-divider">
                    <hr class="divider-line">
                    <div class="version-badge ai-badge">AI Module (ASM003)</div>
                    <hr class="divider-line">
                </div>

                <!-- AI Module APIs (ASM003) -->
                <div class="row g-4 mt-4">
                    <!-- API 7: Get AI Image -->
                    <div class="col-md-6 d-flex">
                        <div class="api-box w-100">
                            <h5>7. Get AI Image</h5>
                            <p><span class="param-label">Method:</span> POST</p>
                            <p><span class="param-label">URL:</span><br>
                                <span class="url-text">{{ url('/api/v1/asm003/getAiImage') }}</span>
                            </p>
                            <p><span class="param-label">Parameters:</span><br>
                                <code>category_id</code> (required) e.g. <code>28</code>
                            </p>
                            <div class="api-details">
                                <h6>Description:</h6>
                                <p>Generates or retrieves AI images based on a category</p>
                            </div>
                        </div>
                    </div>

                    <!-- API 8: Get AI Category -->
                    <div class="col-md-6 d-flex">
                        <div class="api-box w-100">
                            <h5>8. Get AI Category</h5>
                            <p><span class="param-label">Method:</span> GET</p>
                            <p><span class="param-label">URL:</span><br>
                                <span class="url-text">{{ url('/api/v1/asm003/getAiCategory') }}</span>
                            </p>
                            <div class="api-details">
                                <h6>Description:</h6>
                                <p>Returns all AI image categories</p>
                            </div>
                        </div>
                    </div>

                    <!-- API 9: Get AI Videos -->
                    <div class="col-md-6 d-flex">
                        <div class="api-box w-100">
                            <h5>9. Get AI Videos</h5>
                            <p><span class="param-label">Method:</span> POST</p>
                            <p><span class="param-label">URL:</span><br>
                                <span class="url-text">{{ url('/api/v1/asm003/getAiVideos') }}</span>
                            </p>
                            <p><span class="param-label">Parameters:</span><br>
                                <code>category_id</code> (required) e.g. <code>5</code>
                            </p>
                            <div class="api-details">
                                <h6>Description:</h6>
                                <p>Fetches AI-generated videos by category</p>
                            </div>
                        </div>
                    </div>

                    <!-- API 10: Get Video Categories -->
                    <div class="col-md-6 d-flex">
                        <div class="api-box w-100">
                            <h5>10. Get Video Categories</h5>
                            <p><span class="param-label">Method:</span> GET</p>
                            <p><span class="param-label">URL:</span><br>
                                <span class="url-text">{{ url('/api/v1/asm003/getVideoCategories') }}</span>
                            </p>
                            <div class="api-details">
                                <h6>Description:</h6>
                                <p>Lists all available AI video categories</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- AI Module Divider -->
                <div class="version-divider">
                    <hr class="divider-line">
                    <div class="version-badge ai-badge">AI Module (ASM013)</div>
                    <hr class="divider-line">
                </div>

                <!-- AI Module APIs (ASM013) -->
                <div class="row g-4 mt-4">
                    <!-- API 11: Get AI Image -->
                    <div class="col-md-6 d-flex">
                        <div class="api-box w-100">
                            <h5>11. Get AI Image</h5>
                            <p><span class="param-label">Method:</span> POST</p>
                            <p><span class="param-label">URL:</span><br>
                                <span class="url-text">{{ url('/api/v1/asm013/getAiImageByCategoryId') }}</span>
                            </p>
                            <p><span class="param-label">Parameters:</span><br>
                                <code>category_id</code> (required) e.g. <code>28</code>
                            </p>
                            <div class="api-details">
                                <h6>Description:</h6>
                                <p>Generates or retrieves AI images based on a category</p>
                            </div>
                        </div>
                    </div>

                    <!-- API 12: Get AI Category -->
                    <div class="col-md-6 d-flex">
                        <div class="api-box w-100">
                            <h5>12. Get AI Category</h5>
                            <p><span class="param-label">Method:</span> GET</p>
                            <p><span class="param-label">URL:</span><br>
                                <span class="url-text">{{ url('/api/v1/asm013/getAiCategory') }}</span>
                            </p>
                            <div class="api-details">
                                <h6>Description:</h6>
                                <p>Returns all AI image categories</p>
                            </div>
                        </div>
                    </div>

                    <!-- API 13: Get AI Videos -->
                    <div class="col-md-6 d-flex">
                        <div class="api-box w-100">
                            <h5>13. Get AI Videos</h5>
                            <p><span class="param-label">Method:</span> POST</p>
                            <p><span class="param-label">URL:</span><br>
                                <span class="url-text">{{ url('/api/v1/asm013/getAiVideoByCategoryId') }}</span>
                            </p>
                            <p><span class="param-label">Parameters:</span><br>
                                <code>category_id</code> (required) e.g. <code>5</code>
                            </p>
                            <div class="api-details">
                                <h6>Description:</h6>
                                <p>Fetches AI-generated videos by category</p>
                            </div>
                        </div>
                    </div>

                    <!-- API 14: Get Video Categories -->
                    <div class="col-md-6 d-flex">
                        <div class="api-box w-100">
                            <h5>14. Get Video Categories</h5>
                            <p><span class="param-label">Method:</span> GET</p>
                            <p><span class="param-label">URL:</span><br>
                                <span class="url-text">{{ url('/api/v1/asm013/getAiVideoCategory') }}</span>
                            </p>
                            <div class="api-details">
                                <h6>Description:</h6>
                                <p>Lists all available AI video categories</p>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- AI Module Divider -->
                <div class="version-divider">
                    <hr class="divider-line">
                    <div class="version-badge ai-badge">AI Module (ASM044)</div>
                    <hr class="divider-line">
                </div>

                <!-- AI Module APIs (ASM044) -->
                <div class="row g-4 mt-4">
                    <!-- API 15: Get Video Categories -->
                    <div class="col-md-6 d-flex">
                        <div class="api-box w-100">
                            <h5>15. Get Video Categories</h5>
                            <p><span class="param-label">Method:</span> GET</p>
                            <p><span class="param-label">URL:</span><br>
                                <span class="url-text">{{ url('/api/v1/asm044/getVideoCategories') }}</span>
                            </p>
                            <div class="api-details">
                                <h6>Description:</h6>
                                <p>Fetches a list of video categories available for a given AI app.</p>
                            </div>
                        </div>
                    </div>

                    <!-- API 16: Get Category Videos -->
                    <div class="col-md-6 d-flex">
                        <div class="api-box w-100">
                            <h5>16. Get Category Videos</h5>
                            <p><span class="param-label">Method:</span> POST</p>
                            <p><span class="param-label">URL:</span><br>
                                <span class="url-text">{{ url('/api/v1/asm044/getCategoryVideos') }}</span>
                            </p>
                            <p><span class="param-label">Parameters:</span><br>
                                <code>category_id</code> (required) e.g. <code>5</code>
                            </p>
                            <div class="api-details">
                                <h6>Description:</h6>
                                <p>Returns all videos that belong to the specified category.</p>
                            </div>
                        </div>
                    </div>

                    <!-- API 17: Get Video Template Categories -->
                    <div class="col-md-6 d-flex">
                        <div class="api-box w-100">
                            <h5>17. Get Video Template Categories</h5>
                            <p><span class="param-label">Method:</span> GET</p>
                            <p><span class="param-label">URL:</span><br>
                                <span class="url-text">{{ url('/api/v1/asm044/getVideoTemplateCategories') }}</span>
                            </p>
                            <p><span class="param-label">Parameters:</span><br>
                                <code>app</code> (required) e.g. <code>wallpaper</code>
                            </p>
                            <div class="api-details">
                                <h6>Description:</h6>
                                <p>Fetches video template categories for the specified app.</p>
                            </div>
                        </div>
                    </div>

                    <!-- API 18: Get Template Video Category Wise -->
                    <div class="col-md-6 d-flex">
                        <div class="api-box w-100">
                            <h5>18. Get Template Video Category Wise</h5>
                            <p><span class="param-label">Method:</span> POST</p>
                            <p><span class="param-label">URL:</span><br>
                                <span class="url-text">{{ url('/api/v1/asm044/GetTemplateVideoCategoryWise') }}</span>
                            </p>
                            <p><span class="param-label">Parameters:</span><br>
                                <code>app</code> (required) e.g. <code>wallpaper</code><br>
                                <code>cat</code> (required) e.g. <code>test</code><br>
                                <code>page</code> (required) e.g. <code>1</code>
                            </p>
                            <div class="api-details">
                                <h6>Description:</h6>
                                <p>Returns a paginated list of video templates for the specified app and category.</p>
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
