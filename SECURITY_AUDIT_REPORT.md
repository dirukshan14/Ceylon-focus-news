# Ceylon Focus News — Security Audit & Hardening Report

Scope: the Laravel 11 source package you supplied (`app/`, `routes/`, `resources/views/`, `database/`, `bootstrap/app.php`, `composer.json`, `.env.example`, `README.md`).

## 0. Read this first — what the package does and does not contain

| Your brief mentioned | What is actually in the ZIP |
|---|---|
| Daily Newspaper / PDF upload, view, download, delete | **Does not exist.** No PDF code, model, route or view anywhere. Nothing to audit or break. If it lives in another copy of the project, send that copy. |
| Comments, profile editing, password-reset, REST/JSON APIs, AJAX | None present. Only server-rendered forms; no `routes/api.php`; no `fetch`/`axios`; one JSON response (`categories/reorder`) with no caller in the views. |
| `vendor/`, `config/*`, `artisan`, `public/index.php`, `package.json`, `composer.lock`, `tests/` | **Not in the ZIP** (the README says this is overlaid on a fresh `composer create-project laravel/laravel`). So session/cookie/debug settings come from your skeleton + `.env`; I addressed those through `.env` templates, not by inventing config files. |

**Testing limitation (important):** the sandbox I worked in blocks Packagist, so I could not `composer install` Laravel. I therefore could **not** boot the app, run `php artisan test`, or run `composer audit`. What I did verify is listed in §5. The Laravel feature tests I added are written but **have not been executed** — run `php artisan test` on your machine (§7).

---

## 1. Findings

Severity = realistic impact on *this* site. "Fixed" means code changed in this ZIP.

### High
| # | Finding | Files | Status |
|---|---|---|---|
| H1 | **Stored XSS in article body.** `{!! $article->body !!}` printed raw HTML; any Author/Reporter (lowest staff role) could plant `<script>`/`onerror` that runs in readers' and Editors'/Admins' browsers (session abuse, admin takeover). | `resources/views/frontend/article-show.blade.php`, `app/Models/Article.php` | **Fixed** — allow-list sanitizer applied on save *and* on render (old rows are protected without touching the DB). Formatting, links, images, tables, YouTube/Vimeo/Facebook iframes kept. |
| H2 | **Privilege escalation through admin management.** Anyone holding `manage-administrators` could reset the password of / deactivate / delete a **Super Admin** (account takeover) and assign the Super Admin role; anyone with `manage-roles` could grant themselves `manage-administrators`/any permission. Safe only while nobody but the Super Admin holds those permissions — fragile because roles are editable. | `app/Policies/UserPolicy.php`, `Admin/AdministratorController.php`, `Admin/RoleController.php` | **Fixed** — only a Super Admin can touch Super Admin accounts, assign the Super Admin role or grant `manage-administrators`/`manage-roles`; nobody can grant permissions they don't hold. |
| H3 | **Default/demo credentials seeded anywhere.** `db:seed` always created demo admin/editor/author accounts with password `password`, and a Super Admin with the published `ChangeMe!12345` if `.env` wasn't edited. | `database/seeders/DatabaseSeeder.php` | **Fixed** — demo users/content skipped when `APP_ENV=production`; seeding aborts in production if the Super Admin password is the example value or < 12 chars. |

