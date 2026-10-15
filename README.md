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
