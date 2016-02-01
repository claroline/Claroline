README
======

This repository provides the basic application structure of the Claroline
platform.
It doesn't contain the sources nor the third-party libraries required to make
the application fully functional. Those sources have to be installed following
the procedure described below.

If you want to contribute or directly browse the sources of the project, here
is a (non-exhaustive) list of their dedicated repositories:

- [CoreBundle][core]
- [KernelBundle][kernel]
- [InstallationBundle][install]
- [MigrationBundle][migration]
- [ForumBundle][forum]
- [AnnouncementBundle][announcement]
- [RssReaderBundle][rssreader]
- ...

Project setup
-------------

### Minimum requirements

- PHP >= 5.5
- PHP extensions:
    - fileinfo (for mime type detection)
    - curl (for facebook authentication)
    - mcrypt
    - [gd][gd] (for simple icon creation)
    - intl
- PHP configuration (*php.ini*):
    - `memory_limit` should be >= 256Mb (web server *and* CLI)
    - `date.timezone` should be correctly configured ([supported timezones][timezones])
- MySQL >=5.0 (MariaDB should work as well)
- A web server like Apache or Nginx
- A global installation of [composer][composer] (for dependency management)
- A global installation of [Node.js][node] (for frontend build tools)

### Additional (recommended) requirements

- A *nix OS (development is done on Debian)
- PHP extensions:
    - [ffmpeg][ffmpeg] (for video thumbnail creation)
- PHP configuration (*php.ini*):
    - web server `memory_limit` should be >= 512Mb
    - CLI `memory_limit` should be >= 3072Mb (composer updates consume a lot of RAM)
- A cache system like [Varnish][varnish]

### Development installation

#### With the installation script

```
curl -sS https://raw.githubusercontent.com/claroline/repository-scripts/master/install.sh | sh
php app/console claroline:install
```

#### Step by step

- Clone this repository
- Create an *app/config/parameters.yml* file based on
  *app/config/parameters.yml.dist*
  and fill at least the main db parameters (database doesn't have to exist,
  but if it exists, it must be empty)
- Make the following directories (and their children) writable from the command
  line and the web server (for further explanation on common permissions issues
  and solutions with Symfony2, read [this][symfo-config]):
    - *app/sessions*
    - *app/cache*
    - *app/logs*
    - *app/config*
    - *files*
    - *web/uploads*
- Create a *composer.json* based on one of the following *composer* files:
    - *composer-min.json* (minimal installation, without plugins)
    - *composer-max.json* (complete installation, with plugins)
    - *composer-v6.json* (complete installation based on the v6/dev version)
- Run the following commands:
    - `composer update --prefer-source` *(\*)*
    - `npm install` 
    - `php app/console claroline:install`
    - `npm run build`
    - `rm app/config/operations.xml`

*(\*)* At this point, you can ignore the following error(s): *Class
    Claroline\BundleRecorder\ScriptHandler is not autoloadable, can not call
    post-package-install script*

The application should now be accessible in your browser at the following URI's:

- *[site]/web/app_dev.php* (development environment)
- *[site]/web/app.php* (production environment)

If the css doesn't show up, try:

```
php app/console assets:install web --symlink
php app/console assetic:dump
```

You can create a first admin user with:

```
php app/console claroline:user:create -a
```

### Update

To update your installation, use:

```
composer update --prefer-source
php app/console claroline:update
```

### Plugin installation

Plugin packages are managed by composer like any other package in the platform.
You can install or uninstall the sources of a plugin by adding or removing
the package in the `require` section of your composer.json and running
`composer update`, or using shortcuts like `composer require [...]`.

Once the plugin package is in your *vendor* directory, you can proceed to the
(un-)installation using one the following commands:

```
php app/console claroline:plugin:install FooBarBundle
php app/console claroline:plugin:uninstall FooBarBundle
```

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

[composer]:     https://getcomposer.org
[node]:         https://nodejs.org
[timezones]:    http://www.php.net/manual/en/timezones.php
[varnish]:      https://www.varnish-cache.org
[gd]:           http://www.php.net/manual/en/book.image.php
[ffmpeg]:       http://ffmpeg-php.sourceforge.net
[symfo-config]: http://symfony.com/doc/2.7/book/installation.html#checking-symfony-application-configuration-and-setup
[core-doc]:     https://github.com/claroline/CoreBundle/blob/master/Resources/doc/index.md
