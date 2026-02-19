@echo off
title Quishing Defender - Control Panel
color 0B

:menu
cls
echo ====================================================
echo        QUISHING DEFENDER - CONTROL PANEL
echo ====================================================
echo.
echo    [1] Jalankan Website (Laravel)
echo    [2] Jalankan Telegram Bot
echo    [3] Jalankan Keduanya (Website + Bot)
echo    [4] Setup Ulang (npm install & build)
echo    [5] Keluar
echo.
echo ====================================================
get_char.exe > nul
set /p choice="Pilih menu [1-5]: "

if "%choice%"=="1" goto start_web
if "%choice%"=="2" goto start_bot
if "%choice%"=="3" goto start_all
if "%choice%"=="4" goto setup
if "%choice%"=="5" exit

goto menu

:start_web
start "Quishing Web" cmd /c "cd quishing-laravel && start-laravel.bat"
goto menu

:start_bot
start "Quishing Bot" cmd /c "cd telegram_bot && start-bot.bat"
goto menu

:start_all
start "Quishing Web" cmd /c "cd quishing-laravel && start-laravel.bat"
timeout /t 5 > nul
start "Quishing Bot" cmd /c "cd telegram_bot && start-bot.bat"
goto menu

:setup
cls
echo Melakukan setup ulang...
cd quishing-laravel
call npm install
call npm run build
echo.
echo Setup selesai!
pause
cd ..
goto menu
