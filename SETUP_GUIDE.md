# Daily News Portal — Full Setup Guide

Two ways to set this up: **automated** (one script) or **manual** (full control, step by step).
Both produce the exact same result.

---

## Option A — Automated setup (recommended)

Requires PHP 8.2+, Composer, and MySQL already installed (see Option B, Section 1, if you need to
install those first).

```bash
cd news-portal-package/scripts
chmod +x setup.sh
./setup.sh
```

The script will interactively ask for:
- where to create the Laravel project (default `./news-portal`)
- your MySQL database name, app username/password, and MySQL root password (needed once, to create the DB)
- your app URL

It then does everything: creates the Laravel project, creates the database and DB user, writes `.env`,
copies in every controller/model/migration/seeder/route/view, registers the `admin` middleware alias,
runs `composer dump-autoload`, links storage, migrates, and seeds. At the end it prints your login URL
and credentials.

After it finishes:
```bash
cd news-portal   # or whatever directory you chose
php artisan serve
```

Then open `resources/views/layouts/app.blade.php` and paste in your existing HTML header/footer design
(clearly marked placeholders are inside).

Skip to **[Post-install checklist](#post-install-checklist)** below.

---

## Option B — Manual setup, step by step

### 1. Install prerequisites

**PHP 8.2+**
```bash
sudo apt update
sudo apt install php8.3 php8.3-cli php8.3-mysql php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip php8.3-gd unzip curl -y
php -v
```

**Composer**
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
composer -V
```

**MySQL**
```bash
sudo apt install mysql-server -y
sudo mysql_secure_installation
```

**Node.js/npm** (optional — only if you want to compile assets with Vite instead of the plain CSS provided)
```bash
curl -fsSL https://deb.nodesource.com/setup_lts.x | sudo -E bash -
sudo apt install -y nodejs
```

### 2. Create the Laravel project

```bash
composer create-project laravel/laravel news-portal
cd news-portal
```

### 3. Create the database

```bash
mysql -u root -p
```
```sql
CREATE DATABASE news_portal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'news_user'@'localhost' IDENTIFIED BY 'a-strong-password';
GRANT ALL PRIVILEGES ON news_portal.* TO 'news_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 4. Configure `.env`

Edit `news-portal/.env`:
```
APP_NAME="Daily News Portal"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=news_portal
DB_USERNAME=news_user
DB_PASSWORD=a-strong-password

SESSION_DRIVER=database
FILESYSTEM_DISK=public
```

```bash
php artisan key:generate
```

### 5. Copy in the application files

From inside your `news-portal/` project, with `PKG` pointing at the unzipped `news-portal-package/`:

```bash
PKG=/path/to/news-portal-package

# Models
cp $PKG/app-files/Models/*.php app/Models/

# Controllers
mkdir -p app/Http/Controllers/Admin app/Http/Controllers/Auth
cp $PKG/app-files/Http/Controllers/Admin/*.php app/Http/Controllers/Admin/
cp $PKG/app-files/Http/Controllers/Auth/*.php app/Http/Controllers/Auth/
cp $PKG/app-files/Http/Controllers/HomeController.php app/Http/Controllers/
cp $PKG/app-files/Http/Controllers/NewsController.php app/Http/Controllers/

# Middleware
cp $PKG/app-files/Http/Middleware/AdminMiddleware.php app/Http/Middleware/

# Migrations & seeders
cp $PKG/database-files/migrations/*.php database/migrations/
cp $PKG/database-files/seeders/*.php database/seeders/

# Routes
cp $PKG/routes-files/web.php routes/web.php

# Views
cp -r $PKG/resources-files/views/* resources/views/

# CSS + JS + images (the actual Ceylon Focus News design)
mkdir -p public/css public/js public/images
cp $PKG/public-files/css/*.css public/css/
cp $PKG/public-files/js/*.js public/js/
cp $PKG/public-files/images/*.svg public/images/
```

**Register the `admin` middleware** — open `bootstrap/app.php` and add inside `withMiddleware()`:
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'admin' => \App\Http\Middleware\AdminMiddleware::class,
    ]);
})
```
*(Reference copy: `docs/bootstrap_app_snippet.php`. If your Laravel version still has
`app/Http/Kernel.php`, add the same line to `$middlewareAliases` there instead.)*

**The design is already in place** — `layouts/app.blade.php`, `home.blade.php`, `news/show.blade.php`,
`news/category.blade.php`, and `news/search.blade.php` already contain the full Ceylon Focus News design
(masthead, breaking ticker, focus-bracket hero, category bands, the 4-video "Watch" section), with every
section driven by real Eloquent data. There's no HTML to paste in — if you want to adjust the palette or
type later, everything is controlled from the CSS variables at the top of `public/css/app.css` and
`public/css/admin.css`.

### 6. Run migrations and seeders

```bash
php artisan storage:link
php artisan migrate --seed
composer dump-autoload
```

This creates the tables and seeds:
- Admin login: **admin@example.com / Admin@12345**
- 7 categories
- 15 sample articles

### 7. Start the server

```bash
php artisan serve
```

- Front page: `http://localhost:8000`
- Admin login: `http://localhost:8000/admin/login`

---

## Post-install checklist

1. Log in with `admin@example.com` / `Admin@12345`.
2. **Change that password immediately**:
   ```bash
   php artisan tinker
   ```
   ```php
   $u = \App\Models\User::where('email','admin@example.com')->first();
   $u->password = \Illuminate\Support\Facades\Hash::make('your-new-password');
   $u->save();
   exit
   ```
3. **Admin → Categories** — adjust or add your real categories.
4. **Admin → News** — add real articles; delete the 15 sample ones when ready.
5. **Admin → Advertisements** — upload ad banners per position (header, sidebar, article top/bottom, footer).
6. **Admin → Homepage Videos** — fill in the 4 YouTube/Facebook URLs for the bottom-of-homepage section.
7. Merge your original front-page HTML/CSS into `resources/views/layouts/app.blade.php` and the view
   files if you haven't already.
8. For production: set `APP_ENV=production`, `APP_DEBUG=false`, run `php artisan config:cache route:cache view:cache`,
   and serve via Nginx/Apache + PHP-FPM instead of `php artisan serve`.

---

## Admin panel reference

| Section | What it does |
|---|---|
| **Dashboard** | Stats (total/published/draft news, categories, ads, videos) + recent-articles table. |
| **News** | Add/edit/delete articles — title, category, excerpt, content, featured image, draft/published status. Slugs auto-generate. |
| **Categories** | Add/edit/delete. A category with existing articles can't be deleted. |
| **Advertisements** | Upload image, set target URL, choose position (header/sidebar/article top/article bottom/footer), toggle active, set sort order. |
| **Homepage Videos** | 4 fixed slots. Paste a YouTube or Facebook URL — auto-converted to an embed. Toggle active or remove per slot. |

## How article pages work

`/news/{slug}` shows: full content, view-count increment, **recently added news** (5 latest across all
categories) in the sidebar, **related news** (up to 4 more from the same category), and any active ads
for `article_top` / `article_bottom` / `sidebar` positions.

## Troubleshooting

| Problem | Fix |
|---|---|
| 419 Page Expired on login | `php artisan config:clear`; confirm the `sessions` table exists (created by `migrate`) |
| Images not showing | Confirm `php artisan storage:link` was run and `FILESYSTEM_DISK=public` |
| "Class not found" | `composer dump-autoload` |
| Can't access `/admin` | The account needs `role = admin` in the `users` table |
| `setup.sh` fails on MySQL step | Confirm root password is correct and MySQL is running (`sudo systemctl status mysql`) |
