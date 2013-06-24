README
======

- [Project setup](#project-setup)
  - [Requirements](#requirements)
  - [Quick start](#quick-start)
  - [Quick update](#quick-update)
  - [Plugin installation](#plugin-installation)
- [Development tools](#development-tools)
  - [Testing](#testing)
  - [Build and Static analysis](#build-and-static-analysis)
  - [Miscellaneous](#miscellaneous)
- [Documentation](#documentation)

Project setup
-------------

### Requirements

- PHP >= 5.3.3
- PHP extensions :
    - fileinfo (for mime type detection)
    - Optionaly :
        - [gd][1] (for simple icon creation)
        - [ffmpeg][2] (for video thumbnail creation)
- A global installation of [composer][3] (for dependency management)

### Quick start

Clone the project and its associated plugins with:

```sh
$ git clone --recursive https://github.com/claroline/Claroline.git
```

Checkout the master branch of each plugin with:

```sh
$ git submodule foreach git checkout master
```

Use the automatic install script:

```sh
$ php app/dev/raw_install
```

Make the following directories (and their children) writable from the command
line and the webserver

- *app/cache*
- *app/logs*
- *app/config/local*
- *files*
- *templates*
- *test*
- *src/core/Claroline/CoreBundle/Resources/public/css/themes*
- *src/core/Claroline/CoreBundle/Resources/views/less-generated*

For further explanation on common permissions issues and solutions with
Symfony2, read [this][5].

Open your browser and go to *[site]/web/app.php* for production environment or
*[site]/web/app_dev.php* for development environment.

### Quick update

To update your installation to the last development state, use :

```sh
$ git pull
$ git submodule update --init --recursive
$ git submodule foreach git checkout master
```

Then launch the installation script mentioned above:
```sh
$ php app/dev/raw_install
```

***Warning*** : this is a quick dev tool, it will drop existing databases
(both prod and test) and create new ones.

### Plugin installation

You can install or uninstall a plugin with:

```sh
$ php app/console claroline:plugin:install [vendor] [bundle short name]
$ php app/console claroline:plugin:uninstall [vendor] [bundle short name]
```

A new plugin can be added to the module list with :

```sh
$ git submodule add http://github.com/vendor/SomeBundle.git src/plugin/Vendor/SomeBundle
```

Development tools
-----------------

### Testing

In order to run the test suite you must have [phpunit][6] installed on your
system.

Run the complete test suite with:

```sh
$ phpunit -c app
```
Run the tests for a single directory with:

```sh
$ phpunit -c app src/core/Claroline/CoreBundle
```

### Build and Static analysis

The *app/build/tools* directory gathers configuration files for various
analysis and build tools (PHPMD, PHPCS, JSHint, Ant, etc.).

You can install and use them locally (see their respective documentation for
usage) or visit our continuous integration server [here][7].

### Miscellaneous

To have the core **Less** and **TwigJs** assets automatically processed and dumped when
they have changed, you can run the provided [watchr][8] script:

```sh
$ watchr src/core/Claroline/CoreBundle/Resources/watchr/refresh_assets.rb
```

Documentation
-------------

For development documentation, see [Claroline/CoreBundle/Resources/doc/index.md][9].


[1]: http://www.php.net/manual/en/book.image.php
[2]: http://ffmpeg-php.sourceforge.net/
[3]: http://getcomposer.org/doc/00-intro.md
[4]: http://lesscss.org/#-server-side-usage
[5]: http://symfony.com/doc/current/book/installation.html#configuration-and-setup
[6]: http://www.phpunit.de/manual/current/en/index.html
[7]: http://dev.claroline.net:8080/job/Claronext/
[8]: https://github.com/mynyml/watchr
[9]: src/core/Claroline/CoreBundle/Resources/doc/index.md
