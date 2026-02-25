# Installation (Web Wizard)

## Pré-requis
- PHP 8.3+
- Extensions: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`
- Dossiers inscriptibles: `storage/`, `bootstrap/cache/`

## Assistant `/install`
1. Checks serveur
2. Configuration base MySQL/MariaDB + test connexion
3. Configuration app (`.env`, APP_KEY, locale, timezone)
4. Installation système (migrations + seeders core + cache clear + storage link best-effort)
5. Création admin (`super_admin`)
6. Finalisation + lock

## Verrouillage installateur
Après finalisation, `/install` redirige vers `/admin/login`.

## Reprise d'installation
L'état est persisté dans:
- `storage/app/install_state.json`
- table `install_state` (quand disponible)

## Reset DEV uniquement
```bash
rm -f storage/app/install.lock storage/app/install_state.json
php artisan migrate:fresh --seed
```
