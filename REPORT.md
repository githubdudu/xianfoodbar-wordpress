# Restaurant Ordering System — Full Report

## What It Is

This is a **WordPress theme embedding a full Symfony 6.0 microkernel application**. It's not a traditional theme or plugin — it uses WordPress for database/user management while running Symfony for routing, dependency injection, and templating. The target market appears to be Chinese restaurants, given the language throughout.

**Version**: 2.4.9

---

## Directory Structure

```
mytheme/
├── config/          # Symfony configuration (bundles, routes, services)
├── src/
│   ├── Controller/  # HTTP handlers (Admin/, Api/, System/, + root controllers)
│   ├── Core/        # Base controllers, UI builders, SSE, Excel export
│   ├── Entity/      # Doctrine entities (Post, Account, Store, Message, TempUser)
│   ├── Model/       # DB models: Order, OrderDetail, Menu, MenuCategory, MenuDiscount, Desk
│   ├── Form/        # Symfony form types (8 files)
│   └── Service/     # AdminMenuGenerator, AdminMessage, AdminLogger, AdminRequests
├── templates/       # Twig templates (admin/, user/, order/)
├── public/
│   ├── build/       # Webpack Encore bundles
│   └── umi/         # Umi.js (React) SPA assets
├── vendor/          # Composer packages
├── functions.php    # WordPress hooks + table creation
├── upgrade.php      # DB migrations
└── composer.json
```

---

## Custom Database Tables

All prefixed with `wp_res_`:

| Table | Purpose |
|---|---|
| `wp_res_desk` | Restaurant table/desk management |
| `wp_res_menu` | Menu items |
| `wp_res_menu_category` | Menu categories |
| `wp_res_order` | Order records |
| `wp_res_order_detail` | Order line items |
| `wp_res_menu_discount` | Time-based discounts |

ORM: `dbout/wp-orm` (built on CakePHP query builder + Laravel pagination).

---

## Feature Set

| Feature | Details |
|---|---|
| **Dine-in ordering** | QR code per table → customer submits order |
| **Takeaway/delivery** | Admin creates orders with address, delivery date/time |
| **Shared tables** | Multiple parties share one table (`is_pin`/`pin_num`) |
| **Kitchen display** | Real-time SSE stream showing uncooked items |
| **Menu management** | Categories, items, enable/disable toggle |
| **Discounts** | Time-based, percentage or fixed amount |
| **Payment types** | Card, cash, WeChat/Alipay, bank transfer, other (hardcoded) |
| **Order statuses** | Draft (0), Paid (1), Completed (2) |
| **Excel export** | Orders and menu data via PhpSpreadsheet |
| **Admin panel** | Full CRUD for orders, menus, desks, discounts, settings |

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend framework | Symfony 6.0 (microkernel) |
| WordPress integration | Custom `WordPressFunc` trait + wp-orm |
| Frontend SPA | Umi.js (React, Ant Group) |
| UI components | Ant Design |
| Build pipeline | Webpack Encore |
| Templating | Twig |
| Excel export | PhpSpreadsheet |
| Real-time | Server-Sent Events (SSE) |

---

## API Endpoints

### Public (customer-facing)

- `POST /api/order/add` — create/add to order
- `GET /api/order/info/{id}` — order details
- `GET /api/order/check/{sn}` — validate order
- `GET /api/menu/get/{id}` — fetch menu item
- `GET /api/desk/get/{id}` — desk info

### Admin

- Full CRUD for orders, menus, categories, discounts, desks
- `GET /api/admin/menu_info` — SSE stream for kitchen display
- `POST /api/upload` — file upload

---

## Security Observations

### In Place

- CSRF token on login
- `isAdmin()`/`isLogin()` guards on most endpoints
- Parameterized queries via ORM (no raw SQL injection risk)

### Gaps

- No input sanitization on most endpoints (raw `$_REQUEST` data stored directly)
- CSRF protection only on login — most admin POST endpoints are unprotected
- No rate limiting (login brute force, order spam)
- Login state stored as a filesystem file (`logined` file in theme root) — fragile and non-standard
- Order serial numbers use `'od' . date('YmdHis') . rand(1111, 9999)` — only 8,889 unique values per second, collision risk under load

---

## Notable Issues

1. **Hardcoded NZ timezone** — `date_default_timezone_set('NZ')` in `functions.php:3`, regardless of actual restaurant location.
2. **All comments in Chinese** — limits maintainability for non-Chinese developers.
3. **Unused entities** — `Account`, `Store`, `Message`, `TempUser` are defined but not wired up.
4. **SSE polling loop** — kitchen display polls every 5 seconds in a PHP loop; not scalable.
5. **N+1 query pattern** — orders fetched with `with('details')` then iterated in PHP for counts.
6. **Payment types hardcoded** in PHP — no admin configuration option.
7. **Upgrade/migration system** — manual string replacement in `upgrade.php` on compiled JS bundles (fragile).

---

## Overall Assessment

**Strengths**: Complete end-to-end restaurant workflow, modern frontend (React/Ant Design), real-time kitchen display, solid admin panel, Excel exports, discount system.

**Weaknesses**: Security hardening needed before production use (sanitization, CSRF on all mutations), timezone bug, polling-based real-time is inefficient, hybrid WP+Symfony architecture creates maintenance complexity.

This is a functional, feature-rich system well-suited for small-to-medium restaurants, but needs security and stability work before scaling or exposing publicly.
