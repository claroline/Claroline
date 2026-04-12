# Migration CI GitHub Actions → Azure Pipelines — Plan d'implémentation

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Créer `azure-pipelines.yml` à la racine du dépôt, équivalent fonctionnel de `.github/workflows/ci.yml`, déclenchant les vérifications de style et les tests PHPUnit sur chaque pull request dans Azure DevOps.

**Architecture:** Un seul fichier `azure-pipelines.yml` avec deux jobs séquentiels (`CodingStyle` puis `PHPUnit`). Pas de templates externes. Le job PHPUnit ne démarre que si CodingStyle passe.

**Tech Stack:** Azure Pipelines YAML, PHP 8.2, Composer, npm, Webpack, PHPUnit, PHPMD, PHPCSFixer, ESLint, MySQL 8.0 (pré-installé sur les agents Microsoft-hosted Ubuntu)

---

## Fichiers concernés

| Fichier | Action |
|---|---|
| `azure-pipelines.yml` | **Créé** à la racine du dépôt |
| `.github/workflows/ci.yml` | Conservé sans modification |

---

## Task 1 : Squelette du pipeline et déclencheur

**Files:**
- Create: `azure-pipelines.yml`

- [ ] **Step 1 : Créer `azure-pipelines.yml` avec le déclencheur et le pool par défaut**

```yaml
trigger: none

pr:
  branches:
    include:
      - '*'

pool:
  vmImage: 'ubuntu-latest'

jobs: []
```

- [ ] **Step 2 : Valider la syntaxe YAML**

```bash
python3 -c "import yaml, sys; yaml.safe_load(open('azure-pipelines.yml'))" && echo "YAML valide"
```

Résultat attendu : `YAML valide`

---

## Task 2 : Job CodingStyle

**Files:**
- Modify: `azure-pipelines.yml`

Ce job reproduit le job `cs` de `ci.yml`. Il calcule la liste des fichiers PHP et JS modifiés par la PR, puis exécute les linters uniquement sur ces fichiers.

- [ ] **Step 1 : Remplacer `jobs: []` par le job CodingStyle complet**

```yaml
trigger: none

pr:
  branches:
    include:
      - '*'

pool:
  vmImage: 'ubuntu-latest'

jobs:
  - job: CodingStyle
    displayName: 'Coding Style'
    steps:
      - checkout: self
        fetchDepth: 2

      - script: |
          sudo update-alternatives --set php /usr/bin/php8.2
          sudo update-alternatives --set phar /usr/bin/phar8.2
          php --version
        displayName: 'Use PHP 8.2'

      - script: |
          git fetch origin
          TARGET_BRANCH=$(echo "$(System.PullRequest.TargetBranch)" | sed 's|refs/heads/||')
          git diff --name-only --diff-filter=AM origin/$TARGET_BRANCH > git_diff_files.txt
          DIFF_PHP=$(cat git_diff_files.txt | grep '.\+\.php' | sed ':a;N;$!ba;s/\n/ /g')
          DIFF_JS=$(cat git_diff_files.txt | grep -E 'Resources/modules/.+\.(js|jsx)$' | sed ':a;N;$!ba;s/\n/ /g')
          echo "##vso[task.setvariable variable=DIFF_PHP]$DIFF_PHP"
          echo "##vso[task.setvariable variable=DIFF_JS]$DIFF_JS"
        displayName: 'List modified files'

      - script: wget -c https://phpmd.org/static/latest/phpmd.phar -O phpmd
        displayName: 'Install PHPMD'
        condition: ne(variables['DIFF_PHP'], '')

      - script: |
          FILES=$(echo "$DIFF_PHP" | tr ' ' ',')
          php phpmd "$FILES" text phpmd.xml --minimum-priority 1
        displayName: 'Run PHPMD checks'
        condition: ne(variables['DIFF_PHP'], '')
        env:
          DIFF_PHP: $(DIFF_PHP)

      - script: wget https://cs.symfony.com/download/php-cs-fixer-v3.phar -O php-cs-fixer
        displayName: 'Install PHPCSFixer'
        condition: ne(variables['DIFF_PHP'], '')

      - script: php php-cs-fixer fix --dry-run --diff --config=.php-cs-fixer.php --path-mode=intersection $DIFF_PHP
        displayName: 'Run PHPCSFixer checks'
        condition: ne(variables['DIFF_PHP'], '')
        env:
          PHP_CS_FIXER_IGNORE_ENV: 1
          DIFF_PHP: $(DIFF_PHP)

      - script: npm install eslint@9.39.4 eslint-plugin-react@7.37.5 @eslint/js@9 @eslint/eslintrc@3 globals @stylistic/eslint-plugin --legacy-peer-deps
        displayName: 'Install ESLint'
        condition: ne(variables['DIFF_JS'], '')

      - script: node_modules/.bin/eslint --ext js --ext jsx $DIFF_JS
        displayName: 'Run ESLint checks'
        condition: ne(variables['DIFF_JS'], '')
        env:
          DIFF_JS: $(DIFF_JS)
```

