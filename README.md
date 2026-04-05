# RestInnov Demo App (V2)

## Project purpose
RestInnov is a high-fidelity, static exhibition demo for hospitality/property operations. It demonstrates reservation-to-operations orchestration without any backend.

## Architecture summary
- Pure HTML/CSS/Vanilla JS ES modules.
- Hash router with explicit route guarding.
- Central state + localStorage persistence.
- Seeded relational demo dataset (works offline, no fetch required).
- Workflow engine for deterministic business chain simulation.
- Reusable in-app modal and toast UI components (no browser prompts).

## Modules
- Login + fake auth + quick role switch
- Dashboard (role-specific)
- Public Booking (workflow summary output)
- Properties (cards/table toggle + detail + manager CRUD)
- Reservations (detail panel + manager CRUD)
- Customers (manager CRUD)
- Operations / Housekeeping / Maintenance (scoped views + status/notes updates)
- Quality (detail, checklist, anomalies, maintenance follow-up)
- Documents (scoped visibility + manager metadata CRUD)
- Notifications (role-scoped feed + read/unread)
- Settings + Demo Guide (demo controls and scenario)

## Roles and credentials
- manager@restinnov.demo / Demo123!
- owner@restinnov.demo / Demo123!
- housekeeping@restinnov.demo / Demo123!
- maintenance@restinnov.demo / Demo123!
- inspector@restinnov.demo / Demo123!

## How to run
1. Start a static server from repo root:
   - `python3 -m http.server 8080`
2. Open `http://localhost:8080`.

## Reset demo data
- Open **Settings** or **Demo Guide**.
- Click **Reset Demo Data** to restore seeded baseline.

## Suggested exhibition flow
1. Public Booking: create a reservation.
2. Switch to Manager: show new reservation, notifications, tasks, timeline.
3. Switch to Owner: show owned-property scoped visibility.
4. Switch to Housekeeping: show generated prep tasks.
5. Switch to Inspector: open quality check, add anomaly.
6. Switch to Maintenance: show generated follow-up maintenance task.

## V2 changelog (what improved)
- Added route guards with redirect + in-app feedback toast.
- Added action guards enforcing permissions at handler level.
- Replaced `prompt/alert/confirm` with reusable modal dialogs and toasts.
- Improved public booking success into business-workflow summary card.
- Upgraded dashboards with role-specific content blocks.
- Added reservation detail modal with linked tasks/notifications/timeline.
- Enhanced quality module detail + anomaly-to-maintenance visibility.
- Improved operations usability with filters, notes editing modal, empty states.
- Added properties cards/table toggle and detail modal.
- Improved notifications/data scoping per role and owned properties.
- Extended i18n dictionary for key UI labels/actions.
