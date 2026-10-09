# Ceylon Focus News — Laravel News Portal

A modern news portal with a full admin panel, Super Admin / RBAC system, video &
social embed management, advertisement management, and SEO-friendly public pages.

> **Read this before you start.** This project's PHP/Blade/migration source code
> is complete and ready to drop into a Laravel installation, but it was written
> without network access, so `vendor/` (Composer) and `node_modules/` are **not**
> included. Follow the setup steps below — they take about five minutes.

> **Security-hardened build.** See `SECURITY_AUDIT_REPORT.md` for findings, fixes, tests and production deployment steps (use `.env.production.example` on a live server).

---

## 1. What's included

- Laravel 11-style application code: `app/`, `database/`, `routes/`, `resources/views/`
- 11 migrations covering: users, roles/permissions (RBAC), categories + subcategories,
  tags, articles + gallery images, videos, advertisements + impression/click logs,
  social links, media library, settings, breaking news ticker, audit logs
- Full RBAC: Super Admin, Administrator, Editor, Author roles + granular permissions,
  enforced with middleware (`CheckPermission`, `CheckRole`, `EnsureAdminAccess`) and
  Policies (`ArticlePolicy`, `UserPolicy`) — **all checks are server-side**, never
  just hidden buttons
- Admin panel: dashboard, news CRUD (with SEO fields, gallery, tags, scheduling),
  categories, videos (paste-a-URL → auto embed for YouTube/Facebook/Instagram/
  TikTok/Vimeo/MP4), advertisements (image/HTML/AdSense, 12 positions, device
  targeting, scheduling, CTR tracking), social links, website settings,
  administrator management, role/permission management
- Public site: homepage (breaking ticker, hero, latest, trending, per-category
  sections), article page (SEO tags, OG tags, NewsArticle JSON-LD, related
  articles), category/tag/author pages, `/videos` with VideoObject JSON-LD,
  `/search`, ad slots rendered in a **sandboxed iframe** (so ad HTML/JS can never
  touch the parent page — see Security notes)
- Seeders: permissions, roles, categories, a Super Admin from `.env`, demo
  admin/editor/author accounts, and demo articles/videos/ads

## 2. What you need to add yourself

- Run `composer install` / `npm install` locally (see step 3) — not runnable here
- A rich text editor wired to the `body` textarea in
  `resources/views/admin/articles/_form.blade.php` (TipTap, Quill, or CKEditor —
  the field and validation already expect HTML in that textarea)
- Compiled Tailwind via Vite for production. To keep this deliverable runnable
  with zero build step, views load **Tailwind via CDN** — swap this for
  `npm run build` + `@vite` directives before going to production (CDN Tailwind
  is fine for development, not for production performance/CSP)
- XML sitemap / RSS feed / robots.txt routes (the DB and SEO fields to support
  them are already in place; add two small controllers when you're ready)
- Comments, newsletter, and contact-message modules were intentionally left out
  of this version to match the scope you asked for — the `audit_logs` and
  `settings` tables are structured so they'd drop in cleanly later if needed

## 3. Setup

```bash
# 1. Create a fresh Laravel 11 app, then copy this project's app/, database/,
#    routes/, resources/, bootstrap/app.php over it (overwrite).
composer create-project laravel/laravel ceylon-focus-news
# copy files from this delivery into the new project, overwriting duplicates

cd ceylon-focus-news
composer require laravel/ui intervention/image spatie/laravel-sluggable

# 2. Environment
cp .env.example .env
php artisan key:generate

# 3. Configure your MySQL database in .env (DB_DATABASE, DB_USERNAME, DB_PASSWORD)
#    Also set SUPER_ADMIN_EMAIL / SUPER_ADMIN_PASSWORD for local dev.

# 4. Database
php artisan migrate --seed
php artisan storage:link

# 5. Run it
php artisan serve
```

Visit `http://localhost:8000` for the public site and
`http://localhost:8000/admin/login` for the admin panel.

## 4. Default local accounts (from seeders — change immediately, see Security)

| Role          | Email                              | Password        |
|---------------|-------------------------------------|-----------------|
| Super Admin   | value of `SUPER_ADMIN_EMAIL` in `.env` | value of `SUPER_ADMIN_PASSWORD` |
| Administrator | administrator@ceylonfocus.test      | `password`      |
| Editor        | editor@ceylonfocus.test             | `password`      |
| Author        | author@ceylonfocus.test             | `password`      |

All admin accounts have `must_change_password = true` on first login where
applicable, forcing a real password to be set before continuing.

## 5. RBAC model

- `is_super_admin` flag on `users` bypasses every permission check
  (`User::isSuperAdmin()`) — this is the one true "god mode" flag.
- Roles (`roles` table) bundle permissions (`permissions` table) via
  `permission_role`. Users get roles via `role_user`.
- Per-user permission overrides are supported via `permission_user`
  (e.g. give one Editor extra `manage-advertisements` access without a new role).
- Every admin route is wrapped in `middleware('permission:xyz')` in
  `routes/web.php` — removing a button in Blade is never the only protection.
- `ArticlePolicy` additionally restricts Authors to editing only their own,
  unpublished articles; `publish-news` is checked server-side even if a client
  tampers with the `status` field in a form submission.
- The primary Super Admin account can't be deleted or demoted by anyone
  (`UserPolicy::delete()`), preventing accidental full lockout.

## 6. Security notes

- Advertisement HTML/JS/AdSense code renders inside a **sandboxed `<iframe
  srcdoc>`** (`resources/views/frontend/partials/ad-slot.blade.php`) so
  malicious or buggy ad code cannot read cookies or manipulate the main page.
  Still only paste code from ad networks you trust.
- Admin login (`/admin/login`) is rate-limited (5 attempts/60s per email+IP).
- Passwords are hashed via Laravel's `hashed` cast; new admins get a random
  16-character temporary password and must change it before accessing anything.
- All file uploads should be validated for MIME/size in production — the
  current rules (`image`, `mimes:...`, `max:...`) are a baseline; add
  virus-scanning if this will accept uploads from many editors.
- CSRF is on by default (Laravel), and all destructive actions require POST/DELETE
  with a confirmation dialog in the admin UI.

## 7. Extending this

Every admin module follows the same pattern (controller → form request/inline
validation → Blade `_form.blade.php` partial shared by create/edit → index
table). Use `admin/articles/` or `admin/categories/` as the template for new
modules like comments or newsletter if you add them later.
