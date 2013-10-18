PathBundle
==========

### Install
add to the Claroline composer.json
```sh
"innova/path-bundle": "dev-master" 
```

Then execute :
```sh
composer update --prefer-dist -o  
php app/console claroline:update
php app/console assets:install --symlink
```

### Uninstall 
```sh
php app/console claroline:plugin:uninstall InnovaPathBundle 
```