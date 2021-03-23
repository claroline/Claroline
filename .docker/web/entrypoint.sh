#!/bin/bash

set -e

# Wait for MySQL to respond, depends on mysql-client
echo "Waiting for $DB_HOST..."
while ! mysqladmin ping -h "$DB_HOST" --silent; do
  echo "MySQL is down"
  sleep 1
done

echo "MySQL is up"

if [ -f files/installed ]; then
  echo "Claroline is already installed"

  currentVersion=$(head -n 1 VERSION.txt)
  versionLastUsed=$(head -n 1 files/versionLastUsed.txt)

  if [[ "$currentVersion" != "$versionLastUsed" ]]; then
    echo "New version detected, updating..."
    php bin/configure # we run it again to regenerate parameters.yml inside the volume
    php bin/check
    composer enable-maintenance
    composer delete-cache
    php bin/console claroline:update -vvv
    composer disable-maintenance
    chmod -R 750 var files config
    chown -R www-data:www-data var files config
    composer delete-cache # fixes SAML errors
  fi
else
  echo "Installing Claroline..."
  php bin/configure # we run it again to generate parameters.yml inside the volume
  php bin/check
  composer bundles
  php bin/console claroline:install -vvv

  if [[ -v PLATFORM_NAME ]]; then
    echo "Changing platform name to $PLATFORM_NAME";
    sed -i "/name: claroline/c\name: $PLATFORM_NAME" files/config/platform_options.json
  fi

  if [[ -v PLATFORM_SUPPORT_EMAIL ]]; then
    echo "Changing platform support email to $PLATFORM_SUPPORT_EMAIL";
    sed -i "/support_email: null/c\support_email: $PLATFORM_SUPPORT_EMAIL" files/config/platform_options.json
  fi

  USERS=$(mysql $DB_NAME -u $DB_USER -p$DB_PASSWORD -h $DB_HOST -se "select count(*) from claro_user")

  if [ "$USERS" == "0" ] && [ -v ADMIN_FIRSTNAME ] && [ -v ADMIN_LASTNAME ] && [ -v ADMIN_USERNAME ] && [ -v ADMIN_PASSWORD ]  && [ -v ADMIN_EMAIL ]; then
    echo '*********************************************************************************************************************'
    echo "Creating default admin user : $ADMIN_FIRSTNAME $ADMIN_LASTNAME $ADMIN_USERNAME $ADMIN_PASSWORD $ADMIN_EMAIL"
    echo '*********************************************************************************************************************'

    php bin/console claroline:user:create -a $ADMIN_FIRSTNAME $ADMIN_LASTNAME $ADMIN_USERNAME $ADMIN_PASSWORD $ADMIN_EMAIL
  else
    echo 'Users already exist or no admin vars detected, Claroline installed without an admin account'
  fi

  echo "Setting correct file permissions"
  chmod -R 750 var files config
  chown -R www-data:www-data var files config

  echo "Clean cache after setting correct permissions, fixes SAML issues"
  rm -rf var/cache/prod
  touch files/installed
fi

cp VERSION.txt files/versionLastUsed.txt

exec "$@"
