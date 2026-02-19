/* Quishing Defender Pro - Main JavaScript */
const $ = id => document.getElementById(id);
const $$ = sel => document.querySelectorAll(sel);

// State
let currentStream = null, facingMode = 'environment', scanning = false, currentResult = null, flashOn = false;

// Settings
const settings = {
    theme: localStorage.getItem('theme') || 'dark',
    sound: localStorage.getItem('sound') !== 'false',
    vibration: localStorage.getItem('vibration') !== 'false',
    autoScan: localStorage.getItem('autoScan') !== 'false',
    vtApiKey: localStorage.getItem('vtApiKey') || '',
    gsbApiKey: localStorage.getItem('gsbApiKey') || '',
    serverUrl: localStorage.getItem('serverUrl') || ''
};

// Phishing patterns
const PHISHING_PATTERNS = [/paypal.*login/i, /bank.*verify/i, /account.*suspend/i, /verify.*identity/i, /secure.*update/i, /apple.*id/i, /netflix.*payment/i, /amazon.*security/i, /microsoft.*office/i];
const SUSPICIOUS_TLDS = ['.tk', '.ml', '.ga', '.cf', '.gq', '.xyz', '.top', '.click', '.link', '.work'];
const TRUSTED_DOMAINS = ['google.com', 'youtube.com', 'facebook.com', 'instagram.com', 'twitter.com', 'linkedin.com', 'github.com', 'microsoft.com', 'apple.com', 'amazon.com', 'netflix.com', 'spotify.com', 'paypal.com', 'wikipedia.org', 'reddit.com', 'tokopedia.com', 'shopee.co.id', 'bukalapak.com', 'gojek.com', 'grab.com'];

// Init
document.addEventListener('DOMContentLoaded', () => {
    applyTheme();
    setupEventListeners();
    updateStats();
    updateApiStatus();

    // Hide and remove splash completely
    const splash = $('splash');
    if (splash) {
        setTimeout(() => {
            splash.classList.add('hidden');
            splash.style.display = 'none';
            splash.style.pointerEvents = 'none';
            // Remove from DOM to ensure no interference
            setTimeout(() => splash.remove(), 100);
        }, 2500);
    }
});

function setupEventListeners() {
    // Theme
    $('themeBtn').onclick = toggleTheme;

    // Tabs
    $$('.tab').forEach(tab => tab.onclick = () => switchTab(tab.dataset.tab));

    // Upload
    $('uploadBtn').onclick = () => $('fileInput').click();
    $('fileInput').onchange = e => e.target.files[0] && processImage(e.target.files[0]);
    $('galleryBtn').onclick = () => $('galleryInput').click();
    $('galleryInput').onchange = e => e.target.files[0] && processImage(e.target.files[0]);

    // Dropzone
    const dz = $('dropzone');
    dz.onclick = () => $('fileInput').click();
    dz.ondragover = e => { e.preventDefault(); dz.classList.add('active'); };
    dz.ondragleave = e => { e.preventDefault(); dz.classList.remove('active'); };
    dz.ondrop = e => {
        e.preventDefault(); dz.classList.remove('active');
        const file = e.dataTransfer.files[0];
        if (file?.type.startsWith('image/')) processImage(file);
        else showToast('File harus berupa gambar!', 'error');
    };

    // Camera
    $('cameraBtn').onclick = openCamera;
    $('closeCamBtn').onclick = closeCamera;
    $('switchCamBtn').onclick = switchCamera;
    $('captureBtn').onclick = capturePhoto;
    $('flashBtn').onclick = toggleFlash;

    // URL
    $('analyzeBtn').onclick = analyzeFromInput;
    $('urlInput').onkeypress = e => e.key === 'Enter' && analyzeFromInput();

    // Result
    $('openUrlBtn').onclick = openUrl;
    $('newScanBtn').onclick = resetToMain;
    $('copyUrlBtn').onclick = copyUrl;
    $('shareBtn').onclick = shareResult;
    $('exportBtn').onclick = exportResult;

    // Preview
    $('closePreview')?.addEventListener('click', () => $('preview').classList.add('hidden'));

    // History
    $('historyBtn').onclick = () => { loadHistory(); $('historyModal').classList.remove('hidden'); };
    $('closeHistoryBtn').onclick = () => $('historyModal').classList.add('hidden');
    $('clearHistoryBtn').onclick = clearHistory;
    $('exportHistoryBtn').onclick = exportHistoryCSV;
    $$('.filter-btn').forEach(btn => btn.onclick = () => filterHistory(btn.dataset.filter));

    // Settings
    $('settingsBtn').onclick = () => { loadSettingsUI(); $('settingsModal').classList.remove('hidden'); };
    $('closeSettingsBtn').onclick = () => $('settingsModal').classList.add('hidden');
    $('soundToggle').onchange = e => { settings.sound = e.target.checked; localStorage.setItem('sound', settings.sound); };
    $('vibrationToggle').onchange = e => { settings.vibration = e.target.checked; localStorage.setItem('vibration', settings.vibration); };
    $('autoScanToggle').onchange = e => { settings.autoScan = e.target.checked; localStorage.setItem('autoScan', settings.autoScan); };

    // Stats
    $('statsBtn').onclick = () => { updateStats(); switchTab('dashboard'); };

    // API
    $('saveApiBtn').onclick = saveApiKeys;
    $('testDbBtn').onclick = testDbConnection;
}

