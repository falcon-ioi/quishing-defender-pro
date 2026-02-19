/**
 * URL Analyzer Service
 * Analyzes URLs for potential phishing and security threats
 * 
 * DEMO MODE: Uses simulated analysis (no real API calls)
 * TODO: Integrate VirusTotal and Google Safe Browsing APIs for production
 */

// Known phishing patterns and suspicious indicators
const PHISHING_PATTERNS = [
    /paypal.*login/i,
    /bank.*verify/i,
    /account.*suspend/i,
    /verify.*identity/i,
    /secure.*update/i,
    /login.*confirm/i,
    /microsoft.*office.*365/i,
    /apple.*id.*lock/i,
    /netflix.*payment/i,
    /amazon.*security/i
];

const SUSPICIOUS_TLDS = [
    '.tk', '.ml', '.ga', '.cf', '.gq',  // Free domains often used for phishing
    '.xyz', '.top', '.work', '.click', '.link',
    '.review', '.country', '.cricket', '.science'
];

const TRUSTED_DOMAINS = [
    'google.com', 'youtube.com', 'facebook.com', 'instagram.com',
    'twitter.com', 'linkedin.com', 'github.com', 'microsoft.com',
    'apple.com', 'amazon.com', 'netflix.com', 'spotify.com',
    'paypal.com', 'ebay.com', 'wikipedia.org', 'reddit.com',
    'stackoverflow.com', 'medium.com', 'wordpress.com'
];

const KNOWN_MALICIOUS_DEMO = [
    'malware-test.com',
    'phishing-demo.tk',
    'fake-bank-login.ml',
    'suspicious-link.xyz'
];

/**
 * Analyze a URL for security threats
 * @param {string} url - URL to analyze
 * @returns {Object} - Analysis result
 */
