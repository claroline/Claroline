---
layout: default
title: Commands
---

# Commands

## Installation and Updates

```sh
$ php bin/console claroline:install
```

```sh
$ php bin/console claroline:update FROM_VERSION TO_VERSION
```

### Database

You can drop the database using:

```sh
$ php bin/console doctrine:database:drop --force
```

You can create an empty database using:

```sh
$ php bin/console doctrine:database:create
```

### Database migrations

> **ATTENTION: ** You MUST NOT use the doctrine default `doctrine:schema:update --force` to apply 
> schema modifications to your DB.

Generate migrations for entity modifications :

```sh
$ php bin/console claroline:migration:generate FooBarBundle
```

Apply migrations to the database :

```sh
$ php bin/console claroline:migration:upgrade FooBarBundle
```

> **NB: ** Pending migrations are automatically run by the `claroline:update` command.


### Plugins management

Plugins are registered with the command:

```sh
$ php bin/console claroline:plugin:install FooBarBundle
```

You can remove plugins with:

```sh
$ php bin/console claroline:plugin:uninstall FooBarBundle
```

**Tips:** The list of registered bundle is saved in the file
**files/config/bundles.ini**.

If you need to add a bundle, you can simply add a line in this file if it
already exists in your vendors. If you don't want a bundle to be instanciated,
you can remove the relevant line here. Errors in the installation may remove the
line concerning the bundle wich crashed (including the ClarolineCoreBundle).

### Loading users

You can create a new admin using the command:

```sh
$ php bin/console claroline:user:create -a
```

Removing the -a option will create a regular user.

## Theme
### Declare the theme to the Claroline Application
You can create your theme using the command:
```sh
$ php bin/console claroline:theme:create MY_THEME_NAME
```

### Rebuild to update your theme
After all modifications, you can rebuild the theme using:
````sh
$ php bin/console claroline:theme:build
````

**Rebuild themes which are installed in the platform**

``php bin/console claroline:theme:build [options]``

#### Rebuild only the theme currently used by the platform :

``php bin/console claroline:theme:build --current``

or

``php bin/console claroline:theme:build -c``

#### Rebuild only this theme :

``php bin/console claroline:theme:build --theme[=THEME]``

or

``php bin/console claroline:theme:build -t[=THEME]``

#### Rebuild themes without using cache :

``php bin/console claroline:theme:build --no-cache``

or

``php bin/console claroline:theme:build -nc``


## Refresh

### Clearing the cache

You can clear the symfony cache using:

```sh
$ php bin/console cache:clear
```

**Tips:** It's often better to remove the cache manually using
**$ rm -rf app/cache/**


## Code quality

See [Code analysis](Claroline/sections/dev/code-analysis) for more information.

### PHP Mess Detector

```sh
$ vendor/bin/phpmd src/plugin/FooVendor/BarBundle text phpmd.xml
```

### PHP CS Fixer

```sh
$ vendor/bin/php-cs-fixer fix --config=.php_cs src/plugin/FooVendor/BarBundle
```

### ESLint

```sh
$ node_modules/.bin/eslint path/to/the/file/to/fix --fix
```


## Tests

## Translations

### claroline:debug:translation LANGUAGE [--domain=] [--main_lang=] [--fqcn=] [-f]

This command will allow you to reorder translations and adding the missing keys of foreign languages files. It will show you missing translations in the console (those where the translation keys are equals to the translations).

- LOCALE : The language you want to check.
- [--domain=]:  the translation domain (default: platform)
- [--main_lang]= : the language file containing all translations (default: fr)
- [--fqcn]= : The bundle you want to check the translations (default: ClarolineCoreBundle)
- [-f]: Update the translation file (reorder and inject the missing keys).

```sh
php bin/console claroline:debug:translation en --domain=forum --main_lang=fr --fqcn=ClarolineForumBundle -f`
```
