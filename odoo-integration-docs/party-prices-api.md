# FieldKonnect Inbound API: Party-wise Product Pricing

**Audience:** Duke Odoo development team
**Direction:** Odoo pushes data to FieldKonnect
**Version:** 1 (21 September 2026)

## 1. Environment and authentication

| Item | Value |
|---|---|
| Base URL | `https://<fieldkonnect-domain>/api/v1/odoo` |
| Test key | Shared separately by the FieldKonnect team (starts with `odk_test_`) |
| Live key | Shared after testing is signed off (starts with `odk_live_`) |

There is one URL for testing and production. **The key decides where your data goes.**

- A **test key** writes to an isolated test table. It never affects production prices, so you can send, change and re-send test data freely.
- A **live key** writes to the production table. You only change the `X-API-Key` value when going live. Nothing else changes.

Headers on every request:

```
X-API-Key: <your key>           (required)
Content-Type: application/json  (required for POST)
Accept: application/json        (required)
X-Correlation-ID: <uuid>        (optional; we generate one if missing and return it)
```

- Never put the key in the URL or query string.
- HTTPS only.
- Rate limit: 60 requests per minute per IP. Send up to 500 records per request.

## 2. Send party prices

`POST /party-prices`

Body:

```json
{
  "records": [ { ...price record... }, { ... } ]
}
```

`records` must hold 1 to 500 objects.

### 2.1 Price record fields

| Field | Type | Required | Rules / meaning |
|---|---|---|---|
| external_id | string(100) | Yes | Immutable Odoo price-rule ID. Our upsert key. |
| company_code | string(50) | Only if Odoo is multi-company | Upsert key is `company_code + external_id` |
| price_list_code | string(50) | Yes | Odoo pricelist code |
| price_list_name | string(150) | No | Display name |
| party_external_id | string(100) | Yes | Odoo partner ID, e.g. `res.partner:8421` |
| party_code | string(100) | Yes | Customer/distributor code; must match the code used in FieldKonnect |
| product_external_id | string(100) | Yes | Odoo product **variant** ID, e.g. `product.product:15580` |
| product_code | string(100) | Yes | Product code; must match the FieldKonnect product code |
| currency_code | string(3) | Yes | ISO 4217, e.g. `INR` |
| base_price | number | Yes | ≥ 0. Price before the party rule |
| party_price | number | Yes | ≥ 0. Final price for this party; before tax unless `tax_inclusive` is true |
| discount_percent | number | No | 0 to 100 |
| tax_inclusive | boolean | Yes | Whether `party_price` includes tax |
| minimum_quantity | number | No (default 1) | Slab minimum, ≥ 0 |
| maximum_quantity | number \| null | No | null means no maximum; must be ≥ minimum_quantity |
| uom_code | string(50) | Yes | Unit of measure, e.g. `NOS` |
| valid_from | datetime | Yes | ISO 8601 with offset, e.g. `2026-09-01T00:00:00Z` |
| valid_to | datetime \| null | No | null means open-ended; must be ≥ valid_from |
| priority | integer | No (default 0) | Higher wins when rules overlap |
| active | boolean | Yes | Rule state |
| is_deleted | boolean | No (default false) | Logical delete. We never physically delete. |
| updated_at | datetime | Yes | Last change time in Odoo. Used for ordering (see 2.3) |

Formats: numbers as JSON numbers with a dot decimal (no commas or currency symbols). Unknown optional values must be `null`, not `""`.

### 2.2 Example request

```json
{
  "records": [
    {
      "external_id": "product.pricelist.item:9001",
      "price_list_code": "DIST-WEST-2026",
      "price_list_name": "Distributor West 2026",
      "party_external_id": "res.partner:8421",
      "party_code": "D100245",
      "product_external_id": "product.product:15580",
      "product_code": "PUMP-5HP-001",
      "currency_code": "INR",
      "base_price": 22000.00,
      "party_price": 20500.00,
      "discount_percent": 6.8182,
      "tax_inclusive": false,
      "minimum_quantity": 1,
      "maximum_quantity": null,
      "uom_code": "NOS",
      "valid_from": "2026-09-01T00:00:00Z",
      "valid_to": null,
      "priority": 10,
      "active": true,
      "is_deleted": false,
      "updated_at": "2026-09-21T10:20:00Z"
    }
  ]
}
```

### 2.3 Processing rules

