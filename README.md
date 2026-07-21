# Franca Dining & Coffee

Full-stack restaurant website (public site + admin panel) built with PHP + MySQL,
based on the Franca design system (coffee/sage palette, Source Serif 4 + Plus
Jakarta Sans, Material Symbols).

## Requirements
- XAMPP (Apache + MySQL + PHP 8+) — already installed on this machine at `C:\xampp`.
- This project already lives at `C:\xampp\htdocs\franca`.

## Setup
1. Start MySQL and Apache from the XAMPP Control Panel (or `mysql_start.bat` /
   `apache_start.bat` in `C:\xampp`).
2. Import the database:
   ```
   C:\xampp\mysql\bin\mysql.exe -u root --default-character-set=utf8mb4 < C:\xampp\htdocs\franca\sql\schema.sql
   ```
   (The `--default-character-set=utf8mb4` flag is required so accented characters
   like "jamón" import correctly.)
3. Visit **http://localhost/franca/index.php**

If your MySQL root user has a password, or you serve the site from a different
path, edit `config.php`.

## Admin login
**http://localhost/franca/admin/login.php**
- Email: `admin@franca.uy`
- Password: `FrancaAdmin123!`

Change this password immediately if this site is ever exposed beyond localhost —
do so from `admin/settings.php`-style access is not yet built for password
changes, so update it directly via SQL:
```sql
UPDATE users SET password_hash = '<new bcrypt hash>' WHERE email = 'admin@franca.uy';
```
Generate a hash with: `php -r "echo password_hash('yourpassword', PASSWORD_DEFAULT);"`

## What's included

**Public site**
- `index.php` — home page (hero, popular items, newsletter)
- `menu.php` — full menu grouped by category
- `dish.php?id=` — dish detail with ingredients + add to bag
- `cart.php` — bag / checkout, redirects to MercadoPago for payment (guest or logged-in)
- `order-confirmation.php` — payment/order status, only trusts DB state set by the webhook
- `reservations.php` — table booking + location/hours
- `register.php` / `login.php` / `logout.php` / `profile.php` — customer accounts,
  order history (with payment status), reservation history
- `webhook/mercadopago.php` — public endpoint MercadoPago calls to confirm payment (see Payments section)

**Admin panel** (`/admin`, role-protected)
- Dashboard with daily stats (revenue/order counts only include paid orders)
- Menu item CRUD (with image upload or picking from `assets/img`)
- Category CRUD
- Order management — status updates gated on `payment_status = 'paid'`
- Reservation management (confirm/cancel)
- User management (promote/demote admin)
- Site settings (address, hours, contact info, map coordinates)

## Database
See `sql/schema.sql` for the full schema: `users`, `categories`, `menu_items`,
`reservations`, `orders` (with payment columns), `order_items`,
`newsletter_subscribers`, `settings`, `payment_events` (webhook audit log).

## Menu data
The menu is transcribed from the real Franca physical menu (6 photos), in
Spanish, with prices in Uruguayan pesos (`$U`, no decimals). 76 items across
6 categories: **Café**, **Mañanas y Tardes**, **Mediodías**, **Bebidas**,
**Almacén**, **Pedidos Especiales**. `sql/real_menu.sql` is the standalone
reseed script if you need to re-import just the menu without touching users
or reservations (it truncates `orders`/`order_items`/`menu_items`/`categories`
first, since orders reference menu items by foreign key).

Editable from the admin panel (`admin/menu-items.php`, `admin/categories.php`),
including item photos — most items currently reuse the closest available stock
photo from `assets/img/` or have no photo (falls back to the hero image);
upload real dish photos there when available.

## Map
`reservations.php` embeds a keyless Google Maps iframe centered on
`map_lat`/`map_lng` (editable in `admin/settings.php`). No API key needed.

## Payments (MercadoPago) — currently disabled by design

**Orders can only be placed after payment is verified.** There is no "mark as
paid" checkbox and no way to bypass this — `cart.php`'s checkout handler
refuses to create an order at all if `MERCADOPAGO_ACCESS_TOKEN` is empty in
`config.php` (fail-closed), and `admin/orders.php` blocks moving any order
past "pending" into the kitchen queue (preparing/ready/completed) unless
`payment_status = 'paid'` — enforced server-side, not just hidden in the UI.

**Trust model** (see `webhook/mercadopago.php` header comment for full detail):
an order is only ever marked `paid` by `webhook/mercadopago.php`, which
independently re-fetches the payment from MercadoPago's API using our secret
access token and cross-checks the amount and order reference. Nothing from a
redirect URL, a form field, or the raw webhook body is ever trusted directly —
that's what makes it unspoofable. Every webhook call is logged to
`payment_events` for audit, whether or not it changed anything.

### Going live — what you need to do
1. Create a MercadoPago account and get your credentials at
   https://www.mercadopago.com.uy/developers/panel (test/sandbox credentials
   are free, no business verification needed to start testing).
2. Put your **Access Token** in `config.php`:
   ```php
   define('MERCADOPAGO_ACCESS_TOKEN', 'APP_USR-...');
   ```
3. **Webhooks need a public URL.** MercadoPago's servers call
   `notification_url` (`SITE_URL/webhook/mercadopago.php`) directly — they
   cannot reach `http://localhost`. For local testing, expose your XAMPP
   server with a tunnel (e.g. `ngrok http 80`) and set `SITE_URL` in
   `config.php` to the tunnel's HTTPS URL. For production, deploy to a real
   domain and update `SITE_URL` accordingly.
4. Test with MercadoPago's sandbox test cards before going live:
   https://www.mercadopago.com.uy/developers/en/docs/checkout-pro/additional-content/test-cards
5. Once confirmed working end-to-end in sandbox, swap in your production
   Access Token.

Until step 2 is done, the site intentionally shows "Payment Unavailable" on
the checkout page rather than accepting unpaid orders.
