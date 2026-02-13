# SOLUSI CEPAT: QR Code Masih Pakai ID

## Masalah
QR code di admin panel masih menampilkan ID (angka) bukan code (LOC-001).

## Penyebab
Kemungkinan:
1. Migration belum dijalankan → kolom `code` belum ada
2. Migration sudah jalan tapi kolom `code` masih NULL → belum di-seed

## Solusi Tercepat

### Opsi 1: Jalankan SQL Langsung (RECOMMENDED)

1. **Buka phpMyAdmin atau MySQL Workbench**

2. **Pilih database `db_asset_monitoring`**

3. **Jalankan SQL ini:**

```sql
-- Cek apakah kolom code sudah ada
SHOW COLUMNS FROM locations LIKE 'code';

-- Jika belum ada, tambahkan kolom
ALTER TABLE locations ADD COLUMN code VARCHAR(50) NULL UNIQUE AFTER id;
ALTER TABLE locations ADD INDEX idx_code (code);

-- Populate codes
SET @counter = 0;
UPDATE locations 
SET code = CONCAT('LOC-', LPAD((@counter := @counter + 1), 3, '0'))
WHERE code IS NULL
ORDER BY id;

-- Verify
SELECT id, name, code FROM locations;
```

4. **Refresh halaman admin** (Ctrl+F5)

5. **Pilih lokasi** - QR code sekarang harusnya LOC-001

6. **Scan dari HP** - harusnya berhasil!

### Opsi 2: Via Artisan (Jika Terminal Berfungsi)

```bash
# Terminal 1 - Migration
php artisan migrate

# Terminal 2 - Seed codes
php artisan db:seed --class=LocationCodeSeeder
```

### Opsi 3: Manual via Tinker

```bash
php artisan tinker
```

Lalu paste ini:

```php
$locations = \App\Models\Location::whereNull('code')->get();
$index = 1;
foreach($locations as $loc) {
    $loc->code = 'LOC-' . str_pad($index, 3, '0', STR_PAD_LEFT);
    $loc->save();
    echo "Updated: {$loc->name} -> {$loc->code}\n";
    $index++;
}
```

## Verifikasi

Setelah jalankan salah satu opsi di atas:

1. **Cek database:**
```sql
SELECT id, name, code FROM locations;
```

Harusnya muncul:
```
id | name          | code
1  | Ruang Server  | LOC-001
2  | Meeting A     | LOC-002
...
```

2. **Refresh admin panel** (Ctrl+F5)

3. **Pilih lokasi** - lihat QR code di header

4. **Scan QR code** - harusnya berhasil!

## Jika Masih Gagal

Kirim screenshot:
1. Hasil query: `SELECT id, name, code FROM locations LIMIT 3;`
2. QR code yang muncul di admin panel
3. Error message saat scan

Saya akan bantu debug lebih lanjut!
