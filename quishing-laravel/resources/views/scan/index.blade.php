<x-app-layout>
    <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto">
        <!-- Hero Section -->
        <div class="text-center mb-10">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full mb-4 hero-badge">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="text-xs font-semibold text-emerald-300 tracking-wide">REAL-TIME PROTECTION</span>
            </div>
            <h1 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
                Analisis Keamanan <span class="hero-gradient-text">URL & QR Code</span>
            </h1>
            <p class="mt-3 text-base text-slate-400 max-w-2xl mx-auto">
                Deteksi QR Phishing menggunakan VirusTotal, Google Safe Browsing, dan WHOIS Lookup
            </p>
        </div>

        <!-- Tab Navigation -->
        <div class="flex justify-center mb-8">
            <div class="inline-flex p-1.5 rounded-2xl tab-container">
                <button onclick="showTab('url')" id="tab-url" class="scan-tab active">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    URL
                </button>
                <button onclick="showTab('camera')" id="tab-camera" class="scan-tab">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Kamera
                </button>
                <button onclick="showTab('upload')" id="tab-upload" class="scan-tab">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Upload
                </button>
            </div>
        </div>

        <!-- URL Tab -->
        <div id="panel-url" class="tab-panel">
            <div class="glass-card p-6 sm:p-8">
                <div class="text-center mb-6">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl mb-4 icon-box-indigo">
                        <svg class="w-8 h-8" style="color: #818CF8;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    </div>
                    <h2 class="text-xl font-bold text-white">Scan URL</h2>
                    <p class="text-sm text-slate-400 mt-1">Masukkan URL atau domain untuk diperiksa keamanannya</p>
                </div>

                <!-- Scan Type Selector -->
                <div class="mb-6">
                    <div class="flex items-center justify-center gap-2 mb-3">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                        <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider">Pilih Tipe Scan</p>
                    </div>
                    <div class="grid grid-cols-3 gap-2 sm:gap-3 max-w-md mx-auto">
                        <button type="button" onclick="selectScanType('url')" id="type-url" class="type-card active">
                            <div class="type-card-icon type-icon-url">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                            </div>
                            <span class="type-card-label">URL Biasa</span>
                        </button>
                        <button type="button" onclick="selectScanType('financial')" id="type-financial" class="type-card">
                            <div class="type-card-icon type-icon-financial">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            </div>
                            <span class="type-card-label">Keuangan</span>
                        </button>
                        <button type="button" onclick="selectScanType('shorturl')" id="type-shorturl" class="type-card">
                            <div class="type-card-icon type-icon-shorturl">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                            </div>
                            <span class="type-card-label">URL Pendek</span>
                        </button>
                    </div>
                    <div class="mt-3 text-center">
                        <p id="type-desc" class="type-desc">
                            <svg class="w-3.5 h-3.5 inline-block mr-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Scan standar untuk URL website umum
                        </p>
                    </div>
                </div>

                <form action="{{ route('scan.analyze') }}" method="POST" id="url-form">
                    @csrf
                    <input type="hidden" name="scan_type" id="scan_type" value="url">

                    <!-- Protocol + URL Input -->
                    <div class="input-group mb-4">
                        <div class="protocol-wrapper">
                            <select name="protocol" id="protocol" class="protocol-select">
                                <option value="auto">🔄 Otomatis</option>
                                <option value="https">🔒 HTTPS</option>
                                <option value="http">🌐 HTTP</option>
                            </select>
                            <svg class="protocol-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                        <div class="input-divider"></div>
                        <div class="url-input-wrapper">
                            <svg class="url-input-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                            <input type="text" name="url" id="url" required autocomplete="off"
                                class="url-input"
                                placeholder="contoh: google.com atau https://example.com">
                        </div>
                    </div>
                    @error('url')
                        <p class="mb-3 text-sm text-red-400 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                    <button type="submit" class="scan-button group" id="scan-submit-btn">
                        <div class="scan-btn-icon">
                            <svg class="w-5 h-5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <span>Analisis Keamanan</span>
                        <svg class="w-4 h-4 opacity-50 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </button>
                </form>

                <!-- Quick Info -->
                <div class="mt-5 flex flex-wrap justify-center gap-4 text-xs text-slate-500">
                    <span class="flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        VirusTotal
                    </span>
                    <span class="flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Google Safe Browsing
                    </span>
                    <span class="flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        WHOIS Lookup
                    </span>
                </div>
            </div>
        </div>

        <!-- Camera Tab -->
        <div id="panel-camera" class="tab-panel hidden">
            <div class="glass-card p-6 sm:p-8">
                <div class="text-center mb-6">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl mb-4" style="background: rgba(16,185,129,0.1);">
                        <svg class="w-8 h-8" style="color: #10B981;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <h2 class="text-xl font-bold text-white">Scan via Kamera</h2>
                    <p class="text-sm text-slate-400 mt-1">Arahkan kamera ke QR Code</p>
                </div>

                <div id="qr-reader" class="mx-auto rounded-xl overflow-hidden mb-4" style="max-width: 400px;"></div>
                <div id="qr-reader-results" class="mb-4"></div>

                <button onclick="startCamera()" id="start-camera-btn" class="scan-button" style="background: linear-gradient(135deg, #059669, #10B981); box-shadow: 0 4px 15px rgba(16,185,129,0.3);">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    <span>Mulai Kamera</span>
                </button>
                <button onclick="stopCamera()" id="stop-camera-btn" style="display:none;" class="scan-button mt-3 stop-btn">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/></svg>
                    <span>Stop Kamera</span>
                </button>
                <div id="camera-scan-result" class="mt-4"></div>
            </div>
        </div>

        <!-- Upload Tab -->
        <div id="panel-upload" class="tab-panel hidden">
            <div class="glass-card p-6 sm:p-8 transition-all duration-300">
                <div class="text-center mb-6">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl mb-4" style="background: rgba(245,158,11,0.1);">
                        <svg class="w-8 h-8" style="color: #F59E0B;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <h2 class="text-xl font-bold text-white">Upload QR Code</h2>
                    <p class="text-sm text-slate-400 mt-1">Upload gambar QR Code untuk dianalisis</p>
                </div>

                <div id="upload-area" class="relative group" style="position: relative;">
                    <input type="file" id="qr-file" accept="image/*" class="absolute inset-0 w-full h-full cursor-pointer z-10" style="opacity: 0; position: absolute; top: 0; left: 0; width: 100%; height: 100%; cursor: pointer;" onchange="handleFileUpload(event)">
                    
                    <div id="drop-zone" class="upload-zone transition-all duration-300 group-hover:border-indigo-500/50 group-hover:bg-white/5">
                        <div id="initial-upload-state">
                            <div class="mx-auto mb-4 rounded-full flex items-center justify-center bg-slate-800/50 border border-slate-700 transition-transform group-hover:scale-110 group-hover:border-indigo-500/50" style="width: 4.5rem; height: 4.5rem;">
                                <svg class="text-indigo-400 transition-colors group-hover:text-indigo-300" style="width: 2rem; height: 2rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                            </div>
                            <p class="text-slate-200 font-semibold mb-1">Klik atau drag & drop gambar</p>
                            <p class="text-xs text-slate-500">JPG, PNG, GIF — Maksimal 10MB</p>
                        </div>
                        
                        <div id="upload-processing" class="hidden">
                            <div class="mx-auto relative" style="width: 4rem; height: 4rem;">
                                <div class="absolute inset-0 rounded-full border-4 border-slate-700"></div>
                                <div class="absolute inset-0 rounded-full border-4 border-t-indigo-500 border-r-transparent border-b-transparent border-l-transparent animate-spin"></div>
                            </div>
                            <p class="mt-4 text-indigo-400 font-medium animate-pulse">Memproses gambar...</p>
                        </div>
                    </div>
                </div>

                <div id="upload-preview-container" class="hidden mt-6">
                    <div class="relative max-w-xs mx-auto">
                        <img id="preview-image" src="" alt="Preview" class="rounded-xl shadow-2xl border border-white/10 w-full">
                        <button onclick="resetUpload()" class="absolute -top-3 -right-3 p-2 bg-red-500 text-white rounded-full shadow-lg hover:bg-red-600 hover:scale-110 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    
                    <div id="upload-status" class="mt-6"></div>
                    <div id="upload-scan-result" class="mt-4"></div>
                </div>
            </div>
        </div>

        <!-- Recent Scans -->
        @if(isset($recentScans) && $recentScans->count() > 0)
        <div class="mt-10 glass-card p-6">
            <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5" style="color: #818CF8;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Riwayat Scan Terbaru
            </h3>
            <div class="space-y-2">
                @foreach($recentScans as $scan)
                <div class="flex items-center justify-between p-3 rounded-xl transition-all duration-200 hover:bg-white/5 history-row">
                    <div class="flex items-center space-x-3">
                        @if($scan->risk_level === 'SAFE')
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(16,185,129,0.15);">
                                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                        @elseif($scan->risk_level === 'SUSPICIOUS')
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(245,158,11,0.15);">
                                <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                            </div>
                        @else
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(239,68,68,0.15);">
                                <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                            </div>
                        @endif
                        <div>
                            <p class="text-sm font-medium text-slate-200 truncate" style="max-width: 300px;">
                                {{ $scan->url }}
                            </p>
                            <p class="text-xs text-slate-500">{{ $scan->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    <span class="risk-badge risk-{{ strtolower($scan->risk_level) }}">
                        {{ $scan->risk_level == 'SAFE' ? 'Aman' : ($scan->risk_level == 'SUSPICIOUS' ? 'Curiga' : 'Bahaya') }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <style>
        /* Hero Section */
        .hero-badge {
            background: rgba(16,185,129,0.08);
            border: 1px solid rgba(16,185,129,0.2);
        }
        .hero-gradient-text {
            background: linear-gradient(135deg, #818CF8 0%, #6366F1 50%, #A78BFA 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Tab Navigation */
        .tab-container {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
        }
        .scan-tab {
            display: flex; align-items: center; gap: 8px;
            padding: 10px 20px; border-radius: 12px;
            font-size: 0.875rem; font-weight: 600;
            color: #64748b; cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none; background: transparent;
            position: relative;
        }
        .scan-tab:hover { color: #e2e8f0; background: rgba(255,255,255,0.05); }
        .scan-tab.active {
            color: #fff;
            background: linear-gradient(135deg, #4F46E5, #6366F1);
            box-shadow: 0 4px 15px rgba(79,70,229,0.4);
        }

        /* Icon Box */
        .icon-box-indigo {
            background: rgba(99,102,241,0.1);
            border: 1px solid rgba(99,102,241,0.15);
        }

        /* Type Cards */
        .type-card {
            display: flex; flex-direction: column;
            align-items: center; gap: 8px;
            padding: 14px 8px; border-radius: 14px;
            background: rgba(255,255,255,0.03);
            border: 1.5px solid rgba(255,255,255,0.08);
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .type-card:hover {
            background: rgba(255,255,255,0.06);
            border-color: rgba(255,255,255,0.15);
            transform: translateY(-2px);
        }
        .type-card.active {
            border-color: rgba(99,102,241,0.5);
            background: rgba(99,102,241,0.08);
            box-shadow: 0 4px 20px rgba(99,102,241,0.15);
        }
        .type-card.active .type-card-icon { transform: scale(1.1); }
        .type-card.active .type-card-label { color: #e2e8f0; }

        .type-card.active-financial {
            border-color: rgba(16,185,129,0.5);
            background: rgba(16,185,129,0.08);
            box-shadow: 0 4px 20px rgba(16,185,129,0.15);
        }
        .type-card.active-financial .type-card-icon { color: #10B981; }

        .type-card.active-shorturl {
            border-color: rgba(245,158,11,0.5);
            background: rgba(245,158,11,0.08);
            box-shadow: 0 4px 20px rgba(245,158,11,0.15);
        }
        .type-card.active-shorturl .type-card-icon { color: #F59E0B; }

        .type-card-icon {
            width: 40px; height: 40px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 12px;
            transition: all 0.3s ease;
            color: #818CF8;
            background: rgba(255,255,255,0.04);
        }
        .type-icon-url { color: #818CF8; }
        .type-icon-financial { color: #10B981; }
        .type-icon-shorturl { color: #F59E0B; }
        .type-card-label {
            font-size: 0.75rem; font-weight: 600;
            color: #64748b;
            transition: color 0.3s ease;
        }

        /* Type Description */
        .type-desc {
            display: inline-flex;
            align-items: center;
            font-size: 0.75rem;
            color: #64748b;
            padding: 6px 14px;
            border-radius: 8px;
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.05);
            transition: all 0.3s ease;
        }

        /* Input Group */
        .input-group {
            display: flex;
            border-radius: 14px;
            background: rgba(255,255,255,0.04);
            border: 1.5px solid rgba(255,255,255,0.1);
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .input-group:focus-within {
            border-color: #6366F1;
            box-shadow: 0 0 0 3px rgba(99,102,241,0.12);
            background: rgba(255,255,255,0.06);
        }

        .protocol-wrapper {
            position: relative;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
            max-width: 200px;
        }
        .protocol-select {
            padding: 14px 32px 14px 16px;
            font-size: 0.85rem;
            font-weight: 600;
            color: #cbd5e1;
            outline: none;
            background: transparent;
            border: none;
            cursor: pointer;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            min-width: 130px;
        }
        .protocol-select option {
            background: #1e1b3a;
            color: #e2e8f0;
            padding: 8px;
        }
        .protocol-chevron {
            position: absolute;
            right: 10px;
            width: 14px; height: 14px;
            color: #475569;
            pointer-events: none;
        }

        .input-divider {
            width: 1px;
            background: rgba(255,255,255,0.08);
            margin: 10px 0;
            transition: all 0.3s ease;
        }

        .url-input-wrapper {
            flex: 1;
            position: relative;
            display: flex;
            align-items: center;
        }
        .url-input-icon {
            position: absolute;
            left: 14px;
            width: 18px; height: 18px;
            color: #475569;
        }
        .url-input {
            width: 100%;
            padding: 14px 16px 14px 42px;
            font-size: 0.95rem;
            color: #e2e8f0;
            outline: none;
            background: transparent;
            border: none;
        }
        .url-input::placeholder { color: #3f4a5c; }

        /* Scan Button */
        .scan-button {
            width: 100%; display: flex; align-items: center; justify-content: center; gap: 10px;
            padding: 14px 24px; border-radius: 14px;
            font-size: 1rem; font-weight: 700; color: white;
            background: linear-gradient(135deg, #4F46E5, #6366F1);
            border: none; cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 15px rgba(79,70,229,0.3);
            position: relative;
            overflow: hidden;
        }
        .scan-button::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: left 0.6s ease;
        }
        .scan-button:hover::before { left: 100%; }
        .scan-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(79,70,229,0.4);
        }
        .scan-button:active { transform: translateY(0); }
        .scan-btn-icon {
            width: 28px; height: 28px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 8px;
            background: rgba(255,255,255,0.15);
        }
        .stop-btn {
            background: linear-gradient(135deg, #dc2626, #ef4444);
            box-shadow: 0 4px 15px rgba(239,68,68,0.3);
        }

        /* Upload Zone */
        .upload-zone {
            padding: 48px 24px; border-radius: 16px;
            border: 2px dashed rgba(255,255,255,0.1);
            text-align: center;
            transition: all 0.3s ease;
            background: rgba(255,255,255,0.02);
            min-height: 250px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .upload-zone.drag-active {
            border-color: #6366F1;
            background: rgba(99,102,241,0.1);
            transform: scale(1.01);
        }

        /* Risk Badges */
        .risk-badge {
            padding: 5px 14px; border-radius: 9999px;
            font-size: 0.75rem; font-weight: 700;
            letter-spacing: 0.02em;
        }
        .risk-safe { background: rgba(16,185,129,0.15); color: #34D399; }
        .risk-suspicious { background: rgba(245,158,11,0.15); color: #FBBF24; }
        .risk-dangerous { background: rgba(239,68,68,0.15); color: #F87171; }

        /* History Row */
        .history-row {
            background: rgba(255,255,255,0.02);
            border: 1px solid rgba(255,255,255,0.04);
        }
        .history-row:hover {
            background: rgba(255,255,255,0.05);
            border-color: rgba(255,255,255,0.08);
        }

        /* Result Cards */
        .result-card {
            border-radius: 16px; padding: 24px; margin-top: 16px;
            border: 1px solid rgba(255,255,255,0.08);
            animation: slideUp 0.4s ease-out;
        }
        .result-safe { background: rgba(16,185,129,0.08); border-color: rgba(16,185,129,0.2); }
        .result-suspicious { background: rgba(245,158,11,0.08); border-color: rgba(245,158,11,0.2); }
        .result-dangerous { background: rgba(239,68,68,0.08); border-color: rgba(239,68,68,0.2); }
        @keyframes slideUp {
            0% { opacity: 0; transform: translateY(20px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        /* API Card */
        .api-card {
            padding: 16px; border-radius: 12px; text-align: center;
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.06);
            transition: all 0.3s ease;
        }
        .api-card:hover {
            background: rgba(255,255,255,0.05);
            transform: translateY(-2px);
        }

        /* Financial Badge */
        .financial-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 14px;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: 700;
            background: linear-gradient(135deg, rgba(16,185,129,0.15), rgba(16,185,129,0.08));
            color: #34D399;
            border: 1px solid rgba(16,185,129,0.25);
        }

        /* Responsive */
        @media (max-width: 640px) {
            .input-group { flex-direction: column; }
            .input-divider { width: auto; height: 1px; margin: 0 16px; }
            .protocol-wrapper { border-bottom: 1px solid rgba(255,255,255,0.06); }
            .protocol-select { width: 100%; min-width: unset; }
        }
    </style>

    <!-- html5-qrcode Library -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <!-- jsQR Fallback Library -->
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
    <script>
        let html5QrCode = null;
        let isScanning = false;
        let currentScanType = 'url';

        const scanTypeInfo = {
            'url': { desc: 'Scan standar untuk URL website umum', icon: 'ℹ️' },
            'financial': { desc: 'Untuk QRIS, pembayaran bank, e-wallet — toleransi lebih tinggi', icon: '💳' },
            'shorturl': { desc: 'Untuk URL pendek seperti bit.ly, s.id, tinyurl.com', icon: '🔗' }
        };

        function selectScanType(type) {
            currentScanType = type;
            document.getElementById('scan_type').value = type;

            // Reset all cards
            document.querySelectorAll('.type-card').forEach(btn => {
                btn.classList.remove('active', 'active-financial', 'active-shorturl');
            });
            const activeBtn = document.getElementById('type-' + type);
            activeBtn.classList.add('active');
            if (type === 'financial') activeBtn.classList.add('active-financial');
            if (type === 'shorturl') activeBtn.classList.add('active-shorturl');

            // Hide/show protocol selector based on scan type
            const protocolWrapper = document.querySelector('.protocol-wrapper');
            const inputDivider = document.querySelector('.input-divider');
            if (type === 'financial' || type === 'shorturl') {
                protocolWrapper.style.maxWidth = '0';
                protocolWrapper.style.opacity = '0';
                protocolWrapper.style.overflow = 'hidden';
                protocolWrapper.style.padding = '0';
                inputDivider.style.opacity = '0';
                inputDivider.style.width = '0';
                inputDivider.style.margin = '0';
                // Reset protocol to auto when hidden
                document.getElementById('protocol').value = 'auto';
            } else {
                protocolWrapper.style.maxWidth = '200px';
                protocolWrapper.style.opacity = '1';
                protocolWrapper.style.overflow = 'visible';
                protocolWrapper.style.padding = '';
                inputDivider.style.opacity = '1';
                inputDivider.style.width = '1px';
                inputDivider.style.margin = '10px 0';
            }

            // Animate description
            const desc = document.getElementById('type-desc');
            desc.style.opacity = '0';
            desc.style.transform = 'translateY(5px)';
            setTimeout(() => {
                const info = scanTypeInfo[type];
                desc.innerHTML = `<svg class="w-3.5 h-3.5 inline-block mr-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>${info.desc}`;
                desc.style.opacity = '1';
                desc.style.transform = 'translateY(0)';
            }, 200);

            // Update placeholder
            const urlInput = document.getElementById('url');
            if (type === 'financial') {
                urlInput.placeholder = 'URL QRIS, link pembayaran, atau data transaksi';
            } else if (type === 'shorturl') {
                urlInput.placeholder = 'bit.ly/abc123 atau s.id/xyz';
            } else {
                urlInput.placeholder = 'contoh: google.com atau https://example.com';
            }
        }

        function showTab(tabName) {
            document.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));
            document.querySelectorAll('.scan-tab').forEach(b => b.classList.remove('active'));

            document.getElementById('panel-' + tabName).classList.remove('hidden');
            document.getElementById('tab-' + tabName).classList.add('active');

            if (tabName !== 'camera' && isScanning) stopCamera();
        }

        function startCamera() {
            document.getElementById('qr-reader').innerHTML = '';
            html5QrCode = new Html5Qrcode("qr-reader");
            html5QrCode.start(
                { facingMode: "environment" },
                { fps: 10, qrbox: { width: 250, height: 250 } },
                onScanSuccess, () => {}
            ).then(() => {
                isScanning = true;
                document.getElementById('start-camera-btn').style.display = 'none';
                document.getElementById('stop-camera-btn').style.display = 'flex';
            }).catch(err => {
                document.getElementById('qr-reader-results').innerHTML =
                    `<div class="result-card result-dangerous">
                        <p class="text-red-400 font-medium">❌ Gagal membuka kamera</p>
                        <p class="text-sm text-red-300/70 mt-1">${err}</p>
                    </div>`;
            });
        }

        function stopCamera() {
            if (html5QrCode && isScanning) {
                html5QrCode.stop().then(() => {
                    isScanning = false;
                    document.getElementById('start-camera-btn').style.display = 'flex';
                    document.getElementById('stop-camera-btn').style.display = 'none';
                }).catch(() => {});
            }
        }

        function onScanSuccess(decodedText) {
            stopCamera();
            document.getElementById('qr-reader-results').innerHTML =
                `<div class="result-card" style="background:rgba(99,102,241,0.08);border-color:rgba(99,102,241,0.2);">
                    <p class="font-medium" style="color:#818CF8;">✅ QR Code terdeteksi!</p>
                    <p class="text-sm text-slate-400 mt-1 break-all">${decodedText}</p>
                </div>`;
            analyzeUrl(decodedText, 'camera-scan-result');
        }

        // Drag and Drop
        const dropZone = document.getElementById('drop-zone');
        const fileInput = document.getElementById('qr-file');
        const uploadArea = document.getElementById('upload-area');
        
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, preventDefaults, false);
            document.body.addEventListener(eventName, preventDefaults, false);
        });
        
        function preventDefaults(e) { e.preventDefault(); e.stopPropagation(); }
        
        ['dragenter', 'dragover'].forEach(eventName => {
            uploadArea.addEventListener(eventName, () => {
                dropZone.classList.add('drag-active');
                dropZone.style.borderColor = '#6366F1';
                dropZone.style.backgroundColor = 'rgba(99,102,241,0.1)';
                dropZone.style.transform = 'scale(1.02)';
            }, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, () => {
                dropZone.classList.remove('drag-active');
                dropZone.style.borderColor = '';
                dropZone.style.backgroundColor = '';
                dropZone.style.transform = '';
            }, false);
        });

        uploadArea.addEventListener('drop', (e) => handleFiles(e.dataTransfer.files), false);

        function handleFileUpload(event) { handleFiles(event.target.files); }

        // ========== Image Pre-processing Helpers ==========
        function loadImageToCanvas(file, scaleFactor) {
            return new Promise((resolve, reject) => {
                const img = new Image();
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    const scale = scaleFactor || Math.max(1, Math.min(3, 1000 / Math.max(img.width, img.height)));
                    canvas.width = Math.round(img.width * scale);
                    canvas.height = Math.round(img.height * scale);
                    const ctx = canvas.getContext('2d');
                    ctx.imageSmoothingEnabled = true;
                    ctx.imageSmoothingQuality = 'high';
                    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                    resolve(canvas);
                };
                img.onerror = reject;
                img.src = URL.createObjectURL(file);
            });
        }

        function cloneCanvas(src) {
            const c = document.createElement('canvas');
            c.width = src.width; c.height = src.height;
            c.getContext('2d').drawImage(src, 0, 0);
            return c;
        }

        function applyGrayscale(srcCanvas) {
            const canvas = cloneCanvas(srcCanvas);
            const ctx = canvas.getContext('2d');
            const id = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const d = id.data;
            for (let i = 0; i < d.length; i += 4) {
                const g = d[i]*0.299 + d[i+1]*0.587 + d[i+2]*0.114;
                d[i] = d[i+1] = d[i+2] = g;
            }
            ctx.putImageData(id, 0, 0);
            return canvas;
        }

        function applyContrast(srcCanvas, factor) {
            const canvas = cloneCanvas(srcCanvas);
            const ctx = canvas.getContext('2d');
            const id = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const d = id.data;
            const intercept = 128 * (1 - factor);
            for (let i = 0; i < d.length; i += 4) {
                d[i]   = Math.min(255, Math.max(0, d[i]*factor + intercept));
                d[i+1] = Math.min(255, Math.max(0, d[i+1]*factor + intercept));
                d[i+2] = Math.min(255, Math.max(0, d[i+2]*factor + intercept));
            }
            ctx.putImageData(id, 0, 0);
            return canvas;
        }

        function applyThreshold(srcCanvas, threshold) {
            const canvas = cloneCanvas(srcCanvas);
            const ctx = canvas.getContext('2d');
            const id = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const d = id.data;
            for (let i = 0; i < d.length; i += 4) {
                const v = ((d[i]+d[i+1]+d[i+2])/3) > threshold ? 255 : 0;
                d[i] = d[i+1] = d[i+2] = v;
            }
            ctx.putImageData(id, 0, 0);
            return canvas;
        }

        function applySharpen(srcCanvas) {
            const canvas = cloneCanvas(srcCanvas);
            const ctx = canvas.getContext('2d');
            const id = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const src = new Uint8ClampedArray(id.data);
            const w = canvas.width, h = canvas.height;
            const k = [0,-1,0,-1,5,-1,0,-1,0];
            for (let y = 1; y < h-1; y++) {
                for (let x = 1; x < w-1; x++) {
                    for (let c = 0; c < 3; c++) {
                        let v = 0;
                        for (let ky = -1; ky <= 1; ky++)
                            for (let kx = -1; kx <= 1; kx++)
                                v += src[((y+ky)*w+(x+kx))*4+c] * k[(ky+1)*3+(kx+1)];
                        id.data[(y*w+x)*4+c] = Math.min(255, Math.max(0, v));
                    }
                }
            }
            ctx.putImageData(id, 0, 0);
            return canvas;
        }

        // ========== NEW: Ultra-Aggressive Preprocessing ==========

        function applyGamma(srcCanvas, gamma) {
            const canvas = cloneCanvas(srcCanvas);
            const ctx = canvas.getContext('2d');
            const id = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const d = id.data;
            const inv = 1.0 / gamma;
            const lut = new Uint8Array(256);
            for (let i = 0; i < 256; i++) lut[i] = Math.round(255 * Math.pow(i/255, inv));
            for (let i = 0; i < d.length; i += 4) {
                d[i] = lut[d[i]]; d[i+1] = lut[d[i+1]]; d[i+2] = lut[d[i+2]];
            }
            ctx.putImageData(id, 0, 0);
            return canvas;
        }

        function applyOtsuThreshold(srcCanvas) {
            const gray = applyGrayscale(srcCanvas);
            const ctx = gray.getContext('2d');
            const id = ctx.getImageData(0, 0, gray.width, gray.height);
            const d = id.data;
            const total = gray.width * gray.height;
            const hist = new Array(256).fill(0);
            for (let i = 0; i < d.length; i += 4) hist[d[i]]++;
            let sum = 0;
            for (let i = 0; i < 256; i++) sum += i * hist[i];
            let sumB = 0, wB = 0, maxVar = 0, thresh = 128;
            for (let t = 0; t < 256; t++) {
                wB += hist[t]; if (wB === 0) continue;
                const wF = total - wB; if (wF === 0) break;
                sumB += t * hist[t];
                const mB = sumB / wB, mF = (sum - sumB) / wF;
                const v = wB * wF * (mB - mF) * (mB - mF);
                if (v > maxVar) { maxVar = v; thresh = t; }
            }
            return applyThreshold(gray, thresh);
        }

        function applyAdaptiveThreshold(srcCanvas, blockSize, C) {
            blockSize = blockSize || 15; C = C || 8;
            const gray = applyGrayscale(srcCanvas);
            const canvas = cloneCanvas(gray);
            const ctx = canvas.getContext('2d');
            const id = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const d = id.data;
            const w = canvas.width, h = canvas.height;
            const src = new Uint8ClampedArray(d);
            const half = Math.floor(blockSize / 2);
            // Integral image for fast local mean
            const integral = new Float64Array((w+1) * (h+1));
            for (let y = 0; y < h; y++) {
                let rowSum = 0;
                for (let x = 0; x < w; x++) {
                    rowSum += src[(y*w+x)*4];
                    integral[(y+1)*(w+1)+(x+1)] = integral[y*(w+1)+(x+1)] + rowSum;
                }
            }
            for (let y = 0; y < h; y++) {
                for (let x = 0; x < w; x++) {
                    const x1 = Math.max(0, x-half), y1 = Math.max(0, y-half);
                    const x2 = Math.min(w-1, x+half), y2 = Math.min(h-1, y+half);
                    const count = (x2-x1+1) * (y2-y1+1);
                    const s = integral[(y2+1)*(w+1)+(x2+1)] - integral[y1*(w+1)+(x2+1)]
                            - integral[(y2+1)*(w+1)+x1] + integral[y1*(w+1)+x1];
                    const idx = (y*w+x)*4;
                    const val = src[idx] > (s/count - C) ? 255 : 0;
                    d[idx] = d[idx+1] = d[idx+2] = val;
                }
            }
            ctx.putImageData(id, 0, 0);
            return canvas;
        }

        function applyInvert(srcCanvas) {
            const canvas = cloneCanvas(srcCanvas);
            const ctx = canvas.getContext('2d');
            const id = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const d = id.data;
            for (let i = 0; i < d.length; i += 4) {
                d[i] = 255-d[i]; d[i+1] = 255-d[i+1]; d[i+2] = 255-d[i+2];
            }
            ctx.putImageData(id, 0, 0);
            return canvas;
        }

        function extractChannel(srcCanvas, ch) {
            const canvas = cloneCanvas(srcCanvas);
            const ctx = canvas.getContext('2d');
            const id = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const d = id.data;
            const ci = ch === 'r' ? 0 : ch === 'g' ? 1 : 2;
            for (let i = 0; i < d.length; i += 4) {
                const v = d[i+ci]; d[i] = d[i+1] = d[i+2] = v;
            }
            ctx.putImageData(id, 0, 0);
            return canvas;
        }

        function applyMorphology(srcCanvas, op) {
            const gray = applyGrayscale(srcCanvas);
            const ctx = gray.getContext('2d');
            const w = gray.width, h = gray.height;
            const id = ctx.getImageData(0, 0, w, h);
            const src = new Uint8ClampedArray(id.data);
            const tmp = new Uint8ClampedArray(id.data.length);
            for (let i = 3; i < tmp.length; i += 4) tmp[i] = 255;

            function dilate(inp, out) {
                for (let y = 1; y < h-1; y++) for (let x = 1; x < w-1; x++) {
                    let mx = 0;
                    for (let ky = -1; ky <= 1; ky++) for (let kx = -1; kx <= 1; kx++)
                        mx = Math.max(mx, inp[((y+ky)*w+(x+kx))*4]);
                    const i = (y*w+x)*4; out[i] = out[i+1] = out[i+2] = mx;
                }
            }
            function erode(inp, out) {
                for (let y = 1; y < h-1; y++) for (let x = 1; x < w-1; x++) {
                    let mn = 255;
                    for (let ky = -1; ky <= 1; ky++) for (let kx = -1; kx <= 1; kx++)
                        mn = Math.min(mn, inp[((y+ky)*w+(x+kx))*4]);
                    const i = (y*w+x)*4; out[i] = out[i+1] = out[i+2] = mn;
                }
            }

            if (op === 'close') { dilate(src, tmp); erode(tmp, id.data); }
            else { erode(src, tmp); dilate(tmp, id.data); }
            ctx.putImageData(id, 0, 0);
            return gray;
        }

        function applyRotation(srcCanvas, degrees) {
            const rad = degrees * Math.PI / 180;
            const cos = Math.abs(Math.cos(rad)), sin = Math.abs(Math.sin(rad));
            const nW = Math.ceil(srcCanvas.width*cos + srcCanvas.height*sin);
            const nH = Math.ceil(srcCanvas.width*sin + srcCanvas.height*cos);
            const canvas = document.createElement('canvas');
            canvas.width = nW; canvas.height = nH;
            const ctx = canvas.getContext('2d');
            ctx.fillStyle = '#FFF'; ctx.fillRect(0, 0, nW, nH);
            ctx.translate(nW/2, nH/2); ctx.rotate(rad);
            ctx.drawImage(srcCanvas, -srcCanvas.width/2, -srcCanvas.height/2);
            return canvas;
        }

        function rescaleCanvas(srcCanvas, factor) {
            const canvas = document.createElement('canvas');
            canvas.width = Math.round(srcCanvas.width * factor);
            canvas.height = Math.round(srcCanvas.height * factor);
            const ctx = canvas.getContext('2d');
            ctx.imageSmoothingEnabled = true;
            ctx.imageSmoothingQuality = 'high';
            ctx.drawImage(srcCanvas, 0, 0, canvas.width, canvas.height);
            return canvas;
        }

        function applyGaussianBlur(srcCanvas, radius) {
            const canvas = cloneCanvas(srcCanvas);
            const ctx = canvas.getContext('2d');
            ctx.filter = `blur(${radius || 1}px)`;
            ctx.drawImage(srcCanvas, 0, 0);
            ctx.filter = 'none';
            return canvas;
        }

        function applyUnsharpMask(srcCanvas, amount) {
            amount = amount || 1.5;
            const blurred = applyGaussianBlur(srcCanvas, 2);
            const canvas = cloneCanvas(srcCanvas);
            const ctx = canvas.getContext('2d');
            const sD = srcCanvas.getContext('2d').getImageData(0, 0, canvas.width, canvas.height);
            const bD = blurred.getContext('2d').getImageData(0, 0, canvas.width, canvas.height);
            const id = ctx.getImageData(0, 0, canvas.width, canvas.height);
            for (let i = 0; i < id.data.length; i += 4) {
                id.data[i]   = Math.min(255, Math.max(0, sD.data[i]   + amount*(sD.data[i]-bD.data[i])));
                id.data[i+1] = Math.min(255, Math.max(0, sD.data[i+1] + amount*(sD.data[i+1]-bD.data[i+1])));
                id.data[i+2] = Math.min(255, Math.max(0, sD.data[i+2] + amount*(sD.data[i+2]-bD.data[i+2])));
            }
            ctx.putImageData(id, 0, 0);
            return canvas;
        }

        function applyCLAHE(srcCanvas, gridSize, clipLimit) {
            gridSize = gridSize || 8; clipLimit = clipLimit || 2.0;
            const gray = applyGrayscale(srcCanvas);
            const canvas = cloneCanvas(gray);
            const ctx = canvas.getContext('2d');
            const id = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const d = id.data;
            const w = canvas.width, h = canvas.height;
            const tW = Math.ceil(w/gridSize), tH = Math.ceil(h/gridSize);
            for (let ty = 0; ty < gridSize; ty++) {
                for (let tx = 0; tx < gridSize; tx++) {
                    const x1 = tx*tW, y1 = ty*tH;
                    const x2 = Math.min(x1+tW, w), y2 = Math.min(y1+tH, h);
                    const pix = (x2-x1)*(y2-y1);
                    const hist = new Array(256).fill(0);
                    for (let y = y1; y < y2; y++) for (let x = x1; x < x2; x++) hist[d[(y*w+x)*4]]++;
                    const lim = Math.max(1, Math.round(clipLimit * pix / 256));
                    let excess = 0;
                    for (let i = 0; i < 256; i++) { if (hist[i] > lim) { excess += hist[i]-lim; hist[i] = lim; } }
                    const bonus = Math.floor(excess / 256);
                    for (let i = 0; i < 256; i++) hist[i] += bonus;
                    const cdf = new Array(256); cdf[0] = hist[0];
                    for (let i = 1; i < 256; i++) cdf[i] = cdf[i-1]+hist[i];
                    const cdfMin = cdf.find(v => v > 0) || 0;
                    for (let y = y1; y < y2; y++) for (let x = x1; x < x2; x++) {
                        const idx = (y*w+x)*4;
                        const v = Math.round(((cdf[d[idx]]-cdfMin)/(cdf[255]-cdfMin))*255);
                        d[idx] = d[idx+1] = d[idx+2] = Math.min(255, Math.max(0, v));
                    }
                }
            }
            ctx.putImageData(id, 0, 0);
            return canvas;
        }

        function autoCropToContent(srcCanvas) {
            const gray = applyGrayscale(srcCanvas);
            const ctx = gray.getContext('2d');
            const id = ctx.getImageData(0, 0, gray.width, gray.height);
            const d = id.data;
            const w = gray.width, h = gray.height;
            let minX = w, minY = h, maxX = 0, maxY = 0;
            for (let y = 0; y < h; y++) for (let x = 0; x < w; x++) {
                if (d[(y*w+x)*4] < 100) {
                    minX = Math.min(minX, x); minY = Math.min(minY, y);
                    maxX = Math.max(maxX, x); maxY = Math.max(maxY, y);
                }
            }
            if (maxX <= minX || maxY <= minY) return null;
            const padX = Math.round((maxX-minX)*0.1), padY = Math.round((maxY-minY)*0.1);
            minX = Math.max(0, minX-padX); minY = Math.max(0, minY-padY);
            maxX = Math.min(w-1, maxX+padX); maxY = Math.min(h-1, maxY+padY);
            const cW = maxX-minX+1, cH = maxY-minY+1;
            if (cW > w*0.9 && cH > h*0.9) return null;
            const cc = document.createElement('canvas');
            cc.width = cW; cc.height = cH;
            cc.getContext('2d').drawImage(srcCanvas, minX, minY, cW, cH, 0, 0, cW, cH);
            return cc;
        }

        function tryJsQR(canvas) {
            try {
                const ctx = canvas.getContext('2d');
                const id = ctx.getImageData(0, 0, canvas.width, canvas.height);
                const code = jsQR(id.data, canvas.width, canvas.height, { inversionAttempts: 'attemptBoth' });
                return code ? code.data : null;
            } catch(e) { return null; }
        }

        function canvasToFile(canvas, fileName) {
            return new Promise((resolve) => {
                canvas.toBlob((blob) => {
                    resolve(new File([blob], fileName || 'scan.png', { type: 'image/png' }));
                }, 'image/png');
            });
        }

        async function tryHtml5Decode(canvas) {
            try {
                const f = await canvasToFile(canvas);
                const s = new Html5Qrcode("qr-scan-temp");
                return await s.scanFile(f, true);
            } catch(e) { return null; }
        }

        function updateScanProgress(step, total, desc, stage) {
            const stageLabels = ['⚡ Quick', '🔧 Light', '💪 Heavy', '🔥 Extreme', '🖥️ Server'];
            const stageColors = ['#818CF8', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6'];
            const si = stage || 0;
            document.getElementById('upload-status').innerHTML = `
                <div class="text-center py-6">
                    <div class="inline-block w-12 h-12 rounded-full border-4 border-indigo-500/30 border-t-indigo-500 animate-spin mb-4"></div>
                    <p class="text-slate-300 font-medium">🔍 Membaca QR Code...</p>
                    <p class="text-sm mt-2 font-bold" style="color:${stageColors[si]}">${stageLabels[si]} — Strategi ${step}/${total}</p>
                    <p class="text-slate-500 text-xs mt-1">${desc}</p>
                    <div class="mt-3 mx-auto max-w-xs bg-slate-800 rounded-full h-2 overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500" style="width:${Math.round((step/total)*100)}%;background:linear-gradient(90deg,${stageColors[si]},${stageColors[Math.min(si+1,4)]})"></div>
                    </div>
                </div>
            `;
        }

        // ========== Ultra-Aggressive Multi-Strategy Pipeline ==========
        async function handleFiles(files) {
            const file = files[0];
            if (!file) return;

            document.getElementById('upload-area').classList.add('hidden');
            document.getElementById('upload-preview-container').classList.remove('hidden');
            document.getElementById('upload-status').innerHTML = `
                <div class="text-center py-8">
                    <div class="inline-block w-12 h-12 rounded-full border-4 border-indigo-500/30 border-t-indigo-500 animate-spin mb-4"></div>
                    <p class="text-slate-300 font-medium">Memuat gambar...</p>
                </div>`;

            const reader = new FileReader();
            reader.onload = (e) => { document.getElementById('preview-image').src = e.target.result; };
            reader.readAsDataURL(file);

            let tempDiv = document.getElementById('qr-scan-temp');
            if (!tempDiv) {
                tempDiv = document.createElement('div');
                tempDiv.id = 'qr-scan-temp';
                tempDiv.style.display = 'none';
                document.body.appendChild(tempDiv);
            }

            const TOTAL = 35;
            let step = 0;
            let decoded = null;

            // Quick attempt helper (jsQR only, fast)
            async function qr(desc, canvas, stage) {
                if (decoded) return;
                step++;
                updateScanProgress(step, TOTAL, desc, stage);
                await new Promise(r => setTimeout(r, 30));
                decoded = tryJsQR(canvas);
            }

            // Full attempt helper (jsQR + html5-qrcode)
            async function qrFull(desc, canvas, stage) {
                if (decoded) return;
                step++;
                updateScanProgress(step, TOTAL, desc, stage);
                await new Promise(r => setTimeout(r, 30));
                decoded = tryJsQR(canvas);
                if (!decoded) decoded = await tryHtml5Decode(canvas);
            }

            try {
                const canvas = await loadImageToCanvas(file);

                // ===== STAGE 1: QUICK PASS =====
                await qrFull('Scan langsung (original)...', canvas, 0);

                // ===== STAGE 2: LIGHT ENHANCEMENT =====
                if (!decoded) {
                    const gray = applyGrayscale(canvas);
                    await qr('Grayscale...', gray, 1);
                }
                if (!decoded) await qr('Kontras 1.5x...', applyContrast(applyGrayscale(canvas), 1.5), 1);
                if (!decoded) await qrFull('Kontras 2.0x...', applyContrast(applyGrayscale(canvas), 2.0), 1);
                if (!decoded) await qr('Sharpen + kontras...', applyContrast(applySharpen(applyGrayscale(canvas)), 1.5), 1);
                if (!decoded) await qr('Unsharp mask...', applyUnsharpMask(applyGrayscale(canvas), 2.0), 1);

                // ===== STAGE 3: HEAVY ENHANCEMENT =====
                if (!decoded) await qrFull('Otsu auto-threshold...', applyOtsuThreshold(canvas), 2);
                if (!decoded) await qr('Adaptive threshold (blok 11)...', applyAdaptiveThreshold(canvas, 11, 5), 2);
                if (!decoded) await qr('Adaptive threshold (blok 21)...', applyAdaptiveThreshold(canvas, 21, 10), 2);
                if (!decoded) await qr('Adaptive threshold (blok 31)...', applyAdaptiveThreshold(canvas, 31, 15), 2);
                if (!decoded) await qrFull('CLAHE equalization...', applyCLAHE(canvas, 8, 2.0), 2);
                if (!decoded) await qr('Gamma 0.4 (cerahkan)...', applyOtsuThreshold(applyGamma(canvas, 0.4)), 2);
                if (!decoded) await qr('Gamma 0.7 (cerahkan)...', applyOtsuThreshold(applyGamma(canvas, 0.7)), 2);
                if (!decoded) await qr('Gamma 2.0 (gelapkan)...', applyOtsuThreshold(applyGamma(canvas, 2.0)), 2);
                if (!decoded) await qr('Gamma 3.0 (gelapkan)...', applyOtsuThreshold(applyGamma(canvas, 3.0)), 2);

                // Multi-threshold sweep
                for (const t of [70, 90, 110, 130, 150, 170, 190]) {
                    if (!decoded) await qr(`Threshold ${t}...`, applyThreshold(applyGrayscale(canvas), t), 2);
                }

                // Blur + Otsu (noise reduction)
                if (!decoded) await qr('Blur + Otsu...', applyOtsuThreshold(applyGaussianBlur(applyGrayscale(canvas), 1)), 2);
                if (!decoded) await qr('Blur + Adaptive...', applyAdaptiveThreshold(applyGaussianBlur(canvas, 1), 15, 8), 2);

                // ===== STAGE 4: EXTREME =====
                // Color inversion
                if (!decoded) await qrFull('Invert warna...', applyInvert(canvas), 3);
                if (!decoded) await qr('Invert + Otsu...', applyOtsuThreshold(applyInvert(canvas)), 3);

                // Per-channel scanning
                for (const ch of ['r', 'g', 'b']) {
                    if (!decoded) await qr(`Channel ${ch.toUpperCase()} + Otsu...`, applyOtsuThreshold(extractChannel(canvas, ch)), 3);
                }

                // Morphological operations
                if (!decoded) await qr('Morph close + Otsu...', applyOtsuThreshold(applyMorphology(canvas, 'close')), 3);
                if (!decoded) await qr('Morph open + Otsu...', applyOtsuThreshold(applyMorphology(canvas, 'open')), 3);

                // Rotation attempts
                for (const deg of [-10, -5, 5, 10]) {
                    if (!decoded) await qr(`Rotasi ${deg}°...`, applyRotation(canvas, deg), 3);
                }

                // Multi-scale upscaling
                if (!decoded) {
                    const up2 = rescaleCanvas(canvas, 2);
                    await qrFull('Upscale 2x + Otsu...', applyOtsuThreshold(up2), 3);
                }
                if (!decoded) {
                    const up3 = rescaleCanvas(canvas, 3);
                    await qr('Upscale 3x + Adaptive...', applyAdaptiveThreshold(up3, 21, 10), 3);
                }

                // Auto-crop and re-scan
                if (!decoded) {
                    const cropped = autoCropToContent(canvas);
                    if (cropped) {
                        await qrFull('Auto-crop + scan...', cropped, 3);
                        if (!decoded) await qr('Auto-crop + Otsu...', applyOtsuThreshold(cropped), 3);
                        if (!decoded) await qr('Auto-crop + Adaptive...', applyAdaptiveThreshold(cropped, 15, 8), 3);
                        if (!decoded) {
                            const upCrop = rescaleCanvas(cropped, 2);
                            await qr('Auto-crop + Upscale 2x...', applyOtsuThreshold(upCrop), 3);
                        }
                    }
                }

                // Combined extreme pipelines
                if (!decoded) {
                    const extreme1 = applyOtsuThreshold(applyContrast(applySharpen(applyGaussianBlur(applyGrayscale(canvas), 1)), 3.0));
                    await qrFull('Pipeline: Blur→Sharpen→Contrast→Otsu...', extreme1, 3);
                }
                if (!decoded) {
                    const extreme2 = applyAdaptiveThreshold(applyUnsharpMask(applyGrayscale(canvas), 3.0), 15, 5);
                    await qr('Pipeline: Unsharp→Adaptive...', extreme2, 3);
                }
                if (!decoded) {
                    const extreme3 = applyOtsuThreshold(applyCLAHE(applyGamma(canvas, 0.5), 4, 3.0));
                    await qr('Pipeline: Gamma→CLAHE→Otsu...', extreme3, 3);
                }

                // ===== STAGE 5: SERVER-SIDE PHP FALLBACK =====
                if (!decoded) {
                    step = TOTAL;
                    updateScanProgress(step, TOTAL, 'Server-side PHP decoding (fallback terakhir)...', 4);
                    try {
                        const formData = new FormData();
                        formData.append('image', file);
                        const response = await fetch('{{ route("api.scan-qr-image") }}', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: formData,
                        });
                        const result = await response.json();
                        if (result.decoded_text) decoded = result.decoded_text;
                    } catch(e) { console.log('Server fallback error:', e); }
                }

                // ===== SHOW RESULT =====
                if (decoded) {
                    document.getElementById('upload-status').innerHTML =
                        `<div class="result-card" style="background:rgba(99,102,241,0.08);border-color:rgba(99,102,241,0.2);">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:rgba(16,185,129,0.15);">
                                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <div>
                                    <p class="font-bold text-emerald-400">✅ QR Code terdeteksi!</p>
                                    <p class="text-xs text-slate-500">Berhasil di strategi ke-${step} dari ${TOTAL}</p>
                                </div>
                            </div>
                            <p class="text-sm text-slate-400 mt-1 break-all bg-white/5 p-3 rounded-lg">${decoded}</p>
                        </div>`;
                    analyzeUrl(decoded, 'upload-scan-result');
                } else {
                    document.getElementById('upload-status').innerHTML =
                        `<div class="result-card result-dangerous text-center">
                            <p class="text-red-400 font-medium text-lg">❌ QR Code tidak ditemukan</p>
                            <p class="text-sm text-red-300/70 mt-2">Semua ${TOTAL} strategi decoding (termasuk server-side) telah dicoba.</p>
                            <div class="mt-3 p-3 rounded-lg" style="background:rgba(255,255,255,0.03);">
                                <p class="text-xs text-slate-500 font-semibold mb-1">💡 Tips untuk hasil lebih baik:</p>
                                <ul class="text-xs text-slate-500 space-y-1 text-left max-w-sm mx-auto">
                                    <li>• Crop gambar agar hanya QR Code yang terlihat</li>
                                    <li>• Pastikan 3 kotak finder (pojok) tidak terpotong</li>
                                    <li>• Pastikan foto tidak terlalu blur/buram</li>
                                    <li>• Coba dengan pencahayaan yang lebih baik</li>
                                </ul>
                            </div>
                            <button onclick="resetUpload()" class="mt-4 px-5 py-2.5 bg-white/10 hover:bg-white/20 rounded-xl text-sm text-white font-semibold transition-all hover:-translate-y-0.5">Coba Gambar Lain</button>
                        </div>`;
                }
            } catch (err) {
                document.getElementById('upload-status').innerHTML =
                    `<div class="result-card result-dangerous text-center">
                        <p class="text-red-400 font-medium text-lg">❌ Terjadi kesalahan</p>
                        <p class="text-sm text-red-300/70 mt-2">${err.message || 'Gagal memproses gambar'}</p>
                        <button onclick="resetUpload()" class="mt-4 px-5 py-2.5 bg-white/10 hover:bg-white/20 rounded-xl text-sm text-white font-semibold transition-all hover:-translate-y-0.5">Coba Gambar Lain</button>
                    </div>`;
            }
        }

        function resetUpload() {
            document.getElementById('qr-file').value = '';
            document.getElementById('upload-preview-container').classList.add('hidden');
            document.getElementById('upload-area').classList.remove('hidden');
            document.getElementById('upload-scan-result').innerHTML = '';
            document.getElementById('upload-status').innerHTML = '';
        }

        function toggleDetailJs(id) {
            const el = document.getElementById(id);
            const icon = document.getElementById(id + '-icon');
            if (el) {
                el.classList.toggle('hidden');
                if (icon) icon.style.transform = el.classList.contains('hidden') ? '' : 'rotate(180deg)';
            }
        }

        function renderItem(item, idx, prefix, color, textColor, badgeBg, badgeColor) {
            const title = typeof item === 'object' ? item.title : item;
            const detail = typeof item === 'object' ? item.detail : null;
            const severity = typeof item === 'object' ? item.severity : null;
            const category = typeof item === 'object' ? item.category : null;
            const id = `${prefix}-${idx}`;
            let html = `<div class="rounded-lg overflow-hidden" style="background:${color.replace('0.08','0.05')};border:1px solid ${color.replace('0.08','0.1')};">
                <button onclick="toggleDetailJs('${id}')" class="w-full p-3 flex items-start gap-3 text-left cursor-pointer hover:bg-white/5 transition-all">
                    <span class="w-1.5 h-1.5 rounded-full mt-1.5 flex-shrink-0" style="background:${textColor};"></span>
                    <div class="flex-1">
                        <p class="text-sm font-semibold" style="color:${textColor};">${title}</p>`;
            if (severity) {
                html += `<span class="inline-block mt-1 px-2 py-0.5 rounded text-xs font-bold" style="background:${badgeBg};color:${badgeColor};">${severity} • ${category||'UNKNOWN'}</span>`;
            }
            html += `</div>
                    <svg class="w-4 h-4 flex-shrink-0 mt-0.5 transition-transform" style="color:${textColor};opacity:0.5;" id="${id}-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>`;
            if (detail) {
                html += `<div id="${id}" class="hidden px-3 pb-3 pl-7"><p class="text-xs leading-relaxed" style="color:${textColor};opacity:0.6;">${detail}</p></div>`;
            }
            html += `</div>`;
            return html;
        }

        async function analyzeUrl(url, containerId) {
            const c = document.getElementById(containerId);
            c.innerHTML = `<div class="text-center py-8">
                <div class="inline-block w-10 h-10 rounded-full border-4 border-indigo-500/30 border-t-indigo-500 animate-spin mb-3"></div>
                <p class="text-slate-400 text-sm">Menganalisis dengan VirusTotal, Google Safe Browsing & WHOIS...</p>
            </div>`;

            try {
                const res = await fetch('{{ route("api.scan") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ url, scan_type: 'url' })
                });
                const r = await res.json();

                let cls, icon, label, iconColor, ringColor;
                if (r.risk_level === 'SAFE') {
                    cls='result-safe'; icon='✅'; label='AMAN'; iconColor='text-emerald-400'; ringColor='#10B981';
                } else if (r.risk_level === 'SUSPICIOUS') {
                    cls='result-suspicious'; icon='⚠️'; label='MENCURIGAKAN'; iconColor='text-amber-400'; ringColor='#F59E0B';
                } else {
                    cls='result-dangerous'; icon='🚫'; label='BERBAHAYA'; iconColor='text-red-400'; ringColor='#EF4444';
                }

                const pct = r.risk_score;
                const circum = 2 * Math.PI * 38;
                const dashOff = circum - (pct / 100) * circum;

                let h = `<div class="result-card ${cls}">
                    <div class="text-center mb-5">
                        <div class="inline-block relative mb-3" style="width:100px;height:100px;">
                            <svg viewBox="0 0 80 80" style="width:100%;height:100%;transform:rotate(-90deg);">
                                <circle cx="40" cy="40" r="38" fill="none" stroke="rgba(255,255,255,0.06)" stroke-width="6"/>
                                <circle cx="40" cy="40" r="38" fill="none" stroke="${ringColor}" stroke-width="6" stroke-linecap="round" stroke-dasharray="${circum}" stroke-dashoffset="${dashOff}" style="transition:stroke-dashoffset 1s ease-out;"/>
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="text-2xl font-extrabold ${iconColor}">${pct}</span>
                            </div>
                        </div>
                        <h3 class="text-2xl font-extrabold ${iconColor}">${label}</h3>
                        ${r.is_financial ? `<div class="mt-2"><span class="financial-badge">${r.is_qris ? '📱 QRIS Payment' : '💳 Transaksi Keuangan'}</span></div>` : ''}
                        ${(r.qris_info && r.qris_info.merchant_name) ? `<div class="mt-2"><span class="text-lg font-bold text-emerald-300">Atas Nama: ${r.qris_info.merchant_name}</span>${r.qris_info.merchant_city ? `<span class="text-sm text-slate-400 ml-2">📍 ${r.qris_info.merchant_city}</span>` : ''}</div>` : ''}
                        <p class="text-xs text-slate-500 mt-2 break-all max-w-lg mx-auto">${r.url}</p>
                    </div>`;

                // QRIS Merchant Info
                if (r.qris_info && r.qris_info.merchant_name) {
                    h += `<div class="mb-3 p-4 rounded-xl" style="background:linear-gradient(135deg,rgba(16,185,129,0.08),rgba(99,102,241,0.05));border:1px solid rgba(16,185,129,0.15);">
                        <p class="font-bold text-emerald-400 mb-3 text-sm flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:rgba(16,185,129,0.15);">🏪</span>
                            Informasi Merchant
                        </p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="p-3 rounded-lg" style="background:rgba(255,255,255,0.03);"><p class="text-xs text-slate-500 mb-1">Nama Merchant</p><p class="text-sm font-bold text-slate-200">${r.qris_info.merchant_name}</p></div>
                            ${r.qris_info.merchant_city ? `<div class="p-3 rounded-lg" style="background:rgba(255,255,255,0.03);"><p class="text-xs text-slate-500 mb-1">Kota</p><p class="text-sm font-bold text-slate-200">📍 ${r.qris_info.merchant_city}</p></div>` : ''}
                            ${r.qris_info.country_code ? `<div class="p-3 rounded-lg" style="background:rgba(255,255,255,0.03);"><p class="text-xs text-slate-500 mb-1">Kode Negara</p><p class="text-sm font-bold text-slate-200">🌍 ${r.qris_info.country_code}</p></div>` : ''}
                            ${r.qris_info.transaction_amount ? `<div class="p-3 rounded-lg" style="background:rgba(255,255,255,0.03);"><p class="text-xs text-slate-500 mb-1">Nominal</p><p class="text-sm font-bold text-slate-200">💰 Rp ${Number(r.qris_info.transaction_amount).toLocaleString('id-ID')}</p></div>` : ''}
                        </div>
                    </div>`;
                }

                // Threats
                if (r.threats?.length) {
                    h += `<div class="mb-3 p-4 rounded-xl" style="background:rgba(239,68,68,0.08);">
                        <p class="font-bold text-red-400 mb-3 text-sm flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:rgba(239,68,68,0.15);">🚨</span>
                            Ancaman Terdeteksi (${r.threats.length})
                        </p>
                        <div class="space-y-3">${r.threats.map((t,i)=>renderItem(t,i,'jt','rgba(239,68,68,0.08)','#FCA5A5',t.severity==='CRITICAL'?'rgba(239,68,68,0.2)':'rgba(245,158,11,0.2)',t.severity==='CRITICAL'?'#FCA5A5':'#FCD34D')).join('')}</div>
                    </div>`;
                }

                // Warnings
                if (r.warnings?.length) {
                    h += `<div class="mb-3 p-4 rounded-xl" style="background:rgba(245,158,11,0.08);">
                        <p class="font-bold text-amber-400 mb-3 text-sm flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:rgba(245,158,11,0.15);">⚠️</span>
                            Peringatan (${r.warnings.length})
                        </p>
                        <div class="space-y-3">${r.warnings.map((w,i)=>renderItem(w,i,'jw','rgba(245,158,11,0.08)','#FCD34D','rgba(245,158,11,0.2)','#FCD34D')).join('')}</div>
                    </div>`;
                }

                // Safe indicators
                if (r.safe_indicators?.length) {
                    h += `<div class="mb-3 p-4 rounded-xl" style="background:rgba(16,185,129,0.08);">
                        <p class="font-bold text-emerald-400 mb-3 text-sm flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:rgba(16,185,129,0.15);">✅</span>
                            Indikator Aman (${r.safe_indicators.length})
                        </p>
                        <div class="space-y-3">${r.safe_indicators.map((s,i)=>renderItem(s,i,'js','rgba(16,185,129,0.08)','#6EE7B7','rgba(16,185,129,0.2)','#6EE7B7')).join('')}</div>
                    </div>`;
                }

                // API cards
                h += `<div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-4">`;
                if (r.virustotal_result) {
                    const vt = r.virustotal_result;
                    const total = (vt.malicious||0)+(vt.harmless||0)+(vt.undetected||0);
                    h += `<div class="api-card"><p class="text-xs text-slate-500 mb-1">🦠 VirusTotal</p>
                        <p class="text-xl font-bold ${vt.malicious>0?'text-red-400':'text-emerald-400'}">${vt.malicious}/${total}</p></div>`;
                }
                if (r.gsb_result) {
                    h += `<div class="api-card"><p class="text-xs text-slate-500 mb-1">🛡️ Safe Browsing</p>
                        <p class="text-xl font-bold ${r.gsb_result.is_safe?'text-emerald-400':'text-red-400'}">${r.gsb_result.is_safe?'Aman':'Bahaya'}</p></div>`;
                }
                if (r.whois_result?.age_days != null) {
                    h += `<div class="api-card"><p class="text-xs text-slate-500 mb-1">📋 WHOIS</p>
                        <p class="text-xl font-bold text-slate-200">${r.whois_result.age_days} hari</p>
                        <p class="text-xs text-slate-500">${r.whois_result.registered||''}</p></div>`;
                }
                h += `</div>`;

                h += `<div class="mt-4 p-4 rounded-xl text-center" style="background:rgba(99,102,241,0.06);">
                    <p class="text-sm font-medium" style="color:#A5B4FC;">💡 ${r.recommendation}</p>
                </div></div>`;

                c.innerHTML = h;
            } catch (e) {
                c.innerHTML = `<div class="result-card result-dangerous">
                    <p class="text-red-400 font-medium">❌ Gagal menganalisis</p>
                    <p class="text-sm text-red-300/70 mt-1">Error: ${e.message}</p>
                </div>`;
            }
        }
    </script>
</x-app-layout>
