# Guard Portal — Verification Module

System specification for the campus gate security desk: how a scanned QR
code is resolved, what the scanner shows, what gets written to the Daily
Logbook, and when a scan is allowed to cancel an appointment.

Scope: the authenticated desk at `/guard` (Dashboard), `/guard/scanner`
(Scan Pass / QR Verification) and `/guard/logbook` (Daily Logbook). The
guard never reaches a public verification route — visitor passes in
particular are private to the desk.

---

## 1. Scanner display types

The scanner resolves **one** scanned token to **one** record and renders a
single verdict panel. The pass type printed beside the scanned name is not
decorative: it is how the guard knows which kind of transaction is standing
in front of them.

| QR scanned | Resolved as | Displayed as |
|---|---|---|
| Online Appointment | `appointment` | **Appointment Verification Pass** |
| Document Request | `document` | **Digital Claim Pass** |
| Visitor Gate Pass | `visitor` | Visitor Gate Pass |
| Digital Student ID | `student` | Digital ID |

**Rule (strict):**

- Scanning an **Online Appointment** must display an
  **Appointment Verification Pass**.
- Scanning a **Document Request** must display a **Digital Claim Pass**.

These strings are defined once, in `App\Support\GateLogList::TYPES`, and
flow to every surface that names a pass — the scanner's verdict panel, the
Daily Logbook table, and the dashboard's recent-arrivals feed — so the
label can never drift between them.

```
app/Support/GateLogList.php
    'appointment' => 'Appointment Verification Pass',
    'document'    => 'Digital Claim Pass',
```

### The Appointment Verification Pass card

An appointment scan additionally renders the full pass server-side, from the
same shared `x-appointment-pass-card` the student carries, the page
`/verify/appointment/{token}` opens, and the office's QR verification
station shows. The guard never draws its own copy, so the reference,
office, scheduled time, purpose and current status read at the gate can
never contradict what the office sees.

The card is rendered by `resources/views/guard/partials/appointment-pass.blade.php`
and attached to the scan response as `passHtml`.

### Detail the panel carries

Both pass types fill the same fields: ID / Reference, User Type, Target
Department / Office, Purpose, plus **Appointment Date** and **Scheduled
Time** for appointments only.

---

## 2. Automated Daily Logbook

**Rule (strict):** every successful QR code scan by campus gate security is
automatically recorded in the Daily Logbook, capturing:

| Requirement | Column | Notes |
|---|---|---|
| Timestamp | `scanned_at` | Also `time_in` / `time_out` for the session |
| User details | `user_id`, `person_name`, `reference_code` | `user_id` is `NULL` for walk-in visitors, who have no account; `person_name` always carries the name printed on the pass |
| Transaction type | `pass_type`, `pass_id` | `appointment` \| `document` \| `visitor` \| `student`, plus the id of the exact row |
| Guard identification | `guard_id`, `gate` | The signed-in guard and their post (`Main Gate`, etc.) |

Written by `GateController::record()`, called only after a scan resolves
**and** verifies.

### What is *not* written

- A scan that does not verify writes **nothing** — an expired or revoked
  visitor pass, a claim pass that is not ready, a cancelled appointment, an
  unknown QR.
- Rows with a `NULL` `pass_type` are excluded from every logbook view and
  counter. Those are legacy rows from a phone scanning a public
  verification URL; they were not written by the desk and are not gate
  sessions.

### Entry / exit pairing

The first valid scan of a pass today opens a session (`direction = in`,
`time_in` set). The next scan of the same pass closes it (`direction = out`,
`time_out` set). One row per visit, so the log reads as an arrival and a
departure rather than two unrelated events.

### Reading the logbook

`App\Support\GateLogList` is the desk's single view of `gate_logs`:

- `today($search)` — today's sessions, newest first, searchable by name or
  reference code, limited to 100 rows.
- `insideCount()` / `todayCount()` — the dashboard counters.
- Times are rendered through `CampusTime::display()` so the desk reads
  campus time (Asia/Manila), never UTC.

---

## 3. Appointment auto-cancellation rule

**Rule (strict):** if an online appointment is scanned **past its
designated time slot**, the system automatically sets its status to
**Cancelled**.

- **Applies to appointments only.** Document requests are never cancelled
  by a scan — a Digital Claim Pass carries no slot to be late for.
- Triggered by the gate scan itself, in
  `GateController::appointmentPass()`, via
  `AppointmentSweep::cancelIfMissed($appointment, $reason)`.
- Gate scans pass the reason **"Arrived after the allocated time slot."**
  The scheduled sweep uses **"Scheduled time passed without check-in."**

### The boundary (strict to the minute)

The window closes at the **end of the slot plus one minute**:

| Scan time (slot 08:00–09:00) | Result |
|---|---|
| 08:59:59 | Valid — inside the window |
| 09:00:00 | Valid — still the slot's own minute |
| 09:01:00 | **Cancelled** — late |

`hasMissed()` compares with `>=` against `deadline()` (closing time +
1 minute). There is no grace period.

### Why the scan and not a cron alone

The scan is the arrival. Cancelling at the moment of the scan means the
desk never displays a pass as good after the booking stopped being real —
even before the scheduled sweep runs. The sweep still exists for bookings
nobody ever scans.

### What a late scan shows

The verdict panel reports the appointment as **Cancelled** and refuses
entry; the reason is stored on the appointment. An appointment already
`cancelled`, `no_show` or `completed` is likewise refused.

---

## Test coverage

| Spec rule | Test |
|---|---|
| Display types | `GuardPortalTest::test_the_scanner_labels_the_two_pass_types_by_their_given_names` |
| Logbook — four fields | `GuardPortalTest::test_a_successful_scan_is_recorded_in_the_logbook_with_its_four_fields` |
| Logbook — appointments | `GuardPortalTest::test_an_appointment_scan_is_also_recorded_in_the_logbook` |
| Logbook — desk rows only | `GuardPortalTest::test_the_logbook_feed_lists_only_desk_sessions` |
| Logbook — rejected scans write nothing | `GuardPortalTest::test_a_claim_pass_that_is_not_ready_is_rejected_and_logs_nothing` |
| Auto-cancel on gate scan | `AppointmentAutoCancelTest::test_a_gate_scan_at_901_cancels_an_8_to_9_appointment` |
| Document requests exempt | `AppointmentAutoCancelTest::test_a_document_request_scanned_late_is_not_cancelled` |
| Boundary second | `AppointmentAutoCancelTest::test_a_booking_is_still_valid_in_the_last_second_of_its_window` |
| Cron sweep | `AppointmentAutoCancelTest::test_the_auto_cancel_endpoint_cancels_the_missed_bookings` |

Run them with:

```bash
php artisan test --filter="GuardPortalTest|AppointmentAutoCancelTest"
```
