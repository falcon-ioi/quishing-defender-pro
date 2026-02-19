<?php

namespace App\Http\Controllers;

use App\Models\ScanHistory;
use App\Services\UrlAnalyzerService;
use Illuminate\Http\Request;
use Zxing\QrReader;

class ScanController extends Controller
{
    protected UrlAnalyzerService $analyzer;

    public function __construct(UrlAnalyzerService $analyzer)
    {
        $this->analyzer = $analyzer;
    }

    public function index()
    {
        $recentScans = ScanHistory::orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('scan.index', compact('recentScans'));
    }

    public function scan(Request $request)
    {
        $request->validate([
            'url' => 'required|string|max:2048',
            'scan_type' => 'nullable|string|in:url,financial,shorturl',
            'protocol' => 'nullable|string|in:auto,https,http',
        ]);

        $url = trim($request->input('url'));
        $scanType = $request->input('scan_type', 'url');
        $protocol = $request->input('protocol', 'auto');
        
        // Apply selected protocol
        if (!preg_match('/^https?:\/\//', $url)) {
            if ($protocol === 'https') {
                $url = 'https://' . $url;
            } elseif ($protocol === 'http') {
                $url = 'http://' . $url;
            } else {
                $url = 'http://' . $url;
            }
        }

        $result = $this->analyzer->analyze($url, $scanType);

        // Save to history
        $history = ScanHistory::create([
            'url' => $result['url'],
            'domain' => $result['domain'],
            'risk_score' => $result['risk_score'],
            'risk_level' => $result['risk_level'],
            'threats' => $result['threats'],
            'warnings' => $result['warnings'],
            'safe_indicators' => $result['safe_indicators'],
            'virustotal_result' => $result['virustotal_result'] ?? null,
            'gsb_result' => $result['gsb_result'] ?? null,
            'whois_result' => $result['whois_result'] ?? null,
            'recommendation' => $result['recommendation'],
        ]);

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return view('scan.result', compact('result', 'history'));
    }

    public function history()
    {
        $scans = ScanHistory::orderBy('created_at', 'desc')
            ->paginate(20);

        return view('scan.history', compact('scans'));
    }

    public function apiScan(Request $request)
    {
        $request->validate([
            'url' => 'required|string',
            'scan_type' => 'nullable|string|in:url,financial,shorturl',
        ]);

        $url = trim($request->input('url'));
        $scanType = $request->input('scan_type', 'url');
        
        // Add protocol if missing
        if (!preg_match('/^https?:\/\//', $url)) {
            $url = 'http://' . $url;
        }

        $result = $this->analyzer->analyze($url, $scanType);

        // Save to history
        ScanHistory::create([
            'url' => $result['url'],
            'domain' => $result['domain'],
            'risk_score' => $result['risk_score'],
            'risk_level' => $result['risk_level'],
            'threats' => $result['threats'],
            'warnings' => $result['warnings'],
            'safe_indicators' => $result['safe_indicators'],
            'virustotal_result' => $result['virustotal_result'] ?? null,
            'gsb_result' => $result['gsb_result'] ?? null,
            'whois_result' => $result['whois_result'] ?? null,
            'recommendation' => $result['recommendation'],
        ]);

        return response()->json($result);
    }