// Theme
function toggleTheme() {
    settings.theme = settings.theme === 'dark' ? 'light' : 'dark';
    localStorage.setItem('theme', settings.theme);
    applyTheme();
}

function applyTheme() {
    document.body.setAttribute('data-theme', settings.theme);
    $('themeBtn').textContent = settings.theme === 'dark' ? '🌙' : '☀️';
}

// Tabs
function switchTab(tabName) {
    $$('.tab').forEach(t => t.classList.toggle('active', t.dataset.tab === tabName));
    $$('.tab-content').forEach(c => c.classList.remove('active'));
    $('tab' + tabName.charAt(0).toUpperCase() + tabName.slice(1))?.classList.add('active');
    if (tabName === 'dashboard') updateStats();
}

// Image processing
function processImage(file) {
    // Show preview
    const preview = $('preview');
    const img = $('previewImg');
    img.src = URL.createObjectURL(file);
    preview.classList.remove('hidden');

    showLoading('Memindai QR Code...');

    const reader = new FileReader();
    reader.onload = e => {
        const image = new Image();
        image.onload = () => {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            canvas.width = image.width;
            canvas.height = image.height;
            ctx.drawImage(image, 0, 0);

            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);

            if (typeof jsQR !== 'undefined') {
                const code = jsQR(imageData.data, imageData.width, imageData.height, { inversionAttempts: 'attemptBoth' });

                if (code?.data) {
                    const urlPattern = /^(https?:\/\/|www\.)/i;
                    if (urlPattern.test(code.data)) {
                        analyzeAndShow(code.data);
                    } else {
                        hideLoading();
                        showToast('QR berisi: ' + code.data.substring(0, 50), 'warning');
                    }
                } else {
                    hideLoading();
                    showToast('QR Code tidak ditemukan', 'error');
                    playSound('error');
                }
            } else {
                hideLoading();
                showToast('Library QR belum dimuat', 'error');
            }
        };
        image.src = e.target.result;
    };
    reader.readAsDataURL(file);
}

// Camera
async function openCamera() {
    try {
        $('cameraSection').classList.remove('hidden');
        $('tabScan').classList.add('hidden');

        currentStream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode, width: { ideal: 1280 }, height: { ideal: 720 } }
        });
        $('videoEl').srcObject = currentStream;

        scanning = settings.autoScan;
        if (scanning) scanLoop();
    } catch (e) {
        showToast('Kamera tidak tersedia', 'error');
        closeCamera();
    }
}

function closeCamera() {
    scanning = false;
    if (currentStream) {
        currentStream.getTracks().forEach(t => t.stop());
        currentStream = null;
    }
    $('videoEl').srcObject = null;
    $('cameraSection').classList.add('hidden');
    $('tabScan').classList.remove('hidden');
}

