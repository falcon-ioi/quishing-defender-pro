<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScanHistory extends Model
{
    protected $fillable = [
        'url',
        'domain',
        'risk_score',
        'risk_level',
        'threats',
        'warnings',
        'safe_indicators',
        'virustotal_result',
        'gsb_result',
        'whois_result',
        'recommendation',
    ];

    protected $casts = [
        'threats' => 'array',
        'warnings' => 'array',
        'safe_indicators' => 'array',
        'virustotal_result' => 'array',
        'gsb_result' => 'array',
        'whois_result' => 'array',
    ];
}
