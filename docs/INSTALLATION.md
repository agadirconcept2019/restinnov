# Installation

## Installateur web
- Accès: `/install`
- Étapes: prérequis, DB, app, migrations/seed, admin, finalisation.
- Lock: `storage/app/install.lock` bloque une seconde installation.

## CLI (dev)
```bash
php artisan migrate --seed
php artisan db:seed
```

## Reset dev
```bash
rm -f storage/app/install.lock
php artisan migrate:fresh --seed
```
