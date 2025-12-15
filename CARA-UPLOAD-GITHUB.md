# 📤 Cara Upload ke GitHub

## Metode 1: Upload Baru (Hapus Semua File Lama)

### Step 1: Backup Repository Lama (Opsional)
```bash
# Clone repository lama ke folder backup
git clone https://github.com/ahwine/retroloved.git retroloved-backup
```

### Step 2: Hapus Semua File di GitHub
**Via GitHub Web:**
1. Buka https://github.com/ahwine/retroloved
2. Klik setiap file → Delete
3. Atau hapus repository dan buat baru

**Via Git (Recommended):**
```bash
# Clone repository
git clone https://github.com/ahwine/retroloved.git
cd retroloved

# Hapus semua file kecuali .git
rm -rf * (Linux/Mac)
# Atau manual delete semua file di Windows

# Commit penghapusan
git add -A
git commit -m "Clear repository for fresh upload"
git push origin main
```

### Step 3: Copy File Baru
```bash
# Copy semua file dari folder project ke repository
# Windows: Copy manual via File Explorer
# Linux/Mac:
cp -r /path/to/retroloved/* /path/to/repository/
```

### Step 4: Add & Commit Semua File
```bash
cd /path/to/repository

# Add semua file
git add .

# Commit
git commit -m "Initial commit: Complete RetroLoved E-Commerce System

Features:
- Customer registration with email OTP
- Product management with multiple images
- Shopping cart and checkout
- Order tracking with timeline
- Admin dashboard with statistics
- Payment confirmation system
- Notification system
- Responsive design

Tech Stack:
- PHP 8.0+
- MySQL 5.7+
- PHPMailer
- Vanilla JavaScript
- Custom CSS"

# Push ke GitHub
git push origin main
```

---

## Metode 2: Update Repository (Keep Git History)

### Step 1: Clone Repository
```bash
git clone https://github.com/ahwine/retroloved.git
cd retroloved
```

### Step 2: Hapus File Lama
```bash
# Hapus semua file kecuali .git
rm -rf * (Linux/Mac)
# Atau manual delete di Windows
```

### Step 3: Copy File Baru
```bash
# Copy semua file baru
cp -r /path/to/new/retroloved/* .
```

### Step 4: Add, Commit, Push
```bash
# Add semua perubahan
git add -A

# Commit
git commit -m "Major update: Complete system overhaul"

# Push
git push origin main
```

---

## Metode 3: Fresh Start (Buat Repository Baru)

### Step 1: Hapus Repository Lama
1. Buka https://github.com/ahwine/retroloved
2. Settings → Danger Zone → Delete this repository
3. Ketik nama repository untuk konfirmasi

### Step 2: Buat Repository Baru
1. Buka https://github.com/new
2. Repository name: `retroloved`
3. Description: `E-Commerce platform for vintage and preloved fashion`
4. Public/Private: Pilih sesuai kebutuhan
5. **JANGAN** centang "Initialize with README"
6. Klik "Create repository"

### Step 3: Initialize Git di Project
```bash
cd /path/to/retroloved

# Initialize git
git init

# Add remote
git remote add origin https://github.com/ahwine/retroloved.git

# Add semua file
git add .

# Commit
git commit -m "Initial commit: RetroLoved E-Commerce System"

# Push
git branch -M main
git push -u origin main
```

---

## 📋 Checklist Sebelum Upload

### ✅ File yang HARUS Ada
- [ ] README.md (sudah dibuat)
- [ ] .gitignore (sudah dibuat)
- [ ] composer.json
- [ ] backup/retroloved.sql (database)
- [ ] config/database.php
- [ ] config/email.php
- [ ] config/shipping.php
- [ ] Semua folder: admin/, customer/, auth/, assets/, includes/
- [ ] File halaman: index.php, shop.php, faq.php, dll

### ✅ File yang OPSIONAL (Bisa Dihapus)
- [ ] cleanup-files.bat (utility untuk development)
- [ ] reset-database.bat (utility untuk development)
- [ ] reset-database-safe.sql (utility untuk development)
- [ ] ANALISIS-*.md (dokumentasi development)
- [ ] PERBAIKAN-*.md (dokumentasi development)
- [ ] FIX-*.md (dokumentasi development)
- [ ] CARA-*.md (dokumentasi development)