1. **Upsert:** a new `external_id` is created and an existing one is updated. Re-sending the same record is safe and never creates duplicates.
2. **Ordering:** if `updated_at` is **older** than the version we already have, the record is `skipped`. Send changes with a fresh `updated_at`.
3. **Per-record results:** one invalid record does not block the others in the same request.
4. **Unknown party or product code:** the record is still saved and returned with a `warning`. It is linked automatically once the code matches. Please make sure `party_code` and `product_code` match the codes in FieldKonnect.
5. **Deactivate or delete:** send the same `external_id` with `active: false` and/or `is_deleted: true`. Leaving a record out of a request does **not** delete it.
6. **Overlaps:** do not send two active rules with the same priority for the same party, product, quantity and date range.

### 2.4 Response

HTTP **200** when at least one record was saved. HTTP **422** when every record failed. Always check `summary.failed` and `results`.

```json
{
  "success": true,
  "message": "Processed 3 records: 1 created, 1 updated, 0 skipped, 1 failed",
  "mode": "test",
  "summary": { "received": 3, "created": 1, "updated": 1, "skipped": 0, "failed": 1 },
  "results": [
    { "index": 0, "external_id": "product.pricelist.item:9001", "status": "created", "errors": [], "warnings": [] },
    { "index": 1, "external_id": "product.pricelist.item:9002", "status": "updated", "errors": [],
      "warnings": ["party_code 'D999999' not found in FieldKonnect; saved without party link."] },
    { "index": 2, "external_id": "product.pricelist.item:9003", "status": "failed",
      "errors": { "party_price": ["The party price field is required."] }, "warnings": [] }
  ],
  "correlation_id": "89af1ec8-c068-42fc-99ca-03a69a988265"
}
```

`status` is one of `created`, `updated`, `skipped` or `failed`. `index` is the position in your `records` array, starting at 0. `mode` confirms which environment your key wrote to.

## 3. Read back stored prices

`GET /party-prices`

Returns what FieldKonnect has stored for **your key's environment**. Use it to verify your pushes.

| Query parameter | Meaning |
|---|---|
| external_id | One record |
| party_code | All prices of a party |
| product_code | All prices of a product |
| price_list_code | All prices of a pricelist |
| page | Starts at 1 (default 1) |
| page_size | 1–500 (default 100) |

Example: `GET /party-prices?party_code=D100245&page=1&page_size=100`

The response has `data` (rows with `party_id`/`product_id`; null means not linked yet) and `pagination` (`page`, `page_size`, `total_records`, `total_pages`, `has_next`).

## 3a. See your data in the FieldKonnect CRM

You get a CRM login (URL, email/mobile and password) from the FieldKonnect team. After logging in you land on **Odoo Sync**, which shows:

- **Request Logs:** every request you sent, with its correlation ID, counts and the exact errors of failed or skipped records.
- **Test Prices / Live Prices:** what is stored, and which FieldKonnect party and product each `party_code` / `product_code` matched. **Not linked** means the code was not found in FieldKonnect.
- **API keys:** your keys' mode (TEST/LIVE) and when each was last used.

This login can only open the Odoo Sync page.

## 4. Errors

```json
{
  "success": false,
  "error": { "code": "UNAUTHORIZED", "message": "Missing or invalid X-API-Key header", "details": [] },
  "correlation_id": "..."
}
```

| HTTP | When | What to do |
|---|---|---|
| 400 | `records` missing, empty, not an array, or over 500 items | Fix the request body |
| 401 | Missing, wrong or revoked `X-API-Key` | Check the key; contact FieldKonnect |
| 422 | Every record in the request failed validation | Read `results[].errors` |
| 429 | Rate limit exceeded | Wait for `Retry-After` seconds, then retry |
| 500 / 503 | Server error | Retry with backoff. Retrying is safe because requests are idempotent. |

When reporting a problem, quote the `correlation_id`.

## 5. Test checklist for the Odoo team

1. Send 1 valid record → `created`.
2. Send the same record again → `updated`, no duplicate.
3. Change `party_price`, send with a newer `updated_at` → `updated`, and GET shows the new price.
4. Send with an older `updated_at` → `skipped`.
5. Send a record missing `party_price` → `failed` with an error; other records in the same request still succeed.
6. Send `active: false` → GET shows `active: 0`.
7. Quantity slabs: two records for the same party and product with different `minimum_quantity`/`maximum_quantity`.
8. Validity: `valid_to` earlier than `valid_from` → `failed`.
9. Send 500 records in one request, and page through them with GET.
10. Wrong key → 401.
