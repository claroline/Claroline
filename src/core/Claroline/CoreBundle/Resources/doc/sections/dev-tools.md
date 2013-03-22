[[Documentation index]][1]

Development tools
=================

Besides automatic testing tools, several utilities are used to ensure the quality of
our code base. This section provides some basic information about their installation,
configuration and usage (more detailled information can be found in their respective
websites). It's also worth mentionning that all these tools are executed on each build
by our continuous integration server. Reports and graphs are available at this address :

[http://dev.claroline.net:8080/job/Claronext/][2]

Static analysis
---------------

### PHPMD

We use [PHPMD][3] to detect possible conception problems in the code. The recommanded
way to install PHPMD is via [PEAR][4] :

    $ pear channel-discover pear.phpmd.org
    $ pear channel-discover pear.pdepend.org
    $ pear install --alldeps phpmd/PHP_PMD

The ruleset used in the project is defined in *app/build/tools/phpmd.xml*.

Usage example :

    $ phpmd src/plugin/FooVendor/BarBundle text app/build/tools/phpmd.xml

### PHPCS

[PHPCS][5] allows us to detect violations of our coding standard. The installation
of PHPCS is also made via PEAR :

    $ pear install PHP_CodeSniffer-1.5.0RC1

The coding standard used in the project is defined in *app/build/tools/phpcs.xml*. As it
relies on the Symfony conventions (here is a brief [description][6]) and some other sniffs
associated with the PSR initiative,
you'll need to install the following standards :

- [https://github.com/klaussilveira/phpcs-psr][7]
- [https://github.com/opensky/Symfony2-coding-standard][8]

This is done by cloning the repositories in the *Standards* directory of your PHPCS
installation. The name of the directories in which the standards are cloned is important,
so be sure to specify them that way :

    $ git clone https://github.com/klaussilveira/phpcs-psr.git PSR
    $ git clone https://github.com/opensky/Symfony2-coding-standard.git Symfony2

Usage example :

    $ phpcs --extensions=php --standard=app/build/tools/phpcs.xml src/plugin/FooVendor/BarBundle

### PHPCPD

[PHPCPD][9] is a lightweight utility used to detect copy/paste abuses. It can be installed
via PEAR :

    $ pear config-set auto_discover 1
    $ pear install pear.phpunit.de/phpcpd

Usage example :

    $ phpcpd src/plugin/FooVendor/BarBundle

### JSHint

We use [JSHint][10] to enforce the quality of our Javascript code. This tool will detect problems
in your scripts and check for violations of our coding standard (here is a brief [description][11]).
You can install it via the [node][12] package manager :

    $ npm install jshint -g

The configuration of JSHint used in this project is defined in *app/build/tools/jshint.json*.

Usage example :

    $ jshint --config app/build/tools/jshint.json src/plugin/FooVendor/BarBundle

Scripts
-------

If you plan to apply all these tools on the whole project using the configuration mentionned
above, you can use :

    $ php app/dev/code_analysis


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