-- Manual schema for finance, scholarship, blocking, and RPL modules.
-- Prerequisites:
-- - Tables `mahasiswa`, `tahun_akademik`, `users`, `mata_kuliah`, and `krs` must already exist.
-- - Recommended storage engine: InnoDB
-- - Recommended charset/collation: utf8mb4 / utf8mb4_unicode_ci

CREATE TABLE IF NOT EXISTS `beasiswa` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) NOT NULL,
  `jenis` varchar(255) NOT NULL,
  `coverage_type` varchar(255) NOT NULL DEFAULT 'partial',
  `coverage_percent` tinyint unsigned NOT NULL DEFAULT 0,
  `kuota` int unsigned NOT NULL DEFAULT 0,
  `aktif` tinyint(1) NOT NULL DEFAULT 1,
  `deskripsi` text NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `mahasiswa_beasiswa` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `mahasiswa_id` bigint unsigned NOT NULL,
  `beasiswa_id` bigint unsigned NOT NULL,
  `tahun_akademik_id` bigint unsigned NOT NULL,
  `nomor_sk` varchar(255) NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pengajuan',
  `mulai_berlaku` date NULL,
  `berakhir_berlaku` date NULL,
  `catatan` text NULL,
  `disetujui_oleh` bigint unsigned NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mb_mhs_ta_idx` (`mahasiswa_id`, `tahun_akademik_id`),
  KEY `mb_status_ta_idx` (`status`, `tahun_akademik_id`),
  CONSTRAINT `mahasiswa_beasiswa_mahasiswa_id_foreign` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mahasiswa_beasiswa_beasiswa_id_foreign` FOREIGN KEY (`beasiswa_id`) REFERENCES `beasiswa` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mahasiswa_beasiswa_tahun_akademik_id_foreign` FOREIGN KEY (`tahun_akademik_id`) REFERENCES `tahun_akademik` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mahasiswa_beasiswa_disetujui_oleh_foreign` FOREIGN KEY (`disetujui_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tagihan_mahasiswa` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `mahasiswa_id` bigint unsigned NOT NULL,
  `tahun_akademik_id` bigint unsigned NOT NULL,
  `jenis_tagihan` varchar(255) NOT NULL,
  `nominal` bigint unsigned NOT NULL DEFAULT 0,
  `terbayar` bigint unsigned NOT NULL DEFAULT 0,
  `status` varchar(255) NOT NULL DEFAULT 'belum_bayar',
  `jatuh_tempo` date NULL,
  `catatan` text NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tm_mhs_ta_idx` (`mahasiswa_id`, `tahun_akademik_id`),
  KEY `tm_status_jenis_idx` (`status`, `jenis_tagihan`),
  CONSTRAINT `tagihan_mahasiswa_mahasiswa_id_foreign` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tagihan_mahasiswa_tahun_akademik_id_foreign` FOREIGN KEY (`tahun_akademik_id`) REFERENCES `tahun_akademik` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pembayaran_mahasiswa` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tagihan_mahasiswa_id` bigint unsigned NOT NULL,
  `mahasiswa_id` bigint unsigned NOT NULL,
  `metode_pembayaran` varchar(255) NOT NULL,
  `jumlah_bayar` bigint unsigned NOT NULL DEFAULT 0,
  `tanggal_bayar` date NULL,
  `status_verifikasi` varchar(255) NOT NULL DEFAULT 'pending',
  `bukti_transfer` varchar(255) NULL,
  `diverifikasi_oleh` bigint unsigned NULL,
  `catatan_verifikasi` text NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pm_mhs_status_idx` (`mahasiswa_id`, `status_verifikasi`),
  KEY `pm_tagihan_status_idx` (`tagihan_mahasiswa_id`, `status_verifikasi`),
  CONSTRAINT `pembayaran_mahasiswa_tagihan_mahasiswa_id_foreign` FOREIGN KEY (`tagihan_mahasiswa_id`) REFERENCES `tagihan_mahasiswa` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pembayaran_mahasiswa_mahasiswa_id_foreign` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pembayaran_mahasiswa_diverifikasi_oleh_foreign` FOREIGN KEY (`diverifikasi_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `blokir_mahasiswa` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `mahasiswa_id` bigint unsigned NOT NULL,
  `tahun_akademik_id` bigint unsigned NULL,
  `tipe_blokir` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'aktif',
  `alasan` text NOT NULL,
  `dibuat_oleh` bigint unsigned NULL,
  `dicabut_oleh` bigint unsigned NULL,
  `expired_at` datetime NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bm_mhs_tipe_status_idx` (`mahasiswa_id`, `tipe_blokir`, `status`),
  CONSTRAINT `blokir_mahasiswa_mahasiswa_id_foreign` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`id`) ON DELETE CASCADE,
  CONSTRAINT `blokir_mahasiswa_tahun_akademik_id_foreign` FOREIGN KEY (`tahun_akademik_id`) REFERENCES `tahun_akademik` (`id`) ON DELETE SET NULL,
  CONSTRAINT `blokir_mahasiswa_dibuat_oleh_foreign` FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `blokir_mahasiswa_dicabut_oleh_foreign` FOREIGN KEY (`dicabut_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rpl_pengajuan` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `mahasiswa_id` bigint unsigned NOT NULL,
  `tahun_akademik_id` bigint unsigned NOT NULL,
  `judul_pengajuan` varchar(255) NOT NULL,
  `jenis_pengalaman` varchar(255) NULL,
  `nama_instansi` varchar(255) NULL,
  `periode_mulai` date NULL,
  `periode_selesai` date NULL,
  `uraian_pengalaman` text NULL,
  `target_sks_dimohon` tinyint unsigned NOT NULL DEFAULT 0,
  `status` varchar(255) NOT NULL DEFAULT 'draft',
  `total_sks_diakui` tinyint unsigned NOT NULL DEFAULT 0,
  `catatan` text NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rp_mhs_ta_idx` (`mahasiswa_id`, `tahun_akademik_id`),
  KEY `rp_status_idx` (`status`),
  CONSTRAINT `rpl_pengajuan_mahasiswa_id_foreign` FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rpl_pengajuan_tahun_akademik_id_foreign` FOREIGN KEY (`tahun_akademik_id`) REFERENCES `tahun_akademik` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rpl_dokumen` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `rpl_pengajuan_id` bigint unsigned NOT NULL,
  `jenis_dokumen` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `keterangan` text NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rpl_dokumen_rpl_pengajuan_id_index` (`rpl_pengajuan_id`),
  CONSTRAINT `rpl_dokumen_rpl_pengajuan_id_foreign` FOREIGN KEY (`rpl_pengajuan_id`) REFERENCES `rpl_pengajuan` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rpl_konversi` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `rpl_pengajuan_id` bigint unsigned NOT NULL,
  `mata_kuliah_id` bigint unsigned NOT NULL,
  `nilai_awal` varchar(255) NULL,
  `nilai_konversi` varchar(255) NULL,
  `sks_diakui` tinyint unsigned NOT NULL DEFAULT 0,
  `disetujui` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rk_pengajuan_disetujui_idx` (`rpl_pengajuan_id`, `disetujui`),
  CONSTRAINT `rpl_konversi_rpl_pengajuan_id_foreign` FOREIGN KEY (`rpl_pengajuan_id`) REFERENCES `rpl_pengajuan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rpl_konversi_mata_kuliah_id_foreign` FOREIGN KEY (`mata_kuliah_id`) REFERENCES `mata_kuliah` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `krs`
  ADD COLUMN `keuangan_status` varchar(255) NOT NULL DEFAULT 'clear' AFTER `status`,
  ADD COLUMN `keuangan_catatan` text NULL AFTER `keuangan_status`,
  ADD COLUMN `keuangan_checked_at` datetime NULL AFTER `keuangan_catatan`;
