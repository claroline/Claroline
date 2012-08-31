## Project setup

### Quick start

After cloning the project :

* make sure the version of PHP you're running is >= 5.3.2 (required)

* create an *app/config/local/parameters.yml* file according to *app/config/local/parameters.yml.dist*
  (currently only db settings are required)

* install a local copy of [composer][composer_website] in the root directory of the project

* use the automatic install script (php must be in $PATH ) : `php app/dev/raw_install`

* make the *app/cache*, *app/logs*, *app/config/local*, *files* and *test* directories
  (and their children) writable from the webserver (for further explanation on common
  permissions issues and solutions with Symfony, read [this][symfony_doc_install])

* *For dev* :
    * check that either the SQLite3 or PDO_SQLite extension are enabled in your
      PHP configuration in order for the profiler to work
    * make a global installation of [less][less_install] ([node][node_website] module)
      in order to have the css files compiled (*src/core/Claroline/CoreBundle/Resources/public/css/themes*
      must be writable)

* open your browser and go to *[site]/web/app.php* (prod) or *[site]/web/app_dev.php* (dev)

[composer_website]: http://getcomposer.org/download/
[symfony_doc_install]: http://symfony.com/doc/current/book/installation.html#configuration-and-setup
[less_install]: http://lesscss.org/#-server-side-usage
[node_website]: http://nodejs.org/

### Quick update

To update your installation to the last development state, you can use the installation script
mentioned above :   `php app/dev/raw_install`

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
PHPCS, Ant, etc.). You can install and use them locally (see their respective documentation
for usage) or visit our continuous integration server [here][ci_website].

[ci_website]: http://dev.claroline.net:8080/job/Claronext/

### Other tools

To have the core Less and TwigJs assets automatically processed and dumped when they have changed,
you can run the provided [watchr][watchr_website] script :

`watchr src/core/Claroline/CoreBundle/Resources/watchr/refresh_assets.rb`

[watchr_website]: https://github.com/mynyml/watchr
