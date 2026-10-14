# FieldKonnect Outbound API: Employee Leaves

**Audience:** Duke Odoo development team
**Direction:** Odoo reads data from FieldKonnect
**Version:** 1 (14 October 2026)

Same base URL, key and headers as the attendance API (see [attendance-api.md](attendance-api.md), section 1).

## 1. When data is available

Live. Employees apply for leave in the FieldKonnect app and managers approve or reject it in FieldKonnect. Each call returns the current state, so a leave's `status` can change between calls (Pending → Approved or Rejected).

A leave deleted in FieldKonnect is removed completely and no longer appears. To stay in sync, re-read the whole month and treat a `leave_id` that is missing as deleted.

## 2. Get leaves

`GET /leaves`

| Query param | Required | Meaning |
|---|---|---|
| month | No (default current month) | `YYYY-MM`. Returns every leave that touches the month, including ones that start before or end after it. |
| employee_code | No | Only this employee |
| status | No | `Pending`, `Approved` or `Rejected` |
| page | No (default 1) | Page number |
| page_size | No (default 100) | 1 to 500 |

Read all pages until `pagination.has_next` is `false`.

### 2.1 Record fields

One record per leave application. Upsert key: `leave_id`.

| Field | Type | Meaning |
|---|---|---|
| leave_id | integer | FieldKonnect leave ID |
| employee_code | string \| null | Employee code. `null` if the employee has no code in FieldKonnect. |
| employee_name | string \| null | Employee name |
| from_date | date | `YYYY-MM-DD` |
| to_date | date | `YYYY-MM-DD` |
| leave_time_start | time | `10:00:00` for Full Day and First Half, `13:00:00` for Second Half |
| leave_time_end | time | `18:00:00` for Full Day and Second Half, `13:00:00` for First Half |
| type | string | `Full Day Leave`, `First Half Leave` or `Second Half Leave` |
| balance_type | string | `Casual Balance` or `Comp-off Balance` |
| leave_days | number | `0.5` for a half day, otherwise the number of calendar days from `from_date` to `to_date` |
| reason | string \| null | Reason given by the employee |
| status | string | `Pending`, `Approved` or `Rejected` |
| applied_on | datetime | When the leave was applied, `YYYY-MM-DD HH:MM:SS` |
| updated_at | datetime | Last change (for example approval), `YYYY-MM-DD HH:MM:SS` |
| time_zone | string | `Asia/Kolkata` |

### 2.2 Example

`GET /leaves?month=2026-07`

```json
{
  "success": true,
  "message": "Records fetched successfully",
  "month": "2026-07",
  "data": [
    {
      "leave_id": 812,
      "employee_code": "42592",
      "employee_name": "Ramesh Kumar",
      "from_date": "2026-07-29",
      "to_date": "2026-07-30",
      "leave_time_start": "10:00:00",
      "leave_time_end": "18:00:00",
      "type": "Full Day Leave",
      "balance_type": "Casual Balance",
      "leave_days": 2,
      "reason": "Personal Reasons",
      "status": "Approved",
      "applied_on": "2026-07-29 12:56:00",
      "updated_at": "2026-07-29 15:10:00",
      "time_zone": "Asia/Kolkata"
    }
  ],
  "pagination": { "page": 1, "page_size": 100, "total_records": 1, "total_pages": 1, "has_next": false },
  "correlation_id": "..."
}
```

## 3. Errors

| HTTP | code | When |
|---|---|---|
| 400 | VALIDATION_ERROR | `month` is not `YYYY-MM`, or `status` is not Pending / Approved / Rejected |
| 401 | UNAUTHORIZED | `X-API-Key` missing, wrong or revoked |
| 429 | — | More than 60 requests per minute |
