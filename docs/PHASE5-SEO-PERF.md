# PHASE 5 — SEO, Sitemap, Redirects, Performance & Security

## Source of truth SEO
- Primary SEO source is Core table `seo_meta` (polymorphic `metaable_type`, `metaable_id`, `locale`).
- `App\Core\Seo\SeoManager` resolves metadata in this order:
  1. `seo_meta` for current locale
  2. `seo_meta` for default locale
  3. content fallback from the entity translation (title/excerpt/description)
- Canonical URLs are always absolute.

## Sitemap strategy
- Route: `/sitemap.xml`.
- Core registry: `App\Core\Sitemap\SitemapRegistry` + `SitemapProviderInterface`.
- Providers are registered by modules:
  - CmsPages
  - RealEstate
  - Blog
- Only published content is included.
- Sitemap output is cached for 30 minutes (`sitemap:xml`).

## Redirect strategy
- Core middleware: `App\Http\Middleware\RedirectMiddleware`.
- Redirect lookup uses `redirects.from_path` (path only).
- Supports 301/302 and increments `hits` atomically.
- Open redirects are blocked (internal paths or same-host absolute URLs only).
- Query string is preserved when destination has no query string.

## Caching keys
- `sitemap:xml`
- `home:featured_properties:{count}`
- `blog:categories:active`
- `settings:{group}:{key}`
- `menu:{slug}`
- `taxonomy:{key}`

## Forms hardening
- Public forms use throttle middleware `forms-public`.
- Honeypot field (`company_name`) remains active.
- Added minimum submit time field (`submitted_at`) validation.
- Notifications are queued with `SendFormNotificationJob`.
- Dispatch failures do not block submission persistence.

## Audit logs
- Core table: `audit_logs`.
- Sensitive actions audited:
  - publish/update/delete on Pages, Posts, Properties
  - create/update redirects
- Admin UI:
  - `/admin/audit-logs`
  - `/admin/audit-logs/{id}`