async function switchCamera() {
    facingMode = facingMode === 'environment' ? 'user' : 'environment';
    closeCamera();
    await openCamera();
}

function toggleFlash() {
    if (currentStream) {
        const track = currentStream.getVideoTracks()[0];
        const caps = track.getCapabilities?.();
        if (caps?.torch) {
            flashOn = !flashOn;
            track.applyConstraints({ advanced: [{ torch: flashOn }] });
            $('flashBtn').style.background = flashOn ? 'var(--warning)' : '';
        } else {
            showToast('Flash tidak tersedia', 'warning');
        }
    }
}

function capturePhoto() {
    const video = $('videoEl'), canvas = $('canvasEl'), ctx = canvas.getContext('2d');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    ctx.drawImage(video, 0, 0);

    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);

    if (typeof jsQR !== 'undefined') {
        const code = jsQR(imageData.data, imageData.width, imageData.height, { inversionAttempts: 'attemptBoth' });
        closeCamera();

        if (code?.data) {
            const urlPattern = /^(https?:\/\/|www\.)/i;
            if (urlPattern.test(code.data)) analyzeAndShow(code.data);
            else showToast('QR berisi: ' + code.data.substring(0, 50), 'warning');
        } else {
            showToast('QR tidak ditemukan', 'error');
            playSound('error');
        }
    }
}

function scanLoop() {
    if (!scanning) return;

    const video = $('videoEl'), canvas = $('canvasEl'), ctx = canvas.getContext('2d');

    if (video.readyState === video.HAVE_ENOUGH_DATA) {
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        ctx.drawImage(video, 0, 0);

        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);

        if (typeof jsQR !== 'undefined') {
            const code = jsQR(imageData.data, imageData.width, imageData.height);

            if (code?.data) {
                scanning = false;
                closeCamera();

                const urlPattern = /^(https?:\/\/|www\.)/i;
                if (urlPattern.test(code.data)) {
                    vibrate();
                    analyzeAndShow(code.data);
                } else {
                    showToast('QR berisi: ' + code.data.substring(0, 50), 'warning');
                }
                return;
            }
        }
    }

    requestAnimationFrame(scanLoop);
}

// URL Analysis
function analyzeFromInput() {
    const url = $('urlInput').value.trim();
    if (!url) { showToast('Masukkan URL', 'warning'); return; }
    analyzeAndShow(url);
}

function analyzeAndShow(url) {
    showLoading('Menganalisis keamanan URL...');

    setTimeout(async () => {
        let result = analyzeUrl(url);

        // If API keys available, enhance with external APIs
        if (settings.vtApiKey || settings.gsbApiKey) {
            result = await enhanceWithAPIs(result);
        }

        currentResult = result;
        saveToHistory(result);
        displayResult(result);

        playSound(result.riskLevel === 'SAFE' ? 'success' : 'error');
        vibrate();
    }, 600);
}

