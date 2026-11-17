# Deploying to Hostinger (shared hosting)

This guide takes the Branham Samwel blog from your computer to a live Hostinger website, then covers updates, backups and fixing common problems.

No Docker, Node.js or special server software is needed on Hostinger. Everything is built on your computer and uploaded as a single zip.

---

## Contents

1. [What you need](#1-what-you-need)
2. [How the site is laid out on Hostinger](#2-how-the-site-is-laid-out-on-hostinger)
3. [Build the upload package](#3-build-the-upload-package-on-your-computer)
4. [Prepare hPanel](#4-prepare-hpanel)
5. [Upload and extract](#5-upload-and-extract)
6. [Create the `.env` file](#6-create-the-env-file)
7. [Initialise the site over SSH](#7-initialise-the-site-over-ssh)
8. [Add the cron jobs](#8-add-the-cron-jobs)
9. [Launch checklist](#9-launch-checklist)
10. [Updating the live site](#10-updating-the-live-site)
11. [Backups](#11-backups)
12. [Troubleshooting](#12-troubleshooting)
13. [Without SSH (fallback)](#13-without-ssh-fallback)

---

## 1. What you need

**Hostinger plan:** **Premium** or **Business** web hosting, or higher. The site needs:

| Feature | Why |
|---|---|
| SSH access | To run the one-time setup commands |
| Cron jobs | To publish scheduled articles and send newsletter emails |
| MySQL database | To store articles, settings, subscribers and messages |
| Email account | To send contact notifications and newsletters |
| PHP 8.4 | The installed packages require it |

The cheapest single-website plan may lack SSH or cron, so check before buying.

**On your computer** (already set up for development):

- PHP 8.4, Composer, Node.js and npm
- Git (the packaging command uses it to list project files)
- The project working locally (`php artisan test` passes)

**Decide before you start:**

- The domain **`branhamsamwel.com`**, already added to Hostinger
- The sending mailbox **`newsletter@branhamsamwel.com`** (see [4.3](#43-email-account-and-dns-for-subscriber-emails))
- The admin login email and a strong password

---

## 2. How the site is laid out on Hostinger

Hostinger serves your domain from a folder called `public_html`. Laravel keeps its code, settings and uploads **outside** the web root, so only the public files go in `public_html`, and the app sits in a private folder beside it:

```
/home/uXXXXXXXXX/domains/branhamsamwel.com/
├── branham-blog/      ← the app (private: code, vendor, storage, .env)
└── public_html/       ← web root (index.php, .htaccess, build/, storage link)
```

`public_html/index.php` finds the `branham-blog` folder next to it on its own. Keep the folder name **exactly** `branham-blog`.

---

## 3. Build the upload package (on your computer)

Stop `npm run dev` if it is running. Then, in the project folder:

```bash
php artisan test
php artisan deploy:package-hostinger
```

The command:

1. Builds the CSS and JavaScript (`npm run build`).
2. Copies the project files that belong on a server.
3. Installs Composer packages **without** development tools.
4. Writes **`branham-blog-hostinger.zip`** in the project folder.

The zip contains two folders, `branham-blog/` and `public_html/`. It never contains:

- `.env` (your local secrets)
- your local database or uploaded images
- logs, tests or `node_modules`
- `public/hot`: if this leftover from `npm run dev` were uploaded, the live site would try to load its CSS from your computer and appear unstyled

Option: `php artisan deploy:package-hostinger --skip-build` reuses the existing `public/build` instead of rebuilding.

---

## 4. Prepare hPanel

Log in to [hPanel](https://hpanel.hostinger.com) and open **Websites → Manage** for your domain.

### 4.1 PHP version and extensions

**Advanced → PHP Configuration**

- **PHP version:** select **8.4** and save.
- **PHP extensions:** make sure these are ticked: `gd`, `fileinfo`, `mbstring`, `openssl`, `pdo_mysql`, `intl`, `zip`.

`gd` must support WebP, because uploaded images are converted to WebP. Hostinger's `gd` does by default.

### 4.2 Database

**Databases → MySQL Databases**

1. Create a database, e.g. `blog`, which becomes `uXXXXXXXXX_blog`.
2. Create a user with a strong password, e.g. also `uXXXXXXXXX_blog`.
3. Write down the **database name**, **username** and **password**.

The host is `localhost`.

### 4.3 Email account and DNS for subscriber emails

Every email the site sends comes from **one mailbox on `branhamsamwel.com`**:

| Email | Sent to | From |
|---|---|---|
| Subscription confirmation (double opt-in) | The new subscriber | `newsletter@branhamsamwel.com` |
| New article newsletter | Every confirmed subscriber | `newsletter@branhamsamwel.com` |
| New contact message notification | `branham@narrisinternational.com` (the Email in admin **Settings**) | `newsletter@branhamsamwel.com`, with reply-to set to the visitor |

The sender's display name is **Branham Samwel** (`MAIL_FROM_NAME`). Replying to a contact notification goes straight to the visitor.

**1. Create the mailbox.** **Emails → Email Accounts**: create `newsletter@branhamsamwel.com` with a strong password and note it. You can pick another name, such as `hello@` or `notifications@`; then use that address for both `MAIL_USERNAME` and `MAIL_FROM_ADDRESS` in step 6.

**2. Check the DNS records.** This is what keeps subscriber emails out of spam. Gmail, Yahoo and Outlook reject or spam-folder mail from domains without these records. In **Emails → (your domain) → DNS settings / Email health**, Hostinger shows each record's status:

| Record | What it does | Expected |
|---|---|---|
| **MX** | Lets the mailbox receive replies | Points to Hostinger's mail servers |
| **SPF** (TXT on `@`) | Says Hostinger may send mail for the domain | `v=spf1 include:_spf.mail.hostinger.com ~all` |
| **DKIM** (TXT/CNAME, `hostingermail…._domainkey`) | Signs each email so it can't be forged | Shown by Hostinger; must say *active* |
| **DMARC** (TXT on `_dmarc`) | Tells receivers what to do with failed mail | Start with `v=DMARC1; p=none; rua=mailto:newsletter@branhamsamwel.com` |

If the domain's nameservers point to Hostinger, MX, SPF and DKIM are usually added automatically. Use the **Fix / Add records** button if any show as missing. If the domain is registered elsewhere (e.g. a Kenyan registrar), add these records in that registrar's DNS panel. Only **one** SPF record may exist; if there is already one, merge the `include:` into it.

DMARC may need adding by hand under **Domains → DNS / Nameservers → DNS records**. After a few weeks of clean sending you can tighten it to `p=quarantine`.

**3. Sending limits.** Hostinger limits how many emails a mailbox can send per hour and per day. That's fine for contact messages and a newsletter list of a few hundred. As the list grows into the thousands, check your plan's limit. A dedicated newsletter service (Brevo, Mailchimp) may be needed then.

Newsletter emails already include **one-click unsubscribe** headers (`List-Unsubscribe`), which Gmail and Yahoo require from newsletter senders, as well as an unsubscribe link in the email body.

### 4.4 SSL certificate

**Security → SSL**

Install the free SSL certificate and wait until it shows **Active**. The site forces HTTPS, so it must work first.

### 4.5 SSH access

**Advanced → SSH Access**

Enable SSH and note the command shown, for example:

```
ssh -p 65002 uXXXXXXXXX@123.45.67.89
```

---

## 5. Upload and extract

**Files → File Manager**

1. Open `domains/branhamsamwel.com/`.
2. Open `public_html/` and **delete** Hostinger's placeholder `default.php`, if present.
3. Go back up to `domains/branhamsamwel.com/`.
4. **Upload** `branham-blog-hostinger.zip` here.
5. Right-click the zip → **Extract**, into the current folder.
6. Check that you now have `branham-blog/` **beside** `public_html/`, and that `public_html/` contains `index.php`, `.htaccess` and `build/`.
7. Delete the zip from the server.

> Hidden files such as `.htaccess` may not show in File Manager. Turn on **Show hidden files** in its settings to check.

---

## 6. Create the `.env` file

The `.env` file holds the live settings and passwords. It is never part of the zip.

In File Manager, open `branham-blog/`, **copy** `.env.hostinger.example` to a new file named **`.env`**, and edit it:

```dotenv
APP_NAME="Branham Samwel"
APP_ENV=production
APP_KEY=                       # filled in automatically in step 7
APP_DEBUG=false
APP_URL=https://branhamsamwel.com

# Absolute path of public_html (see below)
PUBLIC_PATH=/home/uXXXXXXXXX/domains/branhamsamwel.com/public_html

# Scheduled publish times are entered in East Africa Time
APP_PUBLISHING_TIMEZONE=Africa/Nairobi

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=uXXXXXXXXX_blog
DB_USERNAME=uXXXXXXXXX_blog
DB_PASSWORD=your-database-password

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true
CACHE_STORE=database

QUEUE_CONNECTION=database

MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_SCHEME=smtps
MAIL_USERNAME=newsletter@branhamsamwel.com
MAIL_PASSWORD=your-mailbox-password
MAIL_FROM_ADDRESS=newsletter@branhamsamwel.com
MAIL_FROM_NAME="${APP_NAME}"

ADMIN_NAME="Branham Samwel"
ADMIN_EMAIL=branham@narrisinternational.com
ADMIN_PASSWORD=a-long-strong-password
```

**What each important value means**

| Setting | Value |
|---|---|
| `APP_URL` | The full site address with `https://`, no trailing slash |
| `PUBLIC_PATH` | The absolute path of `public_html`. Over SSH, `cd ~/domains/branhamsamwel.com/public_html && pwd` prints it. It tells Laravel where the built CSS/JS and the image link live. |
| `DB_*` | From step 4.2 |
| `MAIL_*` | From step 4.3. Username and from-address are the full mailbox address. |
| `ADMIN_EMAIL` / `ADMIN_PASSWORD` | The admin login created by the seeder. Remove the password from `.env` after step 7. |

> **Never** set `APP_DEBUG=true` on the live site. It shows passwords and code to visitors when an error happens.

---

## 7. Initialise the site over SSH

Connect with the SSH command from step 4.5 (on Windows, use PowerShell or Windows Terminal), then:

```bash
cd ~/domains/branhamsamwel.com/branham-blog

php -v
```

`php -v` must report **PHP 8.4**. If it shows another version, use `/opt/alt/php84/usr/bin/php` instead of `php` in every command below and in the cron jobs.

```bash
php artisan key:generate --force   # sets APP_KEY in .env
php artisan migrate --force        # creates the database tables
php artisan db:seed --force        # admin user, the five categories, default settings
php artisan storage:link           # makes uploaded images visible at /storage
php artisan optimize               # caches config, routes, views and events for speed
chmod -R 775 storage bootstrap/cache
```

Production seeding adds **no** demo articles.

Then secure the admin password:

1. Edit `.env` and empty the `ADMIN_PASSWORD=` line.
2. Re-cache the config:

   ```bash
   php artisan config:cache
   ```

> Whenever you change `.env` later, run `php artisan config:cache` afterwards, or the change has no effect.

---

## 8. Add the cron jobs

**Advanced → Cron Jobs.** Add **two** jobs, both set to run **every minute** (`* * * * *`). Replace `uXXXXXXXXX` and the domain with your own:

**Publish scheduled articles**

```
/usr/bin/php /home/uXXXXXXXXX/domains/branhamsamwel.com/branham-blog/artisan schedule:run
```

**Send queued newsletter emails**

```
/usr/bin/php /home/uXXXXXXXXX/domains/branhamsamwel.com/branham-blog/artisan queue:work --stop-when-empty --tries=3 --max-time=50
```

If `php -v` in step 7 was not 8.4, replace `/usr/bin/php` with `/opt/alt/php84/usr/bin/php`.

Why these are needed:

- Articles scheduled for a future time (entered in East Africa Time) are published by the first job within a minute of their time.
- When the author sends an article to subscribers, the emails are queued and the second job sends them in the background, so the admin page never times out.

---

## 9. Launch checklist

Open `https://branhamsamwel.com` and work through:

**Public site**

- [ ] Home page loads with the correct fonts and colours, not unstyled
- [ ] `http://` redirects to `https://`
- [ ] Articles, Categories, About and Contact pages load
- [ ] A missing page, e.g. `/does-not-exist`, shows the styled 404 page

**Admin** (`https://branhamsamwel.com/admin/login`)

- [ ] Sign in with `ADMIN_EMAIL` and the password you set
- [ ] **Settings:** set the site name, tagline and About text, and paste the LinkedIn, X, Instagram, TikTok and Facebook profile addresses (leave any unused platform empty)
- [ ] **Settings:** enter the phone number **with country code** (e.g. `+254 111 429 478`), so the WhatsApp link appears on the Contact page
- [ ] Save, and check that the **"Settings saved."** message appears and the About page shows the new text
- [ ] Create an article with a featured image and an image inside the body; publish it and view it
- [ ] Schedule a test article 3–5 minutes ahead and confirm it goes live on time
- [ ] Change the admin password under **Password**

**Email**

- [ ] Send a message through the Contact form and confirm it reaches `branham@narrisinternational.com` and shows under **Messages**
- [ ] Subscribe to the newsletter with your own address, click the confirmation link, then use the unsubscribe link
- [ ] Subscribe with a **Gmail** address and check that the confirmation arrives in the inbox (not spam) from *Branham Samwel &lt;newsletter@branhamsamwel.com&gt;*. In Gmail, **⋮ → Show original** should say `PASS` for SPF, DKIM and DMARC.
- [ ] Optional: send a newsletter to an address from [mail-tester.com](https://www.mail-tester.com) and aim for 9/10 or better

**Sharing and SEO**

- [ ] Paste an article link into LinkedIn's [Post Inspector](https://www.linkedin.com/post-inspector/) and check the image, title and excerpt
- [ ] `https://branhamsamwel.com/sitemap.xml`, `/robots.txt` and `/feed` load

---

## 10. Updating the live site

After making and testing changes locally:

1. **Build a new package** on your computer:

   ```bash
   php artisan test
   php artisan deploy:package-hostinger
   ```

2. **Back up first** (see [Backups](#11-backups)), at least the database and `branham-blog/storage/app/public`.
3. **Put the site in maintenance mode** over SSH:

   ```bash
   cd ~/domains/branhamsamwel.com/branham-blog
   php artisan down
   ```

4. **Upload and extract** the new zip in `domains/branhamsamwel.com/`, allowing it to **overwrite** existing files. The zip never contains `.env` or uploaded images, so both are kept.
5. **Finish over SSH:**

   ```bash
   php artisan migrate --force
   php artisan optimize:clear
   php artisan optimize
   php artisan up
   ```

6. Open the site and check that the change is live.

> If you removed files from the project, the old copies stay on the server after extracting. They are harmless, but you can delete them in File Manager.

---

## 11. Backups

| What | Where | How |
|---|---|---|
| Database (articles, settings, subscribers, messages) | MySQL | hPanel → **Backups**, or **Databases → phpMyAdmin → Export** |
| Uploaded images | `branham-blog/storage/app/public/` | hPanel → **Backups**, or compress the folder in File Manager and download it |
| Live settings and passwords | `branham-blog/.env` | Download a copy and keep it somewhere private |

Hostinger makes automatic backups (how often depends on the plan). Take a manual backup **before every update**.

**Restoring:** import the `.sql` file in phpMyAdmin, and upload the images back into `branham-blog/storage/app/public/`.

---

## 12. Troubleshooting

First look at the error log: `branham-blog/storage/logs/laravel.log`. Over SSH:

```bash
tail -n 50 ~/domains/branhamsamwel.com/branham-blog/storage/logs/laravel.log
```

| Problem | Likely cause | Fix |
|---|---|---|
| **500 Server Error** on every page | `.env` missing or wrong, missing `APP_KEY`, or `storage` not writable | Check `.env`, run `php artisan key:generate --force`, `chmod -R 775 storage bootstrap/cache`, then `php artisan config:cache` |
| "Your Composer dependencies require a PHP version >= 8.4" | Website running an older PHP | hPanel → PHP Configuration → **8.4** |
| Blank page or "File not found" | `branham-blog` folder in the wrong place or renamed | It must sit **beside** `public_html`, named exactly `branham-blog` |
| Site has **no styling** | `public_html/build/` missing, or a `public_html/hot` file exists | Delete `public_html/hot` if present, and re-upload `build/` from a fresh package |
| **Uploaded images don't show** | Storage link missing or `PUBLIC_PATH` wrong | Check `PUBLIC_PATH`, then `php artisan config:cache && php artisan storage:link` |
| **"419 Page Expired"** when saving or signing in | Site opened over `http://`, or `APP_URL` doesn't match | Use `https://`, check `APP_URL` and that SSL is active, run `php artisan config:cache` |
| **Emails not arriving** | Wrong SMTP details, or the mailbox send limit was reached | Check `MAIL_*` in `.env`, run `php artisan config:cache`, and look for mail errors in the log |
| **Subscriber emails land in spam** | SPF, DKIM or DMARC missing, or `MAIL_FROM_ADDRESS` differs from the mailbox you log in with | Fix the records in [4.3](#43-email-account-and-dns-for-subscriber-emails). `MAIL_USERNAME` and `MAIL_FROM_ADDRESS` must both be `newsletter@branhamsamwel.com`. |
| **Newsletter emails never send** | Queue cron job missing or wrong | Check the second cron job and its PHP path. Running `php artisan queue:work --stop-when-empty` by hand should send waiting emails |
| **Scheduled articles don't publish** | Scheduler cron job missing or wrong | Check the first cron job. `php artisan articles:publish-scheduled` publishes due articles immediately |
| Scheduled time is 3 hours off | Timezone setting | `APP_PUBLISHING_TIMEZONE=Africa/Nairobi` in `.env`, then `php artisan config:cache` |
| Changes to `.env` have no effect | Config is cached | `php artisan config:cache` |
| Something else looks outdated after an update | Old caches | `php artisan optimize:clear && php artisan optimize` |
| Forgot the admin password | — | Over SSH: `php artisan tinker`, then `App\Models\User::where('email', 'you@example.com')->first()->update(['password' => bcrypt('new-password')]);` |

---

## 13. Without SSH (fallback)

If your plan has no SSH, the setup commands can be run once through cron jobs:

1. On your computer, run `php artisan key:generate --show` and paste the result into `APP_KEY` in the server's `.env`.
2. In **Advanced → Cron Jobs**, add a temporary job set to run every minute:

   ```
   cd /home/uXXXXXXXXX/domains/branhamsamwel.com/branham-blog && /usr/bin/php artisan migrate --force && /usr/bin/php artisan db:seed --force && /usr/bin/php artisan storage:link && /usr/bin/php artisan optimize
   ```

3. Wait two minutes, check that the site works, then **delete this temporary job**. Leaving it would re-run the seeder every minute.
4. Add the two permanent cron jobs from [step 8](#8-add-the-cron-jobs).

For updates without SSH, use a similar temporary job running `php artisan migrate --force && php artisan optimize:clear && php artisan optimize`, then delete it.
