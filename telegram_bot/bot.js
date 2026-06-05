require('dotenv').config();
const { Telegraf } = require('telegraf');
const axios = require('axios');
const jsQR = require('jsqr');
const sharp = require('sharp');
const fs = require('fs');
const path = require('path');

const bot = new Telegraf(process.env.TELEGRAM_BOT_TOKEN);

// Welcome message
bot.start((ctx) => {
    ctx.reply(
        "🛡️ *Selamat Datang di Quishing Defender Bot!*\n\n" +
        "Kirimkan *URL* atau *Foto QR Code* kepada saya, dan saya akan mengecek apakah aman atau berbahaya.\n\n" +
        "🔍 *Cara Pakai:*\n" +
        "1. Ketik URL langsung (contoh: `google.com`)\n" +
        "2. Kirim gambar QR Code (termasuk QR _fancy_ / berwarna / berlogo!)\n\n" +
        "✨ *Fitur Unggulan:*\n" +
        "• Deteksi QR Fancy (berwarna, berlogo, artistik)\n" +
        "• Analisis multi-API (VirusTotal + Google Safe Browsing)\n" +
        "• Deteksi QRIS Payment palsu\n" +
        "• Deteksi typosquatting & URL shortener\n\n" +
        "_Powered by VirusTotal, Google Safe Browsing & WHOIS_",
        { parse_mode: 'Markdown' }
    );
});

bot.help((ctx) => ctx.reply(
    "🔍 *Cara Menggunakan Quishing Defender:*\n\n" +
    "1️⃣ *Scan URL:* Ketik URL langsung\n" +
    "   Contoh: `google.com` atau `https://example.com`\n\n" +
    "2️⃣ *Scan QR Code:* Kirim foto/gambar QR Code\n" +
    "   ✅ Mendukung QR biasa & QR fancy (berwarna/berlogo)\n\n" +
    "3️⃣ *Scan QRIS:* Kirim foto QR QRIS pembayaran\n" +
    "   Akan tampilkan info merchant & cek keamanan",
    { parse_mode: 'Markdown' }
));

// Handle Text (URL)
bot.on('text', async (ctx) => {
    const text = ctx.message.text;
    const urlPattern = /^(https?:\/\/)?[\w][\w.-]*\.[a-z]{2,}(\/\S*)?$/i;
    
    // Also detect QRIS EMV strings (start with 000201)
    const isQris = /^000201/.test(text);

    if (urlPattern.test(text) || isQris) {
        await analyzeUrl(ctx, text);
    } else {
        ctx.reply("⚠️ Itu sepertinya bukan URL yang valid. Pastikan formatnya benar.\n\nContoh: `google.com` atau `https://example.com`", { parse_mode: 'Markdown' });
    }
});

