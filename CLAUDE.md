<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

## Foundational Context

This application is a Laravel application running on PHP 8.4. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If a frontend change doesn't show in the UI or you get a "Unable to locate file in Vite manifest" error, run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists, including path-scoped framework guidelines under `.ai/rules/boost`. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This project uses PHPUnit. Create tests with `php artisan make:test --phpunit {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/phpunit` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.

</laravel-boost-guidelines>

# CLAUDE.md: Branham Samwel Blog Website

Development guide for building this personal blog / thinking platform. Read this file fully before starting any work. The full client requirements are in `blog-website-requirements.md` (keep it in the project root and treat it as the source of truth for content and design decisions).

---

## 1. Project Summary

A personal blog for **Branham Samwel**, a business analyst and social researcher. It is a space for his observations, perspectives and predictions on society, business and the future. Visitors are business leaders, policy makers, researchers and the educated public, globally. Visual and editorial tone: calm, credible, readable, editorial (inspired by fritsspangenberg.com).

Publishing cadence is several articles a week, so **the writing and publishing workflow matters as much as the public site.**

---

## 2. Tech Stack and Ground Rules

| Concern | Choice |
|---|---|
| Framework | Laravel (latest stable; the project is already created by the developer) |
| Frontend | Blade + **Livewire** (latest stable) + **Tailwind CSS** |
| JS | Alpine.js (bundled with Livewire) only where needed. No heavy SPA frameworks |
| Build | Vite (Laravel default) |
| Database | SQLite for local dev; MySQL/MariaDB for production. Keep migrations DB-agnostic |
| Auth | Laravel Breeze (Livewire stack) or equivalent, **admin only**, no public registration |
| Environment | **No Docker.** No Sail, no docker-compose, no Dockerfiles. Use `php artisan serve`, `npm run dev`, and the native local PHP setup |

### Rules for Claude Code

- **Do not** create the Laravel project, change the framework version, or add Docker or Sail. The project already exists; work inside it.
- Follow Laravel conventions: Eloquent models, form requests, policies, resource-style routes, factories and seeders.
- Prefer Livewire components for interactive features (search, filtering, newsletter, contact form, admin CRUD). Use plain Blade for static or SEO-critical pages.
- Keep server-rendered HTML for public pages so they are fast and SEO-friendly. Avoid client-side-only rendering for article content.
- Use Tailwind utility classes with the design tokens in section 4. No custom CSS beyond `resources/css/app.css` base layers, `@apply` in rare cases, and the typography plugin for article bodies.
- Write Pest or PHPUnit feature tests for each phase's key behaviour (use whichever the project already has).
- Run `php artisan test` and `./vendor/bin/pint` before declaring a phase done.
- Make small, logical commits per task with clear messages. **Do not commit `.env`.**
- If a requirement is ambiguous or conflicts with this file, ask before guessing.
- At the end of each phase, stop, summarise what was built, and wait for approval before starting the next phase.

---

## 3. Core Requirements (from client form)

### Pages and features
Home, Articles/Blog (listing), Categories, About, Contact, Featured Articles, Search, Newsletter.

### Categories (fixed seed list)
1. Society and Behaviour
2. Business and Strategy
3. Generations and Lifestyles
4. Institutions and Trust
5. Notes and Observations

Visitors **can filter by category** and **can search** articles.

### Article page must show
Title, featured image, author, publication date, related articles. Sharing: **LinkedIn, Email, Copy link**.

### Content types
Blog posts, opinion pieces, educational articles, research articles. Model this as an article `type` field, not separate tables.

### Contact
Contact form, email (`branham@narrisinternational.com`), phone/WhatsApp (`0111429478`; confirm country code before publishing), LinkedIn.

### Not provided yet
No logo (use a text wordmark placeholder: "Branham Samwel"). No additional pages requested.

---

## 4. Design System

Configure these as Tailwind theme colours in the Tailwind config (or `@theme` block for Tailwind v4):

| Token | Hex | Usage |
|---|---|---|
| `brand` (Deep Green) | `#2F5D50` | Primary brand colour, links, buttons |
| `ink` (Charcoal) | `#24282D` | Headings and article body text |
| `paper` (Warm Ivory) | `#FAF7F2` | Main page background |
| `muted` (Slate Grey) | `#66716D` | Dates, captions, secondary text |

