# Odoo → FieldKonnect Integration (internal guide)

Setup, testing and go-live steps for the Odoo push integration. The API spec for the Odoo team is in [party-prices-api.md](party-prices-api.md).

## Status

| Module | Endpoint | Status |
|---|---|---|
| Party-wise pricing | `POST/GET /api/v1/odoo/party-prices` | Built, ready for Odoo testing |
| CRM "Odoo Sync" menu | Integrations → Odoo Sync → Sync Overview (`/odoo-sync`), Party Wise Pricing (`/odoo-sync/party-prices`) | Built |
| Product category / sub-category / product | — | Not started |
| Parties, leave balance, order dispatch, invoices, outstanding | — | Not started |

## How it works

Odoo **pushes** data to FieldKonnect. We do not call Odoo.

```
Odoo ──POST──► /api/v1/odoo/party-prices   (same URL for testing and production)
                        │
                 X-API-Key header
                 ┌──────┴──────┐
            test key        live key
                 ▼              ▼
   odoo_stg_party_prices   party_product_prices
   (testing only,          (real prices, used by
    app never reads it)     the app after go-live)
```

- We do not run a separate UAT server. Testing happens on the live server, and data sent with the **test key** only goes into `odoo_stg_party_prices`.
- Going live means giving Odoo a **live key**. The URL, payload and Odoo code stay the same.
- Party and product codes are only **read** from live tables (`customers`, `master_distributors`, `products`) to link prices. Nothing in those tables is changed.

## Files

| File | Purpose |
|---|---|
| `database/migrations/2026_09_21_100000_create_odoo_integration_tables.php` | Creates the 4 tables below |
| `app/Models/OdooApiClient.php` | API key model (only a SHA-256 hash of the key is stored) |
| `app/Http/Middleware/VerifyOdooApiKey.php` | `odoo.key` middleware: checks `X-API-Key` and sets the mode |
| `app/Services/Odoo/PartyPriceSync.php` | Validates, links party/product, upserts |
| `app/Http/Controllers/Api/Odoo/PartyPriceController.php` | POST (receive) and GET (read back) |
| `app/Console/Commands/OdooClient.php` | `php artisan odoo:client` to create, list or revoke keys |
| `routes/api.php` | `v1/odoo` route group |
| `database/migrations/2026_09_21_110000_add_odoo_sync_permission_and_role.php` | Permission `odoo_sync_access`, role `odoo`, given to `superadmin` and `odoo` |
| `app/Http/Controllers/OdooSyncController.php` | CRM page + DataTables data (read only) |
| `resources/views/odoo_sync/` | `index` (Sync Overview), `party_prices`, `partials/assets` (shared styles/JS) |
| `app/Http/Middleware/RestrictOdooRole.php` | Keeps `odoo` role users on the Odoo Sync page only (web group) |
| `app/Console/Commands/OdooCrmUser.php` | `php artisan odoo:crm-user` to create the Odoo developer's CRM login |
| `resources/views/layouts/app.blade.php` | Sidebar "Integrations → Odoo Sync" menu (only with `odoo_sync_access`) |
| `app/Http/Controllers/Auth/AuthenticatedSessionController.php` | After login, `odoo` role goes straight to `/odoo-sync` |

## Tables

| Table | What it holds |
|---|---|
| `odoo_api_clients` | Keys issued to Odoo: name, mode (`test`/`live`), active, last used |
| `odoo_sync_logs` | One row per request: correlation id, counts (created/updated/skipped/failed), HTTP status, failed record errors |
| `odoo_stg_party_prices` | Test data |
| `party_product_prices` | Live data |

Both price tables have the same columns. Main ones:

- `company_code` + `external_id`: unique Odoo key. Re-sending the same record updates it and never creates a duplicate.
- `party_code`, `party_type`, `party_id`: `party_type`/`party_id` are filled when `party_code` matches `customers.sap_code`, `customers.customer_code` or `master_distributors.distributor_code`, checked in that order. Otherwise they stay `NULL`.
- `product_code`, `product_id`: `product_id` is filled when `product_code` matches `products.product_code` or `products.sap_code`.
- `party_price`, `base_price`, `discount_percent`, `tax_inclusive`, `minimum_quantity`, `maximum_quantity`, `valid_from`, `valid_to`, `priority`, `active`, `is_deleted`.
- `odoo_updated_at`: Odoo's `updated_at`. An incoming record older than this is skipped.
- `raw_payload`: the exact JSON Odoo sent, for debugging.