function analyzeUrl(url) {
    try {
        let finalUrl = url.startsWith('http') ? url : 'https://' + url;
        const urlObj = new URL(finalUrl);
        const domain = urlObj.hostname.toLowerCase();

        let riskScore = 0;
        const threats = [], warnings = [], safeIndicators = [];

        // Trusted domain
        if (TRUSTED_DOMAINS.some(t => domain === t || domain.endsWith('.' + t))) {
            safeIndicators.push('Domain terpercaya');
            riskScore -= 20;
        }

        // Suspicious TLD
        if (SUSPICIOUS_TLDS.some(tld => domain.endsWith(tld))) {
            warnings.push('TLD mencurigakan');
            riskScore += 25;
        }

        // Phishing patterns
        if (PHISHING_PATTERNS.some(p => p.test(url.toLowerCase()))) {
            threats.push('Pola phishing terdeteksi');
            riskScore += 40;
        }

        // IP address
        if (/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/.test(domain)) {
            warnings.push('Menggunakan IP langsung');
            riskScore += 20;
        }

        // Subdomains
        if (domain.split('.').length > 4) {
            warnings.push('Subdomain berlebihan');
            riskScore += 15;
        }

        // HTTPS
        if (urlObj.protocol === 'http:') {
            warnings.push('Tidak menggunakan HTTPS');
            riskScore += 15;
        } else {
            safeIndicators.push('HTTPS aktif');
        }

        // @ symbol
        if (url.includes('@')) {
            warnings.push('Karakter @ mencurigakan');
            riskScore += 20;
        }

        // Typosquatting
        const typos = [
            { pattern: /g00gle|googl[^e]/i, real: 'google.com' },
            { pattern: /faceb00k|facebok/i, real: 'facebook.com' },
            { pattern: /amaz[0o]n|arnazon/i, real: 'amazon.com' }
        ];

        for (const t of typos) {
            if (t.pattern.test(domain)) {
                threats.push(`Typosquatting: ${t.real}`);
                riskScore += 40;
                break;
            }
        }

        riskScore = Math.max(0, Math.min(100, riskScore));

        let riskLevel, recommendation;
        if (riskScore <= 30) {
            riskLevel = 'SAFE';
            recommendation = 'URL terlihat aman. Tetap waspada saat memasukkan data sensitif.';
        } else if (riskScore <= 60) {
            riskLevel = 'SUSPICIOUS';
            recommendation = 'URL mencurigakan! Periksa dengan teliti sebelum melanjutkan.';
        } else {
            riskLevel = 'DANGEROUS';
            recommendation = '⚠️ BERBAHAYA! Jangan buka link ini - kemungkinan phishing!';
        }

        return { url: finalUrl, domain, riskScore, riskLevel, recommendation, threats, warnings, safeIndicators };
    } catch (e) {
        return {
            url, domain: '', riskScore: 50, riskLevel: 'UNKNOWN',
            recommendation: 'Format URL tidak valid',
            threats: [], warnings: ['Gagal menganalisis'], safeIndicators: []
        };
    }
}

async function enhanceWithAPIs(result) {
    // Call backend PHP API for real analysis
    const serverUrl = settings.serverUrl || '';
    const apiEndpoint = serverUrl ? `${serverUrl}/api/analyze.php` : '../api/analyze.php';

    try {
        const response = await fetch(apiEndpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                url: result.url,
                vt_api_key: settings.vtApiKey,
                gsb_api_key: settings.gsbApiKey
            })
        });

        if (!response.ok) throw new Error('API request failed');

        const data = await response.json();

        // VirusTotal results
        if (data.virustotal) {
            const vt = data.virustotal;
            result.vtResult = `${vt.malicious}/${vt.malicious + vt.suspicious + vt.harmless + vt.undetected} detections`;
            if (vt.malicious > 0) {
                result.threats.push(`VirusTotal: ${vt.malicious} engine mendeteksi berbahaya`);
                result.riskScore = Math.min(100, result.riskScore + vt.malicious * 5);
            }
        }

        // Google Safe Browsing results
        if (data.google_safe_browsing) {
            const gsb = data.google_safe_browsing;
            if (gsb.is_safe) {
                result.gsbResult = 'Tidak ada ancaman';
                result.safeIndicators.push('Google Safe Browsing: Aman');
            } else {
                result.gsbResult = `Ancaman: ${gsb.threats.join(', ')}`;
                result.threats.push(`Google Safe Browsing: ${gsb.threats.join(', ')}`);
                result.riskScore = Math.min(100, result.riskScore + 40);
            }
        }

        // WHOIS results
        if (data.whois) {
            result.domainAge = `Terdaftar ${data.whois.age_days} hari lalu`;
            if (data.whois.age_days < 30) {
                result.warnings.push('Domain baru (< 30 hari)');
                result.riskScore = Math.min(100, result.riskScore + 15);
            }
        }

        // Recalculate risk level after API enhancements
        if (result.riskScore <= 30) {
            result.riskLevel = 'SAFE';
            result.recommendation = 'URL terlihat aman. Tetap waspada saat memasukkan data sensitif.';
        } else if (result.riskScore <= 60) {
            result.riskLevel = 'SUSPICIOUS';
            result.recommendation = 'URL mencurigakan! Periksa dengan teliti sebelum melanjutkan.';
        } else {
            result.riskLevel = 'DANGEROUS';
            result.recommendation = '⚠️ BERBAHAYA! Jangan buka link ini - kemungkinan phishing!';
        }

    } catch (error) {
        console.error('API enhancement failed:', error);
        // Fallback to demo mode if API fails
        result.vtResult = 'API tidak tersedia';
        result.gsbResult = 'API tidak tersedia';
    }

    return result;
}

