# Deploying Franca to Vercel

This app is now configured to deploy on Vercel using the community
[`vercel-php`](https://github.com/vercel-community/php) runtime. A few
things are worth knowing up front:

- **This is an unofficial, community-maintained PHP runtime**, not something
  Vercel builds or supports directly. It works, but Vercel itself doesn't
  recommend it for production-critical apps. Treat it accordingly.
- **Vercel doesn't host MySQL.** You'll need an external MySQL-compatible
  database reachable over the internet (see below).
- **The whole app is one serverless function.** Vercel's free (Hobby) plan
  caps a deployment at 12 functions, and this app has 20+ pages. Every
  request is routed through `api/router.php`, which dispatches to the real
  page script (`api/menu.php`, `api/cart.php`, etc.) based on the request
  path. If you add a new page later, add one line to the `$routes` array at
  the top of `api/router.php` -- it does not need its own Vercel function.

## What's already done

- `vercel.json` -- routes every request to `api/router.php`, configured
  with the `vercel-php@0.9.0` runtime.
- `api/` -- the entire PHP application (previously at the project root)
  now lives here, which `vercel-php` requires. `.htaccess` at the project
  root transparently maps the old URLs back to `api/...` for local XAMPP
  use, so `http://localhost/franca/menu.php` still works exactly as before.
- `config.php` -- every setting (DB credentials, `BASE_URL`, `SITE_URL`,
  MercadoPago keys) now reads from an environment variable first, falling
  back to the local XAMPP defaults if unset. Set the real values as Vercel
  environment variables (below); nothing in the code needs to change.
- **Sessions are now database-backed** (`includes/session_handler.php`,
  new `sessions` table). Serverless functions don't reliably keep files on
  disk between requests, so login, the cart, CSRF tokens, and the language
  switcher all needed to move off PHP's default file-based sessions to
  keep working correctly once deployed. This has been tested locally and
  works identically to before.
- A real preview deploy was already run from this machine and **the build
  succeeds** (single function, ~46MB bundle including all PHP source,
  translations, and images). It's currently reachable only by whoever is
  logged into the linked Vercel account, at:
  `https://franca-ib04ogrv7-santiago-y.vercel.app` -- it will show a
  database connection error until you complete the steps below, since no
  real database is configured yet.

## Steps only you can do

### 1. Create a database

Vercel doesn't host MySQL, so pick an external provider that offers a
MySQL-compatible database reachable from the internet. A few options as of
today (compare current pricing/free-tier terms yourself, since these
change):

- [PlanetScale](https://planetscale.com/) (MySQL-compatible, Vitess-based)
- [Aiven](https://aiven.io/mysql)
- [Railway](https://railway.app/) (MySQL plugin)
- [TiDB Cloud](https://www.pingcap.com/tidb-cloud/) (MySQL-compatible)
- [Clever Cloud](https://www.clever-cloud.com/mysql-hosting/)

Once you have one, note down the host, database name, username, and
password it gives you.

### 2. Import the schema

Run `sql/schema.sql` against your new database (it creates every table and
seeds the real menu). Most providers let you do this via their web console,
or via the `mysql` CLI:

```
mysql -h <host> -u <user> -p <database> < sql/schema.sql
```

### 3. Set environment variables in Vercel

In your Vercel project (Settings -> Environment Variables), add:

| Variable | Value |
|---|---|
| `DB_HOST` | your database host |
| `DB_NAME` | your database name |
| `DB_USER` | your database user |
| `DB_PASS` | your database password |
| `BASE_URL` | *(leave empty)* -- the app is served from the domain root on Vercel |
| `SITE_URL` | your real deployment URL, e.g. `https://franca.vercel.app` |
| `MERCADOPAGO_ACCESS_TOKEN` | only once you have real MercadoPago Checkout Pro credentials -- leave unset to keep checkout safely disabled |
| `MERCADOPAGO_PUBLIC_KEY` | optional, only if you add client-side Bricks later |

### 4. Redeploy

Environment variable changes require a new deployment to take effect:

```
npx vercel --prod
```

or trigger a redeploy from the Vercel dashboard.

### 5. Connect GitHub for automatic deploys (optional but recommended)

The project (`franca` under your Vercel account) already exists from the
CLI link done during setup. In the Vercel dashboard, go to the project's
Settings -> Git and connect it to
[github.com/peladovich/Franca](https://github.com/peladovich/Franca) if it
isn't connected already -- this makes every push to `main` deploy
automatically.

### 6. Going live with MercadoPago payments

Once `SITE_URL` is your real public domain and you've set real
MercadoPago credentials, the payment webhook becomes reachable at
`SITE_URL/webhook/mercadopago.php` and checkout will work end-to-end. See
the README's existing "Going live with payments" section for the general
MercadoPago setup -- nothing about that flow changed for Vercel.
