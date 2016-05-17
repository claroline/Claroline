README
======

[![Join the chat at https://gitter.im/claroline/Claroline](https://badges.gitter.im/claroline/Claroline.svg)](https://gitter.im/claroline/Claroline?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

This repository provides the basic application structure of the Claroline
platform. It doesn't contain the sources nor the third-party libraries 
required to make the application fully functional. Those sources have to 
be installed following one of the procedures described below.

If you want to contribute or directly browse the sources of the project, here
is a (non-exhaustive) list of their dedicated repositories:

- [CoreBundle][core]
- [KernelBundle][kernel]
- [InstallationBundle][install]
- [MigrationBundle][migration]
- [ForumBundle][forum]
- [AnnouncementBundle][announcement]
- [RssReaderBundle][rssreader]

Requirements
------------

For a development installation, you'll need at least:

- PHP >= 5.5 with the following extensions:
    - curl
    - fileinfo
    - [gd][gd]
    - intl
    - mcrypt
    - [ffmpeg][ffmpeg] (optional)
- MySQL/MariaDB >=5.0
- [composer][composer] (recent version)
- [node.js][node] >= 5.5
- [npm][npm] >= 3.7

It's also highly recommended to develop on an UNIX-like OS.

Installation
------------

### 1. From a pre-built archive

A tarball containing everything needed for development and testing 
(pre-fetched sources, database dump, etc.) is made available with every release
of the platform at [packages.claroline.net/releases][releases]. This is the
fastest way to get started:

    curl packages.claroline.net/releases/latest/claroline-6.x.x-dev.tar.gz | tar xzv
    cd claroline-6.x.x-dev
    php scripts/configure.php
    composer fast-install

### 2. From source

The raw installation procedure is comprised of several steps that need to be 
executed in order (fetching php sources, installing dev dependencies, building,
creating the database, etc.). Except for the configuration step, the whole process 
is managed through composer scripts listed in the [composer.json](composer.json)
file. For an installation from scratch, the commands would be:

    git clone http://github.com/claroline/Claroline
    cd Claroline
    php scripts/configure.php
    composer sync-dev

Upgrade
-------

To update an existing development installation, just pull the latest changes 
(or a specific version) of this repository and use the `sync-dev` script:

    git pull
    composer sync-dev

Development
-----------

Some assets of the platform are managed by [webpack][webpack]. In a 
development environment, they require the webpack dev server to be 
running. You can start it with:

    npm run watch

Obviously, you'll also need a PHP-enabled web server to serve the application.
Two alternatives are available.

### 1. Using PHP's built-in web server 

This is the simplest and recommended way of serving the application during
development. To start the server, use the command provided by the symfony
framework (more details [here][symfo-server]):

    php app/console server:start

The application will be available at [http://localhost:8000](http://localhost:8000).

### 2. Using a standalone web server

If you want to use Apache or Nginx during development, make them serve the
*web* directory, and access the application at 
[http://localhost/example-site/app_dev.php](http://localhost/example-site/app_dev.php).

Note that you'll certainly face permissions issues on the following directories:

- *app/cache*
- *app/config*
- *app/logs*
- *app/sessions*
- *files*
- *web/uploads*

All of them must be recursively writable from both the web server and the CLI.
For more information on that subject, see the [configuration section][symfo-config] 
of the official Symfony documentation.

Usage
-----

You can create a first admin user with:

    php app/console claroline:user:create -a

Plugins
-------

Plugins are managed by composer like any other package in the platform.
You can install or uninstall the sources of a plugin by adding or removing
the package from the `require` section of your composer.json and running
`composer update`, or using shortcuts like `composer require ...`.

Once the plugin package is in your *vendor* directory, you can proceed to the
(un-)installation using one the following commands:

    php app/console claroline:plugin:install FooBarBundle
    php app/console claroline:plugin:uninstall FooBarBundle

***Important***: Note that the installation and upgrade procedures of the
platform described above apply only to the "standard" distribution, which
comes with a fixed set of plugins. If you deviate from that set, you'll have
to maintain your own composer files and perform `composer update` and
`php app/console claroline:update` accordingly.

Documentation
-------------

For development documentation, see
[Claroline/CoreBundle/Resources/doc/index.md][core-doc].


[core]:         https://github.com/claroline/CoreBundle
[kernel]:       https://github.com/claroline/KernelBundle
[install]:      https://github.com/claroline/InstallationBundle
[migration]:    https://github.com/claroline/MigrationBundle
[forum]:        https://github.com/claroline/ForumBundle
[announcement]: https://github.com/claroline/AnnouncementBundle
[rssreader]:    https://github.com/claroline/RssReaderBundle

[gd]:           http://www.php.net/manual/en/book.image.php
[ffmpeg]:       http://ffmpeg-php.sourceforge.net
[composer]:     https://getcomposer.org
[node]:         https://nodejs.org
[npm]:          https://docs.npmjs.com
[releases]:     http://packages.claroline.net/releases
[webpack]:      https://webpack.github.io
[symfo-server]: http://symfony.com/doc/2.7/cookbook/web_server/built_in.html
[symfo-config]: http://symfony.com/doc/2.7/book/installation.html#checking-symfony-application-configuration-and-setup
[core-doc]:     https://github.com/claroline/CoreBundle/blob/master/Resources/doc/index.md