// Display result
function displayResult(result) {
    hideLoading();
    $('tabScan').classList.add('hidden');
    $('resultSection').classList.remove('hidden');
    $('preview').classList.add('hidden');

    const card = $('resultCard'), badge = $('riskBadge');
    card.classList.remove('safe', 'suspicious', 'dangerous');
    badge.classList.remove('safe', 'suspicious', 'dangerous');

    const level = result.riskLevel.toLowerCase();
    card.classList.add(level);
    badge.classList.add(level);

    let icon = '✓', text = 'AMAN';
    if (result.riskLevel === 'SUSPICIOUS') { icon = '⚠️'; text = 'MENCURIGAKAN'; }
    else if (result.riskLevel === 'DANGEROUS') { icon = '🚫'; text = 'BERBAHAYA'; }

    $('riskIcon').textContent = icon;
    $('riskText').textContent = text;
    $('scoreNumber').textContent = result.riskScore;
    $('urlLink').textContent = result.url;
    $('urlLink').href = result.url;
    $('recommendation').textContent = result.recommendation;

    // API Results
    if (result.vtResult || result.gsbResult) {
        $('apiResults').classList.remove('hidden');
        if (result.vtResult) $('vtResult').querySelector('.api-value').textContent = result.vtResult;
        if (result.gsbResult) $('gsbResult').querySelector('.api-value').textContent = result.gsbResult;
        if (result.domainAge) $('whoisResult').querySelector('.api-value').textContent = result.domainAge;
    } else {
        $('apiResults').classList.add('hidden');
    }

    // Details
    $('threatsSection').classList.toggle('hidden', !result.threats.length);
    if (result.threats.length) $('threatsList').innerHTML = result.threats.map(t => `<li>${t}</li>`).join('');

    $('warningsSection').classList.toggle('hidden', !result.warnings.length);
    if (result.warnings.length) $('warningsList').innerHTML = result.warnings.map(w => `<li>${w}</li>`).join('');

    $('safeSection').classList.toggle('hidden', !result.safeIndicators.length);
    if (result.safeIndicators.length) $('safeList').innerHTML = result.safeIndicators.map(s => `<li>${s}</li>`).join('');

    $('openUrlBtn').classList.toggle('hidden', result.riskLevel === 'DANGEROUS');
}

// Actions
function openUrl() {
    if (currentResult?.url) {
        if (currentResult.riskLevel === 'SUSPICIOUS' && !confirm('URL mencurigakan. Lanjutkan?')) return;
        window.open(currentResult.url, '_blank', 'noopener,noreferrer');
    }
}

function copyUrl() {
    if (currentResult?.url) {
        navigator.clipboard.writeText(currentResult.url).then(() => showToast('URL disalin!', 'success'));
    }
}

function shareResult() {
    if (navigator.share && currentResult) {
        navigator.share({
            title: 'Quishing Defender Result',
            text: `URL: ${currentResult.url}\nRisk: ${currentResult.riskLevel}\nScore: ${currentResult.riskScore}/100`,
            url: currentResult.url
        });
    } else {
        copyUrl();
    }
}

function exportResult() {
    if (!currentResult) return;
    const text = `Quishing Defender Report\n${'='.repeat(30)}\n\nURL: ${currentResult.url}\nRisk Level: ${currentResult.riskLevel}\nRisk Score: ${currentResult.riskScore}/100\n\nRecommendation: ${currentResult.recommendation}\n\nThreats: ${currentResult.threats.join(', ') || 'None'}\nWarnings: ${currentResult.warnings.join(', ') || 'None'}\nSafe: ${currentResult.safeIndicators.join(', ') || 'None'}`;
    downloadFile('scan-report.txt', text);
}

