# Quick Fix: QR Scanner Debug

## Masalah
QR Scanner mengatakan "QR Code salah" padahal kode sudah benar.

## Kemungkinan Penyebab

### 1. Migration Belum Dijalankan
Kolom `code` belum ada di tabel `locations`.

**Cek:**
```bash
php artisan migrate:status
```

**Fix:**
```bash
php artisan migrate
```

### 2. Location Codes Belum Di-Seed
Kolom `code` ada tapi masih NULL.

**Cek:**
```bash
php artisan tinker
>>> \App\Models\Location::whereNotNull('code')->count()
```

**Fix:**
```bash
php artisan db:seed --class=LocationCodeSeeder
```

### 3. Format QR Code Salah
QR code yang di-scan tidak match dengan format di database.

**Cek format yang benar:**
```bash
php artisan tinker
>>> \App\Models\Location::select('code')->get()
```

Harusnya muncul: LOC-001, LOC-002, dst.

## Update Terbaru

Saya sudah update `ScanController` dengan:
- ✅ Case-insensitive matching
- ✅ Trim whitespace otomatis
- ✅ Debug logging (cek `storage/logs/laravel.log`)
- ✅ Error message yang lebih informatif (menampilkan kode yang tersedia)

## Cara Test

1. **Scan QR code apapun**
2. **Lihat error message** - sekarang akan menampilkan:
   - QR code yang di-scan
   - Daftar kode yang tersedia di database
3. **Cek log** di `storage/logs/laravel.log` untuk detail

## Generate QR Code Test

Untuk test cepat, buka browser dan paste ini:

```
https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=LOC-001
```

Scan QR code yang muncul dari HP.

## Jika Masih Error

Kirim screenshot error message yang baru (yang menampilkan available codes) supaya saya bisa lihat apa yang salah.
