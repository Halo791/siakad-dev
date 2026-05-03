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
