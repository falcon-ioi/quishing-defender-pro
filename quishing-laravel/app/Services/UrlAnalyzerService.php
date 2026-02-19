<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class UrlAnalyzerService
{
    protected string $vtApiKey;
    protected string $gsbApiKey;

    // Trusted financial/banking domains (Indonesian & International)
    protected array $trustedFinancialDomains = [
        // Indonesian Banks
        'bri.co.id', 'bca.co.id', 'mandiri.co.id', 'bni.co.id', 'btn.co.id',
        'cimbniaga.co.id', 'danamon.co.id', 'permatabank.com', 'maybank.co.id',
        'panin.co.id', 'ocbcnisp.com', 'bankjatim.co.id', 'bjb.co.id',
        'bankmega.com', 'bukopin.co.id', 'bankmuamalat.co.id',
        'banksyariahindonesia.co.id', 'bsi.co.id',
        // QRIS & Payment Gateway
        'qris.id', 'qris.online', 'qris.co.id',
        // Indonesian E-Wallets & Fintech
        'gopay.co.id', 'ovo.id', 'dana.id', 'shopeepay.co.id', 'linkaja.co.id',
        'flip.id', 'jenius.com', 'livin.mandiri.co.id', 'blu.co.id',
        // Indonesian E-Commerce (Payment)
        'tokopedia.com', 'shopee.co.id', 'bukalapak.com', 'blibli.com',
        'lazada.co.id', 'traveloka.com', 'tiket.com',
        // International Payment
        'paypal.com', 'stripe.com', 'visa.com', 'mastercard.com',
        'wise.com', 'revolut.com',
    ];

    // General trusted domains
    protected array $trustedDomains = [
        'google.com', 'facebook.com', 'youtube.com', 'microsoft.com',
        'apple.com', 'amazon.com', 'github.com', 'twitter.com',
        'instagram.com', 'linkedin.com', 'wikipedia.org', 'whatsapp.com',
    ];

    // Known URL shortener domains
    protected array $shortUrlDomains = [
        'bit.ly', 'tinyurl.com', 't.co', 's.id', 'is.gd', 'v.gd',
        'goo.gl', 'ow.ly', 'buff.ly', 'rebrand.ly', 'cutt.ly',
        'short.io', 'bl.ink', 'tiny.cc', 'lnkd.in', 'rb.gy',
        'shorturl.at', 'urls.id', 'link.id', 'kfrfrflh.cc',
        'clck.ru', 'tny.im', 'qr.ae', 'adf.ly', 'bc.vc',
    ];

    public function __construct()
    {
        $this->vtApiKey = config('services.virustotal.key', '');
        $this->gsbApiKey = config('services.google_safe_browsing.key', '');
    }

    public function analyze(string $url, string $scanType = 'url'): array
    {
        // Detect QRIS/EMV payment string automatically
        $isQrisPayment = $this->isQrisPaymentString($url);
        $qrisInfo = null;
        if ($isQrisPayment) {
            $scanType = 'financial';
            $qrisInfo = $this->parseQrisData($url);
        }

        // Add protocol if missing
        $originalInput = $url;
        if (!preg_match('/^https?:\/\//', $url)) {
            $url = 'http://' . $url;
        }

        $parsed = parse_url($url);
        $domain = $parsed['host'] ?? '';
        
        // Detect if domain is a trusted financial domain
        $isFinancialDomain = $this->isFinancialDomain($domain);
        if ($isFinancialDomain) {
            $scanType = 'financial';
        }

        // Auto-detect short URL domains
        $isShortUrl = $this->isShortUrl($domain);
        if ($isShortUrl && $scanType === 'url') {
            $scanType = 'shorturl';
        }

        $result = [
            'url' => $url,
            'domain' => $domain,
            'risk_score' => 0,
            'risk_level' => 'SAFE',
            'scan_type' => $scanType,
            'is_financial' => ($scanType === 'financial'),
            'is_short_url' => $isShortUrl,
            'is_qris' => $isQrisPayment,
            'qris_info' => $qrisInfo,
            'threats' => [],
            'warnings' => [],
            'safe_indicators' => [],
            'virustotal_result' => null,
            'gsb_result' => null,
            'whois_result' => null,
            'recommendation' => 'URL terlihat aman',
        ];

        // Add financial trust indicators
        if ($scanType === 'financial') {
            if ($isQrisPayment) {
                $result['safe_indicators'][] = [
                    'title' => 'Terdeteksi sebagai QRIS Payment (format EMV QR)',
                    'detail' => 'Data QR menggunakan format EMV standar yang digunakan oleh sistem pembayaran QRIS Indonesia. Format ini diawali dengan kode 000201 (Payload Format Indicator).',
                    'category' => 'FORMAT'
                ];
                if ($qrisInfo && !empty($qrisInfo['merchant_name'])) {
                    $result['safe_indicators'][] = [
                        'title' => 'Atas Nama: ' . $qrisInfo['merchant_name'],
                        'detail' => 'Nama merchant yang terdaftar pada data QRIS. Pastikan nama ini sesuai dengan penerima pembayaran yang Anda tuju.',
                        'category' => 'MERCHANT'
                    ];
                }
            }
            if ($isFinancialDomain) {
                $result['safe_indicators'][] = [
                    'title' => 'Domain keuangan/perbankan terpercaya (' . $domain . ')',
                    'detail' => 'Domain ini terdaftar dalam daftar institusi keuangan dan perbankan terpercaya di Indonesia. Ini menunjukkan bahwa URL berasal dari sumber resmi.',
                    'category' => 'TRUST'
                ];
                $result['risk_score'] -= 25;
            }
        }

        // Basic analysis (context-aware)
        $result = $this->basicAnalysis($result, $url, $domain, $parsed, $scanType);
        
        // VirusTotal
        if ($this->vtApiKey) {
            $vtResult = $this->checkVirusTotal($url);
            $result['virustotal_result'] = $vtResult;
            if ($vtResult && ($vtResult['malicious'] ?? 0) > 0) {
                $malCount = $vtResult['malicious'];
                $suspCount = $vtResult['suspicious'] ?? 0;
                $result['threats'][] = [
                    'title' => "VirusTotal: {$malCount} engine mendeteksi berbahaya",
                    'detail' => "Dari total engine antivirus yang memindai URL ini, {$malCount} engine menandai sebagai berbahaya" . ($suspCount > 0 ? " dan {$suspCount} engine menandai sebagai mencurigakan" : '') . ". Engine ini termasuk antivirus ternama seperti Kaspersky, BitDefender, ESET, dan lainnya. Semakin banyak engine yang mendeteksi, semakin tinggi kemungkinan URL ini mengandung malware, phishing, atau konten berbahaya lainnya.",
                    'severity' => 'CRITICAL',
                    'category' => 'MALWARE'
                ];
                $result['risk_score'] += min(50, $malCount * 10);
            }
        }
        
        // Google Safe Browsing
        if ($this->gsbApiKey) {
            $gsbResult = $this->checkGoogleSafeBrowsing($url);
            $result['gsb_result'] = $gsbResult;
            if ($gsbResult && !$gsbResult['is_safe']) {
                $gsbThreats = $gsbResult['threats'] ?? [];
                $threatTypes = array_map(function($t) {
                    $map = [
                        'MALWARE' => 'Malware (software berbahaya)',
                        'SOCIAL_ENGINEERING' => 'Social Engineering (phishing/penipuan)',
                        'UNWANTED_SOFTWARE' => 'Software Tidak Diinginkan',
                        'POTENTIALLY_HARMFUL_APPLICATION' => 'Aplikasi Berpotensi Berbahaya',
                    ];
                    return $map[$t] ?? $t;
                }, $gsbThreats);
                $result['threats'][] = [
                    'title' => 'Google Safe Browsing: URL terdeteksi berbahaya',
                    'detail' => 'Google Safe Browsing adalah layanan keamanan Google yang melindungi miliaran perangkat. URL ini terdeteksi mengandung ancaman: ' . implode(', ', $threatTypes) . '. Google secara aktif memblokir akses ke URL seperti ini di browser Chrome, Firefox, dan Safari untuk melindungi pengguna.',
                    'severity' => 'CRITICAL',
                    'category' => implode(', ', $gsbThreats)
                ];
                $result['risk_score'] += 40;
            }
        }

        // Whois
        $whoisResult = $this->getWhoisInfo($url);
        $result['whois_result'] = $whoisResult;
        if ($whoisResult && isset($whoisResult['age_days'])) {
            if ($whoisResult['age_days'] < 30) {
                $result['warnings'][] = [
                    'title' => "Domain baru terdaftar ({$whoisResult['age_days']} hari)",
                    'detail' => "Domain ini baru didaftarkan {$whoisResult['age_days']} hari yang lalu. Domain yang sangat baru sering digunakan oleh penyerang untuk kampanye phishing atau penyebaran malware jangka pendek. Domain legit biasanya sudah terdaftar selama berbulan-bulan atau bertahun-tahun sebelum digunakan.",
                    'severity' => 'HIGH',
                    'category' => 'DOMAIN_AGE'
                ];
                $result['risk_score'] += 25;
            } elseif ($whoisResult['age_days'] < 180) {
                $result['warnings'][] = [
                    'title' => "Domain relatif baru ({$whoisResult['age_days']} hari)",
                    'detail' => "Domain ini terdaftar {$whoisResult['age_days']} hari yang lalu. Meskipun tidak se-mencurigakan domain yang sangat baru, tetap perlu diwaspadai karena domain legit biasanya sudah lama beroperasi.",
                    'severity' => 'MEDIUM',
                    'category' => 'DOMAIN_AGE'
                ];
                $result['risk_score'] += 10;
            } else {
                $result['safe_indicators'][] = [
                    'title' => "Domain sudah terdaftar {$whoisResult['age_days']} hari",
                    'detail' => 'Domain yang sudah lama terdaftar cenderung lebih terpercaya karena penyerang biasanya menggunakan domain baru yang bisa cepat dibuang.',
                    'category' => 'DOMAIN_AGE'
                ];
            }
        }

        // Recalculate risk level
        $result = $this->calculateRiskLevel($result);

        return $result;
    }

    /**
     * Detect if a string is a QRIS/EMV QR Payment Code
     * QRIS strings typically start with "000201" (Payload Format Indicator)
     */
    protected function isQrisPaymentString(string $input): bool
    {
        // Remove protocol if present for raw check
        $raw = preg_replace('/^https?:\/\//', '', $input);
        
        // QRIS/EMV QR codes start with 000201 (Payload Format Indicator = 01)
        if (preg_match('/^000201/', $raw)) {
            return true;
        }

        // Check if the string contains QRIS-related identifiers
        $upperInput = strtoupper($raw);
        if (
            strpos($upperInput, 'QRIS') !== false &&
            (strpos($upperInput, 'ID.CO.') !== false || strpos($upperInput, 'COM.') !== false)
        ) {
            return true;
        }

        return false;
    }

    /**
     * Check if a domain belongs to a trusted financial institution
     */
    protected function isFinancialDomain(string $domain): bool
    {
        $domain = strtolower($domain);
        
        foreach ($this->trustedFinancialDomains as $trusted) {
            if ($domain === $trusted || str_ends_with($domain, '.' . $trusted)) {
                return true;
            }
        }

        // Also check if domain contains known financial identifiers
        $financialKeywords = ['id.co.bri', 'id.co.bca', 'id.co.mandiri', 'id.co.bni', 'co.qris', 'id.co.qris'];
        foreach ($financialKeywords as $keyword) {
            if (strpos($domain, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a domain is a known URL shortener
     */
    protected function isShortUrl(string $domain): bool
    {
        $domain = strtolower(preg_replace('/^www\./', '', $domain));

        foreach ($this->shortUrlDomains as $shortDomain) {
            if ($domain === $shortDomain || str_ends_with($domain, '.' . $shortDomain)) {
                return true;
            }
        }

        return false;
    }

    protected function basicAnalysis(array $result, string $url, string $domain, array $parsed, string $scanType = 'url'): array
    {
        $suspiciousTlds = ['.tk', '.ml', '.ga', '.cf', '.gq', '.xyz', '.top', '.click', '.buzz', '.work'];
        $phishingPatterns = [
            ['pattern' => '/paypal.*login/i', 'target' => 'PayPal'],
            ['pattern' => '/bank.*verify/i', 'target' => 'Bank'],
            ['pattern' => '/account.*suspend/i', 'target' => 'Akun digantung'],
            ['pattern' => '/verify.*identity/i', 'target' => 'Verifikasi identitas'],
            ['pattern' => '/secure.*update/i', 'target' => 'Update keamanan'],
            ['pattern' => '/confirm.*password/i', 'target' => 'Konfirmasi password'],
        ];
        
        $isFinancialContext = ($scanType === 'financial');
        $isTrustedFinancial = $this->isFinancialDomain($domain);
        $isTrustedGeneral = false;

        // Check typosquatting
        $typosquatPatterns = [
            ['pattern' => '/g[0o]{2}gle|go0gle|googl[^e]|goog1e/i', 'real' => 'google.com'],
            ['pattern' => '/faceb[0o]{2}k|facebo0k|facebok/i', 'real' => 'facebook.com'],
            ['pattern' => '/paypa[l1i]|payp[a4]l|paypai/i', 'real' => 'paypal.com'],
            ['pattern' => '/amaz[0o]n|arnazon|amazo[^n]/i', 'real' => 'amazon.com'],
            ['pattern' => '/micros[0o]ft|rnicrosoft|mircosoft/i', 'real' => 'microsoft.com'],
            ['pattern' => '/app[l1]e\.|ap[p]le\./i', 'real' => 'apple.com'],
            ['pattern' => '/netf[l1]ix|netfiix/i', 'real' => 'netflix.com'],
            ['pattern' => '/instag[r]am|1nstagram/i', 'real' => 'instagram.com'],
            ['pattern' => '/br[i1l]\.co\.id|brl\.co\.id/i', 'real' => 'bri.co.id'],
            ['pattern' => '/bc[a4]\.co\.id/i', 'real' => 'bca.co.id'],
            ['pattern' => '/mand[i1l]ri|mandir[i1l]/i', 'real' => 'mandiri.co.id'],
        ];

        foreach ($typosquatPatterns as $typo) {
            if (preg_match($typo['pattern'], $domain)) {
                $isLegit = false;
                foreach (array_merge($this->trustedDomains, $this->trustedFinancialDomains) as $td) {
                    if ($domain === $td || str_ends_with($domain, '.' . $td)) {
                        $isLegit = true;
                        break;
                    }
                }
                if (!$isLegit) {
                    $result['threats'][] = [
                        'title' => 'Typosquatting terdeteksi (meniru ' . $typo['real'] . ')',
                        'detail' => "Domain \"{$domain}\" sangat mirip dengan situs resmi {$typo['real']}. Teknik typosquatting adalah metode dimana penyerang mendaftarkan domain yang sangat mirip dengan situs asli (misalnya g00gle.com alih-alih google.com) untuk menipu pengguna agar memasukkan kredensial login, informasi kartu kredit, atau data sensitif lainnya. Jangan masukkan data apapun di situs ini.",
                        'severity' => 'CRITICAL',
                        'category' => 'PHISHING'
                    ];
                    $result['risk_score'] += 50;
                }
                break;
            }
        }

        // Check trusted general domains
        foreach ($this->trustedDomains as $trusted) {
            if ($domain === $trusted || str_ends_with($domain, '.' . $trusted)) {
                $result['safe_indicators'][] = [
                    'title' => 'Domain terpercaya',
                    'detail' => "Domain {$domain} termasuk dalam daftar domain terpercaya global yang telah diverifikasi keamanannya.",
                    'category' => 'TRUST'
                ];
                $result['risk_score'] -= 20;
                $isTrustedGeneral = true;
                break;
            }
        }

        // Check TLD
        foreach ($suspiciousTlds as $tld) {
            if (str_ends_with($domain, $tld)) {
                $result['warnings'][] = [
                    'title' => "TLD mencurigakan ({$tld})",
                    'detail' => "Top-Level Domain (TLD) {$tld} sering disalahgunakan oleh penyerang karena biaya registrasi yang rendah atau gratis. Banyak domain dengan TLD ini digunakan untuk phishing, spam, dan distribusi malware. Ini tidak berarti semua domain {$tld} berbahaya, namun perlu kewaspadaan ekstra.",
                    'severity' => 'MEDIUM',
                    'category' => 'SUSPICIOUS_TLD'
                ];
                $result['risk_score'] += 25;
                break;
            }
        }

        // Check phishing patterns
        foreach ($phishingPatterns as $pp) {
            if (preg_match($pp['pattern'], $url)) {
                $result['threats'][] = [
                    'title' => 'Pola phishing terdeteksi — ' . $pp['target'],
                    'detail' => "URL ini mengandung pola yang umum ditemukan pada halaman phishing. Pola \"" . $pp['target'] . "\" biasanya digunakan untuk membuat halaman login palsu atau formulir yang meniru situs resmi. Penyerang menggunakan teknik ini untuk mencuri username, password, dan data pribadi korban. JANGAN masukkan data login atau informasi sensitif apapun.",
                    'severity' => 'HIGH',
                    'category' => 'PHISHING'
                ];
                $result['risk_score'] += 40;
                break;
            }
        }

        // Check IP address as host
        if (filter_var($domain, FILTER_VALIDATE_IP)) {
            $result['warnings'][] = [
                'title' => 'Menggunakan IP address langsung (bukan domain)',
                'detail' => "URL ini menggunakan alamat IP ({$domain}) alih-alih nama domain. Situs web legitimate biasanya menggunakan domain yang terdaftar (seperti google.com). Penggunaan IP langsung sering menjadi indikasi server sementara yang digunakan untuk phishing, hosting malware, atau command & control server.",
                'severity' => 'MEDIUM',
                'category' => 'SUSPICIOUS_HOST'
            ];
            $result['risk_score'] += 20;
        }

        // Check excessive subdomains — context-aware
        $subdomainCount = substr_count($domain, '.') - 1;
        if ($subdomainCount > 2) {
            if ($isFinancialContext || $isTrustedFinancial) {
                $result['safe_indicators'][] = [
                    'title' => "Subdomain kompleks ({$subdomainCount}) — normal untuk transaksi keuangan",
                    'detail' => 'Struktur subdomain yang kompleks adalah hal wajar untuk URL transaksi keuangan dan payment gateway.',
                    'category' => 'STRUCTURE'
                ];
            } else {
                $result['warnings'][] = [
                    'title' => "Terlalu banyak subdomain ({$subdomainCount})",
                    'detail' => "URL ini memiliki {$subdomainCount} subdomain. Penyerang sering menggunakan banyak subdomain untuk menyembunyikan domain asli yang berbahaya dan membuat URL terlihat lebih legit. Contoh: login.secure.bank.fakeDomain.com — dimana \"fakeDomain.com\" adalah domain sebenarnya.",
                    'severity' => 'MEDIUM',
                    'category' => 'URL_OBFUSCATION'
                ];
                $result['risk_score'] += 15;
            }
        }

        // Check HTTPS — context-aware
        if (($parsed['scheme'] ?? '') !== 'https') {
            if ($isFinancialContext || $isTrustedFinancial) {
                $result['safe_indicators'][] = [
                    'title' => 'Protokol HTTP (normal untuk data pembayaran QRIS)',
                    'detail' => 'Data pembayaran QRIS yang dikonversi ke URL tidak menggunakan HTTPS. Ini adalah perilaku normal untuk data transaksi yang dikodekan dalam format EMV.',
                    'category' => 'PROTOCOL'
                ];
            } else {
                $result['warnings'][] = [
                    'title' => 'Tidak menggunakan HTTPS',
                    'detail' => 'Situs ini tidak menggunakan enkripsi HTTPS (SSL/TLS). Tanpa HTTPS, data yang Anda kirim (termasuk password dan informasi pribadi) bisa dicegat oleh pihak ketiga melalui serangan Man-in-the-Middle (MitM). Situs modern yang legitimate hampir selalu menggunakan HTTPS.',
                    'severity' => 'MEDIUM',
                    'category' => 'ENCRYPTION'
                ];
                $result['risk_score'] += 15;
            }
        } else {
            $result['safe_indicators'][] = [
                'title' => 'HTTPS aktif',
                'detail' => 'Koneksi menggunakan enkripsi SSL/TLS yang melindungi data dari penyadapan. Namun, HTTPS saja tidak menjamin suatu situs aman dari phishing.',
                'category' => 'ENCRYPTION'
            ];
        }

        // Check URL length — context-aware
        if (strlen($url) > 100) {
            if ($isFinancialContext || $isTrustedFinancial) {
                $result['safe_indicators'][] = [
                    'title' => 'URL panjang (' . strlen($url) . ' karakter) — normal untuk data transaksi',
                    'detail' => 'URL pembayaran memang secara alami lebih panjang karena mengandung data transaksi yang dikodekan.',
                    'category' => 'STRUCTURE'
                ];
            } else {
                $result['warnings'][] = [
                    'title' => 'URL terlalu panjang (' . strlen($url) . ' karakter)',
                    'detail' => 'URL yang sangat panjang bisa menjadi indikasi upaya penyembunyian (obfuscation). Penyerang terkadang menambahkan banyak parameter atau sub-path untuk menyembunyikan tujuan sebenarnya dari URL dan membuat pengguna tidak menyadari bahwa mereka mengunjungi situs berbahaya.',
                    'severity' => 'LOW',
                    'category' => 'URL_OBFUSCATION'
                ];
                $result['risk_score'] += 10;
            }
        }

        // Check for @ symbol in URL (redirect trick)
        if (strpos($url, '@') !== false) {
            $result['threats'][] = [
                'title' => 'URL mengandung karakter @ (teknik redirect berbahaya)',
                'detail' => 'Karakter @ dalam URL digunakan untuk teknik URL spoofing. Browser akan mengabaikan semua teks sebelum @ dan mengarahkan ke domain setelah @. Contoh: https://google.com@evil.com sebenarnya membuka evil.com, bukan google.com. Teknik ini sangat berbahaya karena pengguna berpikir mereka mengunjungi situs terpercaya padahal diarahkan ke situs berbahaya.',
                'severity' => 'HIGH',
                'category' => 'URL_SPOOFING'
            ];
            $result['risk_score'] += 35;
        }

        return $result;
    }

    protected function checkVirusTotal(string $url): ?array
    {
        try {
            // VirusTotal requires URL-safe base64 without padding
            $urlId = rtrim(base64_encode($url), '=');
            
            $response = Http::timeout(15)->withHeaders([
                'x-apikey' => $this->vtApiKey
            ])->get("https://www.virustotal.com/api/v3/urls/{$urlId}");

            if ($response->successful()) {
                $stats = $response->json('data.attributes.last_analysis_stats') ?? [];
                return [
                    'malicious' => $stats['malicious'] ?? 0,
                    'suspicious' => $stats['suspicious'] ?? 0,
                    'harmless' => $stats['harmless'] ?? 0,
                    'undetected' => $stats['undetected'] ?? 0,
                ];
            }
            
            // If URL not found (404), submit it for analysis first
            if ($response->status() === 404) {
                $submitResponse = Http::timeout(15)->withHeaders([
                    'x-apikey' => $this->vtApiKey
                ])->asForm()->post('https://www.virustotal.com/api/v3/urls', [
                    'url' => $url
                ]);
                
                if ($submitResponse->successful()) {
                    // Wait briefly and retry
                    sleep(3);
                    $retryResponse = Http::timeout(15)->withHeaders([
                        'x-apikey' => $this->vtApiKey
                    ])->get("https://www.virustotal.com/api/v3/urls/{$urlId}");
                    
                    if ($retryResponse->successful()) {
                        $stats = $retryResponse->json('data.attributes.last_analysis_stats') ?? [];
                        return [
                            'malicious' => $stats['malicious'] ?? 0,
                            'suspicious' => $stats['suspicious'] ?? 0,
                            'harmless' => $stats['harmless'] ?? 0,
                            'undetected' => $stats['undetected'] ?? 0,
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::warning('VirusTotal Error: ' . $e->getMessage());
        }
        return null;
    }

    protected function checkGoogleSafeBrowsing(string $url): ?array
    {
        try {
            $payload = [
                'client' => ['clientId' => 'quishing-defender', 'clientVersion' => '2.0'],
                'threatInfo' => [
                    'threatTypes' => ['MALWARE', 'SOCIAL_ENGINEERING', 'UNWANTED_SOFTWARE', 'POTENTIALLY_HARMFUL_APPLICATION'],
                    'platformTypes' => ['ANY_PLATFORM'],
                    'threatEntryTypes' => ['URL'],
                    'threatEntries' => [['url' => $url]]
                ]
            ];

            $response = Http::timeout(10)->post(
                "https://safebrowsing.googleapis.com/v4/threatMatches:find?key={$this->gsbApiKey}",
                $payload
            );

            if ($response->successful()) {
                $matches = $response->json('matches') ?? [];
                return [
                    'is_safe' => empty($matches),
                    'threats' => array_map(fn($m) => $m['threatType'] ?? 'UNKNOWN', $matches)
                ];
            }
        } catch (\Exception $e) {
            \Log::warning('GSB Error: ' . $e->getMessage());
        }
        return null;
    }

    protected function getWhoisInfo(string $url): ?array
    {
        $domain = parse_url($url, PHP_URL_HOST);
        if (!$domain) return null;
        
        // Remove www prefix
        $domain = preg_replace('/^www\./', '', $domain);
        
        // Get root domain
        $parts = explode('.', $domain);
        if (count($parts) > 2) {
            // Handle .co.id, .or.id style domains
            $lastTwo = implode('.', array_slice($parts, -2));
            if (in_array($lastTwo, ['co.id', 'or.id', 'go.id', 'ac.id', 'web.id', 'co.uk', 'com.au'])) {
                $domain = implode('.', array_slice($parts, -3));
            } else {
                $domain = implode('.', array_slice($parts, -2));
            }
        }
        
        // Skip IP addresses
        if (filter_var($domain, FILTER_VALIDATE_IP)) {
            return null;
        }

        try {
            $tld = substr($domain, strrpos($domain, '.') + 1);
            $whoisServers = [
                'com' => 'whois.verisign-grs.com',
                'net' => 'whois.verisign-grs.com',
                'org' => 'whois.pir.org',
                'id'  => 'whois.id',
                'io'  => 'whois.nic.io',
                'info' => 'whois.afilias.net',
                'biz' => 'whois.biz',
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
                
                // Parse registrar
                $registrar = null;
                if (preg_match('/Registrar:\s*(.+)/i', $response, $matches)) {
                    $registrar = trim($matches[1]);
                }
                
                if ($creationDate) {
                    $ageDays = (int)((time() - $creationDate) / 86400);
                    return [
                        'domain' => $domain,
                        'registered' => date('Y-m-d', $creationDate),
                        'age_days' => $ageDays,
                        'registrar' => $registrar,
                        'source' => 'whois'
                    ];
                }
            }
        } catch (\Exception $e) {
            \Log::warning('WHOIS Error: ' . $e->getMessage());
        }
        
        // Fallback — include financial domains as popular
        $popularDomains = array_merge(
            ['google.com', 'facebook.com', 'youtube.com', 'amazon.com', 'microsoft.com', 'github.com', 'twitter.com'],
            ['bri.co.id', 'bca.co.id', 'mandiri.co.id', 'bni.co.id', 'qris.id']
        );
        $isPopular = in_array($domain, $popularDomains);
        
        return [
            'domain' => $domain,
            'registered' => $isPopular ? '2000-01-01' : null,
            'age_days' => $isPopular ? 9000 : null,
            'registrar' => null,
            'source' => 'estimated'
        ];
    }

    /**
     * Parse QRIS/EMV QR Code TLV data to extract merchant info.
     */
    protected function parseQrisData(string $input): ?array
    {
        $raw = preg_replace('/^https?:\/\//', '', $input);
        $tlv = $this->parseTlv($raw);
        
        if (empty($tlv)) return null;

        // Extract merchant account info from tag 26-51 (Merchant Account Information)
        $merchantAcquirer = null;
        for ($tag = 26; $tag <= 51; $tag++) {
            $tagStr = str_pad((string)$tag, 2, '0', STR_PAD_LEFT);
            if (isset($tlv[$tagStr])) {
                $subTlv = $this->parseTlv($tlv[$tagStr]);
                // Sub-tag 00 = globally unique identifier (often contains acquirer info)
                if (isset($subTlv['00'])) {
                    $merchantAcquirer = $subTlv['00'];
                }
                break;
            }
        }

        return [
            'merchant_name' => $tlv['59'] ?? null,        // Tag 59: Merchant Name
            'merchant_city' => $tlv['60'] ?? null,        // Tag 60: Merchant City
            'country_code' => $tlv['58'] ?? null,         // Tag 58: Country Code
            'mcc' => $tlv['52'] ?? null,                  // Tag 52: Merchant Category Code
            'transaction_currency' => $tlv['53'] ?? null, // Tag 53: Currency
            'transaction_amount' => $tlv['54'] ?? null,   // Tag 54: Amount
            'postal_code' => $tlv['61'] ?? null,          // Tag 61: Postal Code
            'acquirer' => $merchantAcquirer,
        ];
    }

    /**
     * Parse TLV (Tag-Length-Value) format used in EMV QR codes.
     */
    protected function parseTlv(string $data): array
    {
        $result = [];
        $pos = 0;
        $len = strlen($data);

        while ($pos < $len - 3) {
            $tag = substr($data, $pos, 2);
            $pos += 2;

            if ($pos + 2 > $len) break;
            $valueLen = (int)substr($data, $pos, 2);
            $pos += 2;

            if ($pos + $valueLen > $len) break;
            $value = substr($data, $pos, $valueLen);
            $pos += $valueLen;

            $result[$tag] = $value;
        }

        return $result;
    }

    protected function calculateRiskLevel(array $result): array
    {
        $result['risk_score'] = max(0, min(100, $result['risk_score']));
        
        $threatCount = count($result['threats'] ?? []);
        $warningCount = count($result['warnings'] ?? []);

        if ($result['risk_score'] <= 25) {
            $result['risk_level'] = 'SAFE';
            if ($result['is_financial'] ?? false) {
                $qrisName = $result['qris_info']['merchant_name'] ?? null;
                $result['recommendation'] = 'Transaksi keuangan terverifikasi aman.' . ($qrisName ? ' Atas nama: ' . $qrisName . '.' : '') . ' Domain terpercaya terdeteksi.';
            } else {
                $result['recommendation'] = 'URL terlihat aman. Tetap waspada saat memasukkan data sensitif.';
            }
        } elseif ($result['risk_score'] <= 55) {
            $result['risk_level'] = 'SUSPICIOUS';
            $result['recommendation'] = "URL mencurigakan! Ditemukan {$warningCount} peringatan" . ($threatCount > 0 ? " dan {$threatCount} ancaman" : '') . '. Periksa detail di bawah sebelum melanjutkan. Jangan masukkan data pribadi.';
        } else {
            $result['risk_level'] = 'DANGEROUS';
            $result['recommendation'] = "⚠️ BERBAHAYA! Ditemukan {$threatCount} ancaman serius" . ($warningCount > 0 ? " dan {$warningCount} peringatan" : '') . '. Jangan buka link ini — kemungkinan besar phishing atau malware!';
        }

        return $result;
    }
}
