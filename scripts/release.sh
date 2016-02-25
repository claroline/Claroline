#!/bin/bash

#
# This script creates a tarball in the current working directory containing
# everything that's necessary for a quick install, such as pre-fetched
# vendors and a database dump.
#
# It requires several environment variables to be set:
#
#   - RELEASE_ENV: target environment of the release (prod or dev)
#   - RELEASE_REF: git branch or tag to checkout in claroline/Claroline
#   - RELEASE_DB_NAME: name of the tmp release database
#   - RELEASE_DB_USER: mysql user for the tmp release database
#   - RELEASE_DB_PASSWORD: mysql password for the tmp release database
#
# If the target environment is set to production, dependencies will be
# preferably fetched as archives, otherwise git clones will be used
# for the distribution packages at least.
#

set -e

: ${RELEASE_ENV:?"must be set"}
: ${RELEASE_REF:?"must be set"}
: ${RELEASE_DB_NAME:?"must be set"}
: ${RELEASE_DB_USER:?"must be set"}
: ${RELEASE_DB_PASSWORD:?"must be set"}

ENV=`echo $RELEASE_ENV | tr '[:upper:]' '[:lower:]'`
DIR="claroline-${RELEASE_REF}-${ENV}"

echo "Setting up release database..."
    mysql -u$RELEASE_DB_USER -p$RELEASE_DB_PASSWORD -e "
        DROP DATABASE IF EXISTS \`${RELEASE_DB_NAME}\`;
        CREATE DATABASE \`${RELEASE_DB_NAME}\`;"

echo "Cloning main repo..."
    rm -rf "$DIR"
    git clone https://github.com/claroline/Claroline.git "$DIR"
    cd "$DIR"
    git checkout $RELEASE_REF

echo "Configuring..."
    DB_HOST=localhost \
    DB_NAME=$RELEASE_DB_NAME \
    DB_USER=$RELEASE_DB_USER \
    DB_PASSWORD=$RELEASE_DB_PASSWORD \
    SECRET=`date | md5sum | cut -d' ' -f1` \
    php scripts/configure.php

echo "Installing..."
    if [ "$ENV" = "prod" ]; then
        composer sync
    else
        composer install --prefer-dist
        rm -rf vendor/claroline vendor/formalibre vendor/icap vendor/innova vendor/ujm
        composer sync-dev
        rm -rf vendor/claroline/front-end-bundle/.git # tmp trick to reduce final size
    fi

echo "Dumping database..."
    mysqldump \
        --opt \
        --no-create-db \
        $RELEASE_DB_NAME \
        -u$RELEASE_DB_USER \
        -p$RELEASE_DB_PASSWORD \
        > claroline.sql

echo "Removing local files..."
    rm -f app/config/parameters.yml # don't publish CI config...
    rm -rf app/cache/* app/logs/*
    git checkout . # restore .gitkeep files

echo "Creating archive..."
    cd ..
    tar czvf "${DIR}.tar.gz" ${DIR}
