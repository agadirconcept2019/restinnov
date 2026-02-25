# API V1 (Phase 12B)

## Auth
- `POST /api/auth/token` (email/password) -> bearer token (Sanctum)
- `POST /api/auth/logout` (auth:sanctum)

## Public read-only
- `GET /api/v1/properties`
- `GET /api/v1/properties/{slug}`
- `GET /api/v1/blog/posts`
- `GET /api/v1/blog/posts/{slug}`
- `GET /api/v1/pages/{slug}`

## Private role-based
- Admin: `/api/v1/admin/*` (properties, availability bulk, bookings, booking_requests, ical_feeds)
- Owner: `/api/v1/owner/*` (own properties/bookings/inquiries + booking status patch)
- Support: `/api/v1/support/*` (forms submissions + email logs)

## Security
- Sanctum personal access tokens
- Throttling profiles:
  - `api-auth` (token issue)
  - `api-public`
  - `api-private`
- Permission middleware and role checks are enforced.
- PII-sensitive fields (e.g. `ip_hash`, `user_agent`) are not returned in API resources.

## Spec
- OpenAPI spec: `docs/openapi.yaml`
