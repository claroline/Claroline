# Project setup

## Installation 

### Core install

After having checked out the project :

* make sure the version of PHP you're running is >= 5.3.2 (required)

* create an *app/config/parameters.ini* file according to *app/config/parameters.ini.dist*
  (currently only db settings are required)

* Either :
    * launch : `php ./bin/vendors install`

    * create the database with : `php app/console doctrine:database:create`

    * do the same in test env : `php app/console --env="test" doctrine:database:create`

    * create the tables with :
          * `php app/console doctrine:schema:update --force` or `php app/console doctrine:schema:create`
          * `php app/console init:acl`

    * do the same in test env :
          * `php app/console --env="test" doctrine:schema:update --force` or `php app/console --env="test" doctrine:schema:create`
          * `php app/console --env="test" init:acl`
* Or
    * use the automatic install scripts (php must be in $PATH ):
        * `php bin/factory_install`
        * `php bin/factory_install_test`


* make the *app/cache* and *app/logs* directories (and their children) writable from
  the webserver

* *For dev* : check that either the SQLite3 or PDO_SQLite extension are enabled in your
  php configuration in order for the profiler to work

* open your browser and go to *[site]/web/app.php* (prod) or *[site]/web/app_dev.php* (dev)

### Plugin install

* install/remove a plugin with :
  * `php app/console claroline:plugin:install [vendor] [bundle short name]`
  * `php app/console claroline:plugin:remove [vendor] [bundle short name]`


## Test Suite

In order to run the test suite you must have [phpunit][phpunit_website] installed on your system.

[phpunit_website]: http://www.phpunit.de/manual/current/en/index.html



* Running the complete Test Suite
    * `phpunit -c app`

* Running the tests for a single directory
    * `phpunit -c app src/core/Claroline/CoreBundle`


## Build

The *app/build/tools* directory gathers configuration files for various php tools (PHPMD,
PHPCS, PHPUnit with code coverage, etc.). These can be used individually or get called by
Ant, e.g. within a CIS Job (for a list of relevant tools and a way to use it with Jenkins,
see http://jenkins-php.org/). Providing that the tools are correctly installed, the only
requirement is a valid *app/config/parameters.ini* file (see above).

If you want to launch a build with Ant locally, use :

* `ant -buildfile app/build/tools/build.xml`

**Warning** : building the project with Ant will drop every existing table in the
              databases (prod and test).