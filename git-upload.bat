@echo off
chcp 65001 >nul
echo ========================================
echo   GIT UPLOAD TO GITHUB
echo   RetroLoved E-Commerce System
echo ========================================
echo.
echo Repository: https://github.com/ahwine/retroloved
echo.
echo ⚠️  PERINGATAN:
echo - Script ini akan upload SEMUA file ke GitHub
echo - Pastikan sudah setup Git dan GitHub account
echo - Pastikan sudah clone repository atau init git
echo.
pause
echo.

REM Cek apakah git sudah terinstall
where git >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Git tidak ditemukan!
    echo.
    echo Solusi:
    echo 1. Install Git dari: https://git-scm.com/download/win
    echo 2. Restart CMD setelah install
    echo.
    pause
    exit /b
)

echo ========================================
echo   Memulai Git Upload...
echo ========================================
echo.

REM Cek apakah sudah ada .git folder
if not exist ".git" (
    echo [1/5] Initialize Git Repository...
    git init
    echo ✓ Git initialized
    echo.
    
    echo [2/5] Add Remote Repository...
    set /p remote="Masukkan URL repository (https://github.com/ahwine/retroloved.git): "
    if "%remote%"=="" set remote=https://github.com/ahwine/retroloved.git
    git remote add origin %remote%
    echo ✓ Remote added: %remote%
    echo.
) else (
    echo ✓ Git repository sudah ada
    echo.
)

REM Add semua file
echo [3/5] Adding all files...
git add .
if %errorlevel% neq 0 (
    echo ❌ Git add gagal!
    pause
    exit /b
)
echo ✓ All files added
echo.

REM Commit
echo [4/5] Committing changes...
set /p commit_msg="Masukkan commit message (atau Enter untuk default): "
if "%commit_msg%"=="" set commit_msg=Update: Complete RetroLoved E-Commerce System

git commit -m "%commit_msg%"
if %errorlevel% neq 0 (
    echo ⚠️  Tidak ada perubahan untuk di-commit atau commit gagal
    echo.
)
echo ✓ Changes committed
echo.

REM Push
echo [5/5] Pushing to GitHub...
echo.
echo ⚠️  Anda mungkin diminta login GitHub
echo.

git branch -M main
git push -u origin main

if %errorlevel% equ 0 (
    echo.
    echo ========================================
    echo   UPLOAD BERHASIL! ✅
    echo ========================================
    echo.
    echo Repository: https://github.com/ahwine/retroloved
    echo.
    echo Cek repository Anda di browser!
    echo.
) else (
    echo.
    echo ========================================
    echo   UPLOAD GAGAL! ❌
    echo ========================================
    echo.
    echo Kemungkinan penyebab:
    echo 1. Belum login GitHub
    echo 2. Repository tidak ada
    echo 3. Tidak ada permission
    echo 4. Network error
    echo.
    echo Solusi:
    echo 1. Setup Git credentials:
    echo    git config --global user.name "Your Name"
    echo    git config --global user.email "your-email@example.com"
    echo.
    echo 2. Login GitHub via browser
    echo.
    echo 3. Atau gunakan SSH key
    echo.
)

pause
