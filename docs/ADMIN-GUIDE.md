# Admin Guide

## Access Control (Phase 11)
- Manage role permissions via `/admin/access/roles`.
- Inspect permission catalog via `/admin/access/permissions`.
- Assign user roles from the same roles screen.

## Content (CMS & Blog)
- CMS pages: `/admin/cms-pages/pages`
- Blog posts/categories: `/admin/blog/posts` and `/admin/blog/categories`
- Rich text editors sanitize unsafe HTML before save.

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
