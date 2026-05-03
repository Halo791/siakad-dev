ALTER TABLE `krs`
  ADD COLUMN `keuangan_status` varchar(255) NOT NULL DEFAULT 'clear' AFTER `status`,
  ADD COLUMN `keuangan_catatan` text NULL AFTER `keuangan_status`,
  ADD COLUMN `keuangan_checked_at` datetime NULL AFTER `keuangan_catatan`;
