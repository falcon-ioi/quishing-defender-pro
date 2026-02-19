<x-app-layout>
    <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-6xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-extrabold text-white tracking-tight">Riwayat Scan</h1>
                <p class="text-slate-400 mt-1">Semua hasil scan URL dan QR Code</p>
            </div>
            <a href="{{ route('scan.index') }}" class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-white transition-all duration-300 hover:-translate-y-0.5" style="background: linear-gradient(135deg, #4F46E5, #6366F1); box-shadow: 0 4px 15px rgba(79,70,229,0.3);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Scan Baru
            </a>
        </div>

        <div class="glass-card overflow-hidden">
            @if($scans->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr style="background: rgba(255,255,255,0.03); border-bottom: 1px solid rgba(255,255,255,0.06);">
                            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">URL</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Score</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($scans as $scan)
                        <tr class="transition-colors duration-150 hover:bg-white/5" style="border-bottom: 1px solid rgba(255,255,255,0.04);">
                            <td class="px-5 py-4">
                                @if($scan->risk_level === 'SAFE')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-lg" style="background:rgba(16,185,129,0.15);color:#34D399;">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>Aman
                                    </span>
                                @elseif($scan->risk_level === 'SUSPICIOUS')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-lg" style="background:rgba(245,158,11,0.15);color:#FBBF24;">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>Curiga
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-lg" style="background:rgba(239,68,68,0.15);color:#F87171;">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>Bahaya
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <p class="text-sm font-mono text-slate-200 truncate" style="max-width: 400px;">{{ $scan->url }}</p>
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-sm font-bold
                                    @if($scan->risk_score <= 25) text-emerald-400
                                    @elseif($scan->risk_score <= 55) text-amber-400
                                    @else text-red-400 @endif">
                                    {{ $scan->risk_score }}/100
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-xs text-slate-500">{{ $scan->created_at->diffForHumans() }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-4" style="border-top: 1px solid rgba(255,255,255,0.06);">
                {{ $scans->links() }}
            </div>
            @else
            <div class="text-center py-16">
                <svg class="w-20 h-20 mx-auto mb-4" style="color: #1e293b;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                <p class="text-slate-500 text-lg mb-2">Belum ada riwayat scan</p>
                <p class="text-slate-600 text-sm mb-6">Mulai scan URL atau QR Code pertama Anda</p>
                <a href="{{ route('scan.index') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-bold text-white" style="background: linear-gradient(135deg, #4F46E5, #6366F1); box-shadow: 0 4px 15px rgba(79,70,229,0.3);">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    Mulai Scan
                </a>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
