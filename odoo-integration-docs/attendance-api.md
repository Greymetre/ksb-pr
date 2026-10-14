# FieldKonnect Outbound API: Monthly Attendance

**Audience:** Duke Odoo development team
**Direction:** Odoo reads data from FieldKonnect
**Version:** 1 (14 October 2026)

## 1. Environment and authentication

Same base URL and same key as the party-prices API.

| Item | Value |
|---|---|
| Base URL | `https://duke.fieldkonnect.in/api/v1/odoo` |
| Key | Your existing `X-API-Key` (test or live) |

Headers on every request:

```
X-API-Key: <your key>           (required)
Accept: application/json        (required)
X-Correlation-ID: <uuid>        (optional; we generate one if missing and return it)
```

- Never put the key in the URL or query string.
- HTTPS only.
- Rate limit: 60 requests per minute per IP.

## 2. When data is published

Attendance is published **once a month, for a full completed month**.

- On the **1st of every month at 01:00 IST**, FieldKonnect publishes the previous month, from the 1st to its last day.
  Example: on 1 October 2026 at 01:00, September 2026 (`2026-09`, 1 to 30 September) becomes available.
- The current month is never available. It shows up on the 1st of the next month.
- A published month does not change afterwards. If FieldKonnect has to correct it, it is republished and `generated_at` changes.

Suggested Odoo schedule: pull on the 1st after 02:00 IST.

## 3. Get attendance

`GET /attendance`

| Query param | Required | Meaning |
|---|---|---|
| month | No | `YYYY-MM`, e.g. `2026-09`. If left out, the latest published month is returned. |
| employee_code | No | Only this employee |
| page | No (default 1) | Page number |
| page_size | No (default 100) | 1 to 500 |

Read all pages until `pagination.has_next` is `false`.

### 3.1 Record fields

There is **one record per employee per day** of the month, including days without a punch (absent, week off, holiday, leave). Upsert key: `employee_code` + `attendance_date`.

| Field | Type | Meaning |
|---|---|---|
| employee_code | string \| null | Employee code. `null` if the employee has no code in FieldKonnect. |
| employee_name | string \| null | Employee name |
| attendance_date | date | `YYYY-MM-DD` |
| status | string | Day status, see 3.2 |
| status_code | string | Short code used in the FieldKonnect attendance report, see 3.2 |
| attendance_id | integer \| null | FieldKonnect attendance ID; `null` on days without a punch |
| punchin_time | time \| null | `HH:MM:SS` |
| punchout_date | date \| null | `YYYY-MM-DD`; `null` if the employee did not punch out |
| punchout_time | time \| null | `HH:MM:SS`; `null` if the employee did not punch out |
| total_working_time | string \| null | `HH:MM:SS`; `null` if there is no punch-out |
| time_zone | string | IANA time zone of all dates and times, `Asia/Kolkata` |
| working_type | string \| null | e.g. `Office Work`, `Tour`, `Full Day Leave` |

### 3.2 Status values

Same status as the FieldKonnect attendance report.

| status | status_code | When |
|---|---|---|
| Present | P | Worked 8:30 hours or more |
| Sunday Working | PW | Worked 8:30 hours or more on a Sunday |
| Half Day | HF | Worked 4:30 to under 8:30 hours |
| Absent | A | Worked under 4:30 hours, attendance rejected by the manager, or no punch on a working day |
| Miss Punch | MIS | Punched in but did not punch out |
| Leave | CL | Full-day leave |
| Half Day Leave | 1/2P+1/2CL | Half-day leave |
| Comp Off | Comp-Off | Leave taken from comp-off balance (counts as present) |
| Holiday | H | Holiday for the employee's branch |
| Week Off | WOFF | Sunday without a punch |

Rare values, only for an attendance entry that has no punch-in time: `Leave` (AL), `Half Day Leave` (1/2P+1/2AL), `Comp Off` (Comp Off), `Half Day Comp Off` (1/2P+1/2Comp Off), `Loss Of Pay` (LOPN), `Half Day Loss Of Pay` (1/2P+1/2LOPN), `Comp Off Rejected` (Comp Off N), `Half Day Comp Off Rejected` (1/2P+1/2Comp Off N), `Pending` (PN).

Days before the employee's joining date are not sent.

### 3.3 Example

`GET /attendance?month=2026-09&page=1&page_size=500`

```json
{
  "success": true,
  "message": "Records fetched successfully",
  "month": "2026-09",
  "generated_at": "2026-10-01 01:00:04",
  "data": [
    {
      "employee_code": "1429",
      "employee_name": "Ramesh Kumar",
      "attendance_date": "2026-09-01",
      "status": "Present",
      "status_code": "P",
      "attendance_id": 58213,
      "punchin_time": "10:00:00",
      "punchout_date": "2026-09-01",
      "punchout_time": "18:30:00",
      "total_working_time": "08:30:00",
      "time_zone": "Asia/Kolkata",
      "working_type": "Office Work"
    },
    {
      "employee_code": "1429",
      "employee_name": "Ramesh Kumar",
      "attendance_date": "2026-09-02",
      "status": "Absent",
      "status_code": "A",
      "attendance_id": null,
      "punchin_time": null,
      "punchout_date": null,
      "punchout_time": null,
      "total_working_time": null,
      "time_zone": "Asia/Kolkata",
      "working_type": null
    }
  ],
  "pagination": {
    "page": 1,
    "page_size": 500,
    "total_records": 4210,
    "total_pages": 9,
    "has_next": true
  },
  "correlation_id": "3f1c2b8e-9a41-4e0f-bd51-0c6f0f2d7a11"
}
```

## 4. Errors

```json
{
  "success": false,
  "error": { "code": "NOT_FOUND", "message": "Attendance for this month is not published yet", "details": [{ "month": "2026-10" }] },
  "correlation_id": "..."
}
```

| HTTP | code | When |
|---|---|---|
| 400 | VALIDATION_ERROR | `month` is not `YYYY-MM` |
| 401 | UNAUTHORIZED | `X-API-Key` missing, wrong or revoked |
| 404 | NOT_FOUND | That month is not published (current month, future month, or before the integration started) |
| 429 | — | More than 60 requests per minute |

Send us the `correlation_id` when reporting a problem.

## 5. Test checklist for the Odoo team

1. `GET /attendance` with no `month` returns the latest month.
2. `GET /attendance?month=2026-09` and read all pages; the count equals `pagination.total_records`.
3. `GET /attendance?month=2026-09&employee_code=1429` returns only that employee.
4. Current month returns 404.
5. A wrong key returns 401.

Requests are in `FieldKonnect_Odoo.postman_collection.json`, folder **3. Attendance**.
