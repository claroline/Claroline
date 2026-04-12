# Design : Migration CI GitHub Actions → Azure DevOps

**Date :** 2026-04-12
**Scope :** Migration de `.github/workflows/ci.yml` vers Azure Pipelines (`azure-pipelines.yml`)
**Hors scope :** `release_patch.yml` (traité séparément)

---

## Contexte

Le projet Claroline (PHP 8.2 / Symfony + React/Webpack) migre entièrement de GitHub vers Azure DevOps. Le workflow CI actuel comporte deux jobs :

1. **Coding Style** — vérifie PHPMD, PHPCSFixer et ESLint sur les fichiers modifiés uniquement
2. **PHPUnit** — installe le projet complet (Composer + npm + Webpack + MySQL) et exécute les tests

---

## Architecture

Un seul fichier `azure-pipelines.yml` à la racine du dépôt, avec deux jobs séquentiels.

```
azure-pipelines.yml
└── job: CodingStyle
└── job: PHPUnit (dependsOn: CodingStyle)
```

---

## Déclencheur

```yaml
trigger: none

pr:
  branches:
    include:
      - '*'
```

- `trigger: none` : pas de déclenchement sur push direct
- `pr:` : déclenché sur toute pull request, quelle que soit la branche cible

---

## Job CodingStyle

**Agent :** `ubuntu-latest` (Microsoft-hosted)

**Étapes :**

1. **Checkout** — `checkout: self` avec `fetchDepth: 2`
2. **PHP 8.2** — task `UsePhpVersion@0` (marketplace Azure DevOps)
3. **Calcul du diff** — script bash équivalent au step actuel :
   - `git fetch origin`
   - `git diff --name-only --diff-filter=AM origin/$(System.PullRequest.TargetBranch)`
   - Export des variables `DIFF_PHP` et `DIFF_JS` via `##vso[task.setvariable]`
4. **Install PHPMD** — `condition: ne(variables['DIFF_PHP'], '')`
5. **Run PHPMD** — `condition: ne(variables['DIFF_PHP'], '')`
6. **Install PHPCSFixer** — `condition: ne(variables['DIFF_PHP'], '')`
7. **Run PHPCSFixer** — `condition: ne(variables['DIFF_PHP'], '')`
8. **Install ESLint** — `condition: ne(variables['DIFF_JS'], '')`
9. **Run ESLint** — `condition: ne(variables['DIFF_JS'], '')`

**Traductions clés :**
- `set-output` → `echo "##vso[task.setvariable variable=NAME]value"`
- `github.base_ref` → `$(System.PullRequest.TargetBranch)`
- `if: ${{ env.VAR != '' }}` → `condition: ne(variables['VAR'], '')`

---

## Job PHPUnit

**Agent :** `ubuntu-latest` (Microsoft-hosted)

**Dépendance :** `dependsOn: CodingStyle`

**MySQL :** Les agents Microsoft-hosted Ubuntu embarquent MySQL. Démarrage via script :
```bash
sudo systemctl start mysql
sudo mysql -e "CREATE DATABASE IF NOT EXISTS claroline_test;"
```
Sur les agents Microsoft-hosted, MySQL root n'a pas de mot de passe — on utilise `sudo mysql`. Pas de container sidecar nécessaire.

**Étapes :**

1. **Checkout** — `checkout: self` avec `fetchDepth: 2`
2. **PHP 8.2** — task `UsePhpVersion@0` puis step `apt-get install -y php8.2-mysql` pour l'extension `pdo_mysql`
3. **Cache Composer** — task `Cache@2`, clé `composer | **/composer.json`
4. **Set project parameters** — `php bin/configure --default`
5. **Composer install** — `composer install --no-interaction`
6. **Cache npm** — task `Cache@2`, clé `npm | **/package.json`
7. **npm install** — `npm install --legacy-peer-deps`
8. **Webpack build** — `npm run webpack`
9. **Start MySQL** — `sudo systemctl start mysql` + `sudo mysql -e "CREATE DATABASE IF NOT EXISTS claroline_test;"`
10. **Claroline install** — `php bin/console claroline:install --env=test -vvv`
11. **PHPUnit** — `php bin/phpunit --dont-report-useless-tests`
12. **Create user** — `php bin/console claroline:user:create --env=test -vvv -a John Doe john.doe john.doe john.doe@test.com`

**Traductions clés :**
- `actions/cache@v4` → `Cache@2`
- `services: mysql` → script de démarrage MySQL
- `needs: cs` → `dependsOn: CodingStyle`

---

## Gestion des variables entre steps

Azure DevOps ne partage pas les variables entre steps par défaut. Pour passer `DIFF_PHP` et `DIFF_JS` du step "diff" aux steps suivants dans le même job, on utilise :

```bash
echo "##vso[task.setvariable variable=DIFF_PHP]$value"
```

Et dans les steps suivants, la variable est accessible via `$(DIFF_PHP)` ou `variables['DIFF_PHP']` dans les conditions.

---

## Fichiers produits

| Fichier | Action |
|---|---|
| `azure-pipelines.yml` | Créé à la racine du dépôt |
| `.github/workflows/ci.yml` | Conservé (pas supprimé dans cette itération) |
