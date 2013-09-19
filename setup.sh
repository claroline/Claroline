mysql --user=root --password=vanille -e "drop database if exists claroline_prod"
rm -f app/config/bundles.ini
rm -rf vendor
rm -rf app/cache/*
rm -rf ~/.composer/cache/*
rm composer.json
rm composer.lock
composer require claroline/bundle-recorder ~1.0
cp composer.dist.json composer.json
composer update --prefer-source
php app/console claroline:user:create -a admin admin admin admin
php app/console assetic:dump
