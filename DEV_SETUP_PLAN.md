# Plan: Docker Compose Local Dev Environment for mytheme

## Context
The restaurant ordering system at `/home/linuxdudu/codes/xianfoodbar/mytheme` is a WordPress theme embedding a Symfony 6.0 app. WordPress **must boot first** — Symfony depends on WordPress globals (`$wpdb`, `ABSPATH`, `is_user_logged_in()`, etc.). The ORM library (dbout/wp-orm) throws if `$wpdb` is not loaded.

Goal: create a Docker Compose setup that runs WordPress + MySQL locally, with the theme mounted in the correct path, developer-friendly env, and writable directories.

---

## Key Constraints Found

- Theme folder must be named `mytheme` — asset paths in `public/build/manifest.json` are hardcoded to `/wp-content/themes/mytheme/`
- `vendor/` already exists (195MB) — no `composer install` needed in Docker
- Frontend assets pre-built in `public/build/` and `public/umi/` — no Node.js needed
- **Writable dirs needed by `www-data`:**
  - `var/` (Symfony cache + logs)
  - `public/build/` (`upgrade.php` rewrites `manifest.json` and `version.lock` on first load)
  - `public/upload/` (file uploads)
  - `src/` (`AdminLoginController` writes a `logined` sentinel file here)
  - Theme root itself (writes `version.lock`)
- `.env.local.php` exists and takes precedence over `.env` in Symfony — must be updated for dev mode
- `bin/console` does not exist in this repo — cache clear must use a workaround

---

## Files Created / Modified

### 1. `docker-compose.yml` (NEW)
**Path:** `/home/linuxdudu/codes/xianfoodbar/docker-compose.yml`

```yaml
services:

  db:
    image: mysql:8.0
    restart: unless-stopped
    environment:
      MYSQL_ROOT_PASSWORD: rootpassword
      MYSQL_DATABASE: wordpress
      MYSQL_USER: wordpress
      MYSQL_PASSWORD: wordpress
    volumes:
      - db_data:/var/lib/mysql
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost", "-u", "root", "-prootpassword"]
      interval: 5s
      timeout: 5s
      retries: 10

  wordpress:
    image: wordpress:php8.1-apache
    restart: unless-stopped
    depends_on:
      db:
        condition: service_healthy
    ports:
      - "8080:80"
    environment:
      WORDPRESS_DB_HOST: db:3306
      WORDPRESS_DB_NAME: wordpress
      WORDPRESS_DB_USER: wordpress
      WORDPRESS_DB_PASSWORD: wordpress
      WORDPRESS_TABLE_PREFIX: wp_
      WORDPRESS_DEBUG: "1"
    volumes:
      - ./mytheme:/var/www/html/wp-content/themes/mytheme
      - wp_core:/var/www/html
      - wp_uploads:/var/www/html/wp-content/uploads
    # Custom entrypoint: fix permissions (runs as root), then hand off to the
    # original WP entrypoint so wp-config.php is generated from env vars.
    entrypoint: >
      bash -c "
        chmod -R 777 /var/www/html/wp-content/themes/mytheme/var &&
        chmod -R 777 /var/www/html/wp-content/themes/mytheme/public/build &&
        chmod -R 777 /var/www/html/wp-content/themes/mytheme/public/upload &&
        chmod 777 /var/www/html/wp-content/themes/mytheme &&
        chmod 777 /var/www/html/wp-content/themes/mytheme/src &&
        exec docker-entrypoint.sh apache2-foreground
      "

volumes:
  db_data:
  wp_core:
  wp_uploads:
```

**Design notes:**
- `wp_core` named volume: WordPress image copies core files into `/var/www/html` on first start. A named volume lets this happen once and persist. The theme bind-mount layers on top at the specific themes path.
- `entrypoint` override (not `command`): runs as root so chmod works, then execs the original `docker-entrypoint.sh apache2-foreground` — this triggers WP's config generation logic (generates `wp-config.php` from env vars) then starts Apache. This is the correct way to add pre-start steps without breaking WP's own setup.
- `wp_uploads` is separate from `public/upload/` — WP media vs Symfony uploads.
- The theme is already mounted by the volume — no separate WP download or copy step needed.

---

### 2. `.env` (NEW — Symfony fallback)
**Path:** `/home/linuxdudu/codes/xianfoodbar/mytheme/.env`

```dotenv
APP_ENV=dev
APP_DEBUG=true
APP_SECRET=dev-local-secret-change-for-prod
HIDE_DISCOUNT=false
# ROOT_URI is computed at runtime by WordPress (get_theme_root_uri())
# This file is only read if .env.local.php does not exist
```

---

### 3. `.env.local.php` (MODIFIED)
**Path:** `/home/linuxdudu/codes/xianfoodbar/mytheme/.env.local.php`

Changed `APP_ENV` from `prod` to `dev`, added `APP_DEBUG=true`. The `ROOT_URI` closure is preserved — it returns empty string outside WordPress, correct URI when WordPress is loaded.

