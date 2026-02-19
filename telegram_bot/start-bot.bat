@echo off
title Quishing Defender - Telegram Bot
color 0B

echo ============================================
echo    QUISHING DEFENDER - Telegram Bot
echo ============================================
echo.

cd /d "%~dp0"

echo [1/2] Checking dependencies...
if not exist "node_modules" (
    echo      Installing npm packages...
    call npm install
    if errorlevel 1 (
        echo ERROR: npm install failed!
        pause
        exit /b 1
    )
)

echo [2/2] Starting Telegram Bot...
echo.
echo    Bot is running... Press Ctrl+C to stop/restart.
echo ============================================
echo.

node bot.js

pause
