require('dotenv').config();
const { Telegraf } = require('telegraf');
const axios = require('axios');
const jsQR = require('jsqr');
const Jimp = require('jimp');
const fs = require('fs');

const bot = new Telegraf(process.env.TELEGRAM_BOT_TOKEN);

// Welcome message
bot.start((ctx) => {
    ctx.reply(
        "🛡️ *Selamat Datang di Quishing Defender Bot!*\n\n" +
        "Kirimkan *URL* atau *Foto QR Code* kepada saya, dan saya akan mengecek apakah aman atau berbahaya.\n\n" +
        "🔍 *Cara Pakai:*\n" +
        "1. Ketik URL langsung (contoh: `google.com`)\n" +
        "2. Kirim gambar QR Code\n\n" +
        "_Powered by VirusTotal & Google Safe Browsing_",
        { parse_mode: 'Markdown' }
    );
});

bot.help((ctx) => ctx.reply('Kirimkan URL atau gambar QR Code untuk di-scan.'));

// Handle Text (URL)
bot.on('text', async (ctx) => {
    const text = ctx.message.text;
    const urlPattern = /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/;

    if (urlPattern.test(text)) {
        await analyzeUrl(ctx, text);
    } else {
        ctx.reply("⚠️ Itu sepertinya bukan URL yang valid. Pastikan formatnya benar.");
    }
});

// Handle Photo (QR Code)
bot.on('photo', async (ctx) => {
    try {
        ctx.reply("🔍 Memindai QR Code...");

        // Get high-res photo
        const photos = ctx.message.photo;
        const fileId = photos[photos.length - 1].file_id;
        const fileLink = await ctx.telegram.getFileLink(fileId);

        // Download image
        const image = await Jimp.read(fileLink.href);
        const { data, width, height } = image.bitmap;

        // Scan QR
        const code = jsQR(data, width, height);

        if (code) {
            ctx.reply(`✅ QR Code terdeteksi: \`${code.data}\`\n\nMenganalisis keamanan...`, { parse_mode: 'Markdown' });
            await analyzeUrl(ctx, code.data);
        } else {
            ctx.reply("❌ Tidak dapat menemukan QR Code dalam gambar ini. Pastikan gambar jelas.");
        }
    } catch (error) {
        console.error(error);
        ctx.reply("❌ Terjadi kesalahan saat memproses gambar.");
    }
});

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
console.log("🤖 Quishing Defender Bot is running...");

// Enable graceful stop
process.once('SIGINT', () => bot.stop('SIGINT'));
process.once('SIGTERM', () => bot.stop('SIGTERM'));
