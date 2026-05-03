#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
DEPLOY_DIR="${DEPLOY_DIR:-/private/tmp/siakad-infinityfree}"
SITE_URL="${SITE_URL:-https://hrd-test.infinityfree.me/siakad}"
DB_HOST="${DB_HOST:-sql204.infinityfree.com}"
DB_PORT="${DB_PORT:-3306}"
DB_DATABASE="${DB_DATABASE:-if0_41804951_siakad}"
DB_USERNAME="${DB_USERNAME:-if0_41804951}"
DB_PASSWORD="${DB_PASSWORD:-B0vmd9lAeHHb}"
export SITE_URL
export DB_HOST DB_PORT DB_DATABASE DB_USERNAME DB_PASSWORD

rm -rf "$DEPLOY_DIR"
mkdir -p "$DEPLOY_DIR"

rsync -a \
  --exclude '.git' \
  --exclude 'node_modules' \
  --exclude 'tests' \
  --exclude '.env' \
  --exclude 'database/database.sqlite' \
  --exclude 'storage/logs' \
  "$ROOT_DIR"/ "$DEPLOY_DIR"/

cp "$ROOT_DIR/public/.htaccess" "$DEPLOY_DIR/.htaccess"
cp -R "$ROOT_DIR/public/build" "$DEPLOY_DIR/build"

if [ -f "$ROOT_DIR/public/favicon.ico" ]; then
  cp "$ROOT_DIR/public/favicon.ico" "$DEPLOY_DIR/favicon.ico"
fi

if [ -f "$ROOT_DIR/public/robots.txt" ]; then
  cp "$ROOT_DIR/public/robots.txt" "$DEPLOY_DIR/robots.txt"
fi

cp "$ROOT_DIR/public/index.php" "$DEPLOY_DIR/index.php"

cp "$ROOT_DIR/.env" "$DEPLOY_DIR/.env"

ROOT_DIR="$ROOT_DIR" DEPLOY_DIR="$DEPLOY_DIR" SITE_URL="$SITE_URL" node <<'NODE'
const fs = require('fs');

const root = process.env.ROOT_DIR;
const deploy = process.env.DEPLOY_DIR;
const siteUrl = process.env.SITE_URL;

let index = fs.readFileSync(`${root}/public/index.php`, 'utf8');
index = index.replace(
  "require __DIR__.'/../vendor/autoload.php';",
  "require __DIR__.'/vendor/autoload.php';"
);
index = index.replace(
  "if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {",
  "if (file_exists($maintenance = __DIR__.'/storage/framework/maintenance.php')) {"
);
index = index.replace(
  "/** @var Application $app */\n$app = require_once __DIR__.'/../bootstrap/app.php';",
  "/** @var Application $app */\n$app = require_once __DIR__.'/bootstrap/app.php';"
);
index = index.replace(
  '$app->handleRequest(Request::capture());',
  '$app->usePublicPath(__DIR__);\n\n$app->handleRequest(Request::capture());'
);
fs.writeFileSync(`${deploy}/index.php`, index);

let env = fs.readFileSync(`${root}/.env`, 'utf8');
env = env.replace(/^APP_ENV=local$/m, 'APP_ENV=production');
env = env.replace(/^APP_DEBUG=true$/m, 'APP_DEBUG=false');
env = env.replace(/^APP_URL=http:\/\/localhost:8000$/m, `APP_URL=${siteUrl}`);
env = env.replace(/^SESSION_DOMAIN=null$/m, 'SESSION_DOMAIN=null');
env = env.replace(/^DB_CONNECTION=sqlite$/m, 'DB_CONNECTION=mysql');
env = env.replace(/^DB_HOST=.*$/m, `DB_HOST=${process.env.DB_HOST}`);
env = env.replace(/^DB_PORT=.*$/m, `DB_PORT=${process.env.DB_PORT}`);
env = env.replace(/^DB_DATABASE=.*$/m, `DB_DATABASE=${process.env.DB_DATABASE}`);
env = env.replace(/^DB_USERNAME=.*$/m, `DB_USERNAME=${process.env.DB_USERNAME}`);
env = env.replace(/^DB_PASSWORD=.*$/m, `DB_PASSWORD=${process.env.DB_PASSWORD}`);
if (!/^DB_CHARSET=/m.test(env)) {
  env += `\nDB_CHARSET=utf8mb4\n`;
}
if (!/^DB_COLLATION=/m.test(env)) {
  env += `DB_COLLATION=utf8mb4_unicode_ci\n`;
}

