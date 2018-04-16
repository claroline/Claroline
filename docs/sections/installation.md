---
layout: default
title: Installation
---

### 1. From a pre-built archive

A tarball containing everything needed for development and testing
(pre-fetched sources, database dump, etc.) is made available with every release
of the platform at [packages.claroline.net/releases][releases]. This is the
fastest way to get started:

    curl packages.claroline.net/releases/latest/claroline-16.05.tar.gz | tar xzv
    cd claroline-16.05
    php scripts/configure.php
    composer fast-install

### 2. From source

The raw installation procedure is comprised of several steps that need to be
executed in order (fetching php sources, installing dev dependencies, building,
creating the database, etc.). Except for the configuration step, the whole process
is managed through composer scripts listed in the [composer.json](composer.json)
file. For an installation from scratch, the commands would be:

    git clone http://github.com/claroline/Claroline
    cd Claroline
    php scripts/configure.php
    composer sync-dev

### 3. From web installer

``` curl packages.claroline.net/releases/latest/claroline-16.05.tar.gz | tar xzv ```

Open /install.php from your webserver and follow the instructions.

Upgrade
-------

To update an existing development installation, just pull the latest changes
(or a specific version) of this repository and use the `sync-dev` script:

    git pull
    composer sync-dev
