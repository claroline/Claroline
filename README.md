# Project setup

After having checked out the project :

* be sure the version of PHP you're running is >= 5.3.2 (required)

* launch : `php ./bin/vendors install`

* create an *app/config/parameters.ini* file according to *app/config/parameters.ini.dist*
  (currently only db settings are required)

* create the database with : `php app/console doctrine:database:create`

* do the same in test env : `php app/console --env="test" doctrine:database:create`

* create the tables with : `php app/console doctrine:schema:update --force`

* do the same in test env : `php app/console --env="test" doctrine:schema:update --force`

* make the *app/cache* and *app/logs* directories (and their children) writable from
  the webserver

* *For dev* : check that either the SQLite3 or PDO_SQLite extension are enabled in your
  php configuration in order for the profiler to work

* open your browser and go to *[site]/web/app.php* (prod) or *[site]/web/app_dev.php* (dev)
