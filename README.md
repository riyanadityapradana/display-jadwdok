# Aplikasi Jadwal Dokter

Aplikasi web untuk mengelola dan menampilkan jadwal praktik dokter poli dengan sistem manajemen admin.

## Fitur Utama

### Dashboard Publik
- Tampilan jadwal dokter yang responsif dan menarik
- Navigasi berdasarkan hari dalam seminggu
- Informasi lengkap dokter dan poli
- UI yang bersih dengan tipografi yang baik

### Sistem Admin
- Login admin yang aman
- Dashboard admin dengan statistik
- Manajemen Dokter (Tambah, Edit, Hapus)
- Manajemen Poli (Tambah, Edit, Hapus)
- Manajemen Jadwal dengan fitur:
  - Tambah jadwal baru
  - Edit jadwal existing
  - Hapus jadwal
  - Non-aktifkan jadwal (untuk mengelola perubahan jadwal)
- Manajemen Admin (Tambah admin baru dengan level dan kode admin)

## Teknologi yang Digunakan

- **Backend**: PHP 7+
- **Database**: MySQL
- **Frontend**: HTML5, CSS3, Bootstrap 5, Font Awesome
- **JavaScript**: Bootstrap JS

## Struktur Folder

```
jadwal-dokter/
├── public/
│   └── index.php          # Dashboard publik
├── admin/
│   ├── login.php          # Halaman login admin
│   ├── dashboard.php      # Dashboard admin
│   ├── logout.php         # Logout
│   ├── dokter.php         # Manajemen dokter
│   ├── poli.php          # Manajemen poli
│   ├── jadwal.php        # Manajemen jadwal
│   ├── form_tambah_login.php    # Form tambah admin
│   └── proses_tambah_user.php   # Proses tambah admin
├── config/
│   └── database.php      # Konfigurasi database
├── database/
│   └── schema.sql        # Skema database
└── assets/               # CSS, JS, Images
```

## Instalasi

1. **Setup Database**:
   ```sql
   -- Buat database baru
   CREATE DATABASE jadwal_dokter;

   -- Import skema dari database/schema.sql
   ```

2. **Konfigurasi Database**:
   - Edit file `config/database.php`
   - Sesuaikan host, database name, username, dan password

3. **Setup Web Server**:
   - Pastikan folder `jadwal-dokter` berada di root web server (misal: htdocs)
   - Akses aplikasi melalui browser:
     - Dashboard publik: `http://localhost/jadwal-dokter/public/`
     - Admin login: `http://localhost/jadwal-dokter/admin/login.php`

4. **Login Admin Default**:
   - Username: `admin`
   - Password: `password`

## Fitur Khusus

### Manajemen Jadwal
- **Tanggal Berlaku**: Menentukan kapan jadwal mulai berlaku
- **Status Aktif/Nonaktif**: Mengelola jadwal yang masih berlaku atau sudah tidak berlaku
- **Opsi Non-aktifkan**: Untuk mengubah jadwal lama menjadi non-aktif tanpa menghapus data

### UI/UX
- Desain responsif untuk semua device
- Gradient background yang menarik
- Animasi hover pada card dan button
- Typography yang jelas dan mudah dibaca
- Color coding untuk status jadwal

## Penggunaan

1. **Untuk Pasien**: Akses dashboard publik untuk melihat jadwal dokter
2. **Untuk Admin**:
   - Login ke panel admin
   - Kelola data dokter, poli, dan jadwal
   - Monitor statistik melalui dashboard

## Keamanan

- Session-based authentication untuk admin
- Password hashing menggunakan bcrypt
- Input validation dan sanitization
- SQL injection protection dengan PDO prepared statements

## Pengembangan Lanjutan

Beberapa fitur yang bisa ditambahkan di masa depan:
- Upload foto dokter
- Notifikasi perubahan jadwal
- Export jadwal ke PDF/Excel
- API untuk integrasi dengan sistem lain
- Multi-level admin user
- Log aktivitas admin