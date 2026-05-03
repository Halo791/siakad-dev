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
