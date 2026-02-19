<x-app-layout>
    <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto">
        <!-- Result Card -->
        <div class="glass-card overflow-hidden">
            <!-- Risk Level Banner -->
            @php
                if ($result['risk_level'] === 'SAFE') {
                    $bannerGrad = 'rgba(16,185,129,0.15), rgba(16,185,129,0.03)';
                    $bannerBorder = 'rgba(16,185,129,0.2)';
                    $ringColor = '#10B981';
                    $textColor = 'text-emerald-400';
                    $labelText = 'AMAN';
                    $iconPath = 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z';
                } elseif ($result['risk_level'] === 'SUSPICIOUS') {
                    $bannerGrad = 'rgba(245,158,11,0.15), rgba(245,158,11,0.03)';
                    $bannerBorder = 'rgba(245,158,11,0.2)';
                    $ringColor = '#F59E0B';
                    $textColor = 'text-amber-400';
                    $labelText = 'MENCURIGAKAN';
                    $iconPath = 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z';
                } else {
                    $bannerGrad = 'rgba(239,68,68,0.15), rgba(239,68,68,0.03)';
                    $bannerBorder = 'rgba(239,68,68,0.2)';
                    $ringColor = '#EF4444';
                    $textColor = 'text-red-400';
                    $labelText = 'BERBAHAYA';
                    $iconPath = 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636';
                }
                $score = $result['risk_score'];
                $circumference = 2 * M_PI * 54;
                $dashOffset = $circumference - ($score / 100) * $circumference;
            @endphp

            <div class="p-8 sm:p-10 text-center" style="background: linear-gradient(135deg, {{ $bannerGrad }}); border-bottom: 1px solid {{ $bannerBorder }};">
                <!-- Circular Risk Gauge -->
                <div class="inline-block relative mb-4" style="width: 140px; height: 140px;">
                    <svg viewBox="0 0 120 120" style="width: 100%; height: 100%; transform: rotate(-90deg);">
                        <circle cx="60" cy="60" r="54" fill="none" stroke="rgba(255,255,255,0.06)" stroke-width="8"/>
                        <circle cx="60" cy="60" r="54" fill="none" stroke="{{ $ringColor }}" stroke-width="8"
                            stroke-linecap="round"
                            stroke-dasharray="{{ $circumference }}"
                            stroke-dashoffset="{{ $dashOffset }}"
                            class="risk-ring"/>
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-4xl font-extrabold {{ $textColor }}">{{ $score }}</span>
                        <span class="text-xs text-slate-500 font-semibold">/100</span>
                    </div>
                </div>
                <h2 class="text-3xl font-extrabold {{ $textColor }}">{{ $labelText }}</h2>
                @if($result['is_financial'] ?? false)
                    <div class="mt-3">
                        <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-xl text-xs font-bold" style="background: linear-gradient(135deg, rgba(16,185,129,0.15), rgba(16,185,129,0.08)); color: #34D399; border: 1px solid rgba(16,185,129,0.25);">
                            {{ ($result['is_qris'] ?? false) ? '📱 QRIS Payment Terverifikasi' : '💳 Transaksi Keuangan Terverifikasi' }}
                        </span>
                    </div>
                    @if(!empty($result['qris_info']['merchant_name']))
                    <div class="mt-3">
                        <span class="text-lg font-bold text-emerald-300">Atas Nama: {{ $result['qris_info']['merchant_name'] }}</span>
                        @if(!empty($result['qris_info']['merchant_city']))
                            <span class="text-sm text-slate-400 ml-2">📍 {{ $result['qris_info']['merchant_city'] }}</span>
                        @endif
                    </div>
                    @endif
                @endif
            </div>

            <div class="p-6 space-y-4">
                {{-- QRIS Merchant Info --}}
                @if(!empty($result['qris_info']) && !empty($result['qris_info']['merchant_name']))
                <div class="p-4 rounded-xl" style="background: linear-gradient(135deg, rgba(16,185,129,0.08), rgba(99,102,241,0.05)); border: 1px solid rgba(16,185,129,0.15);">
                    <h4 class="font-bold text-emerald-400 mb-3 text-sm flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center" style="background: rgba(16,185,129,0.15);">🏪</span>
                        Informasi Merchant
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="p-3 rounded-lg" style="background: rgba(255,255,255,0.03);">
                            <p class="text-xs text-slate-500 mb-1">Nama Merchant</p>
                            <p class="text-sm font-bold text-slate-200">{{ $result['qris_info']['merchant_name'] }}</p>
                        </div>
                        @if(!empty($result['qris_info']['merchant_city']))
                        <div class="p-3 rounded-lg" style="background: rgba(255,255,255,0.03);">
                            <p class="text-xs text-slate-500 mb-1">Kota</p>
                            <p class="text-sm font-bold text-slate-200">📍 {{ $result['qris_info']['merchant_city'] }}</p>
                        </div>
                        @endif
                        @if(!empty($result['qris_info']['country_code']))
                        <div class="p-3 rounded-lg" style="background: rgba(255,255,255,0.03);">
                            <p class="text-xs text-slate-500 mb-1">Kode Negara</p>
                            <p class="text-sm font-bold text-slate-200">🌍 {{ $result['qris_info']['country_code'] }}</p>
                        </div>
                        @endif
                        @if(!empty($result['qris_info']['transaction_amount']))
                        <div class="p-3 rounded-lg" style="background: rgba(255,255,255,0.03);">
                            <p class="text-xs text-slate-500 mb-1">Nominal</p>
                            <p class="text-sm font-bold text-slate-200">💰 Rp {{ number_format((float)$result['qris_info']['transaction_amount'], 0, ',', '.') }}</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- URL Info --}}
                <div class="p-4 rounded-xl url-info-card">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-9 h-9 rounded-lg flex items-center justify-center" style="background: rgba(99,102,241,0.1);">
                            <svg class="w-4.5 h-4.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs text-slate-500 mb-1 font-semibold uppercase tracking-wider">URL yang dianalisis</p>
                            <p class="text-sm font-mono text-slate-200 break-all">{{ $result['url'] }}</p>
                            <p class="text-xs text-slate-500 mt-1">Domain: <span class="text-slate-400">{{ $result['domain'] }}</span></p>
                        </div>
                    </div>
                </div>

                {{-- Threats --}}
                @if(count($result['threats'] ?? []) > 0)
                <div class="p-4 rounded-xl detail-section" style="background: rgba(239,68,68,0.06); border-color: rgba(239,68,68,0.15);">
                    <h4 class="font-bold text-red-400 mb-3 text-sm flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center" style="background: rgba(239,68,68,0.15);">🚨</span>
                        Ancaman Terdeteksi ({{ count($result['threats']) }})
                    </h4>
                    <div class="space-y-3">
                        @foreach($result['threats'] as $idx => $threat)
                            <div class="rounded-lg overflow-hidden" style="background: rgba(239,68,68,0.05); border: 1px solid rgba(239,68,68,0.1);">
                                <button onclick="toggleDetail('threat-{{ $idx }}')" class="w-full p-3 flex items-start gap-3 text-left cursor-pointer hover:bg-white/5 transition-all">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-400 mt-1.5 flex-shrink-0"></span>
                                    <div class="flex-1">
                                        <p class="text-sm font-semibold text-red-300">{{ is_array($threat) ? $threat['title'] : $threat }}</p>
                                        @if(is_array($threat) && !empty($threat['severity']))
                                            <span class="inline-block mt-1 px-2 py-0.5 rounded text-xs font-bold"
                                                style="background: {{ $threat['severity'] === 'CRITICAL' ? 'rgba(239,68,68,0.2)' : 'rgba(245,158,11,0.2)' }}; color: {{ $threat['severity'] === 'CRITICAL' ? '#FCA5A5' : '#FCD34D' }};">
                                                {{ $threat['severity'] }} • {{ $threat['category'] ?? 'UNKNOWN' }}
                                            </span>
                                        @endif
                                    </div>
                                    <svg class="w-4 h-4 text-red-400/50 flex-shrink-0 mt-0.5 transition-transform" id="threat-{{ $idx }}-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                @if(is_array($threat) && !empty($threat['detail']))
                                <div id="threat-{{ $idx }}" class="hidden px-3 pb-3 pl-7">
                                    <p class="text-xs text-red-200/60 leading-relaxed">{{ $threat['detail'] }}</p>
                                </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Warnings --}}
                @if(count($result['warnings'] ?? []) > 0)
                <div class="p-4 rounded-xl detail-section" style="background: rgba(245,158,11,0.06); border-color: rgba(245,158,11,0.15);">
                    <h4 class="font-bold text-amber-400 mb-3 text-sm flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center" style="background: rgba(245,158,11,0.15);">⚠️</span>
                        Peringatan ({{ count($result['warnings']) }})
                    </h4>
                    <div class="space-y-3">
                        @foreach($result['warnings'] as $idx => $warning)
                            <div class="rounded-lg overflow-hidden" style="background: rgba(245,158,11,0.05); border: 1px solid rgba(245,158,11,0.1);">
                                <button onclick="toggleDetail('warning-{{ $idx }}')" class="w-full p-3 flex items-start gap-3 text-left cursor-pointer hover:bg-white/5 transition-all">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 mt-1.5 flex-shrink-0"></span>
                                    <div class="flex-1">
                                        <p class="text-sm font-semibold text-amber-300">{{ is_array($warning) ? $warning['title'] : $warning }}</p>
                                        @if(is_array($warning) && !empty($warning['severity']))
                                            <span class="inline-block mt-1 px-2 py-0.5 rounded text-xs font-bold"
                                                style="background: rgba(245,158,11,0.2); color: #FCD34D;">
                                                {{ $warning['severity'] }} • {{ $warning['category'] ?? 'UNKNOWN' }}
                                            </span>
                                        @endif
                                    </div>
                                    <svg class="w-4 h-4 text-amber-400/50 flex-shrink-0 mt-0.5 transition-transform" id="warning-{{ $idx }}-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                @if(is_array($warning) && !empty($warning['detail']))
                                <div id="warning-{{ $idx }}" class="hidden px-3 pb-3 pl-7">
                                    <p class="text-xs text-amber-200/60 leading-relaxed">{{ $warning['detail'] }}</p>
                                </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Safe Indicators --}}
                @if(count($result['safe_indicators'] ?? []) > 0)
                <div class="p-4 rounded-xl detail-section" style="background: rgba(16,185,129,0.06); border-color: rgba(16,185,129,0.15);">
                    <h4 class="font-bold text-emerald-400 mb-3 text-sm flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center" style="background: rgba(16,185,129,0.15);">✅</span>
                        Indikator Aman ({{ count($result['safe_indicators']) }})
                    </h4>
                    <div class="space-y-3">
                        @foreach($result['safe_indicators'] as $idx => $indicator)
                            <div class="rounded-lg overflow-hidden" style="background: rgba(16,185,129,0.05); border: 1px solid rgba(16,185,129,0.1);">
                                <button onclick="toggleDetail('safe-{{ $idx }}')" class="w-full p-3 flex items-start gap-3 text-left cursor-pointer hover:bg-white/5 transition-all">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mt-1.5 flex-shrink-0"></span>
                                    <div class="flex-1">
                                        <p class="text-sm font-semibold text-emerald-300">{{ is_array($indicator) ? $indicator['title'] : $indicator }}</p>
                                    </div>
                                    <svg class="w-4 h-4 text-emerald-400/50 flex-shrink-0 mt-0.5 transition-transform" id="safe-{{ $idx }}-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                @if(is_array($indicator) && !empty($indicator['detail']))
                                <div id="safe-{{ $idx }}" class="hidden px-3 pb-3 pl-7">
                                    <p class="text-xs text-emerald-200/60 leading-relaxed">{{ $indicator['detail'] }}</p>
                                </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- API Results -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    @if(isset($result['virustotal_result']) && $result['virustotal_result'])
                    <div class="p-5 rounded-xl text-center api-result-card">
                        <div class="w-10 h-10 rounded-xl mx-auto mb-2 flex items-center justify-center" style="background: rgba(99,102,241,0.1);">
                            <span class="text-lg">🦠</span>
                        </div>
                        <p class="text-xs text-slate-500 font-semibold mb-1">VirusTotal</p>
                        @php
                            $vt = $result['virustotal_result'];
                            $total = ($vt['malicious'] ?? 0) + ($vt['harmless'] ?? 0) + ($vt['undetected'] ?? 0);
                        @endphp
                        <p class="text-2xl font-extrabold {{ ($vt['malicious'] ?? 0) > 0 ? 'text-red-400' : 'text-emerald-400' }}">
                            {{ $vt['malicious'] ?? 0 }}/{{ $total }}
                        </p>
                        <p class="text-xs text-slate-500 mt-0.5">engine berbahaya</p>
                    </div>
                    @endif

                    @if(isset($result['gsb_result']) && $result['gsb_result'])
                    <div class="p-5 rounded-xl text-center api-result-card">
                        <div class="w-10 h-10 rounded-xl mx-auto mb-2 flex items-center justify-center" style="background: rgba(16,185,129,0.1);">
                            <span class="text-lg">🛡️</span>
                        </div>
                        <p class="text-xs text-slate-500 font-semibold mb-1">Safe Browsing</p>
                        <p class="text-2xl font-extrabold {{ $result['gsb_result']['is_safe'] ? 'text-emerald-400' : 'text-red-400' }}">
                            {{ $result['gsb_result']['is_safe'] ? 'Aman' : 'Berbahaya' }}
                        </p>
                    </div>
                    @endif

                    @if(isset($result['whois_result']) && $result['whois_result'])
                    <div class="p-5 rounded-xl text-center api-result-card">
                        <div class="w-10 h-10 rounded-xl mx-auto mb-2 flex items-center justify-center" style="background: rgba(245,158,11,0.1);">
                            <span class="text-lg">📋</span>
                        </div>
                        <p class="text-xs text-slate-500 font-semibold mb-1">WHOIS</p>
                        @if(isset($result['whois_result']['age_days']) && $result['whois_result']['age_days'] !== null)
                            <p class="text-2xl font-extrabold text-slate-200">
                                {{ number_format($result['whois_result']['age_days']) }}
                            </p>
                            <p class="text-xs text-slate-500 mt-0.5">hari ({{ $result['whois_result']['registered'] ?? '' }})</p>
                        @else
                            <p class="text-sm text-slate-500">N/A</p>
                        @endif
                    </div>
                    @endif
                </div>

                <!-- Recommendation -->
                <div class="p-5 rounded-xl text-center recommendation-card">
                    <div class="w-10 h-10 rounded-xl mx-auto mb-3 flex items-center justify-center" style="background: rgba(99,102,241,0.1);">
                        <span class="text-lg">💡</span>
                    </div>
                    <p class="text-sm font-medium" style="color: #A5B4FC;">{{ $result['recommendation'] }}</p>
                </div>

                <!-- Actions -->
                <div class="flex gap-3 pt-2">
                    <a href="{{ route('scan.index') }}"
                        class="flex-1 flex items-center justify-center gap-2 py-3.5 px-6 rounded-xl font-bold text-white text-sm transition-all duration-300 hover:-translate-y-0.5 action-btn-primary group">
                        <svg class="w-4 h-4 transition-transform group-hover:rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Scan Lagi
                    </a>
                    <a href="{{ route('scan.history') }}"
                        class="flex-1 flex items-center justify-center gap-2 py-3.5 px-6 rounded-xl font-bold text-slate-300 text-sm transition-all duration-300 hover:bg-white/10 action-btn-secondary group">
                        <svg class="w-4 h-4 transition-transform group-hover:rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Riwayat
                    </a>
                </div>
            </div>
        </div>
    </div>

    <style>
        .risk-ring {
            animation: ringDraw 1.2s ease-out forwards;
        }
        @keyframes ringDraw {
            from { stroke-dashoffset: {{ $circumference }}; }
        }

        .url-info-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.06);
        }
        .detail-section {
            border: 1px solid;
            transition: all 0.3s ease;
        }
        .detail-section:hover {
            transform: translateY(-1px);
        }
        .api-result-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.06);
            transition: all 0.3s ease;
        }
        .api-result-card:hover {
            background: rgba(255,255,255,0.06);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }
        .recommendation-card {
            background: rgba(99,102,241,0.06);
            border: 1px solid rgba(99,102,241,0.15);
        }
        .action-btn-primary {
            background: linear-gradient(135deg, #4F46E5, #6366F1);
            box-shadow: 0 4px 15px rgba(79,70,229,0.3);
            position: relative;
            overflow: hidden;
        }
        .action-btn-primary::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: left 0.5s ease;
        }
        .action-btn-primary:hover::before { left: 100%; }
        .action-btn-primary:hover {
            box-shadow: 0 8px 25px rgba(79,70,229,0.4);
        }
        .action-btn-secondary {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
        }
        .action-btn-secondary:hover {
            border-color: rgba(255,255,255,0.2);
        }
    </style>

    <script>
        function toggleDetail(id) {
            const el = document.getElementById(id);
            const icon = document.getElementById(id + '-icon');
            if (el) {
                el.classList.toggle('hidden');
                if (icon) icon.style.transform = el.classList.contains('hidden') ? '' : 'rotate(180deg)';
            }
        }
    </script>
</x-app-layout>
