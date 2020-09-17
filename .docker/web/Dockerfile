FROM php:7.4-apache

LABEL maintainer="Donovan Tengblad"

RUN curl -sL https://deb.nodesource.com/setup_14.x | bash
RUN a2enmod rewrite expires ssl headers

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
RUN docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu/ && docker-php-ext-install ldap

RUN curl -sS https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer

RUN pecl install apcu-beta \
&& echo extension=apcu.so > /usr/local/etc/php/conf.d/apcu.ini

COPY config/php.ini /usr/local/etc/php/

COPY ./files/apache2/claroline.conf /etc/apache2/sites-available/
COPY ./files/apache2/claroline-ssl.conf /etc/apache2/sites-available/

RUN a2dissite 000-default && a2dissite default-ssl && a2ensite claroline.conf

COPY entrypoint.sh /entrypoint.sh

RUN chmod +x /entrypoint.sh

WORKDIR /var/www/html/claroline
ENTRYPOINT ["/entrypoint.sh"]

CMD ["apache2-foreground"]