Design direction:
- Editorial and minimal. Generous whitespace, strong typographic hierarchy, restrained use of colour.
- Serif font for headings and article body (e.g. a Google Font such as Source Serif 4 or Lora), clean sans for UI elements. Self-host or load fonts efficiently.
- Article body max width around 65-75 characters for readability. Use `@tailwindcss/typography` and customise prose colours to the tokens above.
- Fully responsive, mobile-first. Accessible: sufficient contrast (check `muted` on `paper` for small text), visible focus states, semantic HTML, alt text on images.
- Support for dark mode is **not** required. Do not add it unless asked.

---

## 5. Data Model (guideline)

- `users`: admin only (the author).
- `categories`: id, name, slug, description (nullable).
- `articles`: id, user_id, category_id, title, slug (unique), excerpt, body (HTML or Markdown, decide in Phase 2 and stay consistent), type (enum: blog, opinion, educational, research), featured_image (nullable), is_featured (bool), status (draft/published/scheduled), published_at, reading_time (computed or stored), meta_title, meta_description, timestamps, soft deletes.
- `tags` and `article_tag` (optional, add only if approved in Phase 2).
- `subscribers`: id, email (unique), confirmed_at (nullable), unsubscribe_token, timestamps.
- `contact_messages`: id, name, email, subject, message, read_at (nullable), timestamps.
- `pages` or settings table for About page content and site settings (site name, tagline, contact details, social links), editable from the admin.

Add database indexes on `slug`, `status`, `published_at`, `category_id`, `is_featured`.

---

## 6. Development Phases

Work through the phases **in order**. Each phase ends with working, tested, committed code.

### Phase 0: Project Setup and Foundations
- Verify the project boots (`php artisan serve`, `npm run dev`).
- Install and configure Livewire, Tailwind (with typography plugin) and Vite if not already present.
- Configure the design tokens, fonts and base layout in `resources/css/app.css` and the Tailwind config.
- Set up `.env.example` with sensible defaults (SQLite for local). Confirm the app name is "Branham Samwel".
- Create the base Blade layout with header (wordmark, nav), footer, and flash/notification slots.
- Set up Pint, tests and a basic CI-free local workflow documented in `README.md` (how to install, migrate, seed, run, test, with **no Docker**).

**Done when:** a styled blank layout renders with the correct colours and fonts, tests pass.

### Phase 1: Database, Models and Seed Data
- Migrations, models, factories and relationships for categories, articles, subscribers, contact messages, settings.
- Seeders: five categories, one admin user (credentials from env, never hard-coded), sample articles (about 12-15 with varied categories, types and one or two featured) and default site settings.
- Model scopes: `published()`, `featured()`, `inCategory()`.
- Unit and feature tests for relationships and scopes.

**Done when:** `php artisan migrate:fresh --seed` yields a usable dataset.

### Phase 2: Admin Area (Author's Publishing Workflow)
- Authentication for the admin only (no registration route). Protect admin routes with middleware and a policy or gate.
- Livewire admin screens: articles (list with search/filter by status and category, create, edit, delete, restore), categories (edit), subscribers (list, export CSV), contact messages (list, mark read), site settings and About page content.
- Article editor: title, slug (auto-generated, editable), category, type, excerpt, body with a good writing experience (choose a rich text or Markdown editor and justify the choice), featured image upload with validation, featured toggle, status and scheduled publish date, SEO fields.
- Image handling: store on the `public` disk, generate a sensible size, require alt text.
- Scheduled publishing via Laravel scheduler (publish `scheduled` articles when `published_at` passes).
- Tests for authorisation (guests cannot access the admin) and article CRUD.

**Done when:** the author can write, schedule, publish and manage articles without touching code.

### Phase 3: Public Site, Core Pages
- **Home:** hero/intro, featured articles, latest articles, category highlights, newsletter signup.
- **Articles listing:** paginated, newest first, shows image, title, excerpt, category, date, reading time.
- **Category pages:** `/category/{slug}` with the same listing layout.
- **Article page:** `/articles/{slug}` with title, featured image, author, publication date, reading time, body, share buttons (LinkedIn, Email, Copy link via Alpine with a "Copied" confirmation), and related articles (same category, then recent).
- **About page:** content from admin settings.
- Only published articles are visible publicly. Drafts and future-dated articles return 404 for guests.
- Friendly 404 and error pages in the site design.
- Feature tests for visibility rules and routing.