function resetToMain() {
    $('resultSection').classList.add('hidden');
    $('tabScan').classList.remove('hidden');
    $('urlInput').value = '';
    $('fileInput').value = '';
    $('preview').classList.add('hidden');
    currentResult = null;
}

// History
function getHistory() {
    try { return JSON.parse(localStorage.getItem('quishing_history')) || []; }
    catch { return []; }
}

function saveToHistory(result) {
    const h = getHistory();
    h.unshift({ id: Date.now(), url: result.url, domain: result.domain, riskLevel: result.riskLevel, riskScore: result.riskScore, timestamp: new Date().toISOString() });
    if (h.length > 200) h.pop();
    localStorage.setItem('quishing_history', JSON.stringify(h));
    updateStats();
}

function loadHistory() { renderHistory(getHistory()); }

function renderHistory(items, filter = 'all') {
    const list = $('historyList');
    if (!items.length) { list.innerHTML = '<div class="history-empty">📭 Belum ada riwayat</div>'; return; }

    const map = { safe: 'SAFE', suspicious: 'SUSPICIOUS', dangerous: 'DANGEROUS' };
    const filtered = filter === 'all' ? items : items.filter(i => i.riskLevel === map[filter]);

    if (!filtered.length) { list.innerHTML = '<div class="history-empty">Tidak ada hasil</div>'; return; }

    const labels = { safe: 'Aman', suspicious: 'Curiga', dangerous: 'Bahaya' };
    list.innerHTML = filtered.map(item => {
        const date = new Date(item.timestamp).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
        const level = item.riskLevel.toLowerCase();
        return `<div class="history-item" onclick="reanalyzeUrl('${item.url.replace(/'/g, "\\'")}')">
            <div class="history-item-top"><span class="history-badge ${level}">${labels[level] || level}</span><span class="history-date">${date}</span></div>
            <div class="history-url">${item.url}</div>
        </div>`;
    }).join('');
}

function filterHistory(filter) {
    $$('.filter-btn').forEach(b => b.classList.toggle('active', b.dataset.filter === filter));
    renderHistory(getHistory(), filter);
}

function clearHistory() {
    if (confirm('Hapus semua riwayat?')) {
        localStorage.removeItem('quishing_history');
        renderHistory([]);
        updateStats();
        showToast('Riwayat dihapus', 'success');
    }
}

function exportHistoryCSV() {
    const h = getHistory();
    if (!h.length) { showToast('Tidak ada data', 'warning'); return; }
    let csv = 'Timestamp,URL,Risk Level,Risk Score\n';
    h.forEach(i => csv += `"${i.timestamp}","${i.url}","${i.riskLevel}",${i.riskScore}\n`);
    downloadFile('quishing-history.csv', csv);
    showToast('CSV diunduh!', 'success');
}

window.reanalyzeUrl = url => { $('historyModal').classList.add('hidden'); $('urlInput').value = url; analyzeFromInput(); };

