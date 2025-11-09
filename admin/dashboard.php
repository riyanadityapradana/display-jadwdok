<?php
session_start();
require_once '../config/database.php';

// Cek login
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Fungsi untuk mendapatkan data
function getStats($pdo) {
    $stats = [];

    // Total dokter
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM dokter");
    $stats['total_dokter'] = $stmt->fetch()['total'];

    // Total poli
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM poli");
    $stats['total_poli'] = $stmt->fetch()['total'];

    // Total jadwal aktif
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM jadwal WHERE status = 'aktif'");
    $stats['total_jadwal'] = $stmt->fetch()['total'];

    // Jadwal hari ini
    $hariIni = date('N');
    $hariNama = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'][$hariIni - 1];
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM jadwal WHERE hari = ? AND status = 'aktif'");
    $stmt->execute([$hariNama]);
    $stats['jadwal_hari_ini'] = $stmt->fetch()['total'];

    return $stats;
}

$stats = getStats($pdo);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Jadwal Dokter</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
        }
        .sidebar {
            background: linear-gradient(180deg, #343a40 0%, #495057 100%);
            min-height: 100vh;
            color: white;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover {
            color: white;
            background: rgba(255,255,255,0.1);
        }
        .sidebar .nav-link.active {
            background: #007bff;
            color: white;
        }
        .main-content {
            margin-left: 250px;
        }
        .stat-card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }
        .header {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 30px;
        }
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
                    <a class="nav-link active" href="dashboard.php">
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
                    <a class="nav-link" href="form_tambah_login.php">
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
        <div class="header">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-tachometer-alt"></i> Dashboard</h2>
                <div>
                    <span class="text-muted">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                </div>
            </div>
        </div>

        <div class="container-fluid px-4">
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card stat-card text-white bg-primary">
                        <div class="card-body text-center">
                            <div class="stat-icon mb-2">
                                <i class="fas fa-user-md"></i>
                            </div>
                            <h3><?php echo $stats['total_dokter']; ?></h3>
                            <p class="mb-0">Total Dokter</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="card stat-card text-white bg-success">
                        <div class="card-body text-center">
                            <div class="stat-icon mb-2">
                                <i class="fas fa-clinic-medical"></i>
                            </div>
                            <h3><?php echo $stats['total_poli']; ?></h3>
                            <p class="mb-0">Total Poli</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="card stat-card text-white bg-info">
                        <div class="card-body text-center">
                            <div class="stat-icon mb-2">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <h3><?php echo $stats['total_jadwal']; ?></h3>
                            <p class="mb-0">Jadwal Aktif</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="card stat-card text-white bg-warning">
                        <div class="card-body text-center">
                            <div class="stat-icon mb-2">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                            <h3><?php echo $stats['jadwal_hari_ini']; ?></h3>
                            <p class="mb-0">Jadwal Hari Ini</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-bolt"></i> Aksi Cepat</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <a href="dokter.php?action=tambah" class="btn btn-primary w-100">
                                        <i class="fas fa-plus"></i> Tambah Dokter
                                    </a>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <a href="poli.php?action=tambah" class="btn btn-success w-100">
                                        <i class="fas fa-plus"></i> Tambah Poli
                                    </a>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <a href="jadwal.php?action=tambah" class="btn btn-info w-100">
                                        <i class="fas fa-plus"></i> Tambah Jadwal
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            // Inisialisasi DataTable dengan pengaturan custom
            $('#example1').DataTable({
                lengthChange: true,
                paging: true,
                pagingType: 'numbers',
                scrollCollapse: true,
                ordering: true,
                info: true,
                language: {
                    decimal: '',
                    emptyTable: 'Tidak ada data yang tersedia pada tabel ini',
                    processing: 'Sedang memproses...',
                    lengthMenu: 'Tampilkan _MENU_ entri',
                    zeroRecords: 'Tidak ditemukan data yang sesuai',
                    info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ entri',
                    infoEmpty: 'Menampilkan 0 sampai 0 dari 0 entri',
                    infoFiltered: '(disaring dari _MAX_ entri keseluruhan)',
                    infoPostFix: '',
                    search: '',
                    searchPlaceholder: 'Cari Data..',
                    url: '',
                    paginate: {
                        first: 'Pertama',
                        previous: 'Sebelumnya',
                        next: 'Selanjutnya',
                        last: 'Terakhir'
                    }
                }
            });
            // Toastr notification
            <?php if(isset($_GET['msg'])): ?>
                toastr.options = {"positionClass": "toast-top-right", "timeOut": "3000"};
                toastr.success("<?= htmlspecialchars($_GET['msg']) ?>");
            <?php endif; ?>
            <?php if(isset($_GET['err'])): ?>
                toastr.options = {"positionClass": "toast-top-right", "timeOut": "3000"};
                toastr.error("<?= htmlspecialchars($_GET['err']) ?>");
            <?php endif; ?>
            });
        </script>
</body>
</html>