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
   echo "ClarolineConnect is already installed"
else
  echo "Executing configuration script"
  php bin/configure
  php bin/check

  composer install --no-dev --optimize-autoloader

  npm install

  npm run webpack

  php bin/console claroline:install

  if [[ -v PLATFORM_NAME ]]; then
    echo "Changing platform name to $PLATFORM_NAME";
    sed -i "/name: claroline/c\name: $PLATFORM_NAME" files/config/platform_options.json
  fi

  if [[ -v PLATFORM_SUPPORT_EMAIL ]]; then
    echo "Changing platform support email to $PLATFORM_SUPPORT_EMAIL";
    sed -i "/support_email: null/c\support_email: $PLATFORM_SUPPORT_EMAIL" files/config/platform_options.json
  fi

  USERS=$(mysql $DB_NAME -u $DB_USER -p$DB_PASSWORD -h $DB_HOST -se "select count(*) from claro_user")

  # a default non-active user is created by the system with username "claroline-connect", so we create a second user that we can use on clean installs
  if [ "$USERS" == "1" ] && [ -v ADMIN_FIRSTNAME ] && [ -v ADMIN_LASTNAME ] && [ -v ADMIN_USERNAME ] && [ -v ADMIN_PASSWORD ]  && [ -v ADMIN_EMAIL ]; then
    echo '*********************************************************************************************************************'
    echo "Creating default admin user : $ADMIN_FIRSTNAME $ADMIN_LASTNAME $ADMIN_USERNAME $ADMIN_PASSWORD $ADMIN_EMAIL"
    echo '*********************************************************************************************************************'

    php bin/console claroline:user:create -a $ADMIN_FIRSTNAME $ADMIN_LASTNAME $ADMIN_USERNAME $ADMIN_PASSWORD $ADMIN_EMAIL
  else
    echo 'ClarolineConnect installed without an admin account'
  fi

  touch installed
fi

echo "Setting correct file permissions"
chmod -R 750 var files config
chown -R www-data:www-data var files config

echo "Clean cache after setting correct permissions, fixes SAML issues"
rm -rf var/cache/prod

exec "$@"
