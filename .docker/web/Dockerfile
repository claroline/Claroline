FROM php:8.0-apache

LABEL maintainer="Donovan Tengblad"

RUN a2enmod rewrite expires ssl headers

RUN curl -sL https://deb.nodesource.com/setup_16.x | bash
RUN apt-get update && apt-get install -y \
  git \
  mariadb-client \
  wget \
  unzip \
  zip \
  libxml2-dev \
  libmcrypt-dev \
  libcurl4-gnutls-dev \
  xz-utils \
  xfonts-75dpi \
  libldap2-dev \
  libpng-dev \
  libjpeg-dev \
  zlib1g-dev \
  libicu-dev \
  libzip-dev \
  libonig-dev \
  g++ \
  ssl-cert \
  curl \
  nodejs\
  && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install -j$(nproc) xml mysqli curl zip mbstring gettext pdo_mysql gd exif mbstring
RUN docker-php-ext-configure intl
RUN docker-php-ext-install intl

RUN curl -sS https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer

RUN pecl install apcu-beta \
  && echo extension=apcu.so > /usr/local/etc/php/conf.d/apcu.ini

COPY ./.docker/web/config/php.ini /usr/local/etc/php/
COPY ./.docker/web/files/apache2/claroline.conf /etc/apache2/sites-available/
COPY ./.docker/web/files/apache2/claroline-ssl.conf /etc/apache2/sites-available/
RUN a2dissite 000-default && a2dissite default-ssl && a2ensite claroline.conf

COPY . /var/www/html/claroline
WORKDIR /var/www/html/claroline

RUN composer install --no-dev --optimize-autoloader
RUN rm ./config/parameters.yml

RUN npm install --legacy-peer-deps
RUN npm run webpack

RUN chmod 644 ./.docker/mysql

COPY ./.docker/web/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]
CMD ["apache2-foreground"]
