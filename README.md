# RestInnov CMS — Phase 1

CMS immobilier modulaire Laravel 12 (Core + Modules/Plugins), avec installateur web type WordPress.

## Fonctionnel dans ce lot
- Core Laravel indépendant des modules métier.
- Loader de modules via manifests `modules/*/module.json`.
- Installateur web en 6 étapes accessible via `/install`.
- Lock d'installation (`storage/app/install.lock`) + reprise d'état (`install_state` + fichier JSON).
- Auth admin minimale (`/admin/login`) + dashboard protégé (`/admin`).
- Localisation `en/fr/es` (`/`, `/fr`, `/es`).

## Installation rapide
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve
```

Puis ouvrir `http://127.0.0.1:8000/install`.
