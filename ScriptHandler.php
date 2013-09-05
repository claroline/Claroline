<?php

namespace Claroline\BundleRecorder;

use Composer\Script\Event;
use Composer\Script\CommandEvent;
use Composer\Script\PackageEvent;
use Doctrine\Common\Annotations\AnnotationRegistry;

class ScriptHandler
{
    public static function preUpdateCommand(CommandEvent $event)
    {
        self::initAutoload($event, __METHOD__);
    }

    public static function prePackageInstall(PackageEvent $event)
    {
        self::initAutoload($event, __METHOD__);
    }

    public static function postPackageInstall(PackageEvent $event)
    {
        $recorder = new Recorder($event->getComposer());
        $recorder->addBundlesFrom($event->getOperation()->getPackage());
    }

    public static function postPackageUninstall(PackageEvent $event)
    {
        $recorder = new Recorder($event->getComposer());
        $recorder->removeBundlesFrom($event->getOperation()->getPackage());
    }

    private static function initAutoload(Event $event, $scriptName)
    {
        // some classes may need to be loaded during the install process, thus
        // *before* the autoloader is dumped by composer. This method ensures that
        // everything is loadable by forcing to register the autoloader. For the
        // implementation, see Composer\Script\EventDispatcher#getListeners().
        $composer = $event->getComposer();
        $package = $composer->getPackage();
        $generator = $composer->getAutoloadGenerator();
        $packages = $composer->getRepositoryManager()->getLocalRepository()->getCanonicalPackages();
        $packageMap = $generator->buildPackageMap($composer->getInstallationManager(), $package, $packages);
        $map = $generator->parseAutoloads($packageMap, $package);
        $loader = $generator->createLoader($map);

        if (isset($map['classmap'][0])) {
            foreach ($map['classmap'][0] as $path) {
                $loader->add('', $path);
            }
        }

        $loader->register();

        if (class_exists('Doctrine\Common\Annotations\AnnotationRegistry')) {
            AnnotationRegistry::registerLoader(array($loader, 'loadClass'));
        }
    }
}