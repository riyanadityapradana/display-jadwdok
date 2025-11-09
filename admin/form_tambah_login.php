<?php
session_start();
require_once '../config/database.php';

// Cek login
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Admin Baru - Jadwal Dokter</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .sidebar {
            background: linear-gradient(180deg, #343a40 0%, #495057 100%);
            min-height: 100vh;
            color: white;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover { color: white; background: rgba(255,255,255,0.1); }
        .sidebar .nav-link.active { background: #007bff; color: white; }
        .main-content { margin-left: 250px; }
        .card { border-radius: 15px; border: none; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .form-container { background: white; border-radius: 15px; padding: 30px; margin-bottom: 30px; }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar position-fixed" style="width: 250px;">
        <div class="p-3">
            <h4 class="text-center mb-4">
                <i class="fas fa-calendar-check"></i> Admin Panel
            </h4>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="dokter.php">
                        <i class="fas fa-user-md"></i> Manajemen Dokter
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="poli.php">
                        <i class="fas fa-clinic-medical"></i> Manajemen Poli
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="jadwal.php">
                        <i class="fas fa-calendar-alt"></i> Manajemen Jadwal
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="form_tambah_login.php">
                        <i class="fas fa-user-plus"></i> Tambah Admin
                    </a>
                </li>
                <li class="nav-item mt-3">
                    <a class="nav-link text-danger" href="logout.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid px-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-user-plus"></i> Tambah Admin Baru</h2>
                <a href="dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                </a>
            </div>

            <div class="form-container">
                <form action="proses_tambah_user.php" method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" name="nama_lengkap" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Level</label>
                            <select class="form-control" name="level" required>
                                <option value="">-- Pilih Level --</option>
                                <option value="Admin">Admin</option>
                                <option value="Super Admin">Super Admin</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kode Admin (Opsional)</label>
                        <input type="text" class="form-control" name="kode_admin" placeholder="Contoh: ADM001, SA001, dll">
                    </div>

                    <div class="d-grid">
                        <button type="submit" name="simpan" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Simpan Admin Baru
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
