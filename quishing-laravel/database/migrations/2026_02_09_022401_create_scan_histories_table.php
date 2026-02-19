<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scan_histories', function (Blueprint $table) {
            $table->id();
            $table->text('url');
            $table->string('domain')->nullable();
            $table->integer('risk_score')->default(0);
            $table->string('risk_level')->default('SAFE');
            $table->json('threats')->nullable();
            $table->json('warnings')->nullable();
            $table->json('safe_indicators')->nullable();
            $table->json('virustotal_result')->nullable();
            $table->json('gsb_result')->nullable();
            $table->json('whois_result')->nullable();
            $table->text('recommendation')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scan_histories');
    }
};
