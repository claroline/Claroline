---
layout: default
title: Installation & Upgrades
---

# Installation and Upgrades

## Installation

You can install the Claroline platform by downloading the source code or using [Docker](https://www.docker.com/).

### Installation from source (production and development - recommended)

Before starting to install the Claroline platform, make sure your system meet the [Requirements](https://claroline.github.io/Claroline/sections/getting-started/requirements.html)

#### 1. get the source code

You can either clone the GitHub repository:

    # this will automatically grab source code for current version of Claroline
    git clone https://github.com/claroline/Claroline MY_PROJECT_DIR

Or download the archive of our last release on the [Releases page](https://github.com/claroline/Claroline/releases) 
and extract it in your project directory.

> Using [Git](https://git-scm.com/) is the recommended way to install the Claroline platform as it simplifies future platform upgrades.

For the next steps of the installation, you'll need to go inside the Claroline directory.

    cd MY_PROJECT_DIR

#### 2. install external dependencies

    composer install --no-dev --optimize-autoloader
    npm install (for npm 7+ you need to pass --legacy-peer-deps)

#### 3. build the application

    npm run webpack
    php bin/console claroline:install -vvv

#### 4. create the first user (optional)

In order to use your freshly installed Claroline platform, you'll need to create an admin user. 
This can be done with the following command :

    php bin/console claroline:user:create first_name last_name username password email -a

### Installation using Docker (development only)

**Warning**: this is for development/testing purposes *only*, this must **NOT** be used in production environments as it represents huge security risks, maintainability issues and performance degradations.

As a developer, by using Docker you can quickly get the platform running in DEV mode and experiment with code changes.

You can also develop a custom theme in watch mode.

To learn more: [Docker instructions](docs/sections/dev/docker.md)


## Upgrades

The upgrade process slightly change if you upgrade to the next patch version or a major one
(see [release process](https://claroline.github.io/Claroline/sections/dev/release.html) for more information about Claroline versions).

> You can find the version of your Claroline platform by checking the [VERSION.txt](https://github.com/claroline/Claroline/blob/13.1/VERSION.txt) file.

### Upgrade to a patch version 
*For example, from 13.1.0 to 13.1.2*

#### 1. get the source code

    git pull origin 13.1

#### 2. update external dependencies

    composer update --no-dev --optimize-autoloader
    npm install (for npm 7+ you need to pass --legacy-peer-deps)

#### 3. rebuild the application

    php bin/console claroline:update -vvv

### Upgrade to a minor version 
*For example, from 13.0.39 to 13.1.1*

#### 1. get the source code

    git fetch origin
    git checkout 13.1

#### 2. update external dependencies

    composer update --no-dev --optimize-autoloader
    npm install (for npm 7+ you need to pass --legacy-peer-deps)

#### 3. rebuild the application

    php bin/console claroline:update -vvv

### Upgrade to a major version 
*For example, from 13.1 to 14.0*

#### 1. upgrade to the last previous version

Following the instructions of *Upgrade to a minor version*, you'll need to upgrade your platform to the last release of
your current version. See the [Releases page](https://github.com/claroline/Claroline/releases) to find it.

#### 2. get the source code

    git fetch origin
    git checkout 14.0

#### 3. update external dependencies

    composer update --no-dev --optimize-autoloader
    npm install (for npm 7+ you need to pass --legacy-peer-deps)

#### 4. rebuild the application

    php bin/console claroline:update -vvv

## Common problems

Most of the problems which occur after an installation / upgrade can be fixed by :

### Clearing the cache

    rm -rf var/cache/*

### Resetting the directories permissions

As explained in the [Requirements](https://claroline.github.io/Claroline/sections/getting-started/requirements.html), your
web server must have the correct access rights to some of the platform directories. Check the rights are still correct
and fix them if needed.
