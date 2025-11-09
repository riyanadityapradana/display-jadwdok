<?php
session_start();
require_once '../config/database.php';

// Cek login
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Fungsi CRUD Jadwal
function getAllJadwal($pdo) {
    $stmt = $pdo->prepare("
        SELECT j.*, d.nama as nama_dokter, d.gelar, p.nama_poli
        FROM jadwal j
        JOIN dokter d ON j.dokter_id = d.id
        JOIN poli p ON j.poli_id = p.id
        ORDER BY j.hari, j.jam_mulai
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getJadwalById($pdo, $id) {
    $stmt = $pdo->prepare("
        SELECT j.*, d.nama as nama_dokter, d.gelar, p.nama_poli
        FROM jadwal j
        JOIN dokter d ON j.dokter_id = d.id
        JOIN poli p ON j.poli_id = p.id
        WHERE j.id = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getAllDokter($pdo) {
    $stmt = $pdo->query("SELECT id, nama, gelar FROM dokter ORDER BY nama");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAllPoli($pdo) {
    $stmt = $pdo->query("SELECT id, nama_poli FROM poli ORDER BY nama_poli");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function tambahJadwal($pdo, $data) {
    $jenis_pelayanan = isset($data['jenis_pelayanan']) ? implode(',', $data['jenis_pelayanan']) : 'UMUM';
    $stmt = $pdo->prepare("INSERT INTO jadwal (dokter_id, poli_id, hari, jam_mulai, jam_selesai, tanggal_berlaku, jenis_pelayanan, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    return $stmt->execute([
        $data['dokter_id'],
        $data['poli_id'],
        $data['hari'],
        $data['jam_mulai'],
        $data['jam_selesai'],
        $data['tanggal_berlaku'],
        $jenis_pelayanan,
        $data['status']
    ]);
}

function updateJadwal($pdo, $id, $data) {
    $jenis_pelayanan = isset($data['jenis_pelayanan']) ? implode(',', $data['jenis_pelayanan']) : 'UMUM';
    $stmt = $pdo->prepare("UPDATE jadwal SET dokter_id = ?, poli_id = ?, hari = ?, jam_mulai = ?, jam_selesai = ?, tanggal_berlaku = ?, jenis_pelayanan = ?, status = ? WHERE id = ?");
    return $stmt->execute([
        $data['dokter_id'],
        $data['poli_id'],
        $data['hari'],
        $data['jam_mulai'],
        $data['jam_selesai'],
        $data['tanggal_berlaku'],
        $jenis_pelayanan,
        $data['status'],
        $id
    ]);
}

function hapusJadwal($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM jadwal WHERE id = ?");
    return $stmt->execute([$id]);
}

function nonaktifkanJadwal($pdo, $id) {
    $stmt = $pdo->prepare("UPDATE jadwal SET status = 'nonaktif' WHERE id = ?");
    return $stmt->execute([$id]);
}

// Handle POST requests
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'tambah':
                if (tambahJadwal($pdo, $_POST)) {
                    $message = 'Jadwal berhasil ditambahkan';
                    $messageType = 'success';
                } else {
                    $message = 'Gagal menambahkan jadwal';
                    $messageType = 'danger';
                }
                break;

            case 'edit':
                if (updateJadwal($pdo, $_POST['id'], $_POST)) {
                    $message = 'Jadwal berhasil diperbarui';
                    $messageType = 'success';
                } else {
                    $message = 'Gagal memperbarui jadwal';
                    $messageType = 'danger';
                }
                break;

            case 'hapus':
                if (hapusJadwal($pdo, $_POST['id'])) {
                    $message = 'Jadwal berhasil dihapus';
                    $messageType = 'success';
                } else {
                    $message = 'Gagal menghapus jadwal';
                    $messageType = 'danger';
                }
                break;

            case 'nonaktifkan':
                if (nonaktifkanJadwal($pdo, $_POST['id'])) {
                    $message = 'Jadwal berhasil dinonaktifkan';
                    $messageType = 'success';
                } else {
                    $message = 'Gagal menonaktifkan jadwal';
                    $messageType = 'danger';
                }
                break;
        }
    }
}

// Get data
$jadwal = getAllJadwal($pdo);
$dokter = getAllDokter($pdo);
$poli = getAllPoli($pdo);
$editJadwal = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $editJadwal = getJadwalById($pdo, $_GET['id']);
}

$hariOptions = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Jadwal - Admin</title>
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
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
        .status-badge { padding: 4px 8px; border-radius: 12px; font-size: 0.8em; }
        .status-aktif { background: #d4edda; color: #155724; }
        .status-nonaktif { background: #f8d7da; color: #721c24; }
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
                    <a class="nav-link active" href="jadwal.php">
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
                <h2><i class="fas fa-calendar-alt"></i> Manajemen Jadwal</h2>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                    <i class="fas fa-plus"></i> Tambah Jadwal
                </button>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <i class="fas fa-info-circle"></i> <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- Filter Hari -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-9">
                            <h5 class="mb-0"><i class="fas fa-filter"></i> Filter Data</h5>
                        </div>
                        <div class="col-md-3 text-end">
                            <div class="d-flex align-items-center">
                                <label class="me-2 mb-0"><strong>Filter Hari:</strong></label>
                                <select class="form-select form-select-sm" id="filterHari" style="width: auto;">
                                    <option value="">Semua Hari</option>
                                    <?php foreach ($hariOptions as $hari): ?>
                                        <option value="<?php echo $hari; ?>"><?php echo $hari; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabel Jadwal -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped" id="example1">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Dokter</th>
                                    <th>Poli</th>
                                    <th>Hari</th>
                                    <th>Jam</th>
                                    <th>Tanggal Berlaku</th>
                                    <th>Jenis Pelayanan</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($jadwal as $j): ?>
                                    <tr>
                                        <td><?php echo $j['id']; ?></td>
                                        <td><?php echo htmlspecialchars($j['nama_dokter'] . ($j['gelar'] ? ', ' . $j['gelar'] : '')); ?></td>
                                        <td><?php echo htmlspecialchars($j['nama_poli']); ?></td>
                                        <td><?php echo htmlspecialchars($j['hari']); ?></td>
                                        <td><?php echo date('H:i', strtotime($j['jam_mulai'])) . ' - ' . date('H:i', strtotime($j['jam_selesai'])); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($j['tanggal_berlaku'])); ?></td>
                                        <td>
                                            <?php
                                            $pelayanan = explode(',', $j['jenis_pelayanan']);
                                            foreach ($pelayanan as $jenis):
                                                $class = 'badge bg-' . (strtolower($jenis) == 'bpjs' ? 'success' : (strtolower($jenis) == 'umum' ? 'primary' : 'warning'));
                                            ?>
                                                <span class="<?php echo $class; ?> me-1"><?php echo $jenis; ?></span>
                                            <?php endforeach; ?>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?php echo $j['status']; ?>">
                                                <?php echo ucfirst($j['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="?action=edit&id=<?php echo $j['id']; ?>" class="btn btn-warning btn-action">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <button type="button" class="btn btn-secondary btn-action" 
                                                    onclick="nonaktifkanJadwal(<?php echo $j['id']; ?>)">
                                                <i class="fas fa-pause"></i> Nonaktif
                                            </button>
                                            <button type="button" class="btn btn-danger btn-action" 
                                                    onclick="hapusJadwal(<?php echo $j['id']; ?>)">
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

    <!-- Modal Tambah Jadwal -->
    <div class="modal fade" id="modalTambah" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus"></i> Tambah Jadwal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="tambah">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Dokter</label>
                                <select class="form-control select2-dokter" name="dokter_id" required>
                                    <option value="">Pilih Dokter</option>
                                    <?php foreach ($dokter as $d): ?>
                                        <option value="<?php echo $d['id']; ?>">
                                            <?php echo htmlspecialchars($d['nama'] . ($d['gelar'] ? ', ' . $d['gelar'] : '')); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Poli</label>
                                <select class="form-control select2-poli" name="poli_id" required>
                                    <option value="">Pilih Poli</option>
                                    <?php foreach ($poli as $p): ?>
                                        <option value="<?php echo $p['id']; ?>">
                                            <?php echo htmlspecialchars($p['nama_poli']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Hari</label>
                                <select class="form-control select2-hari" name="hari" required>
                                    <option value="">Pilih Hari</option>
                                    <?php foreach ($hariOptions as $hari): ?>
                                        <option value="<?php echo $hari; ?>"><?php echo $hari; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Jam Mulai</label>
                                <input type="time" class="form-control" name="jam_mulai" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Jam Selesai</label>
                                <input type="time" class="form-control" name="jam_selesai" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Berlaku</label>
                                <input type="date" class="form-control" name="tanggal_berlaku" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-control" name="status" required>
                                    <option value="aktif">Aktif</option>
                                    <option value="nonaktif">Nonaktif</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Jenis Pelayanan</label>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="jenis_pelayanan[]" value="BPJS" id="bpjs">
                                            <label class="form-check-label" for="bpjs">
                                                <span class="badge bg-success">BPJS</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="jenis_pelayanan[]" value="UMUM" id="umum" checked>
                                            <label class="form-check-label" for="umum">
                                                <span class="badge bg-primary">UMUM</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="jenis_pelayanan[]" value="ASURANSI" id="asuransi">
                                            <label class="form-check-label" for="asuransi">
                                                <span class="badge bg-warning">ASURANSI</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
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

    <!-- Modal Edit Jadwal -->
    <?php if ($editJadwal): ?>
    <div class="modal fade show" id="modalEdit" tabindex="-1" style="display: block;">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Jadwal</h5>
                    <a href="jadwal.php" class="btn-close"></a>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" value="<?php echo $editJadwal['id']; ?>">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Dokter</label>
                                <select class="form-control select2-dokter" name="dokter_id" required>
                                    <option value="">Pilih Dokter</option>
                                    <?php foreach ($dokter as $d): ?>
                                        <option value="<?php echo $d['id']; ?>" <?php echo ($d['id'] == $editJadwal['dokter_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($d['nama'] . ($d['gelar'] ? ', ' . $d['gelar'] : '')); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Poli</label>
                                <select class="form-control select2-poli" name="poli_id" required>
                                    <option value="">Pilih Poli</option>
                                    <?php foreach ($poli as $p): ?>
                                        <option value="<?php echo $p['id']; ?>" <?php echo ($p['id'] == $editJadwal['poli_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($p['nama_poli']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Hari</label>
                                <select class="form-control select2-hari" name="hari" required>
                                    <option value="">Pilih Hari</option>
                                    <?php foreach ($hariOptions as $hari): ?>
                                        <option value="<?php echo $hari; ?>" <?php echo ($hari == $editJadwal['hari']) ? 'selected' : ''; ?>><?php echo $hari; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Jam Mulai</label>
                                <input type="time" class="form-control" name="jam_mulai" value="<?php echo $editJadwal['jam_mulai']; ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Jam Selesai</label>
                                <input type="time" class="form-control" name="jam_selesai" value="<?php echo $editJadwal['jam_selesai']; ?>" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Berlaku</label>
                                <input type="date" class="form-control" name="tanggal_berlaku" value="<?php echo $editJadwal['tanggal_berlaku']; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-control" name="status" required>
                                    <option value="aktif" <?php echo ($editJadwal['status'] == 'aktif') ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="nonaktif" <?php echo ($editJadwal['status'] == 'nonaktif') ? 'selected' : ''; ?>>Nonaktif</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Jenis Pelayanan</label>
                                <div class="row">
                                    <?php
                                    $selected_pelayanan = explode(',', $editJadwal['jenis_pelayanan']);
                                    ?>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="jenis_pelayanan[]" value="BPJS" id="edit_bpjs"
                                                   <?php echo in_array('BPJS', $selected_pelayanan) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="edit_bpjs">
                                                <span class="badge bg-success">BPJS</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="jenis_pelayanan[]" value="UMUM" id="edit_umum"
                                                   <?php echo in_array('UMUM', $selected_pelayanan) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="edit_umum">
                                                <span class="badge bg-primary">UMUM</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="jenis_pelayanan[]" value="ASURANSI" id="edit_asuransi"
                                                   <?php echo in_array('ASURANSI', $selected_pelayanan) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="edit_asuransi">
                                                <span class="badge bg-warning">ASURANSI</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="jadwal.php" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
    <?php endif; ?>

    <!-- Form Actions -->
    <form id="formHapus" method="POST" style="display: none;">
        <input type="hidden" name="action" value="hapus">
        <input type="hidden" name="id" id="hapusId">
    </form>
    <form id="formNonaktifkan" method="POST" style="display: none;">
        <input type="hidden" name="action" value="nonaktifkan">
        <input type="hidden" name="id" id="nonaktifkanId">
    </form>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <!-- Toastr -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            // Inisialisasi Select2 untuk form modal
            $('.select2-dokter').select2({
                theme: 'bootstrap-5',
                placeholder: 'Cari dan pilih dokter...',
                allowClear: true,
                width: '100%'
            });

            $('.select2-poli').select2({
                theme: 'bootstrap-5',
                placeholder: 'Cari dan pilih poli...',
                allowClear: true,
                width: '100%'
            });

            $('.select2-hari').select2({
                theme: 'bootstrap-5',
                placeholder: 'Pilih hari...',
                allowClear: true,
                width: '100%',
                minimumResultsForSearch: Infinity // Disable search untuk hari
            });

            // Inisialisasi DataTable dengan pengaturan custom
            var table = $('#example1').DataTable({
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

            // Filter berdasarkan hari
            $('#filterHari').on('change', function() {
                var hari = $(this).val();
                if (hari) {
                    table.column(3).search(hari).draw(); // Kolom ke-4 (index 3) adalah kolom Hari
                } else {
                    table.column(3).search('').draw();
                }
            });

            // Reinisialisasi Select2 setelah modal ditampilkan
            $('#modalTambah, #modalEdit').on('shown.bs.modal', function () {
                $(this).find('.select2-dokter, .select2-poli, .select2-hari').select2({
                    theme: 'bootstrap-5',
                    width: '100%'
                });
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

        function hapusJadwal(id) {
            if (confirm('Apakah Anda yakin ingin menghapus jadwal ini?')) {
                document.getElementById('hapusId').value = id;
                document.getElementById('formHapus').submit();
            }
        }

        function nonaktifkanJadwal(id) {
            if (confirm('Apakah Anda yakin ingin menonaktifkan jadwal ini?')) {
                document.getElementById('nonaktifkanId').value = id;
                document.getElementById('formNonaktifkan').submit();
            }
        }
    </script>
</body>
</html>