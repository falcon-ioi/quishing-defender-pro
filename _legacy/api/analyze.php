<?php
/**
 * URL Analysis API
 * Integrates with VirusTotal and Google Safe Browsing
 */

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSON(['error' => 'Method not allowed'], 405);
}

$data = getInput();

if (empty($data['url'])) {
    sendJSON(['error' => 'URL wajib diisi'], 400);
}

$url = $data['url'];
$vtApiKey = $data['vt_api_key'] ?? '';
$gsbApiKey = $data['gsb_api_key'] ?? '';

// Basic analysis (same as client-side)
$result = analyzeUrl($url);

// Enhance with VirusTotal
if (!empty($vtApiKey)) {
    $result['virustotal'] = checkVirusTotal($url, $vtApiKey);
}

// Enhance with Google Safe Browsing
if (!empty($gsbApiKey)) {
    $result['google_safe_browsing'] = checkGoogleSafeBrowsing($url, $gsbApiKey);
}

// WHOIS lookup
$result['whois'] = getWhoisInfo($url);

sendJSON($result);

function analyzeUrl($url) {
    $phishingPatterns = ['/paypal.*login/i', '/bank.*verify/i', '/account.*suspend/i', '/verify.*identity/i'];
    $suspiciousTlds = ['.tk', '.ml', '.ga', '.cf', '.gq', '.xyz', '.top', '.click'];
    $trustedDomains = ['google.com', 'facebook.com', 'youtube.com', 'microsoft.com', 'apple.com', 'amazon.com'];
    
    $parsed = parse_url($url);
    $domain = $parsed['host'] ?? '';
    
    $riskScore = 0;
    $threats = [];
    $warnings = [];
    $safeIndicators = [];
    
    // Check trusted
    foreach ($trustedDomains as $trusted) {
        if ($domain === $trusted || str_ends_with($domain, '.' . $trusted)) {
            $safeIndicators[] = 'Domain terpercaya';
            $riskScore -= 20;
            break;
        }
    }
    
    // Check TLD
    foreach ($suspiciousTlds as $tld) {
        if (str_ends_with($domain, $tld)) {
            $warnings[] = 'TLD mencurigakan';
            $riskScore += 25;
            break;
        }
    }
    
    // Check phishing patterns
    foreach ($phishingPatterns as $pattern) {
        if (preg_match($pattern, $url)) {
            $threats[] = 'Pola phishing terdeteksi';
            $riskScore += 40;
            break;
        }
    }
    
    // Check IP
    if (filter_var($domain, FILTER_VALIDATE_IP)) {
        $warnings[] = 'Menggunakan IP langsung';
        $riskScore += 20;
    }
    
    // Check typosquatting (fake domains that look like real ones)
    $typosquatPatterns = [
        ['pattern' => '/g[0o]{2}gle|go0gle|googl[^e]|goog1e/i', 'real' => 'google.com'],
        ['pattern' => '/faceb[0o]{2}k|facebo0k|facebok/i', 'real' => 'facebook.com'],
        ['pattern' => '/paypa[l1i]|payp[a4]l|paypai/i', 'real' => 'paypal.com'],
        ['pattern' => '/amaz[0o]n|arnazon|amazo[^n]/i', 'real' => 'amazon.com'],
        ['pattern' => '/micros[0o]ft|rnicrosoft|mircosoft/i', 'real' => 'microsoft.com'],
        ['pattern' => '/app[l1]e\.|ap[p]le\./i', 'real' => 'apple.com'],
        ['pattern' => '/netf[l1]ix|netfiix/i', 'real' => 'netflix.com'],
        ['pattern' => '/instag[r]am|1nstagram/i', 'real' => 'instagram.com'],
    ];
    
    foreach ($typosquatPatterns as $typo) {
        if (preg_match($typo['pattern'], $domain)) {
            $threats[] = 'Typosquatting terdeteksi (meniru ' . $typo['real'] . ')';
            $riskScore += 50;
            break;
        }
    }
    
    // Check excessive subdomains (common phishing tactic)
    $subdomainCount = substr_count($domain, '.') - 1;
    if ($subdomainCount > 2) {
        $warnings[] = 'Terlalu banyak subdomain';
        $riskScore += 15;
    }
    
    // Check HTTPS
    if (($parsed['scheme'] ?? '') !== 'https') {
        $warnings[] = 'Tidak menggunakan HTTPS';
        $riskScore += 15;
    } else {
        $safeIndicators[] = 'HTTPS aktif';
    }
    
    $riskScore = max(0, min(100, $riskScore));
    
    if ($riskScore <= 30) {
        $riskLevel = 'SAFE';
        $recommendation = 'URL terlihat aman';
    } else if ($riskScore <= 60) {
        $riskLevel = 'SUSPICIOUS';
        $recommendation = 'URL mencurigakan, periksa dengan teliti';
    } else {
        $riskLevel = 'DANGEROUS';
        $recommendation = 'BERBAHAYA! Jangan buka link ini';
    }
    
    return [
        'url' => $url,
        'domain' => $domain,
        'risk_score' => $riskScore,
        'risk_level' => $riskLevel,
        'recommendation' => $recommendation,
        'threats' => $threats,
        'warnings' => $warnings,
        'safe_indicators' => $safeIndicators
    ];
}

function checkVirusTotal($url, $apiKey) {
    $urlId = base64_encode($url);
    $urlId = rtrim($urlId, '=');
    
    $ch = curl_init("https://www.virustotal.com/api/v3/urls/{$urlId}");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ["x-apikey: {$apiKey}"],
        CURLOPT_TIMEOUT => 10
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        $stats = $data['data']['attributes']['last_analysis_stats'] ?? [];
        return [
            'malicious' => $stats['malicious'] ?? 0,
            'suspicious' => $stats['suspicious'] ?? 0,
            'harmless' => $stats['harmless'] ?? 0,
            'undetected' => $stats['undetected'] ?? 0
        ];
    }
    
    return null;
}

function checkGoogleSafeBrowsing($url, $apiKey) {
    $payload = [
        'client' => ['clientId' => 'quishing-defender', 'clientVersion' => '2.0'],
        'threatInfo' => [
            'threatTypes' => ['MALWARE', 'SOCIAL_ENGINEERING', 'UNWANTED_SOFTWARE'],
            'platformTypes' => ['ANY_PLATFORM'],
            'threatEntryTypes' => ['URL'],
            'threatEntries' => [['url' => $url]]
        ]
    ];
    
    $ch = curl_init("https://safebrowsing.googleapis.com/v4/threatMatches:find?key={$apiKey}");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT => 10
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        $matches = $data['matches'] ?? [];
        return [
            'is_safe' => empty($matches),
            'threats' => array_map(fn($m) => $m['threatType'] ?? 'UNKNOWN', $matches)
        ];
    }
    
    return null;
}

function getWhoisInfo($url) {
    $domain = parse_url($url, PHP_URL_HOST);
    if (!$domain) return null;
    
    // Remove www prefix
    $domain = preg_replace('/^www\./', '', $domain);
    
    // Get root domain (e.g., google.com from mail.google.com)
    $parts = explode('.', $domain);
    if (count($parts) > 2) {
        $domain = implode('.', array_slice($parts, -2));
    }
    
    try {
        // Try to get WHOIS data via socket
        $tld = substr($domain, strrpos($domain, '.') + 1);
        $whoisServers = [
            'com' => 'whois.verisign-grs.com',
            'net' => 'whois.verisign-grs.com',
            'org' => 'whois.pir.org',
            'id' => 'whois.id',
            'io' => 'whois.nic.io'
        ];
        
        $server = $whoisServers[$tld] ?? 'whois.iana.org';
        
        $sock = @fsockopen($server, 43, $errno, $errstr, 5);
        if ($sock) {
            fwrite($sock, $domain . "\r\n");
            $response = '';
            while (!feof($sock)) {
                $response .= fgets($sock, 128);
            }
            fclose($sock);
            
            // Parse creation date
            $creationDate = null;
            if (preg_match('/Creation Date:\s*(.+)/i', $response, $matches) ||
                preg_match('/Created:\s*(.+)/i', $response, $matches) ||
                preg_match('/Registration Time:\s*(.+)/i', $response, $matches)) {
                $creationDate = strtotime(trim($matches[1]));
            }
            
            if ($creationDate) {
                $ageDays = (int)((time() - $creationDate) / 86400);
                return [
                    'domain' => $domain,
                    'registered' => date('Y-m-d', $creationDate),
                    'age_days' => $ageDays,
                    'source' => 'whois'
                ];
            }
        }
    } catch (Exception $e) {
        // Fallback to estimation
    }
    
    // Fallback: estimate based on domain popularity
    $popularDomains = ['google.com', 'facebook.com', 'youtube.com', 'amazon.com', 'microsoft.com'];
    $isPopular = in_array($domain, $popularDomains);
    
    return [
        'domain' => $domain,
        'registered' => $isPopular ? '2000-01-01' : date('Y-m-d', strtotime('-1 year')),
        'age_days' => $isPopular ? 9000 : 365,
        'source' => 'estimated'
    ];
}
