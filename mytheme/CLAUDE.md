# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

`mytheme` is a WordPress theme that embeds a full Symfony 6.0 micro-kernel application. It is one part of a larger repo (`xianfoodbar/`, parent directory) that also contains a `Makefile`, `docker-compose.yml`, custom WP plugins, and `docs/`. This directory (`mytheme/`) is its own Composer/Symfony project root — run all PHP/Symfony commands from here.

### How WordPress and Symfony fit together

- WordPress boots normally (this theme is the active theme). For any request WP can't match to a more specific template, it falls back to the theme's `index.php`, which does `require 'public/index.php'`.
- `public/index.php` is the standard Symfony front controller: it loads `.env`, builds `App\Kernel`, and lets Symfony's router (`config/routes.yaml`, attribute routes in controllers) handle the request end-to-end (including `/api/*` endpoints and the `/adminpanel/*` admin panel).
- So: WP request lifecycle (rewrite rules, `wp-config.php`, plugins) runs first, then for unmatched routes Symfony fully takes over rendering/JSON — there is no API gateway or separate web server for the Symfony part.
- Data access goes through `dbout/wp-orm` (`Dbout\WpOrm\Orm\AbstractModel`), which talks to the *same* `$wpdb`/WordPress MySQL connection — not a separate Doctrine-managed database. `TablePrefixEventListener` is Doctrine-related only (kept for `doctrine/annotations` metadata, table prefixing) and is largely vestigial next to wp-orm.

### Controller hierarchy

```
AbstractController (Symfony)
  -> RESTController   (src/Core/Controller/RESTController.php) — JSON helpers: addJsonData(), sendJson(), decodes JSON request bodies in the constructor
    -> CoreAdminController (src/Core/Controller/CoreAdminController.php) — admin panel scaffolding: tableTemplate()/tabsTemplate()/formTamplate(), dual HTML-or-JSON rendering via CheckType::isHtmlType()
      -> Wordpress (src/Core/Controller/Wordpress.php) — pulls in the WordPressFunc trait (get_posts/etc. wrappers) and WP-specific endpoints (upload, avatar)
```

Real controllers under `src/Controller/` (e.g. `Remote.php`, `MenuController.php`, `DeskController.php`, and everything in `Admin/`, `System/`, `Api/`) extend `Wordpress` (or occasionally `CoreAdminController` directly) and get `sendJson()`/`addJsonData()` for free. `RESTController::sendJson()` builds the whole API response shape (`status`, `message`, `_option`, plus anything added via `addJsonData()`) — don't hand-roll `JsonResponse` in a controller, use these.

`CheckType::isHtmlType()` is a static flag toggled per-request to decide whether `CoreAdminController` template helpers render a Twig admin page or return the same data as JSON — used so the admin panel templates and the JSON API used by the frontend SPA can share one controller method.

### Data layer: watch for dead code

- `App\Model\*` (e.g. `src/Model/Order.php`, `Desk.php`) extends `Dbout\WpOrm\Orm\AbstractModel` and is the **real, live** data layer used by services like `RemoteOrderService`.
- `App\Entity\*` (e.g. `src/Entity/Order.php`) and `App\Model\OrderModel.php` reference a namespace `App\ORM\*` (`Entity`, `Model`, `DataSource`, `Mapping\*`, `Query\Pagination`) that **does not exist anywhere in `src/` or `vendor/`**. These classes would fatal-error if ever autoloaded/instantiated. Treat anything under `App\Entity\` and anything depending on `App\ORM\*` as legacy/dead code, not a second working ORM layer — don't model new code on it.

### `/api/*` is a naming convention, not a public API contract

Of the ~60 routes under `/api/*` (`php bin/console debug:router`), only `/api/remote/getdata/{id}` (`Remote.php`) is a genuine external integration point — the webhook WooCommerce POSTs order data to. Every other `/api/*` route (the `/api/admin/...` admin-panel CRUD endpoints, `/api/order/...`, `/api/menu/...`, `/api/desk/...`, `/api/user/...`, `/api/upload`) is internal: JSON data endpoints consumed only by this app's own bundled frontend JS (admin panel SPA and storefront, built via Webpack Encore into `public/build`), most guarded by `$this->isLogin()`. The `/api/` prefix here just signals "returns JSON via `sendJson()`" — don't treat changing these routes' shapes as a public API break, and don't assume an `/api/*` path implies third-party consumers.

### Exception handling for API routes

`src/EventListener/RemoteOrderExceptionListener.php` (auto-registered as an event subscriber — `App\:` is `autoconfigure: true` in `config/services.yaml`) converts any uncaught exception on the `/api/remote/getdata` webhook path into a JSON error response instead of Symfony's default HTML error page. It exposes the real exception message only when `kernel.debug` (bound via `services.yaml` `_defaults.bind`) is true.

### WordPress functions in tests

There is no real WordPress/MySQL bootstrap in the test suite. `tests/stubs.php` (loaded by `tests/bootstrap.php`) defines a minimal `wpdb` stub and stand-in functions like `get_option()`/`esc_sql()`. When a test needs to control DB query results, replace `$GLOBALS['wpdb']` with a fresh stub (set `nextResults`/`nextRow`) and reset wp-orm's `Database` singleton via reflection — see `RemotePhpTest::setupWpDb()` for the pattern. If you add code that calls a WP function not yet stubbed, add it to `tests/stubs.php` rather than mocking it ad hoc per test.

## Commands

All commands run from this directory (`mytheme/`).

```bash
# Run the full test suite
vendor/bin/phpunit

# Run a single test file
vendor/bin/phpunit tests/Service/RemoteOrderServiceTest.php

# Run a single test method
vendor/bin/phpunit --filter testOrderSync tests/Service/RemoteOrderServiceTest.php

# Lint a single PHP file for syntax errors
php -l src/Controller/Remote.php

# List registered routes / debug the router
php bin/console debug:router

# List listeners for a given kernel event (e.g. verifying RemoteOrderExceptionListener wiring)
php bin/console debug:event-dispatcher kernel.exception

# Frontend assets (Webpack Encore)
npm run dev        # one-off dev build
npm run watch       # rebuild on change
npm run build       # production build
```

From the parent `xianfoodbar/` directory, `make up` / `make down` manage the Docker Compose stack (WordPress + MySQL with this theme bind-mounted), and `make shell` opens a shell in the WordPress container.

## Manually exercising the Remote webhook

`Remote.php` (`/api/remote/getdata/{id}`) is the webhook WooCommerce calls. To test it without WooCommerce, POST directly to it — see the parent `README.md` for full example payloads (basic order, and delivery order with metadata/pickup time).
