@echo off
REM ============================================
REM RetroLoved - Push to GitHub Script
REM ============================================

echo ========================================
echo RetroLoved - Push to GitHub
echo ========================================
echo.

REM Check if git is initialized
if not exist ".git" (
    echo [ERROR] Git repository belum diinisialisasi!
    echo.
    echo Jalankan perintah berikut terlebih dahulu:
    echo   git init
    echo   git remote add origin https://github.com/ahwine/retroloved.git
    echo.
    pause
    exit /b 1
)

echo [1/5] Checking git status...
git status
echo.

echo [2/5] Adding all files (except temp_retroloved)...
git add .
echo Files added successfully.
echo.

echo [3/5] Creating commit...
echo.
echo Pilih commit message:
echo 1. Gunakan commit message detail dari COMMIT_MESSAGE.txt
echo 2. Masukkan commit message manual
echo.
set /p commit_choice="Pilih (1/2): "

if "%commit_choice%"=="1" (
    if exist "COMMIT_MESSAGE.txt" (
        echo.
        echo Menggunakan commit message dari COMMIT_MESSAGE.txt...
        git commit -F COMMIT_MESSAGE.txt
        echo Commit created successfully.
    ) else (
        echo.
        echo [ERROR] File COMMIT_MESSAGE.txt tidak ditemukan!
        echo Menggunakan commit message default...
        git commit -m "Update RetroLoved project - cleanup and improvements"
    )
) else (
    echo.
    set /p commit_message="Enter commit message: "
    if "%commit_message%"=="" (
        set commit_message=Update RetroLoved project - cleanup and improvements
    )
    git commit -m "%commit_message%"
    echo Commit created successfully.
)
echo.

echo [4/5] Checking remote repository...
git remote -v
echo.

echo [5/5] Pushing to GitHub...
echo.
echo Pilih branch yang ingin di-push:
echo 1. main
echo 2. master
echo.
set /p branch_choice="Pilih (1/2): "

if "%branch_choice%"=="1" (
    set branch_name=main
) else if "%branch_choice%"=="2" (
    set branch_name=master
) else (
    echo [ERROR] Pilihan tidak valid!
    pause
    exit /b 1
)

echo.
echo Pushing to %branch_name%...
git push -u origin %branch_name%

if %errorlevel% equ 0 (
    echo.
    echo ========================================
    echo Push to GitHub berhasil!
    echo ========================================
    echo.
    echo Repository: https://github.com/ahwine/retroloved
    echo Branch: %branch_name%
    echo.
    echo File yang di-push:
    echo - Semua file project
    echo - Kecuali: temp_retroloved/, config files, uploaded images
    echo.
) else (
    echo.
    echo ========================================
    echo Push to GitHub gagal!
    echo ========================================
    echo.
    echo Kemungkinan penyebab:
    echo 1. Belum login ke GitHub
    echo 2. Remote repository belum di-set
    echo 3. Tidak ada koneksi internet
    echo 4. Branch tidak ada di remote
    echo.
    echo Solusi:
    echo - Pastikan sudah login: git config --global user.name "Your Name"
    echo - Pastikan sudah login: git config --global user.email "your@email.com"
    echo - Cek remote: git remote -v
    echo - Coba manual: git push origin %branch_name%
    echo.
)

pause