## Setup on the server (one time)

The deploy workflow only runs `git pull`. **Migrations and key creation must be run by hand over SSH.**

```bash
cd <project path>
php artisan migrate --path=database/migrations/2026_09_21_100000_create_odoo_integration_tables.php
php artisan migrate --path=database/migrations/2026_09_21_110000_add_odoo_sync_permission_and_role.php
php artisan odoo:client create "Odoo UAT" --mode=test
php artisan odoo:crm-user "Odoo Developer" <developer email> <developer mobile>
```

`odoo:client` prints the API key **once**. `odoo:crm-user` prints the CRM password **once**. If `odoo:crm-user` fails (for example the live `users` table needs extra columns), create the user from CRM → Users and give it **only** the `odoo` role.

Send the API key and the CRM login to the Odoo developer securely, not in a group chat. Then share [party-prices-api.md](party-prices-api.md) and `FieldKonnect_Odoo.postman_collection.json` with them.

## CRM "Odoo Sync" menu

Sidebar: **Integrations → Odoo Sync**, a parent menu with one sub-menu per module. Visible to users with the `odoo_sync_access` permission: `superadmin` and the `odoo` role. All pages are read only.

- **Sync Overview** (`/odoo-sync`): last request, requests today, failed records today, the module list, API keys (the key itself is never shown), and request logs of **all** modules with correlation ID, module, counts and a "View errors" popup.
- **Party Wise Pricing** (`/odoo-sync/party-prices`): Test Prices and Live Prices tabs. Each row shows the Odoo party/product code and the FieldKonnect party/product it matched, or a **Not linked** badge.

Adding a new Odoo module later:
1. Add a sub-menu `<li>` under `#odooSyncMenu` in `resources/views/layouts/app.blade.php`.
2. Add its label to `OdooSyncController::MODULES` (key = the `entity` written to `odoo_sync_logs`).
3. Add a page like `party_prices.blade.php` and include `odoo_sync.partials.assets` for the shared look.
4. Replace its "Soon" card on the Sync Overview page with a link.

**The `odoo` role is restricted.** `RestrictOdooRole` lets it open only `/odoo-sync*` and log out. Any other CRM URL redirects back to Odoo Sync (403 for AJAX/POST). After login it lands directly on Odoo Sync. Don't give the `odoo` role any other permission. To remove the developer's access, deactivate the user from CRM → Users.

## Checking what Odoo sent (SQL)

```sql
-- Latest requests
SELECT id, correlation_id, mode, method, received_count, created_count, updated_count,
       skipped_count, failed_count, status_code, created_at
FROM odoo_sync_logs ORDER BY id DESC LIMIT 20;

-- Errors of one request
SELECT errors FROM odoo_sync_logs WHERE correlation_id = '<id from Odoo>';

-- Test prices that could not be linked to a party or product
SELECT external_id, party_code, product_code, party_id, product_id
FROM odoo_stg_party_prices WHERE party_id IS NULL OR product_id IS NULL;
```

A missing or invalid key is written to `storage/logs/laravel.log` as `Odoo API key missing/invalid`.

## Going live (after testing is signed off)

1. Make sure unlinked rows (`party_id` / `product_id` NULL) are understood and the codes match.
2. Create the live key: `php artisan odoo:client create "Odoo Production" --mode=live`
3. Send the live key to Odoo. They replace only the `X-API-Key` value.
4. Revoke the test key once it is no longer needed: `php artisan odoo:client list`, then `php artisan odoo:client revoke <id>`.
5. Optional clean-up: `TRUNCATE odoo_stg_party_prices;`

Live data then goes into `party_product_prices`. **No screen or order flow uses this table yet.** Showing party-wise prices in the app or order form is a separate task.

## Managing keys

```bash
php artisan odoo:client list                       # all keys, mode, last used
php artisan odoo:client create "Name" --mode=test  # new key (printed once)
php artisan odoo:client revoke 3                   # disable key id 3 immediately
```

A lost key cannot be recovered because only its hash is stored. Revoke it and create a new one.

## Open points (from the requirements document, section 19)

- Party pricing precedence (party rule → group rule → standard price) and tax inclusion: waiting for Duke/Odoo.
- Which FieldKonnect entity a `party_code` belongs to (customer vs master distributor): we currently check both.
- `company_code`: send it if Odoo is multi-company. Otherwise it can be left out.
