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
- PHP >= 5.4.1
- PHP extensions:
    - fileinfo (for mime type detection)
    - curl
    - mcrypt
    - gd
    - intl

- MySQL >=5.0 (MariaDB should work aswell)
- A web server like Apache or Nginx
- A global installation of [composer][3] (for dependency management)

##### Configuration of php.ini
- The *memory_limit* must be >= 256Mb (also in cli).
- Be sure that you have a correct configuration of time zone
  ([supported timezones][9]).

### Recommended requirements
 - A *nix OS (the dev is done on debian)
 - PHP >= 5.5
 - PHP extensions:
    - fileinfo (for mime type detection)
    - curl (for facebook authentication)
    - mcrypt
    - [gd][1] (for simple icon creation)
    - [ffmpeg][2] (for video thumbnail creation)
    - php5-json
    - php5-intl

- MySQL >=5.0 (MariaDB should work aswell)
- A web server like Apache or Nginx
- A global installation of [composer][3] (for dependency management)
- A cache system like Varnish

##### Configuration of php.ini
- The *memory_limit* must be >= 512mb (for the webserver).
- The *memory_limit* must be >= 4096mb (for the cli: composer updates consume a lot of ram) 
- Be sure that you have a correct configuration of time zone
  ([supported timezones][9]).

### Development installation

#### Quick install
- curl -sS https://raw.githubusercontent.com/claroline/repository-scripts/master/install.sh | sh

#### Full (and complicated) install

- git clone https://github.com/claroline/Claroline.git
- Create an *app/config/parameters.yml* file based on
  *app/config/parameters.yml.dist*
  and fill at least the main db parameters (database doesn't have to exist,
  but if it exists, it must be empty)
- Make the following directories (and their children) writable from the command
  line and the web server (for further explanation on common permissions issues
  and solutions with Symfony2, read [this][5]):
    - *app/sessions*
    - *app/cache*
    - *app/logs*
    - *app/config*
    - *files*
    - *web/uploads*
- Run the following commands:
    - `$ composer require composer/composer dev-master`
    - `$ composer require claroline/bundle-recorder "~5.0"`
    - `$ cp composer.json.min composer.json`
    - `$ composer update --prefer-source` <sub>(1)</sub> 
    - `$ php app/console claroline:install`

<sub> (1) At this point, you can ignore the following error: *Class 
    Claroline\CoreBundle\Library\Maintenance\MaintenanceHandler is not 
    autoloadable, can not call pre-update-cmd script*
</sub>

You can then create a first admin user with:

```sh
$ php app/console claroline:user:create -a
```

If the css doesn't work do :
    - `$ php app/console assetic:dump`

The application is accessible in your browser via:

- *[site]/web/app_dev.php* (development environment)
- *[site]/web/app.php* (production environment)

### Update

To update your installation to the last stable state, use:

```sh
$ composer update --prefer-source
$ php app/console claroline:update
```


### Plugin installation

You can install or uninstall a plugin by adding or removing the package in the
*require* section of your composer.json and running:

```sh
$ composer update vendor/plugin-name --prefer-source
```

If the plugin package is already present in your project and if you simply want
to install or uninstall it locally, you can use one the following commands:

```sh
$ php app/console claroline:plugin:install FooBarBundle
$ php app/console claroline:plugin:uninstall FooBarBundle
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


Documentation
-------------

For development documentation, see
[Claroline/CoreBundle/Resources/doc/index.md][8].


[core]:         https://github.com/claroline/CoreBundle
[kernel]:       https://github.com/claroline/KernelBundle
[install]:      https://github.com/claroline/InstallationBundle
[migration]:    https://github.com/claroline/MigrationBundle
[forum]:        https://github.com/claroline/ForumBundle
[announcement]: https://github.com/claroline/AnnouncementBundle
[rssreader]:    https://github.com/claroline/RssReaderBundle


[1]: http://www.php.net/manual/en/book.image.php
[2]: http://ffmpeg-php.sourceforge.net/
[3]: http://getcomposer.org/doc/00-intro.md
[4]: http://lesscss.org/#-server-side-usage
[5]: http://symfony.com/doc/current/book/installation.html#configuration-and-setup
[6]: http://www.phpunit.de/manual/current/en/index.html
[7]: http://dev.claroline.net:8080/job/Claronext/
[8]: https://github.com/claroline/CoreBundle/blob/master/Resources/doc/index.md
[9]: http://www.php.net/manual/en/timezones.php
