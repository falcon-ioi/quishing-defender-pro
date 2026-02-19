@echo off
title Quishing Defender - Laravel Server
color 0A

echo ============================================
echo    QUISHING DEFENDER - Laravel Server
echo ============================================
echo.

cd /d "%~dp0"

echo [1/3] Checking dependencies...
if not exist "node_modules" (
    echo      Installing npm packages...
    call npm install
    if errorlevel 1 (
        echo ERROR: npm install failed!
        pause
        exit /b 1
    )
)

echo [2/3] Building frontend assets...
if not exist "public\build" (
    call npm run build
    if errorlevel 1 (
        echo ERROR: npm build failed!
        pause
        exit /b 1
    )
)

echo [3/3] Starting Laravel server...
echo.
echo ============================================
echo    Server running at: http://127.0.0.1:8000
echo    Press Ctrl+C to stop
echo ============================================
echo.

php artisan serve

pause
