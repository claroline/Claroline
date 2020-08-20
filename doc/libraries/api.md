# API libraries

## MANDATORY
- friendsofsymfony/jsrouting-bundle
- doctrine/dbal
- doctrine/doctrine-bundle
- doctrine/orm
- stof/doctrine-extensions-bundle
- symfony/monolog-bundle
- symfony/swiftmailer-bundle
- symfony/symfony
- johngrogg/ics-parser (standard pour les Calendrier)
- friendsofsymfony/oauth-server-bundle
- hwi/oauth-bundle
- willdurand/js-translation-bundle
- sensio/framework-extra-bundle
- gedmo/doctrine-extensions
- zendframework/zend-crypt (Utilisé ?)
- besimple/sso-auth-bundle


## DEV DEPENDENCIES
- sensio/generator-bundle


## OURS
- claroline/bundle-recorder
- claroline/front-end-bundle
- claroline/installation-bundle
- claroline/kernel-bundle
- claroline/migration-bundle


## DEBATABLE
- friendsofsymfony/rest-bundle
=> Util surtout pour la serialisation automatique via JMS (sinon la serialisation est plus lourde)
=> Proposition : Ecrire notre propre annotation pour faire ça


## TO REMOVE WITH REFACTORING
- mrclay/minify : minification des JS / remplacé par Webpack
- symfony/assetic-bundle
- knplabs/knp-menu-bundle
- gregwar/captcha-bundle
- zenstruck/form-bundle (support Bootstrap pour les formulaires / Utilisé dans le BadgeBundle)
