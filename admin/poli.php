<?php
session_start();
require_once '../config/database.php';

// Cek login
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Fungsi CRUD Poli
function getAllPoli($pdo) {
    $stmt = $pdo->query("SELECT * FROM poli ORDER BY nama_poli");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPoliById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM poli WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function tambahPoli($pdo, $data) {
    $stmt = $pdo->prepare("INSERT INTO poli (nama_poli, deskripsi) VALUES (?, ?)");
    return $stmt->execute([
        $data['nama_poli'],
        $data['deskripsi']
    ]);
}

function updatePoli($pdo, $id, $data) {
    $stmt = $pdo->prepare("UPDATE poli SET nama_poli = ?, deskripsi = ? WHERE id = ?");
    return $stmt->execute([
        $data['nama_poli'],
        $data['deskripsi'],
        $id
    ]);
}

function hapusPoli($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM poli WHERE id = ?");
    return $stmt->execute([$id]);
}

// Handle POST requests
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'tambah':
                if (tambahPoli($pdo, $_POST)) {
                    $message = 'Poli berhasil ditambahkan';
                    $messageType = 'success';
                } else {
                    $message = 'Gagal menambahkan poli';
                    $messageType = 'danger';
                }
                break;

            case 'edit':
                if (updatePoli($pdo, $_POST['id'], $_POST)) {
                    $message = 'Poli berhasil diperbarui';
                    $messageType = 'success';
                } else {
                    $message = 'Gagal memperbarui poli';
                    $messageType = 'danger';
                }
                break;

            case 'hapus':
                if (hapusPoli($pdo, $_POST['id'])) {
                    $message = 'Poli berhasil dihapus';
                    $messageType = 'success';
                } else {
                    $message = 'Gagal menghapus poli';
                    $messageType = 'danger';
                }
                break;
        }
    }
}

// Get data
$poli = getAllPoli($pdo);
$editPoli = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $editPoli = getPoliById($pdo, $_GET['id']);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Poli - Admin</title>
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <!-- Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
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
        .btn-action { border-radius: 20px; padding: 5px 12px; }
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
                    <a class="nav-link active" href="poli.php">
                        <i class="fas fa-clinic-medical"></i> Manajemen Poli
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="jadwal.php">
                        <i class="fas fa-calendar-alt"></i> Manajemen Jadwal
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
                <h2><i class="fas fa-clinic-medical"></i> Manajemen Poli</h2>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                    <i class="fas fa-plus"></i> Tambah Poli
                </button>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <i class="fas fa-info-circle"></i> <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- Tabel Poli -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped" id="example1">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Nama Poli</th>
                                    <th>Deskripsi</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($poli as $p): ?>
                                    <tr>
                                        <td><?php echo $p['id']; ?></td>
                                        <td><?php echo htmlspecialchars($p['nama_poli']); ?></td>
                                        <td><?php echo htmlspecialchars(substr($p['deskripsi'] ?: '', 0, 50) . (strlen($p['deskripsi'] ?: '') > 50 ? '...' : '')); ?></td>
                                        <td>
                                            <a href="?action=edit&id=<?php echo $p['id']; ?>" class="btn btn-warning btn-action">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <button type="button" class="btn btn-danger btn-action" 
                                                    onclick="hapusPoli(<?php echo $p['id']; ?>, '<?php echo htmlspecialchars($p['nama_poli']); ?>')">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Poli -->
    <div class="modal fade" id="modalTambah" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus"></i> Tambah Poli</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="tambah">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Poli</label>
                            <input type="text" class="form-control" name="nama_poli" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="deskripsi" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Poli -->
    <?php if ($editPoli): ?>
    <div class="modal fade show" id="modalEdit" tabindex="-1" style="display: block;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Poli</h5>
                    <a href="poli.php" class="btn-close"></a>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" value="<?php echo $editPoli['id']; ?>">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Poli</label>
                            <input type="text" class="form-control" name="nama_poli" value="<?php echo htmlspecialchars($editPoli['nama_poli']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="deskripsi" rows="3"><?php echo htmlspecialchars($editPoli['deskripsi']); ?></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="poli.php" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
    <?php endif; ?>

    <!-- Form Hapus -->
    <form id="formHapus" method="POST" style="display: none;">
        <input type="hidden" name="action" value="hapus">
        <input type="hidden" name="id" id="hapusId">
    </form>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <!-- Toastr -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
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

            // Toastr notification untuk pesan sukses/error
            <?php if(isset($_GET['msg'])): ?>
                toastr.options = {"positionClass": "toast-top-right", "timeOut": "3000"};
                toastr.success("<?= htmlspecialchars($_GET['msg']) ?>");
            <?php endif; ?>
            <?php if(isset($_GET['err'])): ?>
                toastr.options = {"positionClass": "toast-top-right", "timeOut": "3000"};
                toastr.error("<?= htmlspecialchars($_GET['err']) ?>");
            <?php endif; ?>
        });

        function hapusPoli(id, nama) {
            if (confirm('Apakah Anda yakin ingin menghapus poli "' + nama + '"?')) {
                document.getElementById('hapusId').value = id;
                document.getElementById('formHapus').submit();
            }
        }
    </script>
</body>
</html>