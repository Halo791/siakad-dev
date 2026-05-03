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
