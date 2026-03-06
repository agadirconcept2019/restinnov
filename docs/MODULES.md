# Modules

Créer un dossier `modules/MyModule/module.json` avec:
- name, slug, version, description
- providers[]
- requires
- routes flags
- permissions[]

Ensuite activer le module via `ModuleManager::activate([...])`.