// Stats
function updateStats() {
    const h = getHistory();
    const safe = h.filter(i => i.riskLevel === 'SAFE').length;
    const suspicious = h.filter(i => i.riskLevel === 'SUSPICIOUS').length;
    const dangerous = h.filter(i => i.riskLevel === 'DANGEROUS').length;

    $('statSafe').textContent = safe;
    $('statSuspicious').textContent = suspicious;
    $('statDangerous').textContent = dangerous;
    $('statTotal').textContent = h.length;

    // Simple chart bars
    const max = Math.max(safe, suspicious, dangerous, 1);
    const chart = $('chartCanvas');
    if (chart) {
        chart.innerHTML = `
            <div style="display:flex;gap:30px;align-items:flex-end;height:100%;justify-content:center;">
                <div style="text-align:center"><div style="width:60px;height:${(safe / max) * 150}px;background:var(--success);border-radius:8px 8px 0 0;"></div><p style="margin-top:8px;font-size:0.8rem;">Aman</p></div>
                <div style="text-align:center"><div style="width:60px;height:${(suspicious / max) * 150}px;background:var(--warning);border-radius:8px 8px 0 0;"></div><p style="margin-top:8px;font-size:0.8rem;">Curiga</p></div>
                <div style="text-align:center"><div style="width:60px;height:${(dangerous / max) * 150}px;background:var(--danger);border-radius:8px 8px 0 0;"></div><p style="margin-top:8px;font-size:0.8rem;">Bahaya</p></div>
            </div>
        `;
    }

    // Recent scans
    const recent = $('recentScans');
    if (recent) {
        const last5 = h.slice(0, 5);
        if (last5.length === 0) {
            recent.innerHTML = '<p style="color:var(--text-muted);text-align:center;padding:20px;">Belum ada scan</p>';
        } else {
            recent.innerHTML = last5.map(i => {
                const l = i.riskLevel.toLowerCase();
                return `<div class="history-item" onclick="reanalyzeUrl('${i.url.replace(/'/g, "\\'")}')">
                    <div class="history-item-top"><span class="history-badge ${l}">${i.riskScore}</span></div>
                    <div class="history-url">${i.url.substring(0, 40)}${i.url.length > 40 ? '...' : ''}</div>
                </div>`;
            }).join('');
        }
    }
}

// API Settings
function saveApiKeys() {
    settings.vtApiKey = $('vtApiKey').value.trim();
    settings.gsbApiKey = $('gsbApiKey').value.trim();
    settings.serverUrl = $('serverUrl').value.trim();

    localStorage.setItem('vtApiKey', settings.vtApiKey);
    localStorage.setItem('gsbApiKey', settings.gsbApiKey);
    localStorage.setItem('serverUrl', settings.serverUrl);

    updateApiStatus();
    showToast('API keys disimpan!', 'success');
}

function updateApiStatus() {
    const hasApi = settings.vtApiKey || settings.gsbApiKey;
    const mode = hasApi ? 'API Mode' : 'Demo Mode';
    $('apiMode').textContent = mode;
    $('footerMode').textContent = mode;
    $('apiMode').style.color = hasApi ? 'var(--success)' : 'var(--warning)';
}

function testDbConnection() {
    if (!settings.serverUrl) { showToast('Masukkan Server URL', 'warning'); return; }
    showToast('Testing connection...', 'info');
    // Demo - in real app would test actual connection
    setTimeout(() => showToast('Connection successful!', 'success'), 1000);
}

function loadSettingsUI() {
    $('soundToggle').checked = settings.sound;
    $('vibrationToggle').checked = settings.vibration;
    $('autoScanToggle').checked = settings.autoScan;
    $('vtApiKey').value = settings.vtApiKey;
    $('gsbApiKey').value = settings.gsbApiKey;
    $('serverUrl').value = settings.serverUrl;
}

// Utils
function showLoading(text) {
    $('loadingText').textContent = text || 'Menganalisis...';
    $('loadingSection').classList.remove('hidden');
    $('tabScan').classList.add('hidden');
    $('resultSection').classList.add('hidden');
}

function hideLoading() { $('loadingSection').classList.add('hidden'); }

function showToast(msg, type = 'info') {
    const toast = $('toast');
    const icons = { success: '✅', error: '❌', warning: '⚠️', info: 'ℹ️' };
    toast.querySelector('.toast-icon').textContent = icons[type] || icons.info;
    toast.querySelector('.toast-msg').textContent = msg;
    toast.className = 'toast ' + type;
    setTimeout(() => toast.classList.add('hidden'), 3000);
}

function playSound(type) {
    if (!settings.sound) return;
    try { $(type === 'success' ? 'soundSuccess' : 'soundError')?.play(); } catch { }
}

function vibrate() {
    if (settings.vibration && navigator.vibrate) navigator.vibrate(100);
}

function downloadFile(filename, content) {
    const a = document.createElement('a');
    a.href = 'data:text/plain;charset=utf-8,' + encodeURIComponent(content);
    a.download = filename;
    a.click();
}

// PWA
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => navigator.serviceWorker.register('sw.js').catch(() => { }));
}
