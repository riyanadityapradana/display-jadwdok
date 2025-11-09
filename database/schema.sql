-- Skema Database untuk Aplikasi Jadwal Dokter

-- Tabel Admin
CREATE TABLE admin (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100),
    level ENUM('Admin', 'Super Admin') DEFAULT 'Admin',
    kode_admin VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel Dokter
CREATE TABLE dokter (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama VARCHAR(100) NOT NULL,
    gelar VARCHAR(50),
    foto VARCHAR(255),
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel Poli
CREATE TABLE poli (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_poli VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel Jadwal
CREATE TABLE jadwal (
    id INT PRIMARY KEY AUTO_INCREMENT,
    dokter_id INT NOT NULL,
    poli_id INT NOT NULL,
    hari ENUM('Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu') NOT NULL,
    jam_mulai TIME NOT NULL,
    jam_selesai TIME NOT NULL,
    tanggal_berlaku DATE NOT NULL,
    jenis_pelayanan SET('BPJS', 'UMUM', 'ASURANSI') DEFAULT 'UMUM',
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (dokter_id) REFERENCES dokter(id) ON DELETE CASCADE,
    FOREIGN KEY (poli_id) REFERENCES poli(id) ON DELETE CASCADE
);

-- Insert data awal
INSERT INTO admin (username, password, nama_lengkap, level) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'Super Admin'); -- password: password

INSERT INTO dokter (nama, gelar, deskripsi) VALUES
('Dr. Ahmad Suyuti', 'Sp.PD', 'Spesialis Penyakit Dalam'),
('Dr. Siti Nurhaliza', 'Sp.OG', 'Spesialis Obstetri dan Ginekologi');

INSERT INTO poli (nama_poli, deskripsi) VALUES
('Poli Penyakit Dalam', 'Penanganan penyakit dalam'),
('Poli Kandungan', 'Pelayanan kesehatan wanita');

INSERT INTO jadwal (dokter_id, poli_id, hari, jam_mulai, jam_selesai, tanggal_berlaku, jenis_pelayanan) VALUES
(1, 1, 'Senin', '08:00:00', '12:00:00', CURDATE(), 'BPJS,UMUM'),
(1, 1, 'Rabu', '13:00:00', '17:00:00', CURDATE(), 'BPJS,ASURANSI'),
(2, 2, 'Selasa', '09:00:00', '14:00:00', CURDATE(), 'UMUM,ASURANSI');