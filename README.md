# Project setup

## Installation 

After having checked out the project :

* make sure the version of PHP you're running is >= 5.3.2 (required)

* create an *app/config/parameters.ini* file according to *app/config/parameters.ini.dist*
  (currently only db settings are required)

* launch : `php ./bin/vendors install`

* create the database with : `php app/console doctrine:database:create`

* do the same in test env : `php app/console --env="test" doctrine:database:create`

* create the tables with :
  * `php app/console doctrine:schema:update --force` or `php app/console doctrine:schema:create`
  * `php app/console init:acl`

* do the same in test dev :
  * `php app/console --env="test" doctrine:schema:update --force` or `php app/console --env="test" doctrine:schema:create`
  * `php app/console --env="test" init:acl`

* make the *app/cache* and *app/logs* directories (and their children) writable from
  the webserver

* *For dev* : check that either the SQLite3 or PDO_SQLite extension are enabled in your
  php configuration in order for the profiler to work

* open your browser and go to *[site]/web/app.php* (prod) or *[site]/web/app_dev.php* (dev)


## Test Suite

In order to run the test suite you must have [phpunit][phpunit_website] installed on your system.

[phpunit_website]: http://www.phpunit.de/manual/current/en/index.html



* Running the complete Test Suite
    * `phpunit -c app`

* Running the test for a single directory
    * `phpunit -c app src/core/Claroline\CoreBundle`


[]
