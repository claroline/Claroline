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

### Requirements

- PHP >= 5.4.1
- PHP extensions:
    - fileinfo (for mime type detection)
    - curl (for facebook authentication)
    - mcrypt
    - Optionaly:
        - [gd][1] (for simple icon creation)
        - [ffmpeg][2] (for video thumbnail creation)
    - If you use PHP 5.5, you need to install the following extensions manually:
        - php5-json
        - php5-intl
- A RDBMS like MySQL (>=5.0, recommended) or PostgreSQL (>= 8.0)
- A web server like Apache or Nginx
- A global installation of [composer][3] (for dependency management)

##### Configuration of php.ini
- The *memory_limit* must be >= 256Mb (also in cli).
- The *post_max_size* and *upload_max_filesize* must be enough in order to
  upload your files.
- Be sure that you have a correct configuration of time zone, this value is case
  sensitive, be sure to use correct upper and lower case, you can find a list of
  supported timezones [here][9].

### Development installation

- Clone this repository
- Create an *app/config/parameters.yml* file based on
  *app/config/parameters.yml.dist*
  and fill at least the main db parameters (database doesn't have to exist,
  but if it exists, it must be empty)
- Make the following directories (and their children) writable from the command
  line and the web server (for further explanation on common permissions issues
  and solutions with Symfony2, read [this][5]):
    - *app/cache*
    - *app/logs*
    - *app/config*
    - *files*
    - *templates*
    - *test*
    - *web/uploads*
    - *web/themes*
    - *web/thumbnails*
- Run the following commands:
    - `$ composer require composer/composer dev-master`
    - `$ composer require claroline/bundle-recorder "~3.0"`
    - `$ cp composer.json.dist composer.json`
    - `$ composer update --prefer-source`
    - `$ php app/console claroline:update`

You can then create a first admin user with:

```sh
$ php app/console claroline:user:create -a
```

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
