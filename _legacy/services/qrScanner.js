const Jimp = require('jimp');
const jsQR = require('jsqr');

/**
 * QR Code Scanner Service
 * Decodes QR codes from image buffers using jsQR
 */

/**
 * Decode QR code from image buffer
 * @param {Buffer} imageBuffer - Image file buffer
 * @returns {Object} - { success: boolean, data: string | null, error: string | null }
 */
async function decodeFromBuffer(imageBuffer) {
    try {
        // Read image using Jimp
        const image = await Jimp.read(imageBuffer);

        // Get image data
        const width = image.bitmap.width;
        const height = image.bitmap.height;

        // Convert to RGBA format for jsQR
        const imageData = new Uint8ClampedArray(image.bitmap.data);

        // Decode QR code
        const qrCode = jsQR(imageData, width, height, {
            inversionAttempts: 'dontInvert'
        });

        if (qrCode) {
            return {
                success: true,
                data: qrCode.data,
                location: qrCode.location
            };
        }

        // Try with inversion if first attempt fails
        const qrCodeInverted = jsQR(imageData, width, height, {
            inversionAttempts: 'attemptBoth'
        });

        if (qrCodeInverted) {
            return {
                success: true,
                data: qrCodeInverted.data,
                location: qrCodeInverted.location
            };
        }

        return {
            success: false,
            data: null,
            error: 'No QR code found in image'
        };

    } catch (error) {
        console.error('QR decode error:', error);
        return {
            success: false,
            data: null,
            error: error.message || 'Failed to decode QR code'
        };
    }
}

/**
 * Extract URL from QR data
 * @param {string} qrData - Raw QR code data
 * @returns {Object} - { isUrl: boolean, url: string | null }
 */
function extractUrl(qrData) {
    if (!qrData) {
        return { isUrl: false, url: null };
    }

    // Check if it's a URL
    const urlPattern = /^(https?:\/\/|www\.)/i;

    if (urlPattern.test(qrData)) {
        // Normalize URL
        let url = qrData;
        if (url.toLowerCase().startsWith('www.')) {
            url = 'https://' + url;
        }
        return { isUrl: true, url: url };
    }

    return { isUrl: false, url: null };
}

module.exports = {
    decodeFromBuffer,
    extractUrl
};