```php
<?php

// LOCAL DEV version — do not commit. To restore prod: composer dump-env prod

return array(
    'APP_ENV' => 'dev',
    'APP_DEBUG' => true,
    'HIDE_DISCOUNT' => false,
    'APP_SECRET' => 'dev-local-secret-change-for-prod',
    'ROOT_URI' => function_exists('get_theme_root_uri') ? get_theme_root_uri() . '/' . basename(__DIR__) : '',
);
```

---

### 4. `Makefile` (NEW)
**Path:** `/home/linuxdudu/codes/xianfoodbar/Makefile`

```makefile
.PHONY: up down logs shell cache-clear ps perms db-shell

up:
	docker compose up -d

down:
	docker compose down

ps:
	docker compose ps

logs:
	docker compose logs -f wordpress

shell:
	docker compose exec wordpress bash

perms:
	docker compose exec wordpress bash -c "\
		chmod -R 777 /var/www/html/wp-content/themes/mytheme/var && \
		chmod -R 777 /var/www/html/wp-content/themes/mytheme/public/build && \
		chmod -R 777 /var/www/html/wp-content/themes/mytheme/public/upload && \
		chmod 777 /var/www/html/wp-content/themes/mytheme && \
		chmod 777 /var/www/html/wp-content/themes/mytheme/src \
	"

cache-clear:
	docker compose exec wordpress bash -c "\
		php /var/www/html/wp-content/themes/mytheme/vendor/symfony/console/Resources/bin/console \
		--project-dir=/var/www/html/wp-content/themes/mytheme cache:clear --env=dev \
	"

db-shell:
	docker compose exec db mysql -u wordpress -pwordpress wordpress
```

---

## How the Admin Account Works

The restaurant admin panel (`/adminpanel/login`) has **no separate user system** — it calls WordPress's `wp_signon()` and checks `is_super_admin()`. The same credentials work for both `/wp-admin` and `/adminpanel/login`.

| Environment | Account source |
|---|---|
| **Dev (Docker)** | Created fresh during the WordPress install wizard. The first account is automatically a super admin. |
| **Production** | Existing WordPress super admin credentials in the live database. |

Dev and prod databases are completely isolated — no data syncs between them.

---

## Folder Structure

The `docker-compose.yml` lives at the project root (one level above the theme). The `wordpress:php8.1-apache` image ships with WordPress core — no separate WP download needed.

```
/home/linuxdudu/codes/xianfoodbar/   ← repo root
├── docker-compose.yml               ← mounts ./mytheme into the container
├── Makefile
├── DEV_SETUP_PLAN.md
└── mytheme/                         ← theme directory
    ├── .env
    ├── .env.local.php               ← modified for dev mode
    └── ...theme files...
```

Inside the container, the theme is accessible at:
```
/var/www/html/wp-content/themes/mytheme/
```

---

## Steps to Start the Dev Environment

```bash
cd /home/linuxdudu/codes/xianfoodbar
docker compose up -d
```

Then:

1. **`http://localhost:8080`** — complete the WordPress install wizard (creates WP tables + admin account)
2. **`http://localhost:8080/wp-admin`** → Appearance → Themes → Activate **"My Theme"** (creates 6 restaurant tables)
3. **`http://localhost:8080/adminpanel/login`** — restaurant admin panel

---

## Verification Commands

```bash
# Containers running
docker compose ps

# Theme mounted correctly
docker compose exec wordpress ls /var/www/html/wp-content/themes/mytheme/

# www-data can write to var/
docker compose exec wordpress su -s /bin/bash www-data -c \
  "touch /var/www/html/wp-content/themes/mytheme/var/test && rm /var/www/html/wp-content/themes/mytheme/var/test"

# Custom restaurant tables exist (after theme activation)
docker compose exec db mysql -u wordpress -pwordpress wordpress -e "SHOW TABLES LIKE 'wp_res_%';"
# Expected: wp_res_desk, wp_res_menu, wp_res_menu_category, wp_res_order, wp_res_order_detail, wp_res_menu_discount

# Symfony admin route responds
curl -s -o /dev/null -w "%{http_code}" http://localhost:8080/adminpanel/login
# Expected: 200
```

---

## Notable Gotchas

| Issue | Fix |
|---|---|
| `wp-config.php` not generated | Use `entrypoint` override (not `command`) — already done |
| `version.lock` write fails | Theme root chmod 777 is in the entrypoint |
| `manifest.json` paths still have `{{ themes }}` | Delete `version.lock` to force `updateVersion()` to re-run on next request |
| Session cookies broken | Always use `localhost:8080`, not `127.0.0.1:8080` |
| `CORS` header hardcodes `localhost:8000` | Not an issue unless running a separate front-end on a different port |
| `bin/console` doesn't exist | Use full vendor path — covered in `make cache-clear` |