### Medium
| # | Finding | Files | Status |
|---|---|---|---|
| M1 | **Draft/unpublished articles readable by any logged-in reader** (`|| auth()->check()`), and public registration is open. Leaks unpublished news. | `app/Http/Controllers/ArticleController.php` | **Fixed** — preview only for admin-panel users. |
| M2 | **No brute-force protection on public login/registration**; admin login limited only per email+IP (credential stuffing across many emails unrestricted). | `PublicAuthController`, `AdminLoginController`, `routes/web.php` | **Fixed** — email+IP limit (5/min) on public login; IP-wide limit on admin login (20 failures/5 min); route-level `throttle` on login/register/password-change/search/ad-click. |
| M3 | **Deactivated/suspended readers could still log in** on the public form. | `PublicAuthController` | **Fixed.** |
| M4 | **URL fields accepted any scheme** (`url` rule): ad click URL, social links, live-score link, canonical URL, video source. Rendered in `href`/`iframe src` → `javascript:`/`data:` and open-redirect risk (ad click used `redirect()->away`). | Ad/Social/Setting/Video controllers, `StoreArticleRequest`, `AdController`, `BreakingNews`, `layouts/app.blade.php` | **Fixed** — `url:http,https` on input; http(s)-only on output (defence in depth for old rows). |
| M5 | **Settings injected into inline JS/CSS** (`primary_color` printed inside a `<script>` string; unvalidated). | `SettingController`, `layouts/app.blade.php` | **Fixed** — `#RRGGBB` validation + `Js::from()` encoding. |
| M6 | **No security response headers** (clickjacking, MIME sniffing, referrer, permissions). | new `Http/Middleware/SecurityHeaders.php`, `bootstrap/app.php`, `config/security.php` | **Fixed** (see CSP note in §6). |
| M7 | **Uploads not protected from execution** if a script ever lands in the web-served storage folder. | new `storage/app/public/.htaccess` | **Fixed (Apache/XAMPP)**; nginx rule in §6. |
| M8 | **No `.gitignore`** → `.env` / `vendor` could be committed. | `.gitignore` (new) | **Fixed.** |
| M9 | **Tailwind loaded from the CDN at runtime, no SRI, in production** — a CDN compromise = site-wide script execution. README itself says "not for production". | `layouts/*.blade.php` | **Not changed** (needs a Vite build, which alters the UI pipeline). See §4. |

### Low / hardening
| # | Finding | Status |
|---|---|---|
| L1 | `is_super_admin` was mass-assignable on `User` (latent escalation if any future `User::create($request->…)`). | **Fixed** — removed from `$fillable`; seeder uses `forceFill`. |
| L2 | `Category` "different:id" parent rule compared against a field never submitted → a category could be its own parent. | **Fixed** (`Rule::notIn`). |
| L3 | Video provider detection regexes unanchored (`evil.com/?x=instagram.com/p/` classed as Instagram). | **Fixed** (host-anchored). |
| L4 | Search: `LIKE` wildcards not escaped; no length cap; array params could reach `(int)`. Also `FULLTEXT` hard-wired (breaks non-MySQL test DBs). | **Fixed.** |
| L5 | Author page revealed users whose only articles are drafts. | **Fixed.** |
| L6 | Forced password change accepted `min:10` only, kept the same session, could re-use the temp password. | **Fixed** (letters+numbers, must differ, session regenerated). *Behaviour change — see §3.* |
| L7 | Validation gaps: image fields without `mimes`, unbounded `body`, `html_code`, `survived_by`, `tribute_message`, `priority`, `start_seconds`, gallery/tag counts. | **Fixed.** |
| L8 | Logout/deactivation paths didn't invalidate the session. | **Fixed.** |
| L9 | Security-relevant events not logged (failed logins, throttling, permission denials). | **Fixed** — `Log::warning`, no passwords/tokens/session IDs/raw e-mails logged. |
| L10 | Temporary admin passwords are shown once in a flash message (stored in the session table until read). | Not changed — emailing them needs mail infra. |
| L11 | Ad-click counter is a GET, inflatable. | Mitigated with `throttle:30,1`; not fully preventable. |
| L12 | "Remember me" on admin login = long-lived admin cookie. | Not changed (UI feature). Consider removing for admins. |
| L13 | `ad position` validation list lacks `obituary_top` / `obituary_bottom` that migration 11 added → those positions can't be saved from the form (functional bug, not security). | Reported only. |
| L14 | Unused packages: `laravel/ui`, `intervention/image`, `spatie/laravel-sluggable` (a custom slug trait is used instead). | Not removed (README tells you to install them). Remove with `composer remove` if you confirm unused. |

