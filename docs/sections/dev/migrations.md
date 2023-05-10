---
layout: default
title: Database migrations
---

# Database migrations

We use [Doctrine Migrations](https://github.com/doctrine/migrations) to generate migrations for the database.

Doctrine Migrations integration bundle provides :

- Generation of migration classes on a per-bundle basis
- Generation for multiple target platforms
- API allowing to execute migrations programmatically


## Commands

You can generate migrations for a specific bundle using:

```sh
php bin/console claroline:migration:generate AcmeFooBundle
```

This command will create migration classes for all the available platforms in
the *Migrations* directory of the bundle.

You can execute a migration using one of the following commands:

```sh
php bin/console claroline:migration:upgrade AcmeFooBundle
php bin/console claroline:migration:downgrade AcmeFooBundle
```

By default, both commands execute the nearest available migration version
(relatively to the current/installed one), but you can specify another target
using the `--target` option:

```sh
php bin/console claroline:migration:downgrade AcmeFooBundle --target=20130101124512
php bin/console claroline:migration:upgrade AcmeFooBundle --target=nearest
php bin/console claroline:migration:upgrade AcmeFooBundle --target=farthest
```

where *farthest* means a full upgrade/downgrade.

The following command displays the list of available versions for a bundle and
highlights the current/installed one:

```sh
php bin/console claroline:migration:version AcmeFooBundle
```

Finally, you can delete generated migration classes which are above the current version
of a bundle using:

```sh
php bin/console claroline:migration:discard AcmeFooBundle
```
This last command is useful if you intend to "merge" several migration classes generated
during development into a single migration class. In such a case, the steps to follow
would be:

```sh
# downgrading to the newest version you want to keep
php bin/console claroline:migration:downgrade AcmeFooBundle --target=20130101124512
# deleting everything above that version
php bin/console claroline:migration:discard AcmeFooBundle
# generating a new migration class
php bin/console claroline:migration:generate AcmeFooBundle
```


## API

The whole API is accessible through the migration [manager class][manager_path]:

```php
<?php

$bundle = $container->get('kernel')->getBundle('AcmeFooBundle');
$container->get('claroline.migration.manager')->upgradeBundle($bundle, '20131201134501');

```

[manager_path]: Manager/Manager.php
