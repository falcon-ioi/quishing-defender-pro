/**
 * URL Analyzer - Client-side URL security analysis
 * Demo mode with pattern matching and heuristic analysis
 */

const PHISHING_PATTERNS = [
    /paypal.*login/i, /bank.*verify/i, /account.*suspend/i, /verify.*identity/i,
    /secure.*update/i, /login.*confirm/i, /microsoft.*office.*365/i,
    /apple.*id.*lock/i, /netflix.*payment/i, /amazon.*security/i
];

const SUSPICIOUS_TLDS = ['.tk', '.ml', '.ga', '.cf', '.gq', '.xyz', '.top', '.work', '.click', '.link'];

const TRUSTED_DOMAINS = [
    'google.com', 'youtube.com', 'facebook.com', 'instagram.com', 'twitter.com',
    'linkedin.com', 'github.com', 'microsoft.com', 'apple.com', 'amazon.com',
    'netflix.com', 'spotify.com', 'paypal.com', 'wikipedia.org', 'reddit.com'
];

const KNOWN_MALICIOUS = ['malware-test.com', 'phishing-demo.tk', 'fake-bank-login.ml'];

function analyzeUrl(url) {
    try {
        const urlObj = new URL(url.startsWith('http') ? url : 'https://' + url);
        const domain = urlObj.hostname.toLowerCase();
        const fullPath = urlObj.pathname + urlObj.search;

        let riskScore = 0;
        const threats = [], warnings = [], safeIndicators = [];

        // Check trusted domain
        if (TRUSTED_DOMAINS.some(t => domain === t || domain.endsWith('.' + t))) {
            safeIndicators.push({ type: 'trusted_domain', message: 'Domain terpercaya dan terverifikasi' });
            riskScore -= 20;
        }

        // Check known malicious
        if (KNOWN_MALICIOUS.some(m => domain.includes(m))) {
            threats.push({ type: 'known_malicious', severity: 'critical', message: 'Domain terdeteksi dalam database malware' });
            riskScore += 80;
        }

        // Check suspicious TLD
        if (SUSPICIOUS_TLDS.some(tld => domain.endsWith(tld))) {
            warnings.push({ type: 'suspicious_tld', message: 'Domain menggunakan TLD yang sering disalahgunakan' });
            riskScore += 25;
        }

        // Check phishing patterns
        const matchedPatterns = PHISHING_PATTERNS.filter(p => p.test(url.toLowerCase()));
        if (matchedPatterns.length > 0) {
            threats.push({ type: 'phishing_pattern', severity: 'high', message: 'URL mengandung pola phishing yang mencurigakan' });
            riskScore += 30 * matchedPatterns.length;
        }

        // Check IP address
        if (/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/.test(domain)) {
            warnings.push({ type: 'ip_address', message: 'URL menggunakan IP address langsung, bukan domain' });
            riskScore += 20;
        }

        // Check excessive subdomains
        if (domain.split('.').length - 2 > 2) {
            warnings.push({ type: 'excessive_subdomains', message: 'Terlalu banyak subdomain (taktik phishing umum)' });
            riskScore += 15;
        }

        // Check HTTPS
        if (urlObj.protocol === 'http:') {
            warnings.push({ type: 'no_https', message: 'Koneksi tidak terenkripsi (HTTP)' });
            riskScore += 15;
        } else {
            safeIndicators.push({ type: 'https', message: 'Koneksi terenkripsi (HTTPS)' });
        }

        // Check long URL
        if (fullPath.length > 200) {
            warnings.push({ type: 'long_path', message: 'URL path sangat panjang (mungkin obfuscation)' });
            riskScore += 10;
        }

        // Check @ symbol
        if (/@|%40/.test(url)) {
            warnings.push({ type: 'suspicious_chars', message: 'URL mengandung karakter mencurigakan (@)' });
            riskScore += 20;
        }

        // Check typosquatting
        const typos = [
            { fake: /g00gle|googl[e]\./, real: 'google.com' },
            { fake: /faceb00k|facebok/, real: 'facebook.com' },
            { fake: /paypa[l1]|paypai/, real: 'paypal.com' },
            { fake: /amaz[0o]n|arnazon/, real: 'amazon.com' }
        ];
        for (const t of typos) {
            if (t.fake.test(domain)) {
                threats.push({ type: 'typosquatting', severity: 'high', message: `Kemungkinan typosquatting dari ${t.real}` });
                riskScore += 40;
                break;
            }
        }

        riskScore = Math.max(0, Math.min(100, riskScore));

        let riskLevel, riskColor, recommendation;
        if (riskScore <= 30) {
            riskLevel = 'SAFE'; riskColor = 'green';
            recommendation = 'URL terlihat aman. Anda bisa melanjutkan dengan hati-hati.';
        } else if (riskScore <= 60) {
            riskLevel = 'SUSPICIOUS'; riskColor = 'yellow';
            recommendation = 'URL mencurigakan. Periksa kembali sebelum mengklik.';
        } else {
            riskLevel = 'DANGEROUS'; riskColor = 'red';
            recommendation = 'JANGAN BUKA! URL ini sangat berbahaya dan kemungkinan phishing.';
        }

        return {
            url: urlObj.href, domain, riskScore, riskLevel, riskColor, recommendation,
            threats, warnings, safeIndicators, analysisTime: new Date().toISOString(), apiMode: 'demo'
        };
    } catch (error) {
        return {
            url, riskScore: 50, riskLevel: 'UNKNOWN', riskColor: 'gray',
            recommendation: 'Tidak dapat menganalisis URL. Berhati-hatilah.',
            threats: [], warnings: [{ type: 'error', message: 'Format URL tidak valid' }],
            safeIndicators: [], analysisTime: new Date().toISOString(), apiMode: 'demo'
        };
    }
}

window.analyzeUrl = analyzeUrl;
