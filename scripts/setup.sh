#!/usr/bin/env bash
# =====================================================================
# Daily News Portal — automated setup script
#
# What this does:
#   1. Creates a fresh Laravel project (via Composer)
#   2. Creates the MySQL database + user
#   3. Writes the .env file
#   4. Copies in all the news-portal-package application files
#   5. Registers the "admin" middleware alias
#   6. Runs migrations + seeders
#   7. Starts the dev server
#
# Requirements (install these FIRST if missing):
#   - PHP 8.2+, Composer, MySQL server, unzip
#
# Usage:
#   chmod +x setup.sh
#   ./setup.sh
#
# Run this script from anywhere; it will ask where to create the
# Laravel project and will look for the package files relative to
# its own location (../ from scripts/).
# =====================================================================

set -e

PKG_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"   # news-portal-package/
echo "Using package files from: $PKG_DIR"
echo

read -rp "Directory to create the new Laravel project in [./news-portal]: " PROJECT_DIR
PROJECT_DIR=${PROJECT_DIR:-./news-portal}

read -rp "MySQL database name [news_portal]: " DB_NAME
DB_NAME=${DB_NAME:-news_portal}

read -rp "MySQL username [news_user]: " DB_USER
DB_USER=${DB_USER:-news_user}

read -rsp "MySQL password for that user (will create it if new): " DB_PASS
echo
read -rsp "MySQL root password (to create the DB/user above): " DB_ROOT_PASS
echo

read -rp "App URL [http://localhost:8000]: " APP_URL
APP_URL=${APP_URL:-http://localhost:8000}

echo
echo "==> 1. Creating Laravel project at $PROJECT_DIR ..."
composer create-project laravel/laravel "$PROJECT_DIR"
cd "$PROJECT_DIR"

echo
echo "==> 2. Creating MySQL database and user ..."
mysql -u root -p"$DB_ROOT_PASS" <<SQL
CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';
GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
SQL

echo
echo "==> 3. Writing .env ..."
php artisan key:generate --ansi

sed -i "s|^APP_NAME=.*|APP_NAME=\"Daily News Portal\"|" .env
sed -i "s|^APP_URL=.*|APP_URL=$APP_URL|" .env
sed -i "s|^DB_CONNECTION=.*|DB_CONNECTION=mysql|" .env
sed -i "s|^DB_HOST=.*|DB_HOST=127.0.0.1|" .env
sed -i "s|^DB_PORT=.*|DB_PORT=3306|" .env
sed -i "s|^DB_DATABASE=.*|DB_DATABASE=$DB_NAME|" .env
sed -i "s|^DB_USERNAME=.*|DB_USERNAME=$DB_USER|" .env
sed -i "s|^DB_PASSWORD=.*|DB_PASSWORD=$DB_PASS|" .env
sed -i "s|^SESSION_DRIVER=.*|SESSION_DRIVER=database|" .env
grep -q "^FILESYSTEM_DISK=" .env && sed -i "s|^FILESYSTEM_DISK=.*|FILESYSTEM_DISK=public|" .env || echo "FILESYSTEM_DISK=public" >> .env

echo
echo "==> 4. Copying application files ..."
mkdir -p app/Http/Controllers/Admin app/Http/Controllers/Auth
cp "$PKG_DIR"/app-files/Models/*.php app/Models/
cp "$PKG_DIR"/app-files/Http/Controllers/Admin/*.php app/Http/Controllers/Admin/
cp "$PKG_DIR"/app-files/Http/Controllers/Auth/*.php app/Http/Controllers/Auth/
cp "$PKG_DIR"/app-files/Http/Controllers/HomeController.php app/Http/Controllers/
cp "$PKG_DIR"/app-files/Http/Controllers/NewsController.php app/Http/Controllers/
cp "$PKG_DIR"/app-files/Http/Middleware/AdminMiddleware.php app/Http/Middleware/
cp "$PKG_DIR"/database-files/migrations/*.php database/migrations/
cp "$PKG_DIR"/database-files/seeders/*.php database/seeders/
cp "$PKG_DIR"/routes-files/web.php routes/web.php
mkdir -p resources/views
cp -r "$PKG_DIR"/resources-files/views/* resources/views/
mkdir -p public/css public/js public/images
cp "$PKG_DIR"/public-files/css/*.css public/css/
cp "$PKG_DIR"/public-files/js/*.js public/js/
cp "$PKG_DIR"/public-files/images/*.svg public/images/

echo
echo "==> 5. Registering 'admin' middleware alias in bootstrap/app.php ..."
if grep -q "withMiddleware(function (Middleware \$middleware) {" bootstrap/app.php; then
    # Insert the alias line right after the withMiddleware closure opens, if not already present
    if ! grep -q "AdminMiddleware::class" bootstrap/app.php; then
        php -r '
            $f = "bootstrap/app.php";
            $c = file_get_contents($f);
            $needle = "withMiddleware(function (Middleware \$middleware) {";
            $inject = $needle . "\n        \$middleware->alias([\n            '\''admin'\'' => \\App\\Http\\Middleware\\AdminMiddleware::class,\n        ]);\n";
            $c = str_replace($needle, $inject, $c);
            file_put_contents($f, $c);
        '
        echo "    Middleware alias added automatically."
    else
        echo "    Alias already present, skipping."
    fi
else
    echo "    WARNING: could not auto-detect withMiddleware() in bootstrap/app.php."
    echo "    Add this manually (see docs/bootstrap_app_snippet.php):"
    echo "      \$middleware->alias(['admin' => \App\Http\Middleware\AdminMiddleware::class]);"
fi

echo
echo "==> 6. Installing composer dependencies (if needed) and autoload ..."
composer dump-autoload

echo
echo "==> 7. Running migrations + seeders ..."
php artisan storage:link
php artisan migrate --seed --force

echo
echo "====================================================================="
echo " Setup complete!"
echo " Project created at: $(pwd)"
echo
echo " Start the server with:"
echo "   cd $(pwd) && php artisan serve"
echo
echo " Front page:  $APP_URL"
echo " Admin login: $APP_URL/admin/login"
echo "   email:    admin@example.com"
echo "   password: Admin@12345   <-- CHANGE THIS after first login"
echo
echo " The Ceylon Focus News design is already wired into the Blade views —"
echo " nothing left to paste in. Adjust colors/type in public/css/app.css"
echo " and public/css/admin.css if needed."
echo "====================================================================="
