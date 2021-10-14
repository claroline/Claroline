#!/bin/bash

set -e

php bin/configure # we run it again to generate parameters.yml inside the volume
composer bundles # we run it again to generate bundles.ini inside the volume
composer delete-cache # fixes install/update errors

# Wait for MySQL to respond, depends on mysql-client
echo "Waiting for $DB_HOST..."
while ! mysqladmin ping -h "$DB_HOST" --silent; do
  echo "MySQL is down"
  sleep 1
done

echo "MySQL is up"

if [ -f files/installed ]; then
  echo "Claroline is already installed, updating and rebuilding themes and translations..."

  php bin/console claroline:update -vvv
else
  echo "Installing Claroline for the first time..."
  php bin/console claroline:install -vvv

  if [[ -v PLATFORM_NAME ]]; then
    echo "Changing platform name to $PLATFORM_NAME";
    sed -i "/name: claroline/c\name: $PLATFORM_NAME" files/config/platform_options.json
  fi

  if [[ -v PLATFORM_SUPPORT_EMAIL ]]; then
    echo "Changing platform support email to $PLATFORM_SUPPORT_EMAIL";
    sed -i "/support_email: null/c\support_email: $PLATFORM_SUPPORT_EMAIL" files/config/platform_options.json
  fi

  echo "In order to create an admin user, run the following command inside the docker container (and replace the variables):"
  echo "php bin/console claroline:user:create -a \$ADMIN_FIRSTNAME \$ADMIN_LASTNAME \$ADMIN_USERNAME \$ADMIN_PASSWORD \$ADMIN_EMAIL"

  touch files/installed
  echo "Claroline installed, created file ./files/installed for future runs of this container"
fi

echo "Clean cache after setting correct permissions, fixes SAML issues"
composer delete-cache # fixes SAML errors

echo "Setting correct file permissions"
chmod -R 750 var files config
chown -R www-data:www-data var files config

exec "$@"
