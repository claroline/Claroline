## Project setup

### Requirements

* PHP >= 5.3.3
* PHP extensions :
    * [ffmpeg][ffmpeg_php_site] (for icon creation)
    * SQLite3 or PDO_SQLite (for the profiler)
* A global installation of [composer][composer_site] (for the dependency management)
* A global installation of [less][less_install] as a [node][node_site] module (for the less files compilation)

[ffmpeg_php_site]: http://ffmpeg-php.sourceforge.net/
[less_install]: http://lesscss.org/#-server-side-usage
[composer_site]: http://getcomposer.org/doc/00-intro.md
[node_site]: http://nodejs.org/

### Quick start

* Clone the project and its associated plugins with `git clone --recursive git://github.com/claroline/Claroline.git`
* Checkout the master branch of each plugin with `git submodule foreach git checkout master`
* Create an *app/config/local/parameters.yml* file according to *app/config/local/parameters.yml.dist*
  (currently only db settings are required)
* Use the automatic install script : `php app/dev/raw_install`
* Make the *app/cache*, *app/logs*, *app/config/local*, *files*, *test* and
  *src/core/Claroline/CoreBundle/Resources/public/css/themes* directories (and their children)
  writable from the command line and the webserver (for further explanation on common permissions
  issues and solutions with Symfony, read [this][symfony_doc_install])
* Open your browser and go to *[site]/web/app.php* (prod) or *[site]/web/app_dev.php* (dev)

[symfony_doc_install]: http://symfony.com/doc/current/book/installation.html#configuration-and-setup

### Quick update

To update your installation to the last development state, use :
`git pull`

`git submodule update --recursive`

Then launch the installation script mentioned above : `php app/dev/raw_install`

***Warning*** : this is a quick dev tool, it will drop existing databases (both prod and test)
and create new ones.

### Plugin installation

You can install or uninstall a plugin with :

  * `php app/console claroline:plugin:install [vendor] [bundle short name]`
  * `php app/console claroline:plugin:uninstall [vendor] [bundle short name]`

A new plugin can be added to the module list with :

`git submodule add git@github.com:vendor/SomeBundle.git src/plugin/Vendor/SomeBundle`

## Development tools

### Testing

In order to run the test suite you must have [phpunit][phpunit_website] installed on your system.

* Run the complete test suite with : `phpunit -c app`
* Run the tests for a single directory with : `phpunit -c app src/core/Claroline/CoreBundle`

[phpunit_website]: http://www.phpunit.de/manual/current/en/index.html

### Build / Static analysis

The *app/build/tools* directory gathers configuration files for various php tools (PHPMD,
PHPCS, Ant, etc.). You can install and use them locally (see their respective documentation
for usage) or visit our continuous integration server [here][ci_website].

[ci_website]: http://dev.claroline.net:8080/job/Claronext/

### Miscellaneous

To have the core Less and TwigJs assets automatically processed and dumped when they have changed,
you can run the provided [watchr][watchr_website] script :

`watchr src/core/Claroline/CoreBundle/Resources/watchr/refresh_assets.rb`

[watchr_website]: https://github.com/mynyml/watchr

## Documentation

For development documentation, see [Claroline/CoreBundle/Resources/doc/index.md][doc_path].

[doc_path]: src/core/Claroline/CoreBundle/Resources/doc/index.md