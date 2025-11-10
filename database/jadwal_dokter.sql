-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 10, 2025 at 03:32 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.0.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `jadwal_dokter`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) DEFAULT NULL,
  `level` enum('Admin','Super Admin') DEFAULT 'Admin',
  `kode_admin` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `nama_lengkap`, `level`, `kode_admin`, `created_at`, `updated_at`) VALUES
(1, 'riyan', '$2y$10$8ZZPVBnBkXmgzFR3lADDxeBbx1T1J00CnqSPKEq5GCqTTsFJhRaSi', 'Riyan Aditya Pradana, S.Kom', 'Super Admin', '', '2025-11-09 07:15:25', '2025-11-09 07:15:25');

-- --------------------------------------------------------

--
-- Table structure for table `dokter`
--

CREATE TABLE `dokter` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `gelar` varchar(50) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dokter`
--

INSERT INTO `dokter` (`id`, `nama`, `gelar`, `foto`, `deskripsi`, `created_at`, `updated_at`) VALUES
(8, 'dr. HARI SUPARJO', 'Sp.OG. M.Kes. S.H.', NULL, 'Spesialis Obstetri & Ginekologi (Kebidanan dan Kandungan)', '2025-11-10 02:01:15', '2025-11-10 02:03:01'),
(9, 'dr. FATHURRAHMAN', 'Sp.OG.,M.Kes', NULL, 'Spesialis Obstetri & Ginekologi (Kebidanan dan Kandungan)', '2025-11-10 02:03:54', '2025-11-10 02:03:54'),
(10, 'Dr. dr. I Made Dwi Jayanegara', 'Sp.N, F.MIN, MH', NULL, 'Spesialis Saraf', '2025-11-10 02:14:41', '2025-11-10 02:14:41'),
(11, 'dr. WINNY MARTALINA SIMANJUNTAK', 'Sp.S', NULL, 'Spesialis Saraf', '2025-11-10 02:17:37', '2025-11-10 02:17:37'),
(12, 'dr. H. GABRIL TAUFIQ BASRI', 'Sp.PD.,FINASIM', NULL, 'Spesialis Penyakit Dalam', '2025-11-10 02:18:39', '2025-11-10 02:18:39'),
(13, 'dr. SYAFITRI YULIANI', 'Sp. PD.,FINASIM', NULL, 'Spesialis Penyakit Dalam', '2025-11-10 02:19:20', '2025-11-10 02:19:20'),
(14, 'dr. JIMMI DIWINDANG PUTRA', 'Sp.PD', NULL, 'Spesialis Penyakit Dalam', '2025-11-10 02:20:21', '2025-11-10 02:20:21');

-- --------------------------------------------------------

--
-- Table structure for table `jadwal`
--

CREATE TABLE `jadwal` (
  `id` int(11) NOT NULL,
  `dokter_id` int(11) NOT NULL,
  `poli_id` int(11) NOT NULL,
  `hari` enum('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu') NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  `tanggal_berlaku` date NOT NULL,
  `jenis_pelayanan` enum('BPJS','UMUM','ASURANSI') DEFAULT 'UMUM',
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jadwal`
--

INSERT INTO `jadwal` (`id`, `dokter_id`, `poli_id`, `hari`, `jam_mulai`, `jam_selesai`, `tanggal_berlaku`, `jenis_pelayanan`, `status`, `created_at`, `updated_at`) VALUES
(8, 8, 2, 'Rabu', '11:00:00', '12:00:00', '2025-11-10', 'BPJS', 'aktif', '2025-11-10 02:21:28', '2025-11-10 02:21:28'),
(9, 8, 2, 'Kamis', '11:00:00', '12:00:00', '2025-11-10', 'BPJS', 'aktif', '2025-11-10 02:22:25', '2025-11-10 02:22:25'),
(10, 8, 2, 'Sabtu', '11:00:00', '12:00:00', '2025-11-10', 'BPJS', 'aktif', '2025-11-10 02:28:56', '2025-11-10 02:28:56');

-- --------------------------------------------------------

--
-- Table structure for table `poli`
--

CREATE TABLE `poli` (
  `id` int(11) NOT NULL,
  `nama_poli` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `poli`
--

INSERT INTO `poli` (`id`, `nama_poli`, `deskripsi`, `created_at`, `updated_at`) VALUES
(2, 'Poli Kandungan', 'Poli Kandungan adalah unit pelayanan yang memberikan pemeriksaan, konsultasi, dan penanganan kesehatan bagi wanita, khususnya yang berkaitan dengan sistem reproduksi, kehamilan, dan persalinan.\r\nPelayanan di poli ini ditangani oleh dokter spesialis obstetri dan ginekologi (Sp.OG) yang berpengalaman, dibantu oleh tenaga perawat serta bidan profesional.', '2025-11-09 05:06:24', '2025-11-10 02:02:43'),
(3, 'Poli Anak', 'Umum', '2025-11-09 07:44:06', '2025-11-09 07:44:06'),
(4, 'Poli Syaraf (Poliklinik Neurologi)', 'Poli Syaraf merupakan unit pelayanan yang berfokus pada diagnosis, pengobatan, dan rehabilitasi gangguan pada sistem saraf, baik otak, sumsum tulang belakang, maupun saraf tepi.\r\nPelayanan dilakukan oleh dokter spesialis saraf (Sp.S) yang berpengalaman dengan dukungan peralatan medis modern.', '2025-11-09 07:44:33', '2025-11-10 02:15:58'),
(5, 'Poli Penyakit Dalam (Poliklinik Interna)', 'Poli Penyakit Dalam memberikan pelayanan pemeriksaan dan pengobatan bagi pasien dewasa dengan berbagai penyakit yang berkaitan dengan organ dalam tubuh.\r\nDipelopori oleh dokter spesialis penyakit dalam (Sp.PD), poli ini menangani berbagai kondisi akut maupun kronis.', '2025-11-10 02:16:20', '2025-11-10 02:16:20');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `dokter`
--
ALTER TABLE `dokter`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jadwal`
--
ALTER TABLE `jadwal`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dokter_id` (`dokter_id`),
  ADD KEY `poli_id` (`poli_id`);

--
-- Indexes for table `poli`
--
ALTER TABLE `poli`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `dokter`
--
ALTER TABLE `dokter`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `jadwal`
--
ALTER TABLE `jadwal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `poli`
--
ALTER TABLE `poli`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `jadwal`
--
ALTER TABLE `jadwal`
  ADD CONSTRAINT `jadwal_ibfk_1` FOREIGN KEY (`dokter_id`) REFERENCES `dokter` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `jadwal_ibfk_2` FOREIGN KEY (`poli_id`) REFERENCES `poli` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
