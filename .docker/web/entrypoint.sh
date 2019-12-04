#!/bin/bash

set -e


# Wait for MySQL to respond, depends on mysql-client
echo "Waiting for $DB_HOST..."
while ! mysqladmin ping -h "$DB_HOST" --silent; do
    echo "MySQL is down"
    sleep 1
done

echo "MySQL is up"

if [ -f installed ]; then
   echo "ClarolineConnect is allready installed"
else
  echo "Executing configuration script"
  php scripts/configure.php

  echo "Composer install"
  if [[ "$ENV" == "dev" ]]; then
    composer sync-dev
  else
    composer sync
  fi

  if [[ -v PLATFORM_NAME ]]; then
    echo "Changing platform name to $PLATFORM_NAME";
    sed -i "/name: claroline/c\name: $PLATFORM_NAME" app/config/platform_options.yml
  fi

  if [[ -v PLATFORM_SUPPORT_EMAIL ]]; then
    echo "Changing platform support email to $PLATFORM_SUPPORT_EMAIL";
    sed -i "/support_email: null/c\support_email: $PLATFORM_SUPPORT_EMAIL" app/config/platform_options.yml
  fi

  USERS=$(mysql $DB_NAME -u $DB_USER -p$DB_PASSWORD -h $DB_HOST -se "select count(*) from claro_user")

  if [ "$USERS" == "0" ] && [ -v ADMIN_FIRSTNAME ] && [ -v ADMIN_LASTNAME ] && [ -v ADMIN_USERNAME ] && [ -v ADMIN_PASSWORD ]  && [ -v ADMIN_EMAIL ]; then
    echo '*********************************************************************************************************************'
    echo "Creating default admin user : $ADMIN_FIRSTNAME $ADMIN_LASTNAME $ADMIN_USERNAME $ADMIN_PASSWORD $ADMIN_EMAIL"
    echo '*********************************************************************************************************************'

    php app/console claroline:user:create -a $ADMIN_FIRSTNAME $ADMIN_LASTNAME $ADMIN_USERNAME $ADMIN_PASSWORD $ADMIN_EMAIL
  else
    echo 'ClarolineConnect installed without an admin account'
  fi

  touch installed
fi

echo "Setting correct file permissions"
chmod -R 777 app/cache app/config app/logs app/sessions files web/uploads

exec "$@"
