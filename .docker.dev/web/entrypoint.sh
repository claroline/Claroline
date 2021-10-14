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

  if [ -f files/versionLastUsed.txt ]; then
    versionLastUsed=$(head -n 1 files/versionLastUsed.txt)
    currentVersion=$(head -n 1 VERSION.txt)

    if [[ "$versionLastUsed" != "$currentVersion" ]]; then
      echo "New version detected, updating..."
      composer install
      npm install --legacy-peer-deps
      php bin/console claroline:update --env=dev -vvv
      chmod -R 777 var files config
      composer delete-cache # fixes SAML errors
    else
      echo "Claroline version is up to date"
    fi
  fi
else
  echo "Installing Claroline..."
  composer install
  npm install --legacy-peer-deps
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

  if [ "$USERS" == "0" ] && [ -v ADMIN_FIRSTNAME ] && [ -v ADMIN_LASTNAME ] && [ -v ADMIN_USERNAME ] && [ -v ADMIN_PASSWORD ]  && [ -v ADMIN_EMAIL ]; then
    echo '*********************************************************************************************************************'
    echo "Creating default admin user : $ADMIN_FIRSTNAME $ADMIN_LASTNAME $ADMIN_USERNAME $ADMIN_PASSWORD $ADMIN_EMAIL"
    echo '*********************************************************************************************************************'

    php bin/console claroline:user:create -a $ADMIN_FIRSTNAME $ADMIN_LASTNAME $ADMIN_USERNAME $ADMIN_PASSWORD $ADMIN_EMAIL
  else
    echo 'Users already exist or no admin vars detected, Claroline installed without an admin account'
  fi

  echo "Setting correct file permissions"
  chmod -R 777 var files config

  echo "Clean cache after setting correct permissions, fixes SAML issues"
  composer delete-cache
  touch files/installed
fi

cp VERSION.txt files/versionLastUsed.txt

echo "webpack-dev-server starting as a background process..."
nohup npm run webpack:dev -- --host=0.0.0.0 --disable-host-check &

echo "Starting Apache2 in the foreground"
exec "$@"
