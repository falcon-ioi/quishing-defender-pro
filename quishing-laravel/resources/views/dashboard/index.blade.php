<x-app-layout>
    <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-extrabold text-white tracking-tight">Dashboard</h1>
            <p class="text-slate-400 mt-1">Statistik dan ringkasan hasil scan</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="glass-card p-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(99,102,241,0.15);">
                        <svg class="w-5 h-5" style="color: #818CF8;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Total Scan</p>
                </div>
                <p class="text-3xl font-extrabold text-white">{{ $totalScans }}</p>
            </div>

            <div class="glass-card p-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(16,185,129,0.15);">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Aman</p>
                </div>
                <p class="text-3xl font-extrabold text-emerald-400">{{ $safeCount }}</p>
            </div>

            <div class="glass-card p-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(245,158,11,0.15);">
                        <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                    </div>
                    <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Curiga</p>
                </div>
                <p class="text-3xl font-extrabold text-amber-400">{{ $suspiciousCount }}</p>
            </div>

            <div class="glass-card p-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(239,68,68,0.15);">
                        <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                    </div>
                    <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Bahaya</p>
                </div>
                <p class="text-3xl font-extrabold text-red-400">{{ $dangerousCount }}</p>
            </div>
        </div>

        <!-- Daily Stats Chart -->
        @if($dailyStats->count() > 0)
        <div class="glass-card p-6 mb-8">
            <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5" style="color: #818CF8;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                Aktivitas 7 Hari Terakhir
            </h3>
            <div class="space-y-3">
                @foreach($dailyStats->reverse() as $stat)
                <div class="flex items-center gap-4">
                    <span class="text-xs text-slate-500 w-20 shrink-0">{{ \Carbon\Carbon::parse($stat->date)->format('d M') }}</span>
                    <div class="flex-1 h-8 rounded-lg overflow-hidden" style="background: rgba(255,255,255,0.03);">
                        @php $maxTotal = $dailyStats->max('total') ?: 1; @endphp
                        <div class="h-full rounded-lg flex items-center px-3 text-xs font-bold text-white transition-all duration-500"
                             style="width: {{ max(($stat->total / $maxTotal) * 100, 10) }}%; background: linear-gradient(135deg, #4F46E5, #6366F1);">
                            {{ $stat->total }} scan
                        </div>
                    </div>
                    @if($stat->dangerous > 0)
                    <span class="text-xs text-red-400 font-medium shrink-0">{{ $stat->dangerous }} bahaya</span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Recent Scans -->
        <div class="glass-card p-6">
            <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5" style="color: #818CF8;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Scan Terbaru
            </h3>
            @if($recentScans->count() > 0)
            <div class="space-y-2">
                @foreach($recentScans as $scan)
                <div class="flex items-center justify-between p-3 rounded-xl transition-all duration-200 hover:bg-white/5" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.04);">
                    <div class="flex items-center space-x-3">
                        @if($scan->risk_level === 'SAFE')
                            <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background: rgba(16,185,129,0.15);">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                        @elseif($scan->risk_level === 'SUSPICIOUS')
                            <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background: rgba(245,158,11,0.15);">
                                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01"/></svg>
                            </div>
                        @else
                            <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background: rgba(239,68,68,0.15);">
                                <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </div>
                        @endif
                        <div>
                            <p class="text-sm font-medium text-slate-200 truncate" style="max-width: 400px;">{{ $scan->url }}</p>
                            <p class="text-xs text-slate-500">{{ $scan->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-bold text-slate-400">{{ $scan->risk_score }}/100</span>
                        @if($scan->risk_level === 'SAFE')
                            <span class="px-2 py-1 text-xs font-semibold rounded-lg" style="background:rgba(16,185,129,0.15);color:#34D399;">Aman</span>
                        @elseif($scan->risk_level === 'SUSPICIOUS')
                            <span class="px-2 py-1 text-xs font-semibold rounded-lg" style="background:rgba(245,158,11,0.15);color:#FBBF24;">Curiga</span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-lg" style="background:rgba(239,68,68,0.15);color:#F87171;">Bahaya</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-12">
                <svg class="w-16 h-16 mx-auto mb-4" style="color: #334155;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                <p class="text-slate-500">Belum ada data scan</p>
                <a href="{{ route('scan.index') }}" class="mt-4 inline-flex items-center gap-2 px-5 py-2 rounded-xl text-sm font-bold text-white" style="background: linear-gradient(135deg, #4F46E5, #6366F1);">
                    Mulai Scan
                </a>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
