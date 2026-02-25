# Architecture

- `app/Core/*`: logique Core (modules, settings).
- `modules/*/module.json`: manifests modules.
- `app/Services/RealEstate/*`: services métier.
- `resources/views/*`: thème public/admin/installateur.

Le `ModuleManifestRepository` charge les manifests et `ModuleManager` pilote l'activation DB.
