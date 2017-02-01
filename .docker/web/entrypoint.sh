#!/bin/bash

set -e

chmod -R 777 app/cache app/config app/logs app/sessions files web/uploads
php scripts/configure.php
composer fast-install

if [[ -v PLATFORM_NAME ]]; then
  echo "Changing platform name to $PLATFORM_NAME";
  sed -i "/name: claroline/c\name: $PLATFORM_NAME" app/config/platform_options.yml
fi

if [[ -v PLATFORM_SUPPORT_EMAIL ]]; then
  echo "Changing platform support email to $PLATFORM_SUPPORT_EMAIL";
  sed -i "/support_email: null/c\support_email: $PLATFORM_SUPPORT_EMAIL" app/config/platform_options.yml
fi

if [[ -v PLATFORM_SSL_ENABLED ]]; then
  echo "Changing ssl enabled to $PLATFORM_SSL_ENABLED";
  sed -i "/ssl_enabled: false/c\ssl_enabled: $PLATFORM_SSL_ENABLED" app/config/platform_options.yml
fi

echo 'Claroline Connect has finished installing.'
  
exec "$@"
