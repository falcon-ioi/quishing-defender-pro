const express = require('express');
const cors = require('cors');
const multer = require('multer');
const path = require('path');
const qrService = require('./services/qrScanner');
const urlAnalyzer = require('./services/urlAnalyzer');

const app = express();
const PORT = process.env.PORT || 3000;

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.static('public'));

// Multer configuration for file uploads
const storage = multer.memoryStorage();
const upload = multer({ 
    storage: storage,
    limits: { fileSize: 10 * 1024 * 1024 }, // 10MB limit
    fileFilter: (req, file, cb) => {
        const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/gif', 'image/webp'];
        if (allowedTypes.includes(file.mimetype)) {
            cb(null, true);
        } else {
            cb(new Error('Invalid file type. Only PNG, JPG, GIF, and WebP are allowed.'));
        }
    }
});

// API Routes

// Health check
app.get('/api/health', (req, res) => {
    res.json({ 
        status: 'ok', 
        message: 'Quishing Defender API is running',
        timestamp: new Date().toISOString()
    });
});

// Scan QR code from uploaded image
app.post('/api/scan/image', upload.single('qrImage'), async (req, res) => {
    try {
        if (!req.file) {
            return res.status(400).json({ 
                success: false, 
                error: 'No image file provided' 
            });
        }

        console.log('Processing uploaded image...');
        
        // Decode QR code from image
        const qrResult = await qrService.decodeFromBuffer(req.file.buffer);
        
        if (!qrResult.success) {
            return res.status(400).json({
                success: false,
                error: 'No QR code detected in the image'
            });
        }

        console.log('QR Code detected:', qrResult.data);

        // Check if the QR data is a URL
        const urlPattern = /^(https?:\/\/|www\.)/i;
        const isUrl = urlPattern.test(qrResult.data);

        if (!isUrl) {
            return res.json({
                success: true,
                qrData: qrResult.data,
                isUrl: false,
                message: 'QR code contains non-URL data'
            });
        }

        // Analyze URL safety
        const analysis = await urlAnalyzer.analyzeUrl(qrResult.data);

        res.json({
            success: true,
            qrData: qrResult.data,
            isUrl: true,
            analysis: analysis
        });

    } catch (error) {
        console.error('Error processing image:', error);
        res.status(500).json({ 
            success: false, 
            error: error.message || 'Failed to process image' 
        });
    }
});

// Analyze URL directly (without QR scan)
app.post('/api/scan/url', async (req, res) => {
    try {
        const { url } = req.body;

        if (!url) {
            return res.status(400).json({ 
                success: false, 
                error: 'URL is required' 
            });
        }

        // Validate URL format
        const urlPattern = /^(https?:\/\/)/i;
        let normalizedUrl = url;
        
        if (!urlPattern.test(url)) {
            normalizedUrl = 'https://' + url;
        }

        console.log('Analyzing URL:', normalizedUrl);

        // Analyze URL safety
        const analysis = await urlAnalyzer.analyzeUrl(normalizedUrl);

        res.json({
            success: true,
            url: normalizedUrl,
            analysis: analysis
        });

    } catch (error) {
        console.error('Error analyzing URL:', error);
        res.status(500).json({ 
            success: false, 
            error: error.message || 'Failed to analyze URL' 
        });
    }
});

// Serve the main page
app.get('/', (req, res) => {
    res.sendFile(path.join(__dirname, 'public', 'index.html'));
});

// Error handling middleware
app.use((error, req, res, next) => {
    if (error instanceof multer.MulterError) {
        if (error.code === 'LIMIT_FILE_SIZE') {
            return res.status(400).json({
                success: false,
                error: 'File too large. Maximum size is 10MB.'
            });
        }
    }
    res.status(500).json({
        success: false,
        error: error.message || 'Internal server error'
    });
});

// Start server
app.listen(PORT, () => {
    console.log(`
╔══════════════════════════════════════════════════════════╗
║                                                          ║
║   🛡️  QUISHING DEFENDER - QR Phishing Protection        ║
║                                                          ║
║   Server running at: http://localhost:${PORT}              ║
║                                                          ║
║   API Endpoints:                                         ║
║   • POST /api/scan/image  - Scan QR from image           ║
║   • POST /api/scan/url    - Analyze URL directly         ║
║   • GET  /api/health      - Health check                 ║
║                                                          ║
╚══════════════════════════════════════════════════════════╝
    `);
});

module.exports = app;
