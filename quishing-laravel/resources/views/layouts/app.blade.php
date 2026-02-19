<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Quishing Defender') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            * { font-family: 'Inter', sans-serif; }
            body { background: #0f0f1a; color: #e2e8f0; }

            /* Splash Screen */
            .splash-overlay {
                position: fixed; top: 0; left: 0;
                width: 100%; height: 100%;
                background: radial-gradient(ellipse at center, #1a1a3e 0%, #0f0f1a 70%);
                display: flex; align-items: center; justify-content: center;
                z-index: 9999; opacity: 1;
                transition: opacity 0.8s cubic-bezier(0.4, 0, 0.2, 1);
            }
            .splash-overlay.fade-out { opacity: 0; pointer-events: none; }
            .splash-content { text-align: center; }
            .splash-logo {
                width: 120px; height: 120px;
                margin: 0 auto 24px;
                animation: logoEntry 1s ease-out forwards;
                filter: drop-shadow(0 0 20px rgba(99,102,241,0.4));
            }
            .splash-title {
                font-size: 2.2rem; font-weight: 800; color: white;
                letter-spacing: -1px; opacity: 0;
                animation: fadeUp 0.6s ease-out 0.5s forwards;
            }
            .splash-highlight { color: #818CF8; }
            .splash-subtitle {
                font-size: 0.9rem; color: #A5B4FC;
                margin-top: 8px; opacity: 0;
                animation: fadeUp 0.6s ease-out 0.8s forwards;
            }
            @keyframes logoEntry {
                0% { transform: scale(0) rotate(-180deg); opacity: 0; }
                60% { transform: scale(1.1) rotate(10deg); opacity: 1; }
                100% { transform: scale(1) rotate(0deg); opacity: 1; }
            }
            @keyframes fadeUp {
                0% { opacity: 0; transform: translateY(20px); }
                100% { opacity: 1; transform: translateY(0); }
            }
            .shield-outer { animation: shieldPulse 1.5s ease-in-out 0.3s infinite alternate; }
            @keyframes shieldPulse { 0% { stroke-opacity: 0.5; } 100% { stroke-opacity: 1; } }
            .qr-block { opacity: 0; }
            .qr1 { animation: blockPop 0.3s ease-out 0.4s forwards; }
            .qr2 { animation: blockPop 0.3s ease-out 0.55s forwards; }
            .qr3 { animation: blockPop 0.3s ease-out 0.7s forwards; }
            .qr4 { animation: blockPop 0.3s ease-out 0.85s forwards; }
            .qr5 { animation: blockPop 0.3s ease-out 0.95s forwards; }
            .qr6 { animation: blockPop 0.3s ease-out 1.05s forwards; }
            @keyframes blockPop {
                0% { opacity: 0; transform: scale(0); }
                70% { transform: scale(1.3); }
                100% { opacity: 1; transform: scale(1); }
            }
            .scan-line { opacity: 0; animation: scanMove 1.2s ease-in-out 1.2s infinite; }
            @keyframes scanMove {
                0% { opacity: 0; transform: translateY(-15px); }
                20% { opacity: 1; } 80% { opacity: 1; }
                100% { opacity: 0; transform: translateY(15px); }
            }

            /* Glass card effect */
            .glass-card {
                background: rgba(255,255,255,0.03);
                backdrop-filter: blur(20px);
                border: 1px solid rgba(255,255,255,0.08);
                border-radius: 16px;
                transition: all 0.3s ease;
            }
            .glass-card:hover {
                border-color: rgba(99,102,241,0.3);
                box-shadow: 0 8px 32px rgba(99,102,241,0.1);
            }

            /* Animated gradient bg */
            .gradient-bg {
                background: linear-gradient(-45deg, #0f0f1a, #1a1a3e, #1e1b4b, #0f0f1a);
                background-size: 400% 400%;
                animation: gradientShift 15s ease infinite;
            }
            @keyframes gradientShift {
                0% { background-position: 0% 50%; }
                50% { background-position: 100% 50%; }
                100% { background-position: 0% 50%; }
            }

            /* Page enter animation */
            .page-enter {
                animation: pageSlideIn 0.5s ease-out;
            }
            @keyframes pageSlideIn {
                0% { opacity: 0; transform: translateY(20px); }
                100% { opacity: 1; transform: translateY(0); }
            }
        </style>
    </head>
    <body class="antialiased gradient-bg min-h-screen">
        <!-- Splash Screen -->
        <div id="splash-screen" class="splash-overlay">
            <div class="splash-content">
                <svg class="splash-logo" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path class="shield-outer" d="M50 5L15 20V45C15 69.5 29.8 90.8 50 95C70.2 90.8 85 69.5 85 45V20L50 5Z" fill="rgba(99,102,241,0.15)" stroke="#6366F1" stroke-width="3"/>
                    <path d="M50 15L25 25V45C25 62.5 35.5 77.5 50 82C64.5 77.5 75 62.5 75 45V25L50 15Z" fill="rgba(15,15,26,0.8)"/>
                    <rect class="qr-block qr1" x="35" y="35" width="10" height="10" rx="2" fill="#818CF8"/>
                    <rect class="qr-block qr2" x="55" y="35" width="10" height="10" rx="2" fill="#818CF8"/>
                    <rect class="qr-block qr3" x="35" y="55" width="10" height="10" rx="2" fill="#818CF8"/>
                    <rect class="qr-block qr4" x="55" y="51" width="4" height="4" rx="1" fill="#A5B4FC"/>
                    <rect class="qr-block qr5" x="51" y="55" width="4" height="4" rx="1" fill="#A5B4FC"/>
                    <rect class="qr-block qr6" x="55" y="59" width="10" height="6" rx="2" fill="#818CF8"/>
                    <line class="scan-line" x1="30" y1="50" x2="70" y2="50" stroke="#10B981" stroke-width="2"/>
                </svg>
                <h1 class="splash-title">Quishing<span class="splash-highlight">Defender</span></h1>
                <p class="splash-subtitle">Melindungi Anda dari QR Phishing</p>
            </div>
        </div>

        <script>
            window.addEventListener('load', function() {
                setTimeout(function() {
                    document.getElementById('splash-screen').classList.add('fade-out');
                    setTimeout(function() {
                        document.getElementById('splash-screen').style.display = 'none';
                    }, 800);
                }, 2200);
            });
        </script>

        @include('layouts.navigation')

        <!-- Page Content -->
        <main class="page-enter">
            {{ $slot }}
        </main>
    </body>
</html>
