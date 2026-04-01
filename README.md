# RestInnov Demo App

## Project purpose
RestInnov is a high-fidelity exhibition demo for hospitality/property operations. It is a static SPA (no backend) that demonstrates reservation-to-operations orchestration across user roles.

## Architecture summary
- Pure HTML/CSS/Vanilla JS ES modules.
- Hash router for all routes.
- Central state with localStorage persistence.
- Seeded demo dataset (no fetch required).
- Workflow engine to simulate business triggers:
  - Public booking -> reservation + tasks + notifications + timeline + quality check
  - Quality anomaly -> maintenance task + notifications + timeline
  - Task status update -> timeline + manager notification for Done/Blocked

## Modules implemented
- Login + fake auth + quick role switch
- Dashboard
- Public Booking
- Properties
- Reservations
- Customers
- Operations
- Housekeeping
- Maintenance
- Quality
- Documents
- Notifications
- Settings
- Demo Guide + reset

## Roles
- Manager
- Property Owner
- Housekeeping Agent
- Maintenance Technician
- Inspector

## Demo users
- manager@restinnov.demo / Demo123!
- owner@restinnov.demo / Demo123!
- housekeeping@restinnov.demo / Demo123!
- maintenance@restinnov.demo / Demo123!
- inspector@restinnov.demo / Demo123!

## How to run
1. From repo root, start a static server:
   - `python3 -m http.server 8080`
2. Open `http://localhost:8080`.

## Reset demo data
- Go to **Demo Guide** and click **Reset Demo Data**.
- This restores the seeded exhibition baseline and clears local changes.

## Suggested exhibition flow
1. Open Public Booking and submit a booking.
2. Switch to Manager:
   - show new reservation, notifications, operations tasks, activity timeline.
3. Switch to Owner and show reservation visibility on owned property.
4. Switch to Housekeeping and show generated prep tasks.
5. Switch to Inspector, add anomaly in Quality.
6. Switch to Maintenance and show generated maintenance task.