### Checked and found OK (no change needed)
* **SQL injection:** no `DB::raw`/`whereRaw`/`selectRaw`/`orderByRaw` with user input; all queries use Eloquent bindings. Only raw SQL is a static migration.
* **CSRF:** every state-changing form has `@csrf` (+`@method`), all on the `web` group; no CSRF exclusions; no AJAX state changes.
* **Passwords:** bcrypt via `hashed` cast / `Hash::make`; `password`/`remember_token` hidden; no plaintext storage.
* **Session fixation:** `session()->regenerate()` on login, `invalidate()` + `regenerateToken()` on logout (already present).
* **Server-side authorization:** every `/admin/*` route is behind `auth` + `admin.access` + `permission:*`; article actions also use `ArticlePolicy`; publish rights re-checked server-side; authors only see/edit their own.
* **Path traversal / LFI:** no user-supplied file paths, `include`/`require`, downloads or arbitrary deletes; deletes use paths stored by the app.
* **File uploads:** `store()` → random hashed names with a content-derived extension; `image` rule (SVG not accepted) + size caps.
* **Ad HTML:** rendered in `<iframe srcdoc sandbox="allow-scripts allow-popups">` (no `allow-same-origin`) so ad code cannot read cookies/DOM — good design, kept.
* **Blade escaping:** only two `{!! !!}` uses; the live-score one is `nl2br(e(...))` (safe). JSON-LD uses `Js::from`.
* **IDOR:** admin resources are permission-gated, not owner-scoped by design; the only owner-scoped resource (articles) is policy-protected.

---

## 2. Modified / added files

| File | Problem → Fix | Why it's safe / what's preserved |
|---|---|---|
| `app/Support/HtmlSanitizer.php` (new) | H1. DOM allow-list sanitizer (no new dependency). | Same formatting tags survive; Tamil/UTF-8 verified. |
| `app/Models/Article.php` | H1 sanitize in `setBodyAttribute`; L4 safe search (escaped LIKE, FULLTEXT only on MySQL/MariaDB/PG). | Reading-time logic unchanged; search results the same on MySQL. |
| `resources/views/frontend/article-show.blade.php` | H1 sanitize at render (protects existing rows). | Layout untouched. |
| `app/Http/Controllers/ArticleController.php` | M1, L5. | Editors/Admins still preview drafts. |
| `app/Policies/UserPolicy.php`, `Admin/AdministratorController.php`, `Admin/RoleController.php` | H2. | Super Admin workflow identical. |
| `database/seeders/DatabaseSeeder.php` | H3, L1. | Local/dev seeding unchanged. |
| `app/Models/User.php` | L1. | All code paths set the flag explicitly. |
| `Auth/PublicAuthController.php`, `Auth/AdminLoginController.php`, `Auth/ForcePasswordChangeController.php` | M2, M3, L6, L8, L9. | Same screens/redirects/messages; limits are generous for humans. |
| `routes/web.php` | M2 `throttle` middleware on login/register/force-change/search/ad-click. No routes removed or renamed. | |
| `Admin/AdvertisementController.php`, `AdController.php`, `Admin/SocialLinkController.php`, `Admin/VideoController.php`, `Admin/SettingController.php`, `Requests/StoreArticleRequest.php`, `Admin/DeathNoticeController.php`, `Admin/CategoryController.php` | M4, M5, L2, L7 stricter validation. | Existing valid data still valid; only `javascript:`/odd-scheme URLs and malformed colours/IDs are now rejected. |
| `Models/BreakingNews.php`, `resources/views/layouts/app.blade.php` | M4/M5 output-side URL & colour guards. | Visual output identical for valid values. |
| `app/Services/VideoEmbedResolver.php` | L3. | YouTube/Vimeo/FB/IG/TikTok/MP4 links resolve as before. |
| `app/Http/Middleware/SecurityHeaders.php` (new), `bootstrap/app.php`, `config/security.php` (new) | M6 headers; optional `TRUSTED_PROXIES`. | CSP split so ads/embeds/Tailwind CDN keep working (§6). |
| `Middleware/CheckPermission.php`, `CheckRole.php`, `EnsureAdminAccess.php` | L8, L9. | |
| `database/migrations/2024_01_11_…obituary_ad_positions.php` | Guarded MySQL-only `ALTER … MODIFY` so tests can run on SQLite. | No change on MySQL/MariaDB. |
| `.gitignore` (new), `.env.example`, `.env.production.example` (new), `storage/app/**/.htaccess`/`.gitignore` (new), `docs/public.htaccess.additions.txt` (new) | M7, M8, session/cookie/HTTPS settings. | Dev `.env` unchanged in behaviour (`APP_DEBUG` stays true locally). |
| `tests/Unit/*`, `tests/Feature/SecurityTest.php` (new) | Regression tests. | |

