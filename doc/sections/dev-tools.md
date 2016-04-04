[[Documentation index]][1]

Development tools
=================

Besides automatic testing tools, several utilities are used to ensure the
quality of our code base. This section provides some basic information about
their installation, configuration and usage (more detailled information can be
found in their respective websites). It's also worth mentionning that all these
tools are executed on each build by our continuous integration server.
Reports and graphs are available at this address :

[http://dev.claroline.net:8080/job/Claronext/][2]

Static analysis
---------------

### PHPMD ###

We use [PHPMD][3] to detect possible conception problems in the code. The
recommanded way to install PHPMD is via [PEAR][4] :
```sh
$ pear channel-discover pear.phpmd.org
$ pear channel-discover pear.pdepend.org
$ pear install --alldeps phpmd/PHP_PMD
```
The ruleset used in the project is defined in *app//tools/phpmd.xml*.

Usage example :

```sh
$ phpmd src/plugin/FooVendor/BarBundle text app/dev/config/phpmd.xml
```

### PHPCS ###

[PHPCS][5] allows us to detect violations of our coding standard.
The installation of PHPCS is also made via PEAR:

```sh
$ pear install PHP_CodeSniffer-1.5.0RC1
```

The coding standard used in the project is defined in
*app/dev/config/phpcs.xml*. As it relies on the Symfony conventions
(here is a brief [description][6]) and some other sniffs associated with the
PSR initiative, you'll need to install the following standards:

- [https://github.com/klaussilveira/phpcs-psr][7]
- [https://github.com/opensky/Symfony2-coding-standard][8]

This is done by cloning the repositories in the *Standards* directory of your
PHPCS installation. The name of the directories in which the standards are
cloned is important, so be sure to specify them that way:

```sh
$ git clone https://github.com/klaussilveira/phpcs-psr.git PSR
$ git clone https://github.com/opensky/Symfony2-coding-standard.git Symfony2
```

Usage example :

```sh
$ phpcs --extensions=php --standard=app/dev/config/phpcs.xml src/plugin/FooVendor/BarBundle
```

### PHPCPD ###

[PHPCPD][9] is a lightweight utility used to detect copy/paste abuses.
It can be installed via PEAR:

```sh
$ pear config-set auto_discover 1
$ pear install pear.phpunit.de/phpcpd
```

Usage example :

```sh
phpcpd src/plugin/FooVendor/BarBundle
```

### JSHint ###

We use [JSHint][10] to enforce the quality of our Javascript code. This tool
will detect problems in your scripts and check for violations of our coding
standard (here is a brief [description][11]).

You can install it via the [node][12] package manager:

```sh
$ npm install jshint -g
```

The configuration of JSHint used in this project is defined in
*app/dev/config/jshint.json*.

Usage example :

```sh
$ jshint --config app/dev/config/jshint.json src/plugin/FooVendor/BarBundle
```

Scripts
-------

If you plan to apply all these tools on the whole project using the
configuration mentionned above, you can use:

```sh
$ php app/dev/code_analysis
```
## Symfony commands

Here is a list of usefull commands we use in order to speed up the development
and initialize a platform. Some of them come from the symfony/doctrine package
while the others were written by our team.

### Installing the platform ###

You can install the platform with fixtures using:

```sh
$ app/dev/raw_install
```

In case you don't need fixtures, simply use:

```sh
$ php app/console claroline:install
```

Plugins are registered with the command:

```sh
$ php app/console claroline:plugin:install
```

You can remove plugins with:

```sh
$ php app/console claroline:plugin:uninstall
```

**Tips:** The list of registered bundle is saved in the file
**app/config/bundles.ini**.

If you need to add a bundle, you can simply add a line in this file if it
already exists in your vendors. If you don't want a bundle to be instanciated,
you can remove the relevant line here. Errors in the installation may remove the
line concerning the bundle wich crashed (including the ClarolineCoreBundle).

### Loading users ###

You can create a new admin using the command:

```sh
$ php app/console claroline:user:create -a
```

Removing the -a option will create a regular user.

### Generating migrations ###

A properly installed platform will include the MigrationBundle.
It includes several usefull commands.
Please read the MigrationBundle readme for me informations

### Database ###

You can drop the database using:

```sh
$ php app/console doctrine:database:drop --force
```

You can create an empty database using:

```sh
$ php app/console doctrine:database:create
```

### Clearing the cache ###

You can clear the symfony cache using:

```sh
$ php app/console cache:clear
```

**Tips:** It's often better to remove the cache manually using
**$ rm -rf app/cache/**

### Dumping assets ###

You can dump assets using:

```sh
$ php app/console assetic:dump
```
**Tips:** This command is required to compile less files and twigjs templates.


[[Documentation index]][1]

[1]:  ../index.md
[2]:  http://dev.claroline.net:8080/job/Claronext/
[3]:  http://phpmd.org/
[4]:  http://pear.php.net/
[5]:  http://pear.php.net/package/PHP_CodeSniffer
[6]:  http://symfony.com/doc/current/contributing/code/standards.html
[7]:  https://github.com/klaussilveira/phpcs-psr
[8]:  https://github.com/opensky/Symfony2-coding-standard
[9]:  https://github.com/sebastianbergmann/phpcpd
[10]: http://www.jshint.com/
[11]: http://javascript.crockford.com/code.html
[12]: http://nodejs.org/
