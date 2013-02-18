## Project setup

### Requirements

* PHP >= 5.3.3
* PHP extensions :
    * [ffmpeg][ffmpeg_php_site] (for icon creation)
    * SQLite3 or PDO_SQLite (for the profiler)
* A global installation of [less][less_install] as a [node][node_website] module (for the css file compilation)

[ffmpeg_php_site]: http://ffmpeg-php.sourceforge.net/
[less_install]: http://lesscss.org/#-server-side-usage
[node_website]: http://nodejs.org/

### Quick start

Use `git clone --recursive git://github.com/claroline/Claroline.git` to clone this repository
and its associated plugins (git submodules).

After cloning the project :

* create an *app/config/local/parameters.yml* file according to *app/config/local/parameters.yml.dist*
  (currently only db settings are required)
* install a local copy of [composer][composer_website] in the root directory of the project
* make the *app/cache*, *app/logs*, *app/config/local*, *files*, *test* and
  *src/core/Claroline/CoreBundle/Resources/public/css/themes* directories (and their children)
  writable from the command line and the webserver (for further explanation on common permissions
  issues and solutions with Symfony, read [this][symfony_doc_install])
* use the automatic install script : `php app/dev/raw_install`
* open your browser and go to *[site]/web/app.php* (prod) or *[site]/web/app_dev.php* (dev)

[composer_website]: http://getcomposer.org/download/
[symfony_doc_install]: http://symfony.com/doc/current/book/installation.html#configuration-and-setup

### Quick update

To update your installation to the last development state after, use :
`git pull`
`git submodule update --recursive`

Then launch the installation script mentioned above : `php app/dev/raw_install`

***Warning*** : this is a quick dev tool, it will drop existing databases (both prod and test)
and create new ones.

### Plugin installation

You can install or uninstall a plugin with :

  * `php app/console claroline:plugin:install [vendor] [bundle short name]`
  * `php app/console claroline:plugin:uninstall [vendor] [bundle short name]`

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