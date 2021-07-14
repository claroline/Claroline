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

We use [PHPMD][3] to detect possible conception problems in the code.

```
The ruleset used in the project is defined in *phpmd.xml*.

Usage example :

```sh
$ phpmd src/plugin/FooVendor/BarBundle text app/dev/config/phpmd.xml
```


### PHPCS

[PHPCS][5] allows us to detect violations of our coding standard.
The installation of PHPCS is also made via PEAR:

The coding standard used in the project is defined in
*.php_cs*. As it relies on the Symfony conventions
(here is a brief [description][6]) and some other sniffs associated with the
PSR initiative, you'll need to install the following standards:

- [https://github.com/klaussilveira/phpcs-psr][7]
- [https://github.com/opensky/Symfony2-coding-standard][8]

Usage example :

```sh
$ phpcs --extensions=php --standard=app/dev/config/phpcs.xml src/plugin/FooVendor/BarBundle
```

### ESLint

We use [ESLint][10] to enforce the quality of our Javascript code. This tool
will detect problems in your scripts and check for violations of our coding
standard (here is a brief [description][11]).

The configuration of ESLint used in this project is defined in
*.eslintrc.json*.

[3]:  http://phpmd.org/
[5]:  http://cs.sensiolabs.org/
[6]:  http://symfony.com/doc/current/contributing/code/standards.html
[7]:  https://github.com/klaussilveira/phpcs-psr
[8]:  https://github.com/opensky/Symfony2-coding-standard
[10]: https://eslint.org
[11]: http://javascript.crockford.com/code.html
[12]: http://nodejs.org/
