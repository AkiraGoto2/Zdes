<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Leaflet -->
    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <style>
        html, body {
            height: 100%;
            margin: 0;
        }

        header {
            height: 65px;
        }

        main {
            height: calc(100vh - 125px); /* header + footer */
            width: 100%;
        }

        #map {
            width: 100%;
            height: 100%;
        }

        footer {
            height: 60px;
        }
    </style>
</head>

<body class="bg-[#FDFDFC] text-[#0c0c0c] flex flex-col min-h-screen">

<!-- HEADER -->
<header class="w-full max-w-[1024px] mx-auto h-[65px] text-sm flex items-center justify-between px-4">

    <div class="flex-shrink-0">
        <a href="/" class="block">
            <x-application-logo style="width: 100px; height: auto;" />
        </a>
    </div>

    <nav class="flex items-center gap-6 ml-6">

        <a href="{{ url('/') }}"
           class="text-[14px] font-medium transition
           {{ request()->is('/') ? 'text-[#4A40E0] underline underline-offset-4' : 'text-[#475569] hover:text-[#4A40E0]' }}">
            Главная
        </a>

        <a href="{{ url('/events') }}"
           class="text-[14px] font-medium transition
           {{ request()->is('events') ? 'text-[#4A40E0] underline underline-offset-4' : 'text-[#475569] hover:text-[#4A40E0]' }}">
            События
        </a>

        <a href="{{ url('/my-events') }}"
           class="text-[14px] font-medium transition
           {{ request()->is('my-events') ? 'text-[#4A40E0] underline underline-offset-4' : 'text-[#475569] hover:text-[#4A40E0]' }}">
            Мои события
        </a>

    </nav>

    @if (Route::has('login'))
        <nav class="flex items-center gap-4">
            @auth
                <a href="{{ url('/dashboard') }}"
                   class="bg-[#4A40E0] text-white rounded-[12px] text-[16px] py-2 px-6 font-semibold">
                    Профиль
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="bg-[#4A40E0] text-white rounded-[12px] text-[16px] py-2 px-6 font-semibold">
                    Войти
                </a>
            @endauth
        </nav>
    @endif

</header>

<!-- MAP -->
<main>
    <div id="map"></div>
</main>

<!-- FOOTER -->
<footer class="flex items-center justify-center text-sm text-gray-500 border-t">
    © {{ date('Y') }} Где Движ — все права защищены
</footer>

<script>
    const center = [55.1540, 61.4026];

    const map = L.map('map', {
        center: center,
        zoom: 12,
        minZoom: 5,
        maxZoom: 19
    });

    // OpenStreetMap слой
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // тестовый маркер
    L.marker(center)
        .addTo(map)
        .bindPopup('Челябинск');
</script>

</body>
</html>