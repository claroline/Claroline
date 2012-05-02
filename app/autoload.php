<?php

use Symfony\Component\ClassLoader\UniversalClassLoader;
use Symfony\Component\ClassLoader\ApcUniversalClassLoader;
use Doctrine\Common\Annotations\AnnotationRegistry;

if ( extension_loaded('apc') ) {
    require_once __DIR__.'/../vendor/symfony/src/Symfony/Component/ClassLoader/ApcUniversalClassLoader.php';
    $loader = new ApcUniversalClassLoader('apc.prefix.');
} else {
    $loader = new UniversalClassLoader();
}

$coreNamespaces = array(
    'Symfony'          => array(__DIR__.'/../vendor/symfony/src', __DIR__.'/../vendor/bundles'),
    'FOS'              =>__DIR__.'/../vendor/bundles',
    'Sensio'           => __DIR__.'/../vendor/bundles',
    'JMS'              => __DIR__.'/../vendor/bundles',
    'Doctrine\\Common' => __DIR__.'/../vendor/doctrine-common/lib',
    'Doctrine\\DBAL'   => __DIR__.'/../vendor/doctrine-dbal/lib',
    'Doctrine\\DBAL\\Migrations' => __DIR__.'/../vendor/doctrine-migrations/lib',
    'Doctrine\\Common\\DataFixtures' => __DIR__.'/../vendor/doctrine-fixtures/lib',
    'Doctrine'         => __DIR__.'/../vendor/doctrine/lib',
    'Monolog'          => __DIR__.'/../vendor/monolog/src',
    'Assetic'          => __DIR__.'/../vendor/assetic/src',
    'Metadata'         => __DIR__.'/../vendor/metadata/src',
    'Stof'             => __DIR__.'/../vendor/bundles',
    'Gedmo'            => __DIR__.'/../vendor/gedmo-doctrine-extensions/lib',
    'org\\bovigo\\vfs' => __DIR__.'/../vendor/vfsstream/src/main/php',
    'Claroline'        => array(
        __DIR__.'/../src/core',
        __DIR__.'/../src/plugin/extension', 
        __DIR__.'/../src/plugin/application', 
        __DIR__.'/../src/plugin/tool'
    ),
);

$pluginNamespaces = array();

if (file_exists(__DIR__.'/config/local/plugin/namespaces'))
{
    $namespaceValues = file(__DIR__.'/config/local/plugin/namespaces', FILE_IGNORE_NEW_LINES);
    $pluginNamespaces = array_fill_keys(
        $namespaceValues, 
        array(
            __DIR__.'/../src/plugin/extension',
            __DIR__.'/../src/plugin/application', 
            __DIR__.'/../src/plugin/tool'
        )
    );
}

$loader->registerNamespaces(array_merge($pluginNamespaces, $coreNamespaces));

$loader->registerPrefixes(array(
    'Twig_Extensions_' => __DIR__.'/../vendor/twig-extensions/lib',
    'Twig_'            => __DIR__.'/../vendor/twig/lib'
));
$loader->registerPrefixFallbacks(array(
    __DIR__.'/../vendor/symfony/src/Symfony/Component/Locale/Resources/stubs',
));
$loader->registerNamespaceFallbacks(array(
    __DIR__.'/../src',
));
$loader->register();

// Swiftmailer needs a special autoloader to allow
// the lazy loading of the init file (which is expensive)
require_once __DIR__.'/../vendor/swiftmailer/lib/classes/Swift.php';
Swift::registerAutoload(__DIR__.'/../vendor/swiftmailer/lib/swift_init.php');

AnnotationRegistry::registerLoader(function($class) use ($loader) {
    $loader->loadClass($class);
    return class_exists($class, false);
});
AnnotationRegistry::registerFile(__DIR__.'/../vendor/doctrine/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php');