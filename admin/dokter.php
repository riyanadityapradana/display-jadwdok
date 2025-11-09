<?php
session_start();
require_once '../config/database.php';

// Cek login
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Fungsi CRUD
function getAllDokter($pdo) {
    $stmt = $pdo->query("SELECT *, CONCAT('../assets/images/', foto) as foto_url FROM dokter ORDER BY nama");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getDokterById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM dokter WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function tambahDokter($pdo, $data) {
    $stmt = $pdo->prepare("INSERT INTO dokter (nama, gelar, foto, deskripsi) VALUES (?, ?, ?, ?)");
    return $stmt->execute([
        $data['nama'],
        $data['gelar'],
        $data['foto'] ?? null,
        $data['deskripsi']
    ]);
}

function updateDokter($pdo, $id, $data) {
    $stmt = $pdo->prepare("UPDATE dokter SET nama = ?, gelar = ?, foto = COALESCE(?, foto), deskripsi = ? WHERE id = ?");
    return $stmt->execute([
        $data['nama'],
        $data['gelar'],
        $data['foto'] ?? null,
        $data['deskripsi'],
        $id
    ]);
}

function hapusDokter($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM dokter WHERE id = ?");
    return $stmt->execute([$id]);
}

// Handle POST requests
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $foto_name = '';

        // Handle file upload
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 2 * 1024 * 1024; // 2MB

            if (in_array($_FILES['foto']['type'], $allowed_types) && $_FILES['foto']['size'] <= $max_size) {
                $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
                $foto_name = 'dokter_' . time() . '_' . uniqid() . '.' . $ext;

                // Buat direktori jika belum ada
                if (!is_dir('../assets/images')) {
                    mkdir('../assets/images', 0755, true);
                }

                if (!move_uploaded_file($_FILES['foto']['tmp_name'], '../assets/images/' . $foto_name)) {
                    $message = 'Gagal mengupload foto';
                    $messageType = 'danger';
                    $foto_name = '';
                }
            } else {
                $message = 'Format foto tidak valid atau ukuran terlalu besar (max 2MB)';
                $messageType = 'danger';
            }
        }

        // Jika tidak ada error dalam upload, lanjutkan proses
        if (!isset($message) || $messageType != 'danger') {
            $postData = $_POST;
            if (!empty($foto_name)) {
                $postData['foto'] = $foto_name;
            }

            switch ($_POST['action']) {
                case 'tambah':
                    if (tambahDokter($pdo, $postData)) {
                        $message = 'Dokter berhasil ditambahkan';
                        $messageType = 'success';
                    } else {
                        $message = 'Gagal menambahkan dokter';
                        $messageType = 'danger';
                    }
                    break;

                case 'edit':
                    if (updateDokter($pdo, $_POST['id'], $postData)) {
                        $message = 'Dokter berhasil diperbarui';
                        $messageType = 'success';
                    } else {
                        $message = 'Gagal memperbarui dokter';
                        $messageType = 'danger';
                    }
                    break;

                case 'hapus':
                    if (hapusDokter($pdo, $_POST['id'])) {
                        $message = 'Dokter berhasil dihapus';
                        $messageType = 'success';
                    } else {
                        $message = 'Gagal menghapus dokter';
                        $messageType = 'danger';
                    }
                    break;
            }
        }
    }
}

// Get data
$dokter = getAllDokter($pdo);
$editDokter = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $editDokter = getDokterById($pdo, $_GET['id']);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Dokter - Admin</title>
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
                    <a class="nav-link active" href="dokter.php">
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
                <h2><i class="fas fa-user-md"></i> Manajemen Dokter</h2>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                    <i class="fas fa-plus"></i> Tambah Dokter
                </button>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <i class="fas fa-info-circle"></i> <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- Tabel Dokter -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped" id="example1">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Foto</th>
                                    <th>Nama</th>
                                    <th>Gelar</th>
                                    <th>Deskripsi</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dokter as $d): ?>
                                    <tr>
                                        <td><?php echo $d['id']; ?></td>
                                        <td>
                                            <?php if (!empty($d['foto']) && file_exists('../assets/images/' . $d['foto'])): ?>
                                                <img src="../assets/images/<?php echo htmlspecialchars($d['foto']); ?>"
                                                     alt="Foto <?php echo htmlspecialchars($d['nama']); ?>"
                                                     style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;">
                                            <?php else: ?>
                                                <div style="width: 50px; height: 50px; border-radius: 50%; background: #007bff; display: flex; align-items: center; justify-content: center; color: white;">
                                                    <i class="fas fa-user-md"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($d['nama']); ?></td>
                                        <td><?php echo htmlspecialchars($d['gelar'] ?: '-'); ?></td>
                                        <td><?php echo htmlspecialchars(substr($d['deskripsi'] ?: '', 0, 50) . (strlen($d['deskripsi'] ?: '') > 50 ? '...' : '')); ?></td>
                                        <td>
                                            <a href="?action=edit&id=<?php echo $d['id']; ?>" class="btn btn-warning btn-action">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <button type="button" class="btn btn-danger btn-action"
                                                    onclick="hapusDokter(<?php echo $d['id']; ?>, '<?php echo htmlspecialchars($d['nama']); ?>')">
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

    <!-- Modal Tambah Dokter -->
    <div class="modal fade" id="modalTambah" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus"></i> Tambah Dokter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="tambah">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Dokter</label>
                            <input type="text" class="form-control" name="nama" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Gelar</label>
                            <input type="text" class="form-control" name="gelar" placeholder="Contoh: Sp.PD, Sp.OG, dll">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Foto Dokter</label>
                            <input type="file" class="form-control" name="foto" accept="image/*">
                            <small class="form-text text-muted">Format: JPG, PNG, GIF. Maksimal 2MB</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="deskripsi" rows="3" placeholder="Deskripsi singkat tentang dokter"></textarea>
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

    <!-- Modal Edit Dokter -->
    <?php if ($editDokter): ?>
    <div class="modal fade show" id="modalEdit" tabindex="-1" style="display: block;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Dokter</h5>
                    <a href="dokter.php" class="btn-close"></a>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" value="<?php echo $editDokter['id']; ?>">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Dokter</label>
                            <input type="text" class="form-control" name="nama" value="<?php echo htmlspecialchars($editDokter['nama']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Gelar</label>
                            <input type="text" class="form-control" name="gelar" value="<?php echo htmlspecialchars($editDokter['gelar']); ?>" placeholder="Contoh: Sp.PD, Sp.OG, dll">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Foto Dokter</label>
                            <input type="file" class="form-control" name="foto" accept="image/*">
                            <small class="form-text text-muted">Biarkan kosong jika tidak ingin mengubah foto. Format: JPG, PNG, GIF. Maksimal 2MB</small>
                            <?php if (!empty($editDokter['foto'])): ?>
                                <div class="mt-2">
                                    <small>Foto saat ini:</small><br>
                                    <img src="../assets/images/<?php echo htmlspecialchars($editDokter['foto']); ?>" alt="Foto saat ini" style="width: 100px; height: 100px; object-fit: cover; border-radius: 10px;">
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="deskripsi" rows="3" placeholder="Deskripsi singkat tentang dokter"><?php echo htmlspecialchars($editDokter['deskripsi']); ?></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="dokter.php" class="btn btn-secondary">Batal</a>
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
    <script>
        function hapusDokter(id, nama) {
            if (confirm('Apakah Anda yakin ingin menghapus dokter "' + nama + '"?')) {
                document.getElementById('hapusId').value = id;
                document.getElementById('formHapus').submit();
            }
        }
    </script>
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
    </script>
</body>
</html>