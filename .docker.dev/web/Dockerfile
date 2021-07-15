FROM php:7.4-apache

LABEL maintainer="Donovan Tengblad"

RUN a2enmod rewrite expires ssl headers

RUN curl -sL https://deb.nodesource.com/setup_14.x | bash
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

RUN wget https://cs.symfony.com/download/php-cs-fixer-v2.phar -O /php-cs-fixer
RUN echo "diffs=\$(git diff --name-only) && php /php-cs-fixer fix --dry-run --diff --config=.php_cs \$diffs && /var/www/html/claroline/node_modules/.bin/eslint --ext js --ext jsx \$diffs" > /usr/local/bin/check-code
RUN echo "diffs=\$(git diff --name-only) && php /php-cs-fixer fix --diff --config=.php_cs \$diffs && /var/www/html/claroline/node_modules/.bin/eslint --fix --ext js --ext jsx \$diffs" > /usr/local/bin/fix-code
RUN chmod +x /usr/local/bin/check-code
RUN chmod +x /usr/local/bin/fix-code

COPY ./.docker.dev/web/config/php.ini /usr/local/etc/php/
COPY ./.docker.dev/web/files/apache2/claroline.conf /etc/apache2/sites-available/
COPY ./.docker.dev/web/files/apache2/claroline-ssl.conf /etc/apache2/sites-available/
RUN a2dissite 000-default && a2dissite default-ssl && a2ensite claroline.conf

COPY . /var/www/html/claroline
WORKDIR /var/www/html/claroline

COPY ./.docker.dev/web/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]
CMD ["apache2-foreground"]