### ✅ Konfigurasi yang Perlu Dicek
- [ ] config/database.php - Pastikan tidak ada password production
- [ ] config/email.php - Pastikan tidak ada password email production
- [ ] .gitignore - Pastikan file sensitif tidak ter-commit

---

## 🔒 Keamanan

### PENTING: Jangan Commit File Sensitif!

**File yang TIDAK BOLEH di-commit:**
- ❌ config/database.php dengan password production
- ❌ config/email.php dengan password email production
- ❌ .env files
- ❌ Backup database dengan data real customer

**Solusi:**
1. Gunakan file example:
```bash
# Rename config files
mv config/database.php config/database.example.php
mv config/email.php config/email.example.php

# Edit dan hapus password
# Lalu commit file example
```

2. Atau tambahkan ke .gitignore:
```
config/database.php
config/email.php
```

---

## 📝 Git Commands Lengkap

### Basic Commands
```bash
# Cek status
git status

# Add file tertentu
git add filename.php

# Add semua file
git add .
git add -A

# Commit
git commit -m "Commit message"

# Push
git push origin main

# Pull (update dari remote)
git pull origin main
```

### Undo Commands
```bash
# Undo add (unstage)
git reset filename.php

# Undo commit (keep changes)
git reset --soft HEAD~1

# Undo commit (discard changes)
git reset --hard HEAD~1

# Undo push (DANGEROUS!)
git push -f origin main
```

### Branch Commands
```bash
# Buat branch baru
git checkout -b feature-name

# Pindah branch
git checkout main

# Merge branch
git merge feature-name

# Hapus branch
git branch -d feature-name
```

---

## 🐛 Troubleshooting

### Error: "Permission denied (publickey)"
**Solusi:**
```bash
# Setup SSH key
ssh-keygen -t ed25519 -C "your-email@example.com"

# Add SSH key ke GitHub
cat ~/.ssh/id_ed25519.pub
# Copy output dan paste ke GitHub Settings → SSH Keys
```

### Error: "Repository not found"
**Solusi:**
```bash
# Cek remote URL
git remote -v

# Update remote URL
git remote set-url origin https://github.com/ahwine/retroloved.git
```

### Error: "Failed to push some refs"
**Solusi:**
```bash
# Pull dulu
git pull origin main --rebase

# Lalu push
git push origin main
```

### Error: "Large files detected"
**Solusi:**
```bash
# Hapus file besar dari git history
git filter-branch --force --index-filter \
  "git rm --cached --ignore-unmatch path/to/large/file" \
  --prune-empty --tag-name-filter cat -- --all

# Force push
git push origin main --force
```

---

## ✅ Verifikasi Setelah Upload

### Cek di GitHub Web
1. Buka https://github.com/ahwine/retroloved
2. Pastikan semua file ter-upload
3. Cek README.md tampil dengan baik
4. Cek struktur folder benar

### Cek Clone Fresh
```bash
# Clone ke folder baru
git clone https://github.com/ahwine/retroloved.git test-clone
cd test-clone

# Cek file lengkap
ls -la

# Test composer install
composer install

# Test website
php -S localhost:8000
```

---

## 📊 Statistik Repository

Setelah upload, repository Anda akan memiliki:
- **~100+ files** (PHP, CSS, JS, Images)
- **~10,000+ lines of code**
- **Complete e-commerce system**
- **Professional documentation**

---

## 🎉 Selesai!

Setelah upload berhasil, repository Anda siap untuk:
- ✅ Di-clone oleh orang lain
- ✅ Dijadikan portfolio
- ✅ Di-deploy ke hosting
- ✅ Dikembangkan lebih lanjut

**Repository URL:** https://github.com/ahwine/retroloved

---

## 📞 Butuh Bantuan?

Jika ada masalah saat upload:
1. Cek dokumentasi Git: https://git-scm.com/doc
2. Cek GitHub Docs: https://docs.github.com
3. Contact: andreabdilillah67@gmail.com
