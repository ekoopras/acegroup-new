<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Pengambilan #{{ $service->id }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-white text-slate-900 min-h-screen flex flex-col items-center justify-center p-6 text-center">

    <div class="w-full max-w-sm">

        <div class="mb-4">
            <h1 class="text-2xl font-black text-slate-800 tracking-tight">
                QR CODE
                <br>
                PENGAMBILAN UNIT
            </h1>
        </div>

        <div class="my-8 inline-block p-2 bg-white">
            {!! QrCode::size(220)->eye('square')->color(15, 23, 42)->margin(0)->generate($service->nomor_surat) !!}
        </div>

        <p class="text-xs text-slate-400 leading-relaxed max-w-[260px] mx-auto font-medium">
            Tunjukkan QR Code ini ke petugas untuk melakukan proses pengambilan unit.
        </p>

        <p class="text-[10px] text-slate-300 font-bold uppercase tracking-[0.2em] mt-12">
            Acegroup Service Center
        </p>

    </div>

</body>

</html>
