# Hosting checklist for finance and RPL modules

## Before import
- Back up the current database.
- Confirm the database already has these prerequisite tables:
  - `mahasiswa`
  - `tahun_akademik`
  - `users`
  - `mata_kuliah`
  - `krs`
- Make sure the hosting MySQL version supports:
  - `ON DELETE SET NULL`
  - `ALTER TABLE ADD COLUMN`
- Use the same database connection that the app already uses in `.env`.

## Run migration files in this order
1. `database/migrations/2026_05_03_000001_create_beasiswa_tables.php`
2. `database/migrations/2026_05_03_000002_create_keuangan_mahasiswa_tables.php`
3. `database/migrations/2026_05_03_000003_create_blokir_mahasiswa_table.php`
4. `database/migrations/2026_05_03_000004_create_rpl_tables.php`
5. `database/migrations/2026_05_03_000005_add_finance_fields_to_krs_table.php`
6. `database/migrations/2026_05_03_000006_add_recognition_fields_to_rpl_pengajuan_table.php`

## If you cannot run Laravel migration on hosting
Use the manual SQL file:
- `docs/finance-rpl-schema.sql`

## If you want separated SQL blocks for phpMyAdmin
Use the split files in:
- `docs/phpmyadmin-import/01-beasiswa.sql`
- `docs/phpmyadmin-import/02-keuangan.sql`
- `docs/phpmyadmin-import/03-blokir.sql`
- `docs/phpmyadmin-import/04-rpl.sql`
- `docs/phpmyadmin-import/05-krs-finance.sql`

## After import
- Clear config cache if the hosting supports Artisan:
  - `php artisan config:clear`
  - `php artisan cache:clear`
- Open `/mahasiswa/krs` and verify:
  - page loads without SQL error
  - menu Keuangan/RPL appears only when tables exist
  - KRS is blocked only when the finance/RPL checks really apply

## Notes
- If the tables are not imported yet, the app will now hide the Keuangan/RPL links and keep KRS page readable instead of throwing a 500 error.
- If only some tables exist, import the missing ones in the same order above.
