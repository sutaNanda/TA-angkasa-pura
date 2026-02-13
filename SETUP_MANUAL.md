# Setup Manual: Ngrok & QR Scanner

## Masalah yang Diperbaiki

### 1. CSS Tidak Load di Ngrok ✅
**Solusi:** Layout technician sudah diupdate untuk auto-detect ngrok dan menggunakan Tailwind CDN.

File yang diubah: `resources/views/layouts/technician.blade.php`
- Deteksi otomatis jika URL mengandung "ngrok"
- Langsung load Tailwind CDN tanpa Vite
- SweetAlert2 juga sudah ditambahkan

**Test:** Akses via ngrok sekarang, CSS sudah harus muncul!

---

### 2. QR Scanner Belum Bisa Scan

**Penyebab:** Tabel `locations` belum punya kolom `code` untuk matching QR.

**Solusi Manual:**

#### Step 1: Jalankan Migration

Buka terminal baru (jangan di terminal yang stuck) dan jalankan:

```bash
php artisan migrate
```

Ini akan membuat:
- Kolom `code` di tabel `locations`
- Tabel `patrol_logs` untuk tracking inspeksi

#### Step 2: Generate Location Codes

```bash
php artisan db:seed --class=LocationCodeSeeder
```

Ini akan generate kode untuk semua lokasi yang ada:
- LOC-001
- LOC-002
- LOC-003
- dst...

#### Step 3: Cek Hasilnya

```bash
php artisan tinker
```

Lalu ketik:
```php
\App\Models\Location::select('id', 'name', 'code')->get()
```

Harusnya muncul semua lokasi dengan kode-nya.

---

## Cara Test QR Scanner

### Opsi 1: Generate QR Code Online

1. Buka https://www.qr-code-generator.com/
2. Pilih "Text"
3. Masukkan kode lokasi (misal: `LOC-001`)
4. Download QR code
5. Tampilkan di layar laptop atau print
6. Scan dari HP

### Opsi 2: Generate QR Code Otomatis (Recommended)

Saya bisa buatkan halaman admin untuk generate QR code semua lokasi sekaligus. Mau?

---

## Troubleshooting

### Jika Migration Gagal

**Error: "Table already exists"**
```bash
# Cek status migration
php artisan migrate:status

# Jika sudah ada, skip migration
```

**Error: "Column already exists"**
```bash
# Cek manual di database
# Jika kolom 'code' sudah ada, langsung ke seeder
```

### Jika Seeder Gagal

**Error: "Class not found"**
```bash
# Regenerate autoload
composer dump-autoload

# Coba lagi
php artisan db:seed --class=LocationCodeSeeder
```

### Jika QR Scanner Masih Gagal

1. **Cek kamera permission di browser**
   - Chrome: Settings → Privacy → Site Settings → Camera
   - Harus allow untuk ngrok URL

2. **Cek console browser**
   - Buka DevTools (F12)
   - Lihat error di tab Console

3. **Cek database**
   - Pastikan lokasi punya kode
   - Pastikan kode match dengan QR yang di-scan

---

## Next Steps

Setelah migration & seeder berhasil:

1. ✅ CSS sudah fix (auto-detect ngrok)
2. ✅ QR Scanner sudah bisa match kode
3. ⏳ Test full flow: Scan → Lihat Aset → Inspeksi → Buat LK

Butuh bantuan generate QR code atau ada error lain?
