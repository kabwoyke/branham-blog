# Branham Samwel: Blog

A personal blog for Branham Samwel, business analyst and social researcher. Built with Laravel, Livewire and Tailwind CSS. No Docker is used anywhere in this project.

## Requirements

- PHP 8.3+ (8.4 recommended) with the `sqlite3`, `mbstring`, `openssl`, `fileinfo` and `gd` extensions
- Composer
- Node.js 20+ and npm

## Install

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate:fresh --seed
php artisan storage:link
```

Before seeding, set `ADMIN_EMAIL` and `ADMIN_PASSWORD` in `.env`. The seeder creates the single admin user from these values and refuses to run without them. Sample articles come from `ArticleSeeder` (development data).

On Windows PowerShell, create the SQLite file with `New-Item database/database.sqlite -ItemType File` and copy the env file with `Copy-Item .env.example .env`.

## Run locally

Use two terminals:

```bash
php artisan serve
npm run dev
```

The site is served at <http://localhost:8000>.

To run the scheduler locally (used for scheduled publishing in later phases):

```bash
php artisan schedule:work
```

## Admin area

Sign in at `/admin/login` with the `ADMIN_EMAIL` and `ADMIN_PASSWORD` from `.env`. There is no public registration. Articles are written in Markdown with a toolbar and live preview; raw HTML in article bodies is stripped when rendered. Featured images are resized to 1600 px wide and stored as WebP on the `public` disk (run `php artisan storage:link`).

Scheduled articles are published by `php artisan articles:publish-scheduled`, which the scheduler runs every minute. Locally use `php artisan schedule:work`; in production add a cron entry that runs `php artisan schedule:run` every minute.

## Test and format

```bash
php artisan test
./vendor/bin/pint
```

## Build assets for production

```bash
npm run build
```

## Design tokens

Defined in `resources/css/app.css` under `@theme`:

| Token | Hex | Use |
|---|---|---|
| `brand` | `#2F5D50` | Links, buttons, brand colour |
| `ink` | `#24282D` | Headings and body text |
| `paper` | `#FAF7F2` | Page background |
| `muted` | `#66716D` | Dates, captions, secondary text |

Font: Plus Jakarta Sans for everything (headings, body and UI), loaded through the Vite `@fonts` directive. Buttons use the `btn`, `btn-primary` and `btn-outline` utilities (rounded corners) defined in `resources/css/app.css`.

## Seeders

`php artisan db:seed` creates the five categories, the admin user (from `ADMIN_EMAIL` and `ADMIN_PASSWORD`) and default site settings. Demo articles and sample images live in `DevelopmentSeeder`, which `DatabaseSeeder` only runs when the app is not in the `production` environment.

## Production deployment

Target: a standard shared or VPS PHP host (PHP 8.3+, Nginx or Apache). No containers are needed.

1. **Upload the code** (git clone or deploy archive) and point the web root at `public/`.
2. **Install dependencies:** `composer install --no-dev --optimize-autoloader`, then `npm ci && npm run build` (build locally and upload `public/build` if the host has no Node).
3. **Create `.env`** from `.env.example` and set:
   - `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://your-domain`
   - `php artisan key:generate` for `APP_KEY`
   - `DB_CONNECTION=mysql` with `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
   - `ADMIN_EMAIL` and a strong `ADMIN_PASSWORD` (used once by the seeder; remove the password from `.env` afterwards)
   - `MAIL_MAILER=smtp` with `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`
   - `SESSION_SECURE_COOKIE=true` once the site is served over HTTPS
4. **Migrate and seed:** `php artisan migrate --force && php artisan db:seed --force` (production seeding adds no demo articles).
5. **Storage link:** `php artisan storage:link` (featured images are served from `storage/app/public`).
6. **Cache for speed:** `php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan event:cache`. Re-run after every deploy.
7. **Scheduler cron** (publishes scheduled articles every minute):
   ```
   * * * * * cd /path/to/branham-blog && php artisan schedule:run >> /dev/null 2>&1
   ```
8. **Queue worker** (optional: emails are currently sent immediately, so a worker is only needed if you queue mail or jobs later): run `php artisan queue:work --tries=3 --stop-when-empty` from cron every minute, or keep `php artisan queue:work` alive with Supervisor. Set `QUEUE_CONNECTION=sync` to skip the worker.
9. **Permissions:** `storage/` and `bootstrap/cache/` must be writable by the web user.
10. **Backups:** schedule a daily database dump (for example `mysqldump`) and back up `storage/app/public` (uploaded images).
11. **Before launch:** confirm the phone country code (the contact page needs it for the WhatsApp link), set the LinkedIn URL and About text in the admin settings, and replace the wordmark if a logo is supplied.

Security headers (`X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`, and HSTS over HTTPS) are added by `App\Http\Middleware\SecurityHeaders`. Article Markdown is rendered with raw HTML stripped and unsafe links blocked; admin sign-in, contact and newsletter forms are rate limited.

## Deploying to Hostinger (shared hosting)

Hostinger serves your domain from `public_html`, so the app is kept in a private sibling folder and only the contents of `public/` go into `public_html`. Layout on the server:

```
domains/your-domain.com/
  branham-blog/   <- the whole project (private)
  public_html/    <- contents of public/ only
```

**1. Prepare locally** (shared hosting has no Node, so assets are built here):

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
```

Zip the project **excluding** `node_modules`, `.git`, `.env`, `tests` and `database/database.sqlite`. Keep `vendor/` and `public/build/` in the zip.

**2. Create in hPanel:** a MySQL database and user (Databases), a mailbox for outgoing mail (Emails), and enable the free SSL certificate (Security > SSL). Use PHP 8.3 or newer (Advanced > PHP Configuration) with the `mbstring`, `openssl`, `fileinfo`, `gd`, `pdo_mysql` and `xml` extensions enabled.

**3. Upload** with File Manager or FTP: extract the zip into `domains/your-domain.com/branham-blog/`, then move everything inside `branham-blog/public/` (including `.htaccess` and `build/`) into `public_html/`. `public/index.php` finds the sibling `branham-blog` folder on its own.

**4. Configure:** copy `.env.hostinger.example` to `branham-blog/.env` and fill it in. `PUBLIC_PATH` must be the absolute path of `public_html` (shown in File Manager); it tells Laravel where the built assets and the `storage` link live. Make `storage/` and `bootstrap/cache/` writable (755 or 775).

**5. Initialise.** With SSH (Premium and Business plans), from `branham-blog/`:

```bash
php artisan key:generate --force
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan event:cache
```

Without SSH, generate the key locally (`php artisan key:generate --show`) and paste it into `APP_KEY`. Then either import a seeded local MySQL dump through phpMyAdmin, or temporarily add one-off cron jobs (hPanel > Advanced > Cron Jobs) running `cd /home/uXXX/domains/your-domain.com/branham-blog && php artisan migrate --force && php artisan db:seed --force && php artisan storage:link && php artisan config:cache`. Remove the one-off job after it has run.

**6. Scheduler** (publishes scheduled articles): in hPanel > Advanced > Cron Jobs add one job running every minute:

```
cd /home/uXXX/domains/your-domain.com/branham-blog && php artisan schedule:run >> /dev/null 2>&1
```

**7. Check:** open the site, sign in at `/admin/login`, upload a featured image, send a test contact message and subscribe to the newsletter to confirm mail works. Then remove `ADMIN_PASSWORD` from `.env`.

Updating later: upload changed files, then run `php artisan migrate --force` and the cache commands again (`php artisan optimize:clear` first if something looks stale). Back up the database from hPanel > Backups or phpMyAdmin, and download `storage/app/public` for uploaded images.