// Handle Photo (QR Code) — with fancy QR preprocessing
bot.on('photo', async (ctx) => {
    try {
        const loadingMsg = await ctx.reply("🔍 Memindai QR Code...\n_Mencoba beberapa strategi preprocessing untuk QR fancy..._", { parse_mode: 'Markdown' });

        // Get high-res photo
        const photos = ctx.message.photo;
        const fileId = photos[photos.length - 1].file_id;
        const fileLink = await ctx.telegram.getFileLink(fileId);

        // Download image to buffer
        const response = await axios.get(fileLink.href, { responseType: 'arraybuffer' });
        const imageBuffer = Buffer.from(response.data);

        // === Multi-strategy QR decode pipeline ===
        const decodeResult = await decodeQrWithPreprocessing(imageBuffer);

        if (decodeResult) {
            await ctx.telegram.deleteMessage(ctx.chat.id, loadingMsg.message_id);
            await ctx.reply(
                `✅ QR Code berhasil didekode!\n` +
                `📋 Strategi: _${decodeResult.strategy}_\n` +
                `🔗 Isi: \`${decodeResult.text}\`\n\n` +
                `Menganalisis keamanan...`,
                { parse_mode: 'Markdown' }
            );
            await analyzeUrl(ctx, decodeResult.text);
        } else {
            // Fallback: try server-side decode via Laravel
            let serverDecoded = null;
            try {
                serverDecoded = await tryServerSideDecode(imageBuffer);
            } catch (e) {
                // Server not available, skip
            }

            await ctx.telegram.deleteMessage(ctx.chat.id, loadingMsg.message_id);

            if (serverDecoded) {
                await ctx.reply(
                    `✅ QR Code berhasil didekode (server-side)!\n` +
                    `📋 Strategi: _${serverDecoded.strategy}_\n` +
                    `🔗 Isi: \`${serverDecoded.text}\`\n\n` +
                    `Menganalisis keamanan...`,
                    { parse_mode: 'Markdown' }
                );
                await analyzeUrl(ctx, serverDecoded.text);
            } else {
                await ctx.reply(
                    "❌ *Tidak dapat menemukan QR Code dalam gambar ini.*\n\n" +
                    "💡 *Tips untuk QR Fancy:*\n" +
                    "• Pastikan gambar jelas dan tidak blur\n" +
                    "• Pastikan QR Code tidak terpotong\n" +
                    "• Coba foto dari sudut tegak lurus\n" +
                    "• Pastikan pencahayaan cukup\n" +
                    "• Jika QR terlalu artistik, coba screenshot dari jarak dekat",
                    { parse_mode: 'Markdown' }
                );
            }
        }
    } catch (error) {
        console.error('Photo processing error:', error);
        ctx.reply("❌ Terjadi kesalahan saat memproses gambar. Coba kirim ulang.");
    }
});

// Handle Document (image files sent as document)
bot.on('document', async (ctx) => {
    const doc = ctx.message.document;
    const mimeType = doc.mime_type || '';
    
    if (!mimeType.startsWith('image/')) {
        return ctx.reply("⚠️ Kirim file gambar (JPG, PNG) yang berisi QR Code.");
    }

    try {
        const loadingMsg = await ctx.reply("🔍 Memindai QR Code dari file...", { parse_mode: 'Markdown' });
        
        const fileLink = await ctx.telegram.getFileLink(doc.file_id);
        const response = await axios.get(fileLink.href, { responseType: 'arraybuffer' });
        const imageBuffer = Buffer.from(response.data);

        const decodeResult = await decodeQrWithPreprocessing(imageBuffer);

        await ctx.telegram.deleteMessage(ctx.chat.id, loadingMsg.message_id);

        if (decodeResult) {
            await ctx.reply(
                `✅ QR Code berhasil didekode!\n📋 Strategi: _${decodeResult.strategy}_\n🔗 Isi: \`${decodeResult.text}\`\n\nMenganalisis keamanan...`,
                { parse_mode: 'Markdown' }
            );
            await analyzeUrl(ctx, decodeResult.text);
        } else {
            await ctx.reply("❌ Tidak dapat menemukan QR Code dalam file ini.");
        }
    } catch (error) {
        console.error('Document processing error:', error);
        ctx.reply("❌ Terjadi kesalahan saat memproses file.");
    }
});


// ============================================================
// QR DECODE WITH MULTI-STRATEGY PREPROCESSING
// ============================================================

/**
 * Mencoba mendekode QR Code dari buffer gambar menggunakan
 * beberapa strategi preprocessing berurutan.
 * 
 * Strategi ini dirancang khusus untuk menangani QR Fancy
 * (berwarna, berlogo, artistik) yang gagal didekode secara langsung.
 * 
 * @param {Buffer} imageBuffer - Buffer gambar asli
 * @returns {Object|null} { text, strategy } atau null jika gagal
 */
