---
layout: default
title: Installation
---

# Installation

You can choose one of the following method to install Claroline :


## From a pre-built archive

A tarball containing everything needed for development and testing
(pre-fetched sources, database dump, etc.) is made available with every release
of the platform at [packages.claroline.net/releases][releases]. This is the
fastest way to get started:

    curl packages.claroline.net/releases/latest/claroline-16.05.tar.gz | tar xzv
    cd claroline-16.05
    php scripts/configure.php
    composer fast-install

## From source

The raw installation procedure is comprised of several steps that need to be
executed in order (fetching php sources, installing dev dependencies, building,
creating the database, etc.). Except for the configuration step, the whole process
is managed through composer scripts listed in the [composer.json](composer.json)
file. For an installation from scratch, the commands would be:

``` 
    git clone -b 11.x http://github.com/claroline/Claroline
    cd Claroline
    php scripts/configure.php
    composer update --prefer-dist --no-dev
    php vendor/sensio/distribution-bundle/Sensio/Bundle/DistributionBundle/Resources/bin/build_bootstrap.php
    npm install -g npm
    npm install
    npm run dll
    npm run webpack
    php app/console claroline:install
    php app/console assetic:dump
    php app/console claroline:theme:build
    php app/console assets:install --symlink
    chmod -R 0777 app/cache
    chmod -R 0777 app/logs
    chmod -R 0777 app/sessions
    chmod -R 0777 files
    chmod -R 0777 app/config/platform_options.yml
```

## From web installer

``` curl packages.claroline.net/releases/latest/claroline-16.05.tar.gz | tar xzv ```

Open /install.php from your webserver and follow the instructions.
