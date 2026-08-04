#!/bin/bash
set -euo pipefail

OC_ROOT=/var/www/html
OC_STORAGE=/var/www/oc-storage
DB_DUMP_FILE=/docker-entrypoint-db-dump/dump.sql
DB_HOST=db
DB_PORT=3306

MYSQL_DATABASE="${MYSQL_DATABASE:-test}"
MYSQL_USER="${MYSQL_USER:-test}"
MYSQL_PASSWORD="${MYSQL_PASSWORD:-test}"
APP_PORT="${APP_PORT:-8081}"
OC_ADMIN_USERNAME="${OC_ADMIN_USERNAME:-admin}"
OC_ADMIN_PASSWORD="${OC_ADMIN_PASSWORD:-admin}"
OC_ADMIN_EMAIL="${OC_ADMIN_EMAIL:-admin@example.local}"
DB_PREFIX="oc_"

mysql_cmd() {
  mysql --skip-ssl --init-command="SET sql_mode=''" -h "$DB_HOST" -P "$DB_PORT" -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE" "$@"
}

echo "==> Waiting for database..."
for i in $(seq 1 60); do
  if mysql_cmd -e "SELECT 1" >/dev/null 2>&1; then
    break
  fi
  sleep 1
done
mysql_cmd -e "SELECT 1" >/dev/null

echo "==> Writing config.php / admin/config.php"
cat > "$OC_ROOT/config.php" <<PHP
<?php
// HTTP
define('HTTP_SERVER', 'http://localhost:${APP_PORT}/');
// HTTPS
define('HTTPS_SERVER', 'http://localhost:${APP_PORT}/');
// DIR
define('DIR_APPLICATION', '${OC_ROOT}/catalog/');
define('DIR_SYSTEM', '${OC_ROOT}/system/');
define('DIR_IMAGE', '${OC_ROOT}/image/');
define('DIR_STORAGE', '${OC_STORAGE}/');
define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');
define('DIR_TEMPLATE', DIR_APPLICATION . 'view/theme/');
define('DIR_CONFIG', DIR_SYSTEM . 'config/');
define('DIR_CACHE', DIR_STORAGE . 'cache/');
define('DIR_DOWNLOAD', DIR_STORAGE . 'download/');
define('DIR_LOGS', DIR_STORAGE . 'logs/');
define('DIR_MODIFICATION', DIR_STORAGE . 'modification/');
define('DIR_SESSION', DIR_STORAGE . 'session/');
define('DIR_UPLOAD', DIR_STORAGE . 'upload/');
// DB
define('DB_DRIVER', 'mysqli');
define('DB_HOSTNAME', '${DB_HOST}');
define('DB_USERNAME', '${MYSQL_USER}');
define('DB_PASSWORD', '${MYSQL_PASSWORD}');
define('DB_DATABASE', '${MYSQL_DATABASE}');
define('DB_PORT', '${DB_PORT}');
define('DB_PREFIX', '${DB_PREFIX}');
PHP

cat > "$OC_ROOT/admin/config.php" <<PHP
<?php
// HTTP
define('HTTP_SERVER', 'http://localhost:${APP_PORT}/admin/');
define('HTTP_CATALOG', 'http://localhost:${APP_PORT}/');
// HTTPS
define('HTTPS_SERVER', 'http://localhost:${APP_PORT}/admin/');
define('HTTPS_CATALOG', 'http://localhost:${APP_PORT}/');
// DIR
define('DIR_APPLICATION', '${OC_ROOT}/admin/');
define('DIR_SYSTEM', '${OC_ROOT}/system/');
define('DIR_IMAGE', '${OC_ROOT}/image/');
define('DIR_STORAGE', '${OC_STORAGE}/');
define('DIR_CATALOG', '${OC_ROOT}/catalog/');
define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');
define('DIR_TEMPLATE', DIR_APPLICATION . 'view/template/');
define('DIR_CONFIG', DIR_SYSTEM . 'config/');
define('DIR_CACHE', DIR_STORAGE . 'cache/');
define('DIR_DOWNLOAD', DIR_STORAGE . 'download/');
define('DIR_LOGS', DIR_STORAGE . 'logs/');
define('DIR_MODIFICATION', DIR_STORAGE . 'modification/');
define('DIR_SESSION', DIR_STORAGE . 'session/');
define('DIR_UPLOAD', DIR_STORAGE . 'upload/');
// DB
define('DB_DRIVER', 'mysqli');
define('DB_HOSTNAME', '${DB_HOST}');
define('DB_USERNAME', '${MYSQL_USER}');
define('DB_PASSWORD', '${MYSQL_PASSWORD}');
define('DB_DATABASE', '${MYSQL_DATABASE}');
define('DB_PORT', '${DB_PORT}');
define('DB_PREFIX', '${DB_PREFIX}');
// OpenCart API
define('OPENCART_SERVER', 'https://www.opencart.com/');
PHP

