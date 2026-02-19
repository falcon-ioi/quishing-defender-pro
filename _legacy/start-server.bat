@echo off
echo ========================================
echo    QUISHING DEFENDER - QR Phishing Protection
echo ========================================
echo.
echo Starting local server...
echo.
echo Buka browser dan akses: http://localhost:8000
echo.
echo Tekan Ctrl+C untuk stop server
echo.
cd public
python -m http.server 8000