- [ ] **Step 2 : Valider la syntaxe YAML**

```bash
python3 -c "import yaml, sys; yaml.safe_load(open('azure-pipelines.yml'))" && echo "YAML valide"
```

Résultat attendu : `YAML valide`

---

## Task 3 : Job PHPUnit

**Files:**
- Modify: `azure-pipelines.yml`

Ce job reproduit le job `php-81` de `ci.yml`. Il installe l'environnement complet (Composer, npm, Webpack, MySQL) et exécute PHPUnit.

- [ ] **Step 1 : Ajouter le job PHPUnit à la suite du job CodingStyle dans `azure-pipelines.yml`**

Ajouter après le job `CodingStyle` (en gardant tout le contenu existant) :

```yaml
  - job: PHPUnit
    displayName: 'PHPUnit (PHP 8.2)'
    dependsOn: CodingStyle
    steps:
      - checkout: self
        fetchDepth: 2

      - script: |
          sudo update-alternatives --set php /usr/bin/php8.2
          sudo update-alternatives --set phar /usr/bin/phar8.2
          sudo apt-get install -y php8.2-mysql
          php --version
          php -m | grep pdo_mysql
        displayName: 'Use PHP 8.2 with pdo_mysql'

      - task: Cache@2
        inputs:
          key: 'composer | "$(Agent.OS)" | **/composer.json'
          path: ~/.composer/cache
          restoreKeys: |
            composer | "$(Agent.OS)"
            composer
        displayName: 'Cache Composer dependencies'

      - script: php bin/configure --default
        displayName: 'Set project parameters'

      - script: composer install --no-interaction
        displayName: 'Install PHP dependencies with Composer'

      - task: Cache@2
        inputs:
          key: 'npm | "$(Agent.OS)" | **/package.json'
          path: ~/.npm
          restoreKeys: |
            npm | "$(Agent.OS)"
            npm
        displayName: 'Cache node modules'

      - script: npm install --legacy-peer-deps
        displayName: 'Install JS dependencies with NPM'

      - script: npm run webpack
        displayName: 'Build JS files with Webpack'

      - script: |
          sudo systemctl start mysql
          sudo mysql -e "CREATE DATABASE IF NOT EXISTS claroline_test;"
        displayName: 'Start MySQL and create database'

      - script: php bin/console claroline:install --env=test -vvv
        displayName: 'Setup Claroline platform'

      - script: php bin/phpunit --dont-report-useless-tests
        displayName: 'Run PHPUnit Tests'

      - script: php bin/console claroline:user:create --env=test -vvv -a John Doe john.doe john.doe john.doe@test.com
        displayName: 'Create a Claroline user'
```

- [ ] **Step 2 : Valider la syntaxe YAML du fichier complet**

```bash
python3 -c "import yaml, sys; yaml.safe_load(open('azure-pipelines.yml'))" && echo "YAML valide"
```

Résultat attendu : `YAML valide`

---

## Task 4 : Commit final

**Files:**
- Modify: `azure-pipelines.yml` (état final)

- [ ] **Step 1 : Vérifier le contenu final**

```bash
cat azure-pipelines.yml
```

Vérifier que le fichier contient bien :
- `trigger: none` et `pr:` au début
- `job: CodingStyle` avec ses 9 steps
- `job: PHPUnit` avec `dependsOn: CodingStyle` et ses 12 steps

- [ ] **Step 2 : Commiter**

```bash
git add azure-pipelines.yml
git commit -m "feat: add Azure Pipelines CI pipeline (migration from GitHub Actions)"
```

---

## Notes de débogage

**Si le diff PHP/JS est vide alors qu'il devrait contenir des fichiers :**
- Vérifier que `$(System.PullRequest.TargetBranch)` est bien défini (il n'est disponible que dans le contexte d'une PR, pas sur un push direct)
- Ajouter un step de debug : `echo "Target branch: $(System.PullRequest.TargetBranch)"`

**Si `pdo_mysql` n'est pas chargé après `apt-get install -y php8.2-mysql` :**
- Vérifier avec `php -m | grep -i pdo`
- Forcer l'activation : `sudo phpenmod -v 8.2 pdo_mysql`

**Si MySQL ne démarre pas :**
- Sur certains agents, utiliser `sudo service mysql start` au lieu de `sudo systemctl start mysql`