chown www-data:www-data "$OC_ROOT/config.php" "$OC_ROOT/admin/config.php"

ALREADY_INSTALLED=$(mysql_cmd -Nse "SHOW TABLES LIKE '${DB_PREFIX}setting'" 2>/dev/null || true)

if [ -z "$ALREADY_INSTALLED" ] && [ -f "$DB_DUMP_FILE" ]; then
  echo "==> Restoring database from docker/db/dump.sql"
  mysql_cmd < "$DB_DUMP_FILE"
elif [ -z "$ALREADY_INSTALLED" ]; then
  echo "==> Fresh database detected, importing OpenCart schema"
  {
    # Убираем декоративные строки-комментарии (так же делает штатный установщик
    # OpenCart) и заменяем MyISAM на InnoDB: ключи utf8mb4 на полных varchar(255)
    # превышают лимит MyISAM в 1000 байт на ключ в MySQL 5.7.
    grep -vE '^--|^#' "$OC_ROOT/install/opencart.sql" | sed 's/ENGINE=MyISAM/ENGINE=InnoDB/g'
  } | mysql_cmd

  salt=$(head -c 32 /dev/urandom | tr -dc 'a-zA-Z0-9' | head -c 9)
  pass_sha1=$(printf '%s' "$OC_ADMIN_PASSWORD" | sha1sum | cut -d' ' -f1)
  pass_hash=$(printf '%s%s' "$salt" "$pass_sha1" | sha1sum | cut -d' ' -f1)
  pass_hash=$(printf '%s%s' "$salt" "$pass_hash" | sha1sum | cut -d' ' -f1)
  encryption_key=$(head -c 1024 /dev/urandom | base64 | tr -dc 'a-zA-Z0-9' | head -c 1024)
  api_key=$(head -c 256 /dev/urandom | base64 | tr -dc 'a-zA-Z0-9' | head -c 256)

  mysql_cmd <<SQL
