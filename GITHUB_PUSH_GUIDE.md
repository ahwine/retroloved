# 🚀 Panduan Push ke GitHub - RetroLoved

## 📋 Persiapan

### 1. Pastikan Git Sudah Terinstall
```bash
git --version
```

Jika belum terinstall, download dari: https://git-scm.com/

### 2. Konfigurasi Git (Jika Belum)
```bash
git config --global user.name "Your Name"
git config --global user.email "your@email.com"
```

---

## 🔧 Cara 1: Menggunakan Script Otomatis (Recommended)

### Langkah-langkah:

1. **Jalankan push_to_github.bat**
   ```
   Double-click file push_to_github.bat
   ```

2. **Ikuti Instruksi**
   - Script akan menampilkan status git
   - Masukkan commit message (atau tekan Enter untuk default)
   - Pilih branch (main atau master)
   - Script akan otomatis push ke GitHub

3. **Selesai!**
   - Jika berhasil, akan muncul pesan sukses
   - Jika gagal, akan muncul troubleshooting tips

---

## 🔧 Cara 2: Manual via Command Line

### Jika Repository Belum Diinisialisasi:

```bash
# 1. Inisialisasi git
git init

# 2. Tambahkan remote repository
git remote add origin https://github.com/ahwine/retroloved.git

# 3. Cek remote
git remote -v
```

### Push ke GitHub:

```bash
# 1. Cek status
git status

# 2. Add semua file (kecuali yang di .gitignore)
git add .

# 3. Commit
git commit -m "Update RetroLoved project - cleanup and improvements"

# 4. Push ke GitHub (pilih salah satu)
git push -u origin main
# atau
git push -u origin master
```

---

## 📁 File yang Akan Di-Push

### ✅ File yang DI-PUSH:
- ✅ Semua file PHP (admin, customer, auth, includes)
- ✅ Assets (CSS, JS, images - kecuali uploaded)
- ✅ Config examples (database.example.php, email.example.php)
- ✅ Documentation files (README.md, GITHUB_SETUP.md, dll)
- ✅ Database schema (retroloved.sql)
- ✅ Scripts (cleanup.bat, reset_database.sql, dll)
- ✅ .gitignore, .htaccess, .user.ini

### ❌ File yang TIDAK DI-PUSH (sesuai .gitignore):
- ❌ config/database.php (sensitive)
- ❌ config/email.php (sensitive)
- ❌ assets/images/products/* (uploaded images)
- ❌ assets/images/profiles/* (uploaded images)
- ❌ temp_retroloved/ (temporary folder)
- ❌ vendor/ (composer dependencies)
- ❌ node_modules/ (npm dependencies)
- ❌ *.log, *.tmp, *.bak (temporary files)
- ❌ .vscode/, .idea/ (IDE files)

---

## 🔍 Troubleshooting

### Error: "fatal: not a git repository"
**Solusi:**
```bash
git init
git remote add origin https://github.com/ahwine/retroloved.git
```

### Error: "remote origin already exists"
**Solusi:**
```bash
git remote remove origin
git remote add origin https://github.com/ahwine/retroloved.git
```

### Error: "failed to push some refs"
**Solusi 1 - Pull dulu:**
```bash
git pull origin main --rebase
git push origin main
```

**Solusi 2 - Force push (HATI-HATI!):**
```bash
git push -f origin main
```

### Error: "Authentication failed"
**Solusi:**
1. Pastikan sudah login ke GitHub
2. Gunakan Personal Access Token (bukan password)
3. Generate token di: https://github.com/settings/tokens
4. Gunakan token sebagai password saat push

### Error: "Permission denied"
**Solusi:**
1. Pastikan Anda punya akses ke repository
2. Cek apakah repository public atau private
3. Pastikan SSH key sudah di-setup (jika pakai SSH)

---

## 📊 Cek Hasil Push

### 1. Via GitHub Website
```
https://github.com/ahwine/retroloved
```

### 2. Via Command Line
```bash
# Cek remote branches
git branch -r

# Cek commit history
git log --oneline

# Cek status
git status
```

---

## 🔄 Update Selanjutnya

Setelah push pertama, untuk update selanjutnya:

```bash
# 1. Add changes
git add .

# 2. Commit
git commit -m "Your commit message"

# 3. Push (tidak perlu -u lagi)
git push origin main
```

Atau gunakan script:
```
push_to_github.bat
```

---

## 📝 Tips

### 1. Commit Message yang Baik
```bash
# Bad
git commit -m "update"

# Good
git commit -m "Add sequential image upload feature"
git commit -m "Fix delete address functionality"
git commit -m "Update Twitter icon to X logo"
```

### 2. Cek Sebelum Push
```bash
# Lihat file yang akan di-commit
git status

# Lihat perubahan detail
git diff

# Lihat file yang di-ignore
git status --ignored
```

### 3. Undo Jika Salah
```bash
# Undo add (sebelum commit)
git reset

# Undo commit (sebelum push)
git reset --soft HEAD~1

# Undo push (HATI-HATI!)
git revert HEAD
git push origin main
```

---

## ✅ Checklist Sebelum Push

- [ ] Sudah jalankan cleanup.bat
- [ ] Sudah jalankan reset_database.sql
- [ ] Config files (database.php, email.php) tidak ter-commit
- [ ] Folder temp_retroloved tidak ter-commit
- [ ] Uploaded images tidak ter-commit
- [ ] Semua file penting sudah ter-commit
- [ ] Commit message sudah jelas
- [ ] Sudah test di local

---

## 🎉 Selesai!

Setelah push berhasil, repository Anda akan tersedia di:
```
https://github.com/ahwine/retroloved
```

Siap untuk di-clone, di-fork, atau di-deploy! 🚀
