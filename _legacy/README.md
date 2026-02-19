# Quishing Defender Pro v2.0

Aplikasi keamanan QR Code untuk mendeteksi phishing dan malware.

## 🚀 Fitur

### UI/UX
- ✅ Dark/Light theme
- ✅ Splash screen animasi
- ✅ Dashboard statistik
- ✅ Sound & vibration effects

### Scanning
- ✅ Scan via kamera (webcam/HP)
- ✅ Upload gambar
- ✅ Gallery access (mobile)
- ✅ Flashlight toggle
- ✅ Auto-scan mode
- ✅ Image preview

### Security
- ✅ VirusTotal API integration
- ✅ Google Safe Browsing API
- ✅ WHOIS domain lookup
- ✅ Pattern-based detection
- ✅ Typosquatting check

### Backend
- ✅ PHP REST API
- ✅ MySQL database
- ✅ User authentication
- ✅ History sync

### Export
- ✅ Copy URL
- ✅ Share result
- ✅ Export report (TXT)
- ✅ Export history (CSV)

---

## 📦 Instalasi

### Frontend Only (Tanpa Backend)
```bash
cd public
python -m http.server 8000
```
Buka http://localhost:8000

### Dengan Backend PHP
1. Copy folder ke htdocs XAMPP
2. Import `api/quishing_db.sql` ke phpMyAdmin
3. Akses http://localhost/quishing-defender/public

---

## 🔑 API Configuration

### VirusTotal
1. Daftar di https://www.virustotal.com
2. Dapatkan API key dari profil
3. Masukkan di tab "API" aplikasi

### Google Safe Browsing
1. Buat project di Google Cloud Console
2. Enable Safe Browsing API
3. Buat API key
4. Masukkan di tab "API" aplikasi

---

## 📁 Struktur

```
Project UAS/
├── public/
│   ├── index.html
│   ├── manifest.json
│   ├── sw.js
│   ├── css/app.css
│   └── js/app.js
├── api/
│   ├── config.php
│   ├── auth.php
│   ├── history.php
│   ├── analyze.php
│   └── quishing_db.sql
└── README.md
```

---

## 📱 Install di HP

1. Buka aplikasi di Chrome HP
2. Tap menu ⋮ → "Add to Home Screen"
3. App terinstall seperti native

---

## 🧪 Testing

| URL | Hasil |
|-----|-------|
| https://google.com | ✅ AMAN |
| https://paypal-login.tk | 🚫 BERBAHAYA |
| http://192.168.1.1 | ⚠️ MENCURIGAKAN |
