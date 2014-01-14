<?php

use Doctrine\Common\Annotations\AnnotationRegistry;
use Composer\Autoload\ClassLoader;

if (file_exists($loaderFile = __DIR__.'/../vendor/autoload.php')) {
    $loader = require $loaderFile;

    AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

    return $loader;
}
