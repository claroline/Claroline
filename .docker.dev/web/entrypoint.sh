#!/bin/bash

set -e

echo "Installing dependencies (or checking if correct ones are installed)"
composer install # if composer.lock exists, this takes ~2 seconds (every subsequent run with no changes to deps)
npm install --legacy-peer-deps # if package-lock.json exists, this takes ~3 seconds (every subsequent run with no changes to deps)
# --legacy-peer-deps is needed until all dependencies are compatible with npm 7 (until npm install runs without error)

# Wait for MySQL to respond, depends on mysql-client
echo "Waiting for $DB_HOST..."
while ! mysqladmin ping -h "$DB_HOST" --silent; do
  echo "MySQL is down"
  sleep 1
done

echo "MySQL is up"

if [ -f files/installed ]; then
  echo "Claroline is already installed, updating and rebuilding themes and translations..."

  php bin/console claroline:update --env=dev -vvv
else
  echo "Installing Claroline for the first time..."
  php bin/console claroline:install --env=dev -vvv

  if [[ -v PLATFORM_NAME ]]; then
    echo "Changing platform name to $PLATFORM_NAME";
    sed -i "/name: claroline/c\name: $PLATFORM_NAME" files/config/platform_options.json
  fi

  if [[ -v PLATFORM_SUPPORT_EMAIL ]]; then
    echo "Changing platform support email to $PLATFORM_SUPPORT_EMAIL";
    sed -i "/support_email: null/c\support_email: $PLATFORM_SUPPORT_EMAIL" files/config/platform_options.json
  fi

  USERS=$(mysql $DB_NAME -u $DB_USER -p$DB_PASSWORD -h $DB_HOST -se "select count(*) from claro_user")

  if [ "$USERS" == "1" ] && [ -v ADMIN_FIRSTNAME ] && [ -v ADMIN_LASTNAME ] && [ -v ADMIN_USERNAME ] && [ -v ADMIN_PASSWORD ]  && [ -v ADMIN_EMAIL ]; then
    echo '*********************************************************************************************************************'
    echo "Creating default admin user for development : $ADMIN_FIRSTNAME $ADMIN_LASTNAME $ADMIN_USERNAME $ADMIN_PASSWORD $ADMIN_EMAIL"
    echo '*********************************************************************************************************************'

    php bin/console claroline:user:create -a $ADMIN_FIRSTNAME $ADMIN_LASTNAME $ADMIN_USERNAME $ADMIN_PASSWORD $ADMIN_EMAIL
  else
    echo 'Users already exist or no admin vars detected, Claroline installed without an admin account'
  fi

  touch files/installed
  echo "Claroline installed, created file ./files/installed for future runs of this container"
fi

echo "Clean cache after setting correct permissions, fixes SAML issues"
composer delete-cache # fixes SAML errors

echo "Setting correct file permissions"
chmod -R 750 var files config
chown -R www-data:www-data var files config

echo "webpack-dev-server starting as a background process..."
nohup npm run webpack:dev -- --host=0.0.0.0 --disable-host-check &

echo "Starting Apache2 in the foreground"
exec "$@"
