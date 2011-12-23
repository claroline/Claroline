## Project setup

### Quick start

After having cloned the project :

* make sure the version of PHP you're running is >= 5.3.2 (required)

* create an *app/config/local/parameters.yml* file according to *app/config/local/parameters.yml.dist*
  (currently only db settings are required)

* use the automatic install script (php must be in $PATH ) : `php bin/factory_install_dev`

* make the *app/cache* and *app/logs* directories (and their children) writable from
  the webserver (for further explanation on common permissions issues and solutions with Symfony, 
  read [this][symfony_doc_install])

* *For dev* : check that either the SQLite3 or PDO_SQLite extension are enabled in your
  PHP configuration in order for the profiler to work

* open your browser and go to *[site]/web/app.php* (prod) or *[site]/web/app_dev.php* (dev)

[symfony_doc_install]: http://symfony.com/doc/current/book/installation.html#configuration-and-setup
  
### Quick update

To update your installation to the last development state, you can use the installation script 
mentioned above :   `php bin/factory_install_dev`

***Warning*** : this is a quick dev tool, it will drop existing databases (both prod and test) 
and create new ones.

### Plugin installation

You can install or uninstall a plugin with :

  * `php app/console claroline:plugin:install [vendor] [bundle short name]`
  * `php app/console claroline:plugin:uninstall [vendor] [bundle short name]`

### Test suite

In order to run the test suite you must have [phpunit][phpunit_website] installed on your system.

[phpunit_website]: http://www.phpunit.de/manual/current/en/index.html

* Run the complete test suite with : `phpunit -c app`

* Run the tests for a single directory with : `phpunit -c app src/core/Claroline/CoreBundle`

### Build tools

The *app/build/tools* directory gathers configuration files for various php tools (PHPMD,
PHPCS, PHPUnit with code coverage, etc.). You can install and use them locally (see their 
respective documentation for usage) or visit our continuous integration server 
at [http://ci.claroline.net/job/Claronext][ci_website].

[ci_website]: http://ci.claroline.net/job/Claronext
