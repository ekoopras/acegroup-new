<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>UjianApp</title>
    <link rel="icon" href="{{ asset('ico.png') }}" type="image/png">

    <!-- asset-css  -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <!-- asset-font  -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- PWA  -->
    <meta name="theme-color" content="#6777ef" />
    <link rel="apple-touch-icon" href="{{ asset('logo.png') }}">
    <link rel="manifest" href="{{ asset('/manifest.json') }}">
</head>

<body>

    <style>
        body {
            font-family: 'Poppins', system-ui, -apple-system, BlinkMacSystemFont,
                'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
    </style>

    <div class="container-fluid bg-ujian">
        <div class="row justify-content-center align-items-center min-vh-100">

            <div class="col-11 col-sm-8 col-md-7 col-lg-5 col-xl-4">


                <div class="card shadow-lg border-0 rounded-4">
                    <div class="card-body p-3 p-sm-4 p-md-5">

                        <div class="accordion" id="accordionPanelsStayOpenExample">

                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed flex-column text-center" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseTwo"
                                        aria-expanded="false" aria-controls="panelsStayOpen-collapseTwo">

                                        <i class="bi bi-android2 fs-1 text-success mb-1"></i>
                                        <span>Install Android</span>

                                    </button>
                                </h2>


                                <div id="panelsStayOpen-collapseTwo" class="accordion-collapse collapse">
                                    <div class="accordion-body">

                                        <div class="d-grid">
                                            <!-- BUTTON INSTALL -->
                                            <button id="pwa-install-btn" type="button" class="btn btn-success">
                                                Install Aplikasi
                                            </button>


                                            <div class="alert alert-secondary small mt-3 mb-0">
                                                <b>Jika tombol install tidak muncul atau gagal install </b>
                                            </div>

                                            <!-- STEP LIST -->
                                            <ol class="list-group list-group-numbered text-start small">
                                                <li class="list-group-item border-0 px-0">
                                                    Ketuk menu <b>⋮</b> di pojok kanan atas browser
                                                </li>
                                                <li class="list-group-item border-0 px-0">
                                                    Pilih <b>Tambahkan ke layar utama</b>
                                                </li>
                                                <li class="list-group-item border-0 px-0">
                                                    Tekan <b>Tambah</b>
                                                </li>
                                            </ol>
                                        </div>

                                    </div>
                                </div>

                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed flex-column text-center" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseThree"
                                        aria-expanded="false" aria-controls="panelsStayOpen-collapseThree">

                                        <i class="bi bi-apple fs-1 mb-1"></i>
                                        <span>Install iOS</span>

                                    </button>

                                </h2>
                                <div id="panelsStayOpen-collapseThree" class="accordion-collapse collapse">
                                    <div class="accordion-body">
                                        <ol class="list-group list-group-numbered small">
                                            <li class="list-group-item border-0 px-0">
                                                Buka halaman ini menggunakan <b>Safari</b>
                                            </li>
                                            <li class="list-group-item border-0 px-0">
                                                Tekan tombol <b>Share</b>
                                                <i class="bi bi-box-arrow-up ms-1"></i>
                                                di bagian bawah layar
                                            </li>
                                            <li class="list-group-item border-0 px-0">
                                                Pilih <b>Add to Home Screen</b>
                                            </li>
                                            <li class="list-group-item border-0 px-0">
                                                Tekan <b>Add</b>
                                            </li>
                                        </ol>

                                        <div class="alert alert-secondary small mt-3 mb-0">
                                            📌 Pastikan menggunakan Safari, bukan Chrome.
                                        </div>

                                    </div>

                                </div>
                            </div>

                        </div>



                    </div>
                </div>

            </div>

        </div>
    </div>

    <style>
        .bg-ujian {
            min-height: 100vh;
            background-color: #0d6efd;

            background-image:
                radial-gradient(circle, rgba(255, 255, 255, 0.15) 1px, transparent 1px);

            background-size: 22px 22px;
        }
    </style>

    <script src="{{ asset('pwa-install.js') }}"></script>

    <!-- asset-js  -->
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>

</body>

</html>
