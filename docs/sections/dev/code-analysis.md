---
layout: default
title: Code analysis
---

# Code analysis

Beside automatic testing tools, several utilities are used to ensure the
quality of our code base. This section provides some basic information about
their installation, configuration and usage (more detailed information can be
found in their respective websites). It's also worth mentioning that all these
tools are executed on each build by our continuous integration.

## Static analysis

### PHPMD

We use [PHPMD][1] to detect possible conception problems in the code.

The ruleset used in the project is defined in *phpmd.xml*.

Usage example :

```sh
$ vendor/bin/phpmd src/plugin/FooVendor/BarBundle text phpmd.xml
```


### PHPCS

[PHPCS][2] allows us to detect violations of our coding standard.
The installation of PHPCS is also made via PEAR:

The coding standard used in the project is defined in *.php_cs*.

Usage example :

```sh
$ vendor/bin/php-cs-fixer fix src/plugin/FooVendor/BarBundle
```

### ESLint

We use [ESLint][3] to enforce the quality of our Javascript code. This tool
will detect problems in your scripts and check for violations of our coding
standard (here is a brief [description][4]).

The configuration of ESLint used in this project is defined in
*.eslintrc.json*.

Usage example :

```sh
$ node_modules/.bin/eslint path/to/the/file/to/fix --fix
```


[1]:  http://phpmd.org/
[2]:  http://cs.sensiolabs.org/
[3]: https://eslint.org
[4]: http://javascript.crockford.com/code.html