async function decodeQrWithPreprocessing(imageBuffer) {
    // Daftar strategi preprocessing, dijalankan berurutan
    // Strategi pertama yang berhasil decode akan digunakan
    const strategies = [
        {
            name: 'Original (tanpa preprocessing)',
            process: async (buf) => buf,
        },
        {
            name: 'Grayscale',
            process: async (buf) => {
                return await sharp(buf).grayscale().png().toBuffer();
            },
        },
        {
            name: 'Grayscale + Kontras Tinggi',
            process: async (buf) => {
                return await sharp(buf)
                    .grayscale()
                    .linear(1.8, -60)  // Increase contrast
                    .png().toBuffer();
            },
        },
        {
            name: 'Grayscale + Kontras Sangat Tinggi',
            process: async (buf) => {
                return await sharp(buf)
                    .grayscale()
                    .linear(2.5, -100)  // Very high contrast
                    .png().toBuffer();
            },
        },
        {
            name: 'Grayscale + Sharpen',
            process: async (buf) => {
                return await sharp(buf)
                    .grayscale()
                    .linear(1.5, -40)
                    .sharpen({ sigma: 2, m1: 2, m2: 1 })
                    .png().toBuffer();
            },
        },
        {
            name: 'Threshold Binarisasi (128)',
            process: async (buf) => {
                return await sharp(buf)
                    .grayscale()
                    .threshold(128)
                    .png().toBuffer();
            },
        },
        {
            name: 'Threshold Binarisasi (100)',
            process: async (buf) => {
                return await sharp(buf)
                    .grayscale()
                    .threshold(100)
                    .png().toBuffer();
            },
        },
        {
            name: 'Threshold Binarisasi (160)',
            process: async (buf) => {
                return await sharp(buf)
                    .grayscale()
                    .threshold(160)
                    .png().toBuffer();
            },
        },
        {
            name: 'Grayscale + Brightness Up + Kontras',
            process: async (buf) => {
                return await sharp(buf)
                    .grayscale()
                    .modulate({ brightness: 1.4 })
                    .linear(1.8, -60)
                    .png().toBuffer();
            },
        },
        {
            name: 'Grayscale + Brightness Down + Kontras',
            process: async (buf) => {
                return await sharp(buf)
                    .grayscale()
                    .modulate({ brightness: 0.6 })
                    .linear(1.8, -40)
                    .png().toBuffer();
            },
        },
        {
            name: 'Upscale 2x + Grayscale + Kontras',
            process: async (buf) => {
                const meta = await sharp(buf).metadata();
                return await sharp(buf)
                    .resize(meta.width * 2, meta.height * 2, { kernel: 'lanczos3' })
                    .grayscale()
                    .linear(2.0, -80)
                    .png().toBuffer();
            },
        },
        {
            name: 'Upscale 2x + Threshold (128)',
            process: async (buf) => {
                const meta = await sharp(buf).metadata();
                return await sharp(buf)
                    .resize(meta.width * 2, meta.height * 2, { kernel: 'lanczos3' })
                    .grayscale()
                    .threshold(128)
                    .png().toBuffer();
            },
        },
        {
            name: 'Negate (Invert Warna)',
            process: async (buf) => {
                return await sharp(buf)
                    .negate({ alpha: false })
                    .png().toBuffer();
            },
        },
        {
            name: 'Negate + Grayscale + Threshold',
            process: async (buf) => {
                return await sharp(buf)
                    .negate({ alpha: false })
                    .grayscale()
                    .threshold(128)
                    .png().toBuffer();
            },
        },
        {
            name: 'Median Filter + Grayscale + Kontras',
            process: async (buf) => {
                return await sharp(buf)
                    .median(3)
                    .grayscale()
                    .linear(2.0, -80)
                    .png().toBuffer();
            },
        },
    ];

    for (const strategy of strategies) {
        try {
            let processedBuffer;
            if (strategy.name === 'Original (tanpa preprocessing)') {
                processedBuffer = imageBuffer;
            } else {
                processedBuffer = await strategy.process(imageBuffer);
            }

            const decoded = await decodeQrFromBuffer(processedBuffer);
            if (decoded) {
                console.log(`✅ QR decoded with strategy: ${strategy.name}`);
                return { text: decoded, strategy: strategy.name };
            }
        } catch (err) {
            // Strategy failed, try next
            continue;
        }
    }

    return null;
}

/**
 * Decode QR code from image buffer using jsQR.
 * @param {Buffer} buf - PNG image buffer
 * @returns {string|null} decoded text or null
 */
async function decodeQrFromBuffer(buf) {
    try {
        const { data, info } = await sharp(buf)
            .ensureAlpha()
            .raw()
            .toBuffer({ resolveWithObject: true });

        const code = jsQR(new Uint8ClampedArray(data), info.width, info.height, {
            inversionAttempts: 'attemptBoth',
        });

        if (code && code.data && code.data.length > 0) {
            return code.data;
        }
    } catch (err) {
        // Decode failed
    }
    return null;
}

/**
 * Fallback: send image to Laravel backend for server-side QR decode.
 * The Laravel backend has additional strategies using PHP GD + ZXing QrReader.
 * @param {Buffer} imageBuffer 
 * @returns {Object|null} { text, strategy }
 */
async function tryServerSideDecode(imageBuffer) {
    const apiUrl = process.env.API_URL || 'http://127.0.0.1:8000';
    const FormData = (await import('form-data')).default;
    
    const form = new FormData();
    form.append('image', imageBuffer, {
        filename: 'qr_scan.png',
        contentType: 'image/png',
    });

    try {
        const response = await axios.post(`${apiUrl}/api/scan-qr-image`, form, {
            headers: form.getHeaders(),
            timeout: 30000,
        });

        if (response.data && response.data.decoded_text) {
            return {
                text: response.data.decoded_text,
                strategy: `Server-side: ${response.data.strategy || 'ZXing'}`,
            };
        }
    } catch (err) {
        // Server not available
    }
    return null;
}


// ============================================================
// URL ANALYSIS
// ============================================================

// Generate text progress bar
function progressBar(score) {
    const filled = Math.round(score / 10);
    const empty = 10 - filled;
    return '█'.repeat(filled) + '░'.repeat(empty);
}