SET CHARACTER SET utf8;
DELETE FROM \`${DB_PREFIX}user\` WHERE user_id = '1';
INSERT INTO \`${DB_PREFIX}user\` SET user_id = '1', user_group_id = '1', username = '${OC_ADMIN_USERNAME}', salt = '${salt}', password = '${pass_hash}', firstname = 'Admin', lastname = 'Admin', email = '${OC_ADMIN_EMAIL}', status = '1', date_added = NOW();

DELETE FROM \`${DB_PREFIX}setting\` WHERE \`key\` = 'config_email';
INSERT INTO \`${DB_PREFIX}setting\` SET \`code\` = 'config', \`key\` = 'config_email', value = '${OC_ADMIN_EMAIL}';

DELETE FROM \`${DB_PREFIX}setting\` WHERE \`key\` = 'config_encryption';
INSERT INTO \`${DB_PREFIX}setting\` SET \`code\` = 'config', \`key\` = 'config_encryption', value = '${encryption_key}';

INSERT INTO \`${DB_PREFIX}api\` SET username = 'Default', \`key\` = '${api_key}', status = 1, date_added = NOW(), date_modified = NOW();
SQL

  api_id=$(mysql_cmd -Nse "SELECT api_id FROM \`${DB_PREFIX}api\` WHERE username = 'Default' ORDER BY api_id DESC LIMIT 1")
  mysql_cmd -e "DELETE FROM \`${DB_PREFIX}setting\` WHERE \`key\` = 'config_api_id'; INSERT INTO \`${DB_PREFIX}setting\` SET \`code\` = 'config', \`key\` = 'config_api_id', value = '${api_id}';"
  mysql_cmd -e "UPDATE \`${DB_PREFIX}setting\` SET value = 'INV-$(date +%Y)-00' WHERE \`key\` = 'config_invoice_prefix';"

  if [ -d "$OC_ROOT/admin/language/ru-ru" ]; then
    echo "==> Installing Russian language pack"
    mysql_cmd <<SQL
INSERT INTO \`${DB_PREFIX}language\` (name, code, locale, image, directory, sort_order, status) VALUES ('Русский', 'ru-ru', 'ru-RU,ru_RU.UTF-8,ru_RU,ru-ru,russian', 'ru.png', 'russian', 2, 1);
UPDATE \`${DB_PREFIX}setting\` SET value = 'ru-ru' WHERE \`key\` IN ('config_language', 'config_admin_language');
SQL
  fi

  echo "==> OpenCart installed. Admin login: ${OC_ADMIN_USERNAME} / ${OC_ADMIN_PASSWORD} (${OC_ADMIN_EMAIL})"
else
  echo "==> Database already initialized, skipping install"
fi

CDEK_DIR="$OC_ROOT/system/library/cdek_official"

if [ -f "$CDEK_DIR/composer.json" ] && [ ! -f "$CDEK_DIR/vendor/autoload.php" ]; then
  echo "==> Installing CDEK module PHP dependencies"
  rm -rf /tmp/cdek-build
  cp -r "$CDEK_DIR" /tmp/cdek-build
  (
    cd /tmp/cdek-build
    export COMPOSER_ALLOW_SUPERUSER=1
    if composer install --no-interaction --no-dev --quiet 2>/tmp/cdek-composer-private.log; then
      echo "    installed from the configured (private) registry"
    else
      echo "    private registry unreachable (needs VPN) - falling back to packagist.org for local dev"
      rm -f composer.lock
      composer config repositories.packagist.org composer https://repo.packagist.org
      composer install --no-interaction --no-dev --quiet
    fi
  )
  rm -rf "$CDEK_DIR/vendor"
  mv /tmp/cdek-build/vendor "$CDEK_DIR/vendor"
  rm -rf /tmp/cdek-build
fi

if [ -f "$OC_ROOT/admin/controller/extension/shipping/cdek_official.php" ]; then
  ALREADY_EXTENSION=$(mysql_cmd -Nse "SELECT code FROM \`${DB_PREFIX}extension\` WHERE type = 'shipping' AND code = 'cdek_official'" 2>/dev/null || true)

  if [ -z "$ALREADY_EXTENSION" ]; then
    echo "==> Installing CDEK shipping extension in the admin panel"

    apache2-foreground &
    APACHE_PID=$!

    for i in $(seq 1 30); do
      curl -sf http://127.0.0.1/ -o /dev/null && break
      sleep 1
    done

    COOKIE_JAR=$(mktemp)
    LOGIN_HEADERS=$(mktemp)

    curl -s -c "$COOKIE_JAR" http://127.0.0.1/admin/ -o /dev/null
    curl -s -b "$COOKIE_JAR" -c "$COOKIE_JAR" -X POST "http://127.0.0.1/admin/index.php?route=common/login" \
      --data-urlencode "username=${OC_ADMIN_USERNAME}" --data-urlencode "password=${OC_ADMIN_PASSWORD}" \
      -D "$LOGIN_HEADERS" -o /dev/null

    USER_TOKEN=$(grep -oE "user_token=[a-zA-Z0-9]+" "$LOGIN_HEADERS" | head -1 | cut -d= -f2)

    if [ -n "$USER_TOKEN" ]; then
      curl -s -b "$COOKIE_JAR" "http://127.0.0.1/admin/index.php?route=extension/extension/shipping/install&extension=cdek_official&user_token=${USER_TOKEN}" -o /dev/null
      echo "    CDEK shipping extension installed"
    else
      echo "    WARNING: admin login failed, could not auto-install the CDEK extension"
    fi

    rm -f "$COOKIE_JAR" "$LOGIN_HEADERS"

    kill "$APACHE_PID"
    wait "$APACHE_PID" 2>/dev/null || true
  fi
fi

exec "$@"
