<?php
session_start();
require_once '../config/database.php';

// Cek login admin
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

if (isset($_POST['simpan'])) {
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $username     = trim($_POST['username']);
    $password     = $_POST['password'];
    $level        = $_POST['level'];
    $kode_admin   = trim($_POST['kode_pokja']); // Form menggunakan 'kode_pokja', tapi database menggunakan 'kode_admin'

    // Validasi input
    if (empty($nama_lengkap) || empty($username) || empty($password) || empty($level)) {
        echo "<script>
                alert('Semua field wajib diisi!');
                window.location = 'form_tambah_login.php';
              </script>";
        exit;
    }

    // Hash password sebelum disimpan
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Debug: tampilkan data yang akan disimpan
        error_log("Data yang akan disimpan: " . print_r([
            'username' => $username,
            'password' => substr($hashed_password, 0, 20) . '...',
            'nama_lengkap' => $nama_lengkap,
            'level' => $level,
            'kode_admin' => $kode_admin
        ], true));

        // Cek apakah username sudah ada
        $stmt = $pdo->prepare("SELECT id FROM admin WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->rowCount() > 0) {
            echo "<script>
                    alert('Username sudah digunakan, silakan pilih yang lain!');
                    window.location = 'form_tambah_login.php';
                  </script>";
            exit;
        }

        // Simpan ke database
        $stmt = $pdo->prepare("INSERT INTO admin (username, password, nama_lengkap, level, kode_admin, created_at)
                              VALUES (?, ?, ?, ?, ?, NOW())");

        $result = $stmt->execute([$username, $hashed_password, $nama_lengkap, $level, $kode_admin]);

        if ($result) {
            echo "<script>
                    alert('Admin baru berhasil ditambahkan!');
                    window.location = 'dashboard.php';
                  </script>";
        } else {
            echo "<script>
                    alert('Gagal menyimpan data: Execute gagal');
                    window.location = 'form_tambah_login.php';
                  </script>";
        }

    } catch(PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo "<script>
                alert('Gagal menyimpan data: " . addslashes($e->getMessage()) . "');
                window.location = 'form_tambah_login.php';
              </script>";
    }
} else {
    // Redirect jika akses langsung
    header('Location: form_tambah_login.php');
    exit;
}
?>
