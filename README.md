README
======

[![Join the chat at https://gitter.im/claroline/Claroline](https://badges.gitter.im/claroline/Claroline.svg)](https://gitter.im/claroline/Claroline?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

This repository provides the basic application structure of the Claroline
platform. It doesn't contain the sources nor the third-party libraries
required to make the application fully functional. Those sources have to
be installed following one of the procedures described below.

If you want to contribute or directly browse the sources of the project,
check the [claroline/Distribution][distribution] repository, which gathers
the standard modules and plugins of the platform.

=======
**/!\ Warning if you wan't to upgrade to 13.x from a older version of Claroline [read this](README.md#Upgrade-From-12x-or-older-to-13x) /!\**

Installation
------------

See Claroline [requirements here](README.md#requirements)

### 1. From source

The raw installation procedure is composed of several steps that need to be
executed in order (fetching php sources, installing dev dependencies, building,
creating the database, etc.). Except for the configuration step, the whole process
is managed through composer scripts listed in the [composer.json](composer.json)
file. For an installation from scratch, the commands would be:

    git clone -b 13.x http://github.com/claroline/Claroline
    cd Claroline
    php bin/check
    php bin/configure
    composer install --no-dev --optimize-autoloader

    npm install
    composer build

    php bin/console claroline:install

Upgrade 13.x
-------

To update your 13.x just follow this steps :

#### 1. Update source

    composer update --no-dev --optimize-autoloader
    npm install
    composer build

#### 2. Launch update script
   
    php bin/console claroline:update



Upgrade From 12.x or older to 13.x
-------

To update an existing installation to 13.x you must **first upgrade to the latest 12.5 branch**

**ATTENTION :**
You may need to update the configuration of your web server as the new application entry point is
now `PROJECT_DIR/public/index.php` instead of `PROJECT_DIR/web/app.php`.

#### 1. go to 12.5 branch

     git fetch origin
     git checkout 12.5

#### 2. Update source

    composer update --no-dev --optimize-autoloader
    npm install
    composer build

#### 3. Launch update script
   
    php bin/console claroline:update 1x.x.xx 12.5.xx

Then you can go to 13.x
    
#### 4. go to 13.x branch

     git fetch origin
     git checkout 13.x

#### 5. Update source

    composer update --no-dev --optimize-autoloader
    npm install
    
    mv app/config/parameters.yml config/parameters.yml
    rm -rf app
    rm -rf web
    
    composer build

#### 6. Launch update script
   
    php bin/console claroline:update

Requirements
------------

For a development installation, you'll need at least:

- PHP >= 7.2 with the following extensions:
    - curl
    - fileinfo
    - [gd][gd]
    - intl
    - mbstring
    - mcrypt
    - xml
    - json
    - zip
- MySQL/MariaDB >=5.0
- [composer][composer] (recent version)
- [node.js][node] >= 8.9
- [npm][npm] >= 6.4

It's also highly recommended to develop on an UNIX-like OS.

For mysql >= 5.7, there is an additonal step:

```
    mysql -u**** -p
    set global sql_mode='';
    exit;
```


Development
-----------

Some assets of the platform are managed by [webpack][webpack]. In a
development environment, they require the webpack dev server to be
running. You can start it with:

    npm run webpack:dev

Obviously, you'll also need a PHP-enabled web server to serve the application.
Two alternatives are available.

### 1. Using Symfony web server (not tested)

This is the simplest way of serving the application during
development. To start the server, use the command provided by the symfony
local server (more details on installation and configuration [here][symfo-server]):

    symfony server:start

The application will be available at [http://localhost:8000](http://localhost:8000).

### 2. Using a standalone web server (recommended)

If you want to use Apache or Nginx during development, make them serve the
*web* directory, and access the application at
[http://localhost/example-site/index.php](http://localhost/example-site/index.php).

Note that you'll certainly face permissions issues on the following directories:

- *config*
- *var/cache*
- *var/log*
- *var/sessions*
- *files*
- *public/uploads*

All of them must be recursively writable from both the web server and the CLI.
For more information on that subject, see the [configuration section][symfo-config]
of the official Symfony documentation.

Usage
-----

You can create a first admin user with:

    php bin/console claroline:user:create -a

Plugins
-------

Plugins are managed by composer like any other package in the platform.
You can install or uninstall the sources of a plugin by adding or removing
the package from the `require` section of your composer.json and running
`composer update`, or using shortcuts like `composer require ...`.

Once the plugin package is in your *vendor* directory, you can proceed to the
(un-)installation using one the following commands:

    php bin/console claroline:plugin:install FooBarBundle
    php bin/console claroline:plugin:uninstall FooBarBundle

***Important***: Note that the installation and upgrade procedures of the
platform described above apply only to the "standard" distribution, which
comes with a fixed set of plugins. If you deviate from that set, you'll have
to maintain your own composer files and perform `composer update` and
`php bin/console claroline:update` accordingly.

Browser support
------------

We recommend to use Claroline Connect with the latest version of Mozila Firefox or Chromium.

We support :
- Mozilla Firefox (latest version)
- Chromium (latest version) and Google Chrome (latest version)
- Microsoft Edge (latest version)
- Safari (latest version)

For complete list : http://caniuse.com/#feat=mutationobserver

Documentation
-------------

For user documentation, see [here](https://support.claroline.com/#/desktop/workspaces/open/documentation/home/accueil).

[distribution]: https://github.com/claroline/Distribution
[gd]:           http://www.php.net/manual/en/book.image.php
[ffmpeg]:       http://ffmpeg-php.sourceforge.net
[composer]:     https://getcomposer.org
[node]:         https://nodejs.org
[npm]:          https://docs.npmjs.com
[webpack]:      https://webpack.github.io
[symfo-server]: https://symfony.com/doc/4.4/setup/symfony_server.html
[symfo-config]: https://symfony.com/doc/4.4/setup/web_server_configuration.html
[dist-doc]:     https://github.com/claroline/Distribution/blob/master/doc/index.md