**Done when:** a visitor can browse, read and share articles on every device size.

### Phase 4: Search, Filtering and Featured Content
- Livewire search component with debounced input, searching title, excerpt and body (database `LIKE` initially; design it so Laravel Scout can be swapped in later).
- Category filtering on the articles listing (Livewire, with URL query string state so filters are shareable and the back button works). Optional filter by article type.
- Dedicated Featured Articles section or page.
- Empty states ("No articles found") and pagination that preserves filters.
- Tests for search results, filtering and pagination.

**Done when:** search and category filter work together and stay in the URL.

### Phase 5: Contact and Newsletter
- **Contact page:** Livewire form (name, email, subject, message) with validation, honeypot and rate limiting. Save to `contact_messages` and send a notification email to `branham@narrisinternational.com`. Show email, phone/WhatsApp (as a `wa.me` link once the country code is confirmed) and LinkedIn.
- **Newsletter:** Livewire signup form (header/footer/home/article end), double opt-in with confirmation email, unsubscribe link, duplicate handling, rate limiting.
- Mail via Laravel mailables; use the `log` mailer locally and keep SMTP settings in `.env`.
- Optional: queue the emails using the `database` queue driver (no Docker or Redis required).
- Tests for validation, throttling, confirmation and unsubscribe.

**Done when:** messages arrive in the admin and by email, and subscribers can confirm and unsubscribe.

### Phase 6: SEO, Performance and Accessibility
- Per-page meta titles and descriptions, canonical URLs, Open Graph and Twitter card tags (important for LinkedIn sharing: use the featured image and excerpt).
- `sitemap.xml` (generated), `robots.txt`, RSS feed (`/feed`), and JSON-LD structured data (`Article`, `WebSite`, `BreadcrumbList`).
- Image optimisation: lazy loading, width/height attributes, responsive sizes.
- Eager loading to prevent N+1 queries; cache expensive queries where sensible.
- Accessibility pass: keyboard navigation, headings order, landmarks, colour contrast, form labels and ARIA for Livewire dynamic updates.
- Performance check with Lighthouse, targeting 90+ on all categories.

**Done when:** pages share nicely on LinkedIn, validate in structured-data tools and meet Lighthouse targets.

### Phase 7: Polish, Hardening and Deployment Prep
- Security review: CSRF, mass assignment, authorisation, file upload validation, XSS-safe rendering of article bodies (sanitise HTML if rich text is used), security headers.
- Final design polish and responsive QA across common breakpoints.
- Production checklist in `README.md`: `.env` setup, `php artisan config:cache route:cache view:cache`, `storage:link`, scheduler cron entry, queue worker (if used), `npm run build`, database backups. Target a standard shared/VPS PHP host, **not** containers.
- Remove demo or placeholder data from production seeders, and keep sample data in a separate dev-only seeder.
- Final full test run and Pint pass.

**Done when:** the site can be deployed to a standard PHP host following the README alone.

---

## 7. Open Questions (ask the developer before the relevant phase)

1. Editor choice for article bodies: rich text (e.g. Trix/TipTap) or Markdown? (Phase 2)
2. Phone number country code for the `wa.me` link and display format. (Phase 5)
3. Newsletter delivery: send from the app, or integrate a provider (Mailchimp, Brevo, etc.)? (Phase 5)
4. Will a logo be supplied later? Until then, use a text wordmark. (Phase 0)
5. Will tags, comments, or analytics be wanted later? These are **out of scope** unless approved.

---

## 8. Out of Scope (do not build unless asked)

- Docker, Sail, or any containerisation
- Public user registration, comments, or member accounts
- Dark mode, multi-language support
- Paid memberships or e-commerce
- Separate JavaScript SPA or API-first architecture

---

## 9. Common Commands

```bash
# Setup
composer install && npm install
cp .env.example .env && php artisan key:generate
touch database/database.sqlite
php artisan migrate:fresh --seed
php artisan storage:link

# Develop (run in separate terminals)
php artisan serve
npm run dev

# Quality
php artisan test
./vendor/bin/pint

# Scheduler locally
php artisan schedule:work
```