<?php
require_once '../config/database.php';

// Fungsi untuk mendapatkan jadwal berdasarkan hari
function getJadwalByHari($pdo, $hari) {
    $stmt = $pdo->prepare("
        SELECT j.*, d.nama as nama_dokter, d.gelar, d.foto, p.nama_poli
        FROM jadwal j
        JOIN dokter d ON j.dokter_id = d.id
        JOIN poli p ON j.poli_id = p.id
        WHERE j.hari = ? AND j.status = 'aktif' AND j.tanggal_berlaku <= CURDATE()
        ORDER BY j.jam_mulai
    ");
    $stmt->execute([$hari]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fungsi untuk menentukan status buka/tutup berdasarkan waktu
function getStatusPraktik($jam_mulai, $jam_selesai, $hari_jadwal, $hari_sekarang) {
    // Jika hari jadwal berbeda dengan hari sekarang, return status berdasarkan hari
    if ($hari_jadwal !== $hari_sekarang) {
        return [
            'status' => 'tutup',
            'text' => 'Tutup Hari Ini',
            'class' => 'tutup',
            'icon' => 'fas fa-times-circle'
        ];
    }

    $waktu_sekarang = date('H:i:s');
    $jam_mulai_time = strtotime($jam_mulai);
    $jam_selesai_time = strtotime($jam_selesai);
    $waktu_sekarang_time = strtotime($waktu_sekarang);

    if ($waktu_sekarang_time >= $jam_mulai_time && $waktu_sekarang_time <= $jam_selesai_time) {
        return [
            'status' => 'buka',
            'text' => 'Sedang Praktik',
            'class' => 'buka',
            'icon' => 'fas fa-check-circle'
        ];
    } elseif ($waktu_sekarang_time < $jam_mulai_time) {
        $selisih_menit = round(($jam_mulai_time - $waktu_sekarang_time) / 60);
        $text = $selisih_menit <= 60 ? "Buka {$selisih_menit} menit lagi" : "Buka pukul {$jam_mulai}";
        return [
            'status' => 'akan_buka',
            'text' => $text,
            'class' => 'akan-buka',
            'icon' => 'fas fa-clock'
        ];
    } else {
        return [
            'status' => 'tutup',
            'text' => 'Sudah Tutup',
            'class' => 'tutup',
            'icon' => 'fas fa-times-circle'
        ];
    }
}

// Fungsi untuk format jenis pelayanan
function formatJenisPelayanan($jenis_pelayanan) {
    if (empty($jenis_pelayanan)) return ['UMUM'];

    $pelayanan = explode(',', $jenis_pelayanan);
    return array_map('trim', $pelayanan);
}

// Hari ini
$hariIni = date('N'); // 1 (Senin) sampai 7 (Minggu)
$hariNama = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'][$hariIni - 1];

// Untuk tabs, aktifkan hari yang sesuai (index 0-6)
$hariAktifIndex = $hariIni - 1; // Index untuk array (0-6)

// Jadwal hari ini
$jadwalHariIni = getJadwalByHari($pdo, $hariNama);

// Semua hari
$semuaHari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
$jadwalSemua = [];
foreach ($semuaHari as $hari) {
    $jadwalSemua[$hari] = getJadwalByHari($pdo, $hari);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Jadwal Dokter</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-top: 50px;
            margin-bottom: 50px;
        }
        .header {
            background: linear-gradient(45deg, #1e3c72, #2a5298);
            color: white;
            padding: 30px;
            border-radius: 20px 20px 0 0;
            text-align: center;
        }
        .jadwal-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            overflow: hidden;
            position: relative;
        }
        .jadwal-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 123, 255, 0.1), transparent);
            transition: left 0.6s;
        }
        .jadwal-card:hover::before {
            left: 100%;
        }
        .jadwal-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }
        .jadwal-card:nth-child(odd) {
            animation: slideInUp 0.6s ease-out forwards;
            opacity: 0;
        }
        .jadwal-card:nth-child(even) {
            animation: slideInUp 0.6s 0.2s ease-out forwards;
            opacity: 0;
        }
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .hari-active {
            background: #007bff;
            color: white;
        }
        .badge {
            font-size: 0.75em;
        }
        .doctor-info {
            background: linear-gradient(135deg, rgba(248, 249, 250, 0.8) 0%, rgba(233, 236, 239, 0.8) 100%);
            backdrop-filter: blur(10px);
            padding: 15px;
            border-radius: 15px;
            margin-bottom: 10px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
        }
        .time-slot {
            background: #e3f2fd;
            padding: 8px 12px;
            border-radius: 20px;
            font-weight: bold;
            color: #1565c0;
        }
        .doctor-photo {
            width: 120px;
            height: 120px;
            border-radius: 20px;
            object-fit: cover;
            border: 4px solid transparent;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 3px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            z-index: 2;
            overflow: hidden;
        }
        .doctor-photo::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: conic-gradient(from 0deg, transparent, rgba(255,255,255,0.3), transparent);
            animation: rotate 3s linear infinite;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .doctor-photo:hover::before {
            opacity: 1;
        }
        .doctor-photo:hover {
            transform: scale(1.1) rotateY(15deg);
            box-shadow: 0 15px 40px rgba(0,123,255,0.4);
            border-color: rgba(255,255,255,0.3);
        }
        .doctor-photo img {
            width: 100%;
            height: 100%;
            border-radius: 17px;
            object-fit: cover;
            transition: all 0.5s ease;
        }
        .doctor-photo:hover img {
            transform: scale(1.05);
        }
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .doctor-card-content {
            display: flex;
            align-items: center;
            gap: 20px;
            position: relative;
        }
        .doctor-details {
            flex: 1;
            position: relative;
            z-index: 2;
        }
        .no-photo {
            width: 120px;
            height: 120px;
            border-radius: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 32px;
            font-weight: bold;
            border: 4px solid transparent;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) padding-box,
                        linear-gradient(135deg, rgba(255,255,255,0.2), rgba(255,255,255,0.1)) border-box;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            z-index: 2;
            overflow: hidden;
        }
        .no-photo::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            transition: left 0.5s ease;
        }
        .no-photo:hover::before {
            left: 100%;
        }
        .no-photo:hover {
            transform: scale(1.1) rotateY(15deg);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.4);
        }
        .card-body {
            position: relative;
            z-index: 1;
        }
        .service-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-top: 10px;
        }
        .service-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75em;
            font-weight: bold;
            border: 2px solid;
            transition: all 0.3s ease;
        }
        .service-badge.bpjs {
            background: linear-gradient(45deg, #00a65a, #28a745);
            color: white;
            border-color: #00a65a;
        }
        .service-badge.bpjs:hover {
            background: linear-gradient(45deg, #28a745, #00a65a);
            transform: scale(1.05);
        }
        .service-badge.umum {
            background: linear-gradient(45deg, #007bff, #0056b3);
            color: white;
            border-color: #007bff;
        }
        .service-badge.umum:hover {
            background: linear-gradient(45deg, #0056b3, #007bff);
            transform: scale(1.05);
        }
        .service-badge.asuransi {
            background: linear-gradient(45deg, #fd7e14, #e8590c);
            color: white;
            border-color: #fd7e14;
        }
        .service-badge.asuransi:hover {
            background: linear-gradient(45deg, #e8590c, #fd7e14);
            transform: scale(1.05);
        }
        .status-indicator {
            position: absolute;
            top: 15px;
            right: 15px;
            z-index: 3;
        }
        .status-badge {
            /* Perubahan di sini: */
            padding: 4px 8px; /* Mengurangi padding dari 6px 12px menjadi 4px 8px */
            border-radius: 15px; /* Sedikit lebih kecil dari 20px */
            font-size: 0.7em; /* Mengecilkan ukuran font dari 0.8em menjadi 0.7em */
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            border: 1px solid rgba(255,255,255,0.3); /* Mengurangi ketebalan border dari 2px menjadi 1px */
            display: flex;
            align-items: center;
            gap: 4px; /* Mengurangi jarak antar ikon dan teks dari 5px menjadi 4px */
        }
        .status-badge i {
            font-size: 0.9em; /* Mengecilkan ukuran ikon dari 1em menjadi 0.9em */
        }
        .status-badge.buka {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            animation: pulse-green 2s infinite;
        }
        .status-badge.akan-buka {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
            color: #212529;
            animation: pulse-yellow 3s infinite;
        }
        .status-badge.tutup {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
        }
        @keyframes pulse-green {
            0%, 100% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7); }
            50% { box-shadow: 0 0 0 8px rgba(40, 167, 69, 0); }
        }
        @keyframes pulse-yellow {
            0%, 100% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.7); }
            50% { box-shadow: 0 0 0 8px rgba(255, 193, 7, 0); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-calendar-check"></i> Jadwal Praktik Dokter</h1>
            <p class="mb-0">Informasi jadwal dokter poli praktik terkini</p>
            <div class="mt-3">
                <small class="text-white-50">
                    <i class="fas fa-clock"></i> Hari ini: <strong><?php echo $hariNama; ?></strong>
                </small>
            </div>
        </div>

        <div class="p-4">
            <!-- Navigasi Hari -->
            <div class="row mb-4">
                <div class="col-12">
                    <ul class="nav nav-pills justify-content-center" id="hariTabs" role="tablist">
                        <?php foreach ($semuaHari as $index => $hari): ?>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link <?php echo ($index == $hariAktifIndex) ? 'active hari-active' : ''; ?>"
                                        id="hari-<?php echo $index; ?>-tab" data-bs-toggle="pill"
                                        data-bs-target="#hari-<?php echo $index; ?>" type="button" role="tab">
                                    <?php echo $hari; ?>
                                    <?php if ($index == $hariAktifIndex): ?>
                                        <span class="badge bg-success ms-1">Hari Ini</span>
                                    <?php endif; ?>
                                </button>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <!-- Konten Jadwal -->
            <div class="tab-content" id="hariTabContent">
                <?php foreach ($semuaHari as $index => $hari): ?>
                    <div class="tab-pane fade <?php echo ($index == $hariAktifIndex) ? 'show active' : ''; ?>"
                         id="hari-<?php echo $index; ?>" role="tabpanel">
                        <div class="row">
                            <?php if (empty($jadwalSemua[$hari])): ?>
                                <div class="col-12">
                                    <div class="alert alert-info text-center">
                                        <i class="fas fa-info-circle"></i> Tidak ada jadwal praktik untuk hari <?php echo $hari; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <?php foreach ($jadwalSemua[$hari] as $index => $jadwal): ?>
                                    <?php
                                    $hari_sekarang = $hariNama;
                                    $status_praktik = getStatusPraktik($jadwal['jam_mulai'], $jadwal['jam_selesai'], $jadwal['hari'], $hari_sekarang);
                                    ?>
                                    <div class="col-md-6 col-lg-6 mb-4">
                                        <div class="card jadwal-card h-100 position-relative"
                                             style="animation-delay: <?php echo ($index * 0.1) . 's'; ?>"
                                             data-jam-mulai="<?php echo $jadwal['jam_mulai']; ?>"
                                             data-jam-selesai="<?php echo $jadwal['jam_selesai']; ?>"
                                             data-hari-jadwal="<?php echo $jadwal['hari']; ?>">
                                            <!-- Status Indicator -->
                                            <div class="status-indicator">
                                                <div class="status-badge <?php echo $status_praktik['class']; ?>">
                                                    <i class="<?php echo $status_praktik['icon']; ?>"></i>
                                                    <span><?php echo $status_praktik['text']; ?></span>
                                                </div>
                                            </div>

                                            <div class="card-body p-4">
                                                <div class="doctor-card-content">
                                                    <div class="doctor-photo-container">
                                                        <?php if (!empty($jadwal['foto']) && file_exists('../assets/images/' . $jadwal['foto'])): ?>
                                                            <div class="doctor-photo">
                                                                <img src="../assets/images/<?php echo htmlspecialchars($jadwal['foto']); ?>"
                                                                     alt="Foto <?php echo htmlspecialchars($jadwal['nama_dokter']); ?>">
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="no-photo">
                                                                <i class="fas fa-user-md"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="doctor-details">
                                                        <div class="doctor-info p-3 rounded">
                                                            <h5 class="card-title mb-3 text-primary fw-bold">
                                                                <i class="fas fa-user-md text-info me-2"></i>
                                                                <?php echo htmlspecialchars($jadwal['nama_dokter']); ?>
                                                                <?php if ($jadwal['gelar']): ?>
                                                                    <small class="text-muted">, <?php echo htmlspecialchars($jadwal['gelar']); ?></small>
                                                                <?php endif; ?>
                                                            </h5>
                                                            <div class="mb-3">
                                                                <span class="badge bg-success p-2 me-2">
                                                                    <i class="fas fa-clinic-medical me-1"></i>
                                                                    <?php echo htmlspecialchars($jadwal['nama_poli']); ?>
                                                                </span>
                                                            </div>
                                                            <div class="d-flex align-items-center justify-content-between">
                                                                <div class="time-slot d-inline-flex align-items-center px-3 py-2">
                                                                    <i class="fas fa-clock me-2"></i>
                                                                    <strong><?php echo date('H:i', strtotime($jadwal['jam_mulai'])); ?> - <?php echo date('H:i', strtotime($jadwal['jam_selesai'])); ?></strong>
                                                                </div>
                                                                <div class="service-badges">
                                                                    <?php
                                                                    $pelayanan = formatJenisPelayanan($jadwal['jenis_pelayanan']);
                                                                    foreach ($pelayanan as $jenis):
                                                                        $class = strtolower($jenis);
                                                                    ?>
                                                                        <span class="service-badge <?php echo $class; ?>">
                                                                            <?php echo htmlspecialchars($jenis); ?>
                                                                        </span>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Auto Scroll Script -->
    <script>
        // Fungsi untuk auto scroll ke bawah
        function autoScroll() {
            const cards = document.querySelectorAll('.jadwal-card');
            let currentCardIndex = 0;
            let scrollInterval;

            function scrollToCard(index) {
                if (cards[index]) {
                    cards[index].scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });

                    // Reset semua card ke opacity normal
                    cards.forEach(card => {
                        card.style.opacity = '0.7';
                        card.style.transform = 'scale(0.95)';
                    });

                    // Highlight card aktif
                    cards[index].style.opacity = '1';
                    cards[index].style.transform = 'scale(1)';

                    // Trigger animasi pada card
                    cards[index].style.animation = 'pulse 0.6s ease-in-out';
                    setTimeout(() => {
                        cards[index].style.animation = '';
                    }, 600);
                }
            }

            function nextCard() {
                scrollToCard(currentCardIndex);
                currentCardIndex = (currentCardIndex + 1) % cards.length;
            }

            // Mulai auto scroll
            scrollInterval = setInterval(nextCard, 3000); // Scroll setiap 3 detik

            // Pause saat mouse hover pada card
            cards.forEach(card => {
                card.addEventListener('mouseenter', () => {
                    clearInterval(scrollInterval);
                });

                card.addEventListener('mouseleave', () => {
                    scrollInterval = setInterval(nextCard, 3000);
                });
            });

            // Kontrol manual dengan keyboard
            document.addEventListener('keydown', (e) => {
                if (e.key === 'ArrowDown' || e.key === ' ') {
                    e.preventDefault();
                    clearInterval(scrollInterval);
                    nextCard();
                    scrollInterval = setInterval(nextCard, 3000);
                }
                if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    clearInterval(scrollInterval);
                    currentCardIndex = (currentCardIndex - 2 + cards.length) % cards.length;
                    nextCard();
                    scrollInterval = setInterval(nextCard, 3000);
                }
            });

            // Scroll otomatis ke card pertama saat load
            setTimeout(() => {
                scrollToCard(0);
            }, 500);
        }

        // Update status setiap menit
        function updateStatusIndicators() {
            const sekarang = new Date();
            const jamSekarang = sekarang.getHours().toString().padStart(2, '0') + ':' +
                               sekarang.getMinutes().toString().padStart(2, '0') + ':' +
                               sekarang.getSeconds().toString().padStart(2, '0');

            // Update status untuk semua card
            document.querySelectorAll('.jadwal-card').forEach(card => {
                const jamMulai = card.dataset.jamMulai;
                const jamSelesai = card.dataset.jamSelesai;
                const hariJadwal = card.dataset.hariJadwal;
                const hariSekarang = '<?php echo $hariNama; ?>';

                if (jamMulai && jamSelesai) {
                    let status, text, className, icon;

                    if (hariJadwal !== hariSekarang) {
                        status = 'tutup';
                        text = 'Tutup Hari Ini';
                        className = 'tutup';
                        icon = 'fas fa-times-circle';
                    } else if (jamSekarang >= jamMulai && jamSekarang <= jamSelesai) {
                        status = 'buka';
                        text = 'Sedang Praktik';
                        className = 'buka';
                        icon = 'fas fa-check-circle';
                    } else if (jamSekarang < jamMulai) {
                        const selisihMenit = Math.round((new Date('1970-01-01T' + jamMulai + ':00').getTime() - new Date('1970-01-01T' + jamSekarang + ':00').getTime()) / 60000);
                        text = selisihMenit <= 60 ? `Buka ${selisihMenit} menit lagi` : `Buka pukul ${jamMulai}`;
                        status = 'akan_buka';
                        className = 'akan-buka';
                        icon = 'fas fa-clock';
                    } else {
                        status = 'tutup';
                        text = 'Sudah Tutup';
                        className = 'tutup';
                        icon = 'fas fa-times-circle';
                    }

                    const statusBadge = card.querySelector('.status-badge');
                    if (statusBadge) {
                        statusBadge.className = `status-badge ${className}`;
                        statusBadge.innerHTML = `<i class="${icon}"></i><span>${text}</span>`;
                    }
                }
            });
        }

        // Jalankan auto scroll setelah halaman load
        window.addEventListener('load', () => {
            // Update status awal
            updateStatusIndicators();

            // Update status setiap menit
            setInterval(updateStatusIndicators, 60000); // Update setiap 60 detik

            // Tunggu sedikit untuk animasi masuk selesai
            setTimeout(autoScroll, 2000);
        });

        // Tambahkan animasi pulse
        const style = document.createElement('style');
        style.textContent = `
            @keyframes pulse {
                0% { box-shadow: 0 0 0 0 rgba(0, 123, 255, 0.7); }
                70% { box-shadow: 0 0 0 10px rgba(0, 123, 255, 0); }
                100% { box-shadow: 0 0 0 0 rgba(0, 123, 255, 0); }
            }

            .jadwal-card {
                transition: all 0.3s ease;
            }

            .auto-scroll-indicator {
                position: fixed;
                bottom: 20px;
                right: 20px;
                background: rgba(0, 123, 255, 0.9);
                color: white;
                padding: 10px 15px;
                border-radius: 25px;
                font-size: 12px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                z-index: 1000;
                display: none;
            }

            .auto-scroll-indicator.show {
                display: block;
                animation: fadeInUp 0.5s ease-out;
            }

            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        `;
        document.head.appendChild(style);

        // Tambahkan indikator auto scroll
        const indicator = document.createElement('div');
        indicator.className = 'auto-scroll-indicator';
        indicator.innerHTML = '<i class="fas fa-play"></i> Auto Scroll Aktif - Gunakan ↑↓ untuk navigasi manual';
        document.body.appendChild(indicator);

        // Tampilkan indikator setelah 3 detik
        setTimeout(() => {
            indicator.classList.add('show');
            setTimeout(() => {
                indicator.classList.remove('show');
            }, 5000);
        }, 3000);
    </script>
</body>
</html>