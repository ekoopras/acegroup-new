<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tracking Unit #{{ $service->id }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/bootstrap.min.css" rel="stylesheet">
    <style>
        .timeline-item {
            border-left: 3px solid #0d6efd;
            position: relative;
            padding-left: 20px;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            width: 12px;
            height: 12px;
            background: #0d6efd;
            border-radius: 50%;
            left: -8px;
            top: 5px;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-4">Tracking Service</h4>

                        <!-- Info Barang -->
                        <div class="row mb-4">
                            <div class="col-6">
                                <small class="text-muted d-block">Unit Barang</small>
                                <strong>{{ $service->nama_barang }}</strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Nama Pelanggan</small>
                                <strong>{{ $status == 'antrean' ? $service->dataClient->nama ?? '-' : $service->nama_client }}</strong>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        @if ($status == 'antrean')
                            <div class="alert alert-info border-0 shadow-sm text-center">
                                <h5 class="mb-1 fw-bold">STATUS: MENUNGGU ANTREAN</h5>
                                <p class="mb-0 small">Unit telah terdaftar di sistem. Mohon tunggu teknisi kami
                                    memproses unit Anda.</p>
                            </div>
                        @else
                            @php $current = $logs->first(); @endphp
                            <div class="alert alert-primary border-0 shadow-sm text-center">
                                <h5 class="mb-1 fw-bold">STATUS: {{ strtoupper($current['status'] ?? 'PROSES') }}</h5>
                                <p class="mb-0 small">Update:
                                    {{ \Carbon\Carbon::parse($current['tanggal'] ?? now())->format('d M Y H:i') }}</p>
                            </div>

                            <!-- Timeline Riwayat -->
                            <h6 class="fw-bold mt-5 mb-3">Riwayat Pengerjaan</h6>
                            <div class="ps-3 mt-4">
                                @foreach ($logs as $log)
                                    <div class="timeline-item mb-4">
                                        <div class="d-flex justify-content-between">
                                            <span class="fw-bold text-dark">{{ $log['status'] }}</span>
                                            <small
                                                class="text-muted">{{ \Carbon\Carbon::parse($log['tanggal'])->format('H:i') }}</small>
                                        </div>
                                        <p class="text-muted small mb-0">{{ $log['keterangan'] }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