No existing view markup, CSS, route name, table, or migration (apart from the guard above) was changed or removed. No data is touched.

---

## 3. Behaviour changes you may notice
* Admin "force password change" now requires letters + numbers (min 10) and a password different from the temporary one.
* Saving a URL like `javascript:…`/`ftp:…` in ad/social/live-score/canonical/video fields is rejected; existing http(s) values are unaffected.
* Colours in Settings must be `#RRGGBB` (the colour picker already produces that).
* Pasted article HTML is cleaned: `<script>`, inline event handlers, `style=""` attributes, forms, SVG, and iframes other than YouTube/Vimeo/Facebook are removed. **If your editors rely on inline `style` attributes or other embeds, tell me and I'll widen the allow-list.**
* Rapid repeated logins/searches now return "too many attempts"/429.

---

## 4. Remaining risks (not fixed)
* **No antivirus scanning.** Validation (content-sniffed MIME, extension allow-list, random names, size caps) + non-executable upload folder is the strongest practical application-level control without extra software. It does **not** guarantee malware-free files (e.g. an image with appended payload is still a valid image). To add ClamAV: install `clamav-daemon`, `composer require xenolope/quahog`, and add a validation rule that streams the uploaded temp file to `clamd` and rejects on `FOUND`. Not possible on a stock XAMPP without installing ClamAV.
* Uploaded images are stored as-is (not re-encoded). Re-encoding with the already-listed `intervention/image` would strip hidden payloads/EXIF; skipped to avoid altering image quality/animated GIFs.
* Tailwind via CDN (M9) — build locally with Vite for production.
* The Full CSP is **report-only** by default (§6).
* No dependency audit possible here (no lockfile, Packagist blocked). Run `composer update` (stays on 11.x) then `composer audit` and `npm audit` (if you add npm).
* Admin 2FA, password-reset flow, e-mail verification and audit-log viewer UI don't exist; consider adding.
* Public registration is open and accounts can't do anything but log in; consider disabling it (or e-mail verification) since readers can't comment.
* The `/up` health route and `/admin/login` are publicly reachable by design.

---

## 5. Testing actually performed
* `php -l` on every PHP file (app, routes, bootstrap, config, database, tests): **0 errors**.
* HtmlSanitizer run against 20 payloads (script, handlers, `javascript:` incl. tab/entity-obfuscated, `data:` URLs, SVG/MathML, `<noscript>` mutation XSS, foreign iframe, style attr/tag, forms, comments, protocol-relative links) and legitimate Tamil content: all dangerous output removed, normal formatting preserved.
* VideoEmbedResolver run against 10 URLs incl. hostile hosts: correct provider detection.
* Manual code review of every route, controller, middleware, policy, model, seeder, migration and Blade view; grep review for raw SQL, `{!! !!}`, `innerHTML`, `@csrf` coverage, file paths.
* **Not run (could not here):** Laravel boot, route list, Blade compilation, PHPUnit, `config:cache`, `composer audit`, browser/CSP checks.