    /**
     * Server-side QR code image scanning fallback.
     * Accepts image upload, applies GD preprocessing, decodes with QrReader.
     */
    public function scanQrImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:10240', // max 10MB
        ]);

        $file = $request->file('image');
        $path = $file->getRealPath();

        // Strategy 1: Direct scan on original image
        $decoded = $this->tryQrDecode($path);
        if ($decoded) {
            return response()->json(['decoded_text' => $decoded, 'strategy' => 'original']);
        }

        // GD-based preprocessing strategies
        $strategies = [
            ['name' => 'grayscale', 'fn' => function($img) {
                imagefilter($img, IMG_FILTER_GRAYSCALE);
                return $img;
            }],
            ['name' => 'grayscale+contrast', 'fn' => function($img) {
                imagefilter($img, IMG_FILTER_GRAYSCALE);
                imagefilter($img, IMG_FILTER_CONTRAST, -50);
                return $img;
            }],
            ['name' => 'grayscale+contrast_high', 'fn' => function($img) {
                imagefilter($img, IMG_FILTER_GRAYSCALE);
                imagefilter($img, IMG_FILTER_CONTRAST, -80);
                return $img;
            }],
            ['name' => 'grayscale+sharpen', 'fn' => function($img) {
                imagefilter($img, IMG_FILTER_GRAYSCALE);
                imagefilter($img, IMG_FILTER_CONTRAST, -30);
                $sharpen = [[-1,-1,-1],[-1,16,-1],[-1,-1,-1]];
                imageconvolution($img, $sharpen, 8, 0);
                return $img;
            }],
            ['name' => 'grayscale+brightness_up', 'fn' => function($img) {
                imagefilter($img, IMG_FILTER_GRAYSCALE);
                imagefilter($img, IMG_FILTER_BRIGHTNESS, 40);
                imagefilter($img, IMG_FILTER_CONTRAST, -50);
                return $img;
            }],
            ['name' => 'grayscale+brightness_down', 'fn' => function($img) {
                imagefilter($img, IMG_FILTER_GRAYSCALE);
                imagefilter($img, IMG_FILTER_BRIGHTNESS, -40);
                imagefilter($img, IMG_FILTER_CONTRAST, -50);
                return $img;
            }],
            ['name' => 'upscale2x+grayscale+contrast', 'fn' => function($img) {
                $w = imagesx($img); $h = imagesy($img);
                $big = imagecreatetruecolor($w * 2, $h * 2);
                imagecopyresampled($big, $img, 0, 0, 0, 0, $w*2, $h*2, $w, $h);
                imagedestroy($img);
                imagefilter($big, IMG_FILTER_GRAYSCALE);
                imagefilter($big, IMG_FILTER_CONTRAST, -60);
                return $big;
            }],
            ['name' => 'negate', 'fn' => function($img) {
                imagefilter($img, IMG_FILTER_NEGATE);
                return $img;
            }],
            ['name' => 'edge_detect+contrast', 'fn' => function($img) {
                imagefilter($img, IMG_FILTER_GRAYSCALE);
                imagefilter($img, IMG_FILTER_SMOOTH, -5);
                imagefilter($img, IMG_FILTER_CONTRAST, -70);
                return $img;
            }],
        ];

        foreach ($strategies as $strategy) {
            try {
                $img = $this->loadGdImage($path);
                if (!$img) continue;
                
                $processed = ($strategy['fn'])($img);
                
                // Save to temp file
                $tmpPath = tempnam(sys_get_temp_dir(), 'qr_') . '.png';
                imagepng($processed, $tmpPath);
                imagedestroy($processed);
                
                $decoded = $this->tryQrDecode($tmpPath);
                @unlink($tmpPath);
                
                if ($decoded) {
                    return response()->json([
                        'decoded_text' => $decoded,
                        'strategy' => $strategy['name'],
                    ]);
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return response()->json(['error' => 'QR code not found', 'decoded_text' => null], 200);
    }

    /**
     * Load image using GD library.
     */
    private function loadGdImage(string $path)
    {
        $info = @getimagesize($path);
        if (!$info) return null;

        switch ($info[2]) {
            case IMAGETYPE_JPEG: return @imagecreatefromjpeg($path);
            case IMAGETYPE_PNG:  return @imagecreatefrompng($path);
            case IMAGETYPE_GIF:  return @imagecreatefromgif($path);
            case IMAGETYPE_WEBP: return @imagecreatefromwebp($path);
            case IMAGETYPE_BMP:  return @imagecreatefrombmp($path);
            default: return null;
        }
    }

    /**
     * Try to decode QR code from image file using QrReader.
     */
    private function tryQrDecode(string $path): ?string
    {
        try {
            $qrReader = new QrReader($path);
            $text = $qrReader->text();
            // QrReader returns empty string or error messages on failure
            if ($text && $text !== '' && strpos($text, 'ERROR') === false && strlen($text) > 1) {
                return $text;
            }
        } catch (\Exception $e) {
            // Silently fail
        }
        return null;
    }
}
