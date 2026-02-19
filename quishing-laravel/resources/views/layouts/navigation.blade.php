<nav class="sticky top-0 z-50" style="background: rgba(15,15,26,0.8); backdrop-filter: blur(20px); border-bottom: 1px solid rgba(255,255,255,0.06);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <!-- Logo -->
                <a href="{{ route('scan.index') }}" class="flex items-center gap-3 group">
                    <svg class="h-9 w-9 transition-transform duration-300 group-hover:scale-110" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" style="filter: drop-shadow(0 0 8px rgba(99,102,241,0.4));">
                        <path d="M50 5L15 20V45C15 69.5 29.8 90.8 50 95C70.2 90.8 85 69.5 85 45V20L50 5Z" fill="rgba(99,102,241,0.15)" stroke="#6366F1" stroke-width="3"/>
                        <path d="M50 15L25 25V45C25 62.5 35.5 77.5 50 82C64.5 77.5 75 62.5 75 45V25L50 15Z" fill="rgba(15,15,26,0.9)"/>
                        <rect x="35" y="35" width="10" height="10" rx="2" fill="#818CF8"/>
                        <rect x="55" y="35" width="10" height="10" rx="2" fill="#818CF8"/>
                        <rect x="35" y="55" width="10" height="10" rx="2" fill="#818CF8"/>
                        <rect x="55" y="51" width="4" height="4" rx="1" fill="#A5B4FC"/>
                        <rect x="51" y="55" width="4" height="4" rx="1" fill="#A5B4FC"/>
                        <rect x="55" y="59" width="10" height="6" rx="2" fill="#818CF8"/>
                    </svg>
                    <span class="text-lg font-bold tracking-tight">
                        <span class="text-white">Quishing</span><span style="color: #818CF8;">Defender</span>
                    </span>
                </a>

                <!-- Navigation Links -->
                <div class="hidden sm:flex items-center ml-10 space-x-1">
                    <a href="{{ route('scan.index') }}"
                       class="nav-pill {{ request()->routeIs('scan.index') ? 'active' : '' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Scan
                    </a>
                    <a href="{{ route('scan.history') }}"
                       class="nav-pill {{ request()->routeIs('scan.history') ? 'active' : '' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Riwayat
                    </a>
                    <a href="{{ route('dashboard') }}"
                       class="nav-pill {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Navigation -->
    <div class="sm:hidden border-t" style="border-color: rgba(255,255,255,0.06);">
        <div class="flex">
            <a href="{{ route('scan.index') }}" class="mobile-nav-item {{ request()->routeIs('scan.index') ? 'active' : '' }}">
                <svg class="w-5 h-5 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <span class="text-xs">Scan</span>
            </a>
            <a href="{{ route('scan.history') }}" class="mobile-nav-item {{ request()->routeIs('scan.history') ? 'active' : '' }}">
                <svg class="w-5 h-5 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-xs">Riwayat</span>
            </a>
            <a href="{{ route('dashboard') }}" class="mobile-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg class="w-5 h-5 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6z"/></svg>
                <span class="text-xs">Dashboard</span>
            </a>
        </div>
    </div>

    <style>
        .nav-pill {
            display: flex; align-items: center; gap: 6px;
            padding: 8px 16px; border-radius: 10px;
            font-size: 0.875rem; font-weight: 500;
            color: #94a3b8; transition: all 0.2s ease;
            text-decoration: none;
        }
        .nav-pill:hover { color: #e2e8f0; background: rgba(255,255,255,0.06); }
        .nav-pill.active {
            color: #818CF8;
            background: rgba(99,102,241,0.1);
            box-shadow: 0 0 12px rgba(99,102,241,0.15);
        }
        .mobile-nav-item {
            flex: 1; text-align: center; padding: 10px 0;
            color: #64748b; text-decoration: none; transition: all 0.2s;
        }
        .mobile-nav-item.active { color: #818CF8; }
        .mobile-nav-item:hover { color: #e2e8f0; }
    </style>
</nav>