---

## 6. Production deployment recommendations
1. Use `.env.production.example`: `APP_ENV=production`, **`APP_DEBUG=false`**, real `APP_URL=https://…`, `SESSION_SECURE_COOKIE=true`, `SESSION_ENCRYPT=true`, `SESSION_SAME_SITE=lax`, `SECURITY_HSTS=true` (only after HTTPS works everywhere).
2. Point the web server document root at `public/` only; make sure `.env`, `vendor/`, `storage/` are not web-reachable. Add `docs/public.htaccess.additions.txt` to `public/.htaccess`. For nginx, deny `\.php$` under `/storage/` and `/uploads`.
3. Database: dedicated MySQL user (not `root`), strong password, DML-only privileges at runtime; DDL only when running migrations; DB not exposed publicly.
4. `php artisan key:generate`, `migrate --force`, seed once with a strong `SUPER_ADMIN_PASSWORD`, then **remove it from `.env`**; change the Super Admin password at first login (forced).
5. `composer install --no-dev --optimize-autoloader`, then `php artisan config:cache route:cache view:cache`.
6. **CSP rollout:** open the site (home, article, video, ads, admin) with DevTools open; if no `Content-Security-Policy-Report-Only` violations appear for legitimate content, set `SECURITY_CSP_ENFORCE=true`. (Ad HTML in `srcdoc` iframes inherits the page CSP, which is why it isn't enforced by default.) Long-term: replace the Tailwind CDN with a Vite build and drop `'unsafe-inline'`/`'unsafe-eval'`.
7. Behind a proxy/Cloudflare: set `TRUSTED_PROXIES` so rate limits see real client IPs.
8. Rotate anything that was ever real in a `.env` you shared. (None found in the ZIP — `.env.example` only has placeholders.)
9. Monitor `storage/logs/laravel.log` for `Failed admin login`, `Admin permission denied`, `…throttled`; set up log rotation and backups (DB + `storage/app/public`).
10. Keep Laravel/PHP patched; schedule `composer audit` in CI.

---

## 7. How to run the tests on your machine
```bash
# after overlaying this package on a fresh Laravel 11 app (see README)
composer install
cp .env.example .env && php artisan key:generate
# phpunit.xml of the stock skeleton uses sqlite :memory: — enable it:
#   <env name="DB_CONNECTION" value="sqlite"/><env name="DB_DATABASE" value=":memory:"/>
php artisan test
```
If any test fails because of my assumptions about your skeleton, send me the output and I'll fix it.

---

## 8. Final checklist
Legend: ✅ addressed in code · 🟡 partly / needs your action · ⬜ not applicable to this package

- ✅ SQL injection protection (already safe; search LIKE hardened)
- ✅ XSS protection (sanitizer + URL/colour guards)
- ✅ CSRF protection (verified, unchanged)
- ✅ Brute-force protection
- ✅ Authentication security
- ✅ Admin authorization (+ escalation guards)
- ✅ Session security (code) · 🟡 cookie flags via `.env`
- ✅ File upload security
- 🟡 Malware upload protection (application-level only; ClamAV steps in §4)
- ✅ Path traversal protection (no vectors found)
- ✅ Mass assignment protection
- ✅ IDOR protection
- ✅ Input validation
- ✅ Security headers (CSP enforcement is your opt-in step)
- 🟡 HTTPS/cookie security (set production `.env`)
- ⬜ API security (no APIs exist)
- ✅ Error handling (needs `APP_DEBUG=false` in production)
- ✅ `.env` protection (`.gitignore`, templates)
- 🟡 Dependency security (cannot audit offline — run `composer audit`)
- 🟡 Database security (use least-privilege DB user)
- ✅ Advertisement security
- ⬜ Newspaper PDF security (feature not in this package)
- ✅ Logging/monitoring
- 🟡 Final security testing (static checks done; Laravel test-suite written but **not run here**)
