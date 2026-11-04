<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MTQN XLII Pesisir Selatan Tahun 2026</title>

    {{-- SEO dasar --}}
    <meta name="description" content="Portal resmi e-MTQ MTQN XLII Kabupaten Pesisir Selatan Tahun 2026. Pantau jadwal, pendaftaran peserta, daftar ulang, hasil penilaian, dan klasemen perlombaan Musabaqah Tilawatil Quran secara digital.">
    <meta name="keywords" content="MTQN XLII, MTQ Pesisir Selatan, MTQ 2026, Musabaqah Tilawatil Quran, e-MTQ, jadwal MTQ, hasil MTQ, klasemen MTQ, Pesisir Selatan">
    <meta name="author" content="Panitia MTQN XLII Kabupaten Pesisir Selatan">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#0f172a">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Open Graph (Facebook, WhatsApp, Telegram, LinkedIn) --}}
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="e-MTQ Pesisir Selatan">
    <meta property="og:locale" content="id_ID">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="MTQN XLII Pesisir Selatan Tahun 2026">
    <meta property="og:description" content="Portal resmi e-MTQ MTQN XLII Kabupaten Pesisir Selatan Tahun 2026. Pantau jadwal, pendaftaran, hasil penilaian, dan klasemen perlombaan secara digital.">
    <meta property="og:image" content="{{ asset('app_logo.png') }}">
    <meta property="og:image:secure_url" content="{{ asset('app_logo.png') }}">
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:alt" content="Logo e-MTQ MTQN XLII Kabupaten Pesisir Selatan Tahun 2026">

    {{-- Twitter / X --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="MTQN XLII Pesisir Selatan Tahun 2026">
    <meta name="twitter:description" content="Portal resmi e-MTQ MTQN XLII Kabupaten Pesisir Selatan Tahun 2026. Jadwal, pendaftaran, hasil penilaian, dan klasemen perlombaan.">
    <meta name="twitter:image" content="{{ asset('app_logo.png') }}">
    <meta name="twitter:image:alt" content="Logo e-MTQ MTQN XLII Kabupaten Pesisir Selatan Tahun 2026">

    {{-- Tailwind via CDN, sama seperti file HTML awal --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Vite entry khusus landing --}}
    @vite('resources/js/landing.js')

    <link href="{{ asset('app_logo.png') }}" rel="icon">
    <link rel="apple-touch-icon" href="{{ asset('app_logo.png') }}">
    {{-- <link rel="manifest" href="{{ asset('/manifest.json') }}"> --}}

    <style>
        [v-cloak]>* {
            display: none
        }

        [v-cloak]::before {
            content: "Loading…";
            margin: 0 auto;
            color: #e5e7eb;
        }
    </style>

    <script>
        // kirim data events & login URL ke Vue
        window.__INITIAL_EVENTS__ = @json($events ?? []);
        window.__LOGIN_URL__ = "{{ route('login') }}";
    </script>
</head>

<body class="bg-slate-950 text-slate-100">
    <div id="app" v-cloak></div>
</body>

</html>
