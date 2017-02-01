#!/bin/bash

set -e

# Wait for MySQL to respond, depends on mysql-client
while ! mysqladmin ping -h"$DB_HOST" --silent; do
    echo "MySQL is down"
    sleep 1
done

echo "MySQL is up"

echo "Setting correct file permissions"
chmod -R 777 app/cache app/config app/logs app/sessions files web/uploads

echo "Executing configuration script"
php scripts/configure.php

echo "Composer install"
composer sync-dev

if [[ -v PLATFORM_NAME ]]; then
  echo "Changing platform name to $PLATFORM_NAME";
  sed -i "/name: claroline/c\name: $PLATFORM_NAME" app/config/platform_options.yml
fi

if [[ -v PLATFORM_SUPPORT_EMAIL ]]; then
  echo "Changing platform support email to $PLATFORM_SUPPORT_EMAIL";
  sed -i "/support_email: null/c\support_email: $PLATFORM_SUPPORT_EMAIL" app/config/platform_options.yml
fi

exec "$@"
