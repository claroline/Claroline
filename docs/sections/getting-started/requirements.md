---
layout: default
title: Requirements
---

# Requirements

- PHP 7.2 or higher with the following extensions:
    - curl
    - dom
    - fileinfo
    - gd
    - intl
    - json
    - mbstring
    - openssl
    - pdo
    - pdo_mysql
    - simplexml
    - zip
- MySQL/MariaDB 8.0 or higher
- [Composer](https://getcomposer.org) 2 or higher
- [node.js](https://nodejs.org) 10 or higher
- [npm](https://docs.npmjs.com) 6 or higher
- [Git](https://git-scm.com/)

It's also highly recommended installing Claroline on an UNIX-like OS.

## Web server

You'll also need a PHP-enabled web server to serve the application.
Two alternatives are available.

### 1. Using Symfony web server (not tested)

This is the simplest way of serving the application during
development. To start the server, use the command provided by the symfony
local server (more details on installation and configuration [here](https://symfony.com/doc/4.4/setup/symfony_server.html)):

    symfony server:start

The application will be available at [http://localhost:8000](http://localhost:8000).

### 2. Using a standalone web server (recommended)

If you want to use Apache or Nginx during development, make them serve the
*public* directory, and access the application at
[http://localhost/example-site/index.php](http://localhost/example-site/index.php).

## Directories permissions

Note that you'll certainly face permissions issues on the following directories:

- *config*
- *var/cache*
- *var/log*
- *var/sessions*
- *files*
- *public/uploads*

All of them must be recursively writable from both the web server and the CLI.
For more information on that subject, see the [configuration section](https://symfony.com/doc/4.4/setup/web_server_configuration.html)
of the official Symfony documentation.