// Analyze Function
async function analyzeUrl(ctx, url) {
    try {
        const loadingMsg = await ctx.reply("⏳ Sedang memeriksa ke VirusTotal & Google Safe Browsing...");

        // Call Backend API (Laravel)
        const response = await axios.post(process.env.API_URL, {
            url: url,
            scan_type: 'url'  // Let backend auto-detect actual type
        });

        const result = response.data;
        const riskLevel = result.risk_level;
        const score = result.risk_score;

        // Header icon and label based on risk
        let icon, label;
        if (riskLevel === 'SAFE') { icon = '✅'; label = 'AMAN'; }
        else if (riskLevel === 'SUSPICIOUS') { icon = '⚠️'; label = 'MENCURIGAKAN'; }
        else { icon = '🚫'; label = 'BERBAHAYA'; }

        // Build message
        let message = `${icon} *Hasil Analisis: ${label}*\n`;
        message += `\`[${progressBar(score)}]\` ${score}/100\n`;
        message += `🔗 \`${result.url}\`\n`;

        // Show scan type badges
        if (result.is_financial && result.is_qris) {
            message += `\n📱 *QRIS PAYMENT TERDETEKSI*\n`;
            // Show merchant info from QRIS
            if (result.qris_info && result.qris_info.merchant_name) {
                message += `\n🏪 *Informasi Merchant:*\n`;
                message += `  👤 Atas Nama: *${result.qris_info.merchant_name}*\n`;
                if (result.qris_info.merchant_city) {
                    message += `  📍 Kota: ${result.qris_info.merchant_city}\n`;
                }
                if (result.qris_info.country_code) {
                    message += `  🌍 Negara: ${result.qris_info.country_code}\n`;
                }
                if (result.qris_info.transaction_amount) {
                    message += `  💰 Nominal: Rp ${Number(result.qris_info.transaction_amount).toLocaleString('id-ID')}\n`;
                }
            }
        } else if (result.is_financial) {
            message += `\n💳 *TRANSAKSI KEUANGAN*\n`;
        }
        if (result.is_short_url) {
            message += `\n🔗 *SHORT URL TERDETEKSI*\n`;
        }

        // Threats
        if (result.threats && result.threats.length > 0) {
            message += `\n🚨 *Ancaman Terdeteksi (${result.threats.length}):*\n`;
            result.threats.forEach(t => {
                if (typeof t === 'object') {
                    const sev = t.severity === 'CRITICAL' ? '🔴' : t.severity === 'HIGH' ? '🟠' : '🟡';
                    message += `  ${sev} *${t.title}*\n`;
                    if (t.detail) message += `    _${t.detail}_\n`;
                } else {
                    message += `  • ${t}\n`;
                }
            });
        }

        // Warnings
        if (result.warnings && result.warnings.length > 0) {
            message += `\n⚠️ *Peringatan (${result.warnings.length}):*\n`;
            result.warnings.forEach(w => {
                if (typeof w === 'object') {
                    message += `  🟡 *${w.title}*\n`;
                    if (w.detail) message += `    _${w.detail}_\n`;
                } else {
                    message += `  • ${w}\n`;
                }
            });
        }

        // Safe Indicators
        if (result.safe_indicators && result.safe_indicators.length > 0) {
            message += `\n✅ *Indikator Aman (${result.safe_indicators.length}):*\n`;
            result.safe_indicators.forEach(s => {
                if (typeof s === 'object') {
                    message += `  ✅ ${s.title}\n`;
                } else {
                    message += `  • ${s}\n`;
                }
            });
        }

        // API Results
        message += `\n━━━━━━━━━━━━━━━\n`;
        if (result.virustotal_result) {
            const vt = result.virustotal_result;
            const total = (vt.malicious || 0) + (vt.suspicious || 0) + (vt.harmless || 0) + (vt.undetected || 0);
            const vtIcon = vt.malicious > 0 ? '🔴' : '🟢';
            message += `${vtIcon} *VirusTotal:* ${vt.malicious}/${total} engine\n`;
        }

        if (result.gsb_result) {
            const gsb = result.gsb_result;
            const gsbIcon = gsb.is_safe ? '🟢' : '🔴';
            message += `${gsbIcon} *Safe Browsing:* ${gsb.is_safe ? 'Aman' : 'BERBAHAYA'}\n`;
        }

        if (result.whois_result && result.whois_result.age_days != null) {
            const whois = result.whois_result;
            const whoisIcon = whois.age_days > 180 ? '🟢' : (whois.age_days > 30 ? '🟡' : '🔴');
            message += `${whoisIcon} *WHOIS:* ${whois.age_days} hari (${whois.registered || 'N/A'})\n`;
        }

        message += `━━━━━━━━━━━━━━━\n`;
        message += `\n💡 *Rekomendasi:* ${result.recommendation}`;

        // Delete loading message and send result
        await ctx.telegram.deleteMessage(ctx.chat.id, loadingMsg.message_id);
        await ctx.reply(message, { parse_mode: 'Markdown' });

    } catch (error) {
        console.error('API Error:', error.message);
        ctx.reply("❌ Gagal menghubungi server analisis. Pastikan backend PHP sedang berjalan.");
    }
}

// Start Bot
bot.launch();
console.log("🤖 Quishing Defender Bot v2.0 is running...");
console.log("✨ QR Fancy preprocessing: 15 strategies enabled");

// Enable graceful stop
process.once('SIGINT', () => bot.stop('SIGINT'));
process.once('SIGTERM', () => bot.stop('SIGTERM'));
