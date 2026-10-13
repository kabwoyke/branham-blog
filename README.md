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

Fonts: Source Serif 4 (headings and article body) and Instrument Sans (UI), loaded through the Vite `@fonts` directive.
