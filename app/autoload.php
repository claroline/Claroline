<?php

use Doctrine\Common\Annotations\AnnotationRegistry;

$loader = require __DIR__.'/../vendor/autoload.php';

// intl
if (!function_exists('intl_get_error_code')) {
    require_once __DIR__.'/../vendor/symfony/symfony/src/Symfony/Component/Locale/Resources/stubs/functions.php';

    $loader->add('', __DIR__.'/../vendor/symfony/symfony/src/Symfony/Component/Locale/Resources/stubs');
}

if (file_exists(__DIR__ . '/config/local/plugin/namespaces')) {
    $namespaces = file(__DIR__ . '/config/local/plugin/namespaces', FILE_IGNORE_NEW_LINES);

    foreach ($namespaces as $namespace) {
        $loader->add($namespace, __DIR__ . '/../src/plugin');
    }
}

$loader->add('Claroline', array(__DIR__.'/../src/core', __DIR__.'/../src/plugin'));

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

return $loader;
