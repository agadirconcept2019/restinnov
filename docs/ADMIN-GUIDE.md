# Admin Guide

## Access Control (Phase 11+12)
- Manage role permissions via `/admin/access/roles`.
- Inspect permission catalog via `/admin/access/permissions`.
- Assign user roles from the same roles screen.
- Bulk and export actions are guarded by module/domain permissions and `admin.exports.run`.

## Productivity Toolkit (Phase 12A)
- Admin tables now support filters/search/sorting and pagination on key screens.
- Saved views: on supported screens, save current query as a reusable view (`resource_key` scoped).
- Bulk actions are available directly in table pages for high-volume operations.
- Exports are queued (database queue), then downloadable from `/admin/exports`.

## Content (CMS & Blog)
- CMS pages: `/admin/cms-pages/pages` (bulk publish/unpublish, locale filter).
- Blog posts/categories: `/admin/blog/posts` and `/admin/blog/categories` (bulk publish/unpublish, category/locale filters).
- Rich text editors sanitize unsafe HTML before save.

## Real Estate
- Properties: `/admin/real-estate/properties`
  - search/filtres (status/city/type/owner/featured)
  - bulk publish/unpublish/assign owner
  - export CSV
- Booking requests: `/admin/real-estate/booking-requests` (bulk status)
- Bookings: `/admin/real-estate/bookings` (bulk status + export CSV)

## Forms & Communications
- Form submissions: `/admin/forms/submissions`
  - filters + bulk mark processed/archive
  - export CSV
- Email logs: `/admin/communications/logs`
  - filters + bulk retry failed
  - export CSV

## Media
- Upload and manage media metadata from `/admin/media`.
- Variants metadata (`thumb`, `medium`, `large`) is stored in `media.meta.variants`.

## Menus
- Update hierarchy and sort order at `/admin/menus`.
- Locale-aware items remain stored in `menu_items.locale`.

## SEO
- SEO values are persisted in core `seo_meta` table via admin content forms.
- Fields include title, description, canonical, robots and optional schema JSON.

## Existing modules and ops (unchanged)
- Bookings/invoices: `/admin/real-estate/bookings`
- Communications templates/logs: `/admin/communications/*`
- Migration profiles/runs/rollback: `/admin/migration/*`
- Ops health/jobs: `/admin/ops/*`
