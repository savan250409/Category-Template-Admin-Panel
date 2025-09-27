<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Pages / Login - NiceAdmin Bootstrap Template</title>
    <meta content="" name="description" />
    <meta content="" name="keywords" />
    <!-- Favicons -->
    <link href="{{ asset('NiceAdmin\images\icon\logo-2023 copy.png') }}" rel="icon" />

    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect" />
    <link
        href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i"
        rel="stylesheet" />
    <!-- Vendor CSS Files -->
    <link href="NiceAdmin/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
    <link href="NiceAdmin/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet" />
    <link href="NiceAdmin/assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet" />
    <link href="NiceAdmin/assets/vendor/quill/quill.snow.css" rel="stylesheet" />
    <link href="NiceAdmin/assets/vendor/quill/quill.bubble.css" rel="stylesheet" />
    <link href="NiceAdmin/assets/vendor/remixicon/remixicon.css" rel="stylesheet" />
    <link href="NiceAdmin/assets/vendor/simple-datatables/style.css" rel="stylesheet" />
    <!-- Template Main CSS File -->
    <link href="NiceAdmin/assets/css/style.css" rel="stylesheet" />
</head>

<body>
    <main>
        <div class="container">
            <section
                class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4 bg-light">
                <div class="">
                    <div class="row justify-content-center" style="width: 1500px;  padding-right: 15rem">
                        <div class="col-lg-5 col-md-7 d-flex flex-column align-items-center justify-content-center">

                            <div class="card shadow-lg border-0 rounded-4 p-4"
                                style="background: rgba(255,255,255,0.8); backdrop-filter: blur(10px);">
                                <div class="card-body">

                                    <h4 class="card-title text-center fw-bold mb-2">🔐 Login to Your Account</h4>
                                    <p class="text-center text-muted small mb-4">Enter your email & password to access
                                        your dashboard</p>

                                    @if (session('error'))
                                        <div class="alert alert-danger text-center py-2" role="alert">
                                            {{ session('error') }}
                                        </div>
                                    @endif

                                    <form action="{{ route('admin-auth') }}" method="POST">
                                        @csrf

                                        <div class="form-floating mb-3">
                                            <input type="email" name="email" class="form-control" id="email"
                                                placeholder="Email" required>
                                            <label for="email">📧 Email Address</label>
                                        </div>

                                        <div class="form-floating mb-4">
                                            <input type="password" name="password" class="form-control"
                                                id="yourPassword" placeholder="Password" required>
                                            <label for="yourPassword">🔒 Password</label>
                                        </div>

                                        <button class="btn btn-primary w-100 py-2 fw-semibold" type="submit">
                                            🚀 Login
                                        </button>
                                    </form>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- End #main -->

    <!-- Vendor JS Files -->
    <script src="NiceAdmin/assets/vendor/apexcharts/apexcharts.min.js"></script>
    <script src="NiceAdmin/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="NiceAdmin/assets/vendor/chart.js/chart.umd.js"></script>
    <script src="NiceAdmin/assets/vendor/echarts/echarts.min.js"></script>
    <script src="NiceAdmin/assets/vendor/quill/quill.js"></script>
    <script src="NiceAdmin/assets/vendor/simple-datatables/simple-datatables.js"></script>
    <script src="NiceAdmin/assets/vendor/tinymce/tinymce.min.js"></script>
    <script src="NiceAdmin/assets/vendor/php-email-form/validate.js"></script>

    <!-- Template Main JS File -->
    <script src="NiceAdmin/assets/js/main.js"></script>
</body>
</html>
