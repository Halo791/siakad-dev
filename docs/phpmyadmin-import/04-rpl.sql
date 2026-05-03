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