async function analyzeUrl(url) {
    try {
        const urlObj = new URL(url);
        const domain = urlObj.hostname.toLowerCase();
        const fullPath = urlObj.pathname + urlObj.search;

        // Initialize risk factors
        let riskScore = 0;
        const threats = [];
        const warnings = [];
        const safeIndicators = [];

        // === Check 1: Trusted Domain ===
        const isTrusted = TRUSTED_DOMAINS.some(trusted =>
            domain === trusted || domain.endsWith('.' + trusted)
        );

        if (isTrusted) {
            safeIndicators.push({
                type: 'trusted_domain',
                message: 'Domain terpercaya dan terverifikasi'
            });
            riskScore -= 20;
        }

        // === Check 2: Known Malicious (Demo) ===
        const isKnownMalicious = KNOWN_MALICIOUS_DEMO.some(mal =>
            domain.includes(mal)
        );

        if (isKnownMalicious) {
            threats.push({
                type: 'known_malicious',
                severity: 'critical',
                message: 'Domain terdeteksi dalam database malware'
            });
            riskScore += 80;
        }

        // === Check 3: Suspicious TLD ===
        const hasSuspiciousTld = SUSPICIOUS_TLDS.some(tld =>
            domain.endsWith(tld)
        );

        if (hasSuspiciousTld) {
            warnings.push({
                type: 'suspicious_tld',
                message: 'Domain menggunakan TLD yang sering disalahgunakan'
            });
            riskScore += 25;
        }

        // === Check 4: Phishing Patterns ===
        const fullUrl = url.toLowerCase();
        const matchedPatterns = PHISHING_PATTERNS.filter(pattern =>
            pattern.test(fullUrl)
        );

        if (matchedPatterns.length > 0) {
            threats.push({
                type: 'phishing_pattern',
                severity: 'high',
                message: 'URL mengandung pola phishing yang mencurigakan',
                count: matchedPatterns.length
            });
            riskScore += 30 * matchedPatterns.length;
        }

        // === Check 5: IP Address instead of Domain ===
        const ipPattern = /^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/;
        if (ipPattern.test(domain)) {
            warnings.push({
                type: 'ip_address',
                message: 'URL menggunakan IP address langsung, bukan domain'
            });
            riskScore += 20;
        }

        // === Check 6: Excessive Subdomains ===
        const subdomainCount = domain.split('.').length - 2;
        if (subdomainCount > 2) {
            warnings.push({
                type: 'excessive_subdomains',
                message: 'Terlalu banyak subdomain (taktik phishing umum)'
            });
            riskScore += 15;
        }

        // === Check 7: HTTP (not HTTPS) ===
        if (urlObj.protocol === 'http:') {
            warnings.push({
                type: 'no_https',
                message: 'Koneksi tidak terenkripsi (HTTP)'
            });
            riskScore += 15;
        } else {
            safeIndicators.push({
                type: 'https',
                message: 'Koneksi terenkripsi (HTTPS)'
            });
        }

        // === Check 8: Long URL Path (potential obfuscation) ===
        if (fullPath.length > 200) {
            warnings.push({
                type: 'long_path',
                message: 'URL path sangat panjang (mungkin obfuscation)'
            });
            riskScore += 10;
        }

        // === Check 9: Suspicious characters in URL ===
        const suspiciousChars = /@|%40/;
        if (suspiciousChars.test(url)) {
            warnings.push({
                type: 'suspicious_chars',
                message: 'URL mengandung karakter mencurigakan (@)'
            });
            riskScore += 20;
        }

        // === Check 10: Typosquatting Detection ===
        const typoPatterns = [
            { fake: /g00gle|googl[e]\./, real: 'google.com' },
            { fake: /faceb00k|facebok/, real: 'facebook.com' },
            { fake: /paypa[l1]|paypai/, real: 'paypal.com' },
            { fake: /amaz[0o]n|arnazon/, real: 'amazon.com' },
            { fake: /micros[o0]ft|rnicrosoft/, real: 'microsoft.com' }
        ];

        for (const pattern of typoPatterns) {
            if (pattern.fake.test(domain)) {
                threats.push({
                    type: 'typosquatting',
                    severity: 'high',
                    message: `Kemungkinan typosquatting dari ${pattern.real}`
                });
                riskScore += 40;
                break;
            }
        }

        // === Simulate API checks (Demo Mode) ===
        const simulatedApiResults = await simulateExternalApiCheck(domain);

        if (simulatedApiResults.virusTotalFlags > 0) {
            threats.push({
                type: 'virustotal_detection',
                severity: 'critical',
                message: `Terdeteksi oleh ${simulatedApiResults.virusTotalFlags} engine keamanan`,
                engines: simulatedApiResults.virusTotalFlags
            });
            riskScore += simulatedApiResults.virusTotalFlags * 10;
        }

        if (simulatedApiResults.googleSafeBrowsing) {
            threats.push({
                type: 'safe_browsing_alert',
                severity: 'critical',
                message: 'Terblokir oleh Google Safe Browsing'
            });
            riskScore += 50;
        }

        // === Calculate Final Risk Score ===
        riskScore = Math.max(0, Math.min(100, riskScore));

        // Determine risk level
        let riskLevel, riskColor, recommendation;

        if (riskScore <= 30) {
            riskLevel = 'SAFE';
            riskColor = 'green';
            recommendation = 'URL terlihat aman. Anda bisa melanjutkan dengan hati-hati.';
        } else if (riskScore <= 60) {
            riskLevel = 'SUSPICIOUS';
            riskColor = 'yellow';
            recommendation = 'URL mencurigakan. Periksa kembali sebelum mengklik.';
        } else {
            riskLevel = 'DANGEROUS';
            riskColor = 'red';
            recommendation = 'JANGAN BUKA! URL ini sangat berbahaya dan kemungkinan phishing.';
        }

        return {
            url: url,
            domain: domain,
            riskScore: riskScore,
            riskLevel: riskLevel,
            riskColor: riskColor,
            recommendation: recommendation,
            threats: threats,
            warnings: warnings,
            safeIndicators: safeIndicators,
            analysisTime: new Date().toISOString(),
            apiMode: 'demo' // Will change to 'live' when real APIs are integrated
        };

    } catch (error) {
        console.error('URL analysis error:', error);
        return {
            url: url,
            riskScore: 50,
            riskLevel: 'UNKNOWN',
            riskColor: 'gray',
            recommendation: 'Tidak dapat menganalisis URL. Berhati-hatilah.',
            threats: [],
            warnings: [{
                type: 'analysis_error',
                message: 'Gagal menganalisis URL: ' + error.message
            }],
            safeIndicators: [],
            analysisTime: new Date().toISOString(),
            apiMode: 'demo'
        };
    }
}

/**
 * Simulate external API checks (Demo Mode)
 * In production, this will call real VirusTotal and Google Safe Browsing APIs
 */
async function simulateExternalApiCheck(domain) {
    // Simulate network delay
    await new Promise(resolve => setTimeout(resolve, 500));

    // Demo: Return flags for known malicious domains
    const demoMalicious = {
        'malware-test.com': { virusTotalFlags: 15, googleSafeBrowsing: true },
        'phishing-demo.tk': { virusTotalFlags: 23, googleSafeBrowsing: true },
        'fake-bank-login.ml': { virusTotalFlags: 18, googleSafeBrowsing: true },
        'suspicious-link.xyz': { virusTotalFlags: 5, googleSafeBrowsing: false }
    };

    return demoMalicious[domain] || { virusTotalFlags: 0, googleSafeBrowsing: false };
}

module.exports = {
    analyzeUrl
};