if (!/^ASSET_URL=/m.test(env)) {
  env += `\nASSET_URL=${siteUrl}\n`;
}

fs.writeFileSync(`${deploy}/.env`, env);
NODE

RAW_SQL="$DEPLOY_DIR/siakad.raw.sql"
SQL_FILE="$DEPLOY_DIR/siakad.sql"
sqlite3 "$ROOT_DIR/database/database.sqlite" .dump > "$RAW_SQL"

RAW_SQL="$RAW_SQL" SQL_FILE="$SQL_FILE" node <<'NODE'
const fs = require('fs');

const rawSql = fs.readFileSync(process.env.RAW_SQL, 'utf8').replace(/\r\n/g, '\n');
const lines = rawSql.split('\n');
const tableNames = [];
const output = [];

function removeCheckConstraints(sql) {
  let result = sql;

  while (true) {
    const match = result.match(/\s+check\s*\(/i);
    if (!match) {
      return result;
    }

    const start = match.index;
    let index = start + match[0].length;
    let depth = 1;

    while (index < result.length && depth > 0) {
      const char = result[index];
      if (char === '(') {
        depth += 1;
      } else if (char === ')') {
        depth -= 1;
      }
      index += 1;
    }

    result = `${result.slice(0, start)}${result.slice(index)}`;
  }
}

for (let line of lines) {
  line = line.trimEnd();

  if (!line) {
    continue;
  }

  if (
    line === 'PRAGMA foreign_keys=OFF;' ||
    line === 'BEGIN TRANSACTION;' ||
    line === 'COMMIT;'
  ) {
    continue;
  }

  if (/^DELETE FROM sqlite_sequence;$/i.test(line)) {
    continue;
  }

  if (/^INSERT INTO sqlite_sequence VALUES\(/i.test(line)) {
    continue;
  }

  if (/^CREATE TABLE IF NOT EXISTS "sqlite_sequence"/i.test(line)) {
    continue;
  }

  line = line.replace(/"/g, '`');
  const tableMatch = line.match(/^CREATE TABLE IF NOT EXISTS `([^`]+)`/i);
  if (tableMatch) {
    tableNames.push(tableMatch[1]);
  }
  line = line.replace(/,\s*foreign key\([^)]*\) references [^,;]+?(?=,\s*foreign key|,\s*primary key|\);)/gi, '');
  line = removeCheckConstraints(line);
  line = line.replace(/\binteger primary key autoincrement not null\b/gi, 'BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY');
  line = line.replace(/\binteger primary key autoincrement\b/gi, 'BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY');
  line = line.replace(/\bvarchar\b(?!\s*\()/gi, 'varchar(255)');
  line = line.replace(/default\s+\('([^']*)'\)/gi, "default '$1'");
  line = line.replace(/default\s+\((\d+(?:\.\d+)?)\)/gi, 'default $1');

  output.push(line);
}

const drops = [
  'SET FOREIGN_KEY_CHECKS=0;',
  ...tableNames.reverse().map((table) => `DROP TABLE IF EXISTS \`${table}\`;`),
  'SET FOREIGN_KEY_CHECKS=1;',
  '',
];

fs.writeFileSync(process.env.SQL_FILE, `${drops.join('\n')}${output.join('\n')}\n`);
NODE

cp "$SQL_FILE" "$ROOT_DIR/siakad.sql"

rm -f "$RAW_SQL"

cat <<EOF
Deploy package ready:
$DEPLOY_DIR

Upload the contents of that folder to:
/htdocs/siakad

Site URL:
$SITE_URL
EOF
