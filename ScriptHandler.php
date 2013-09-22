<?php

namespace Claroline\BundleRecorder;

use Composer\Composer;
use Composer\Script\CommandEvent;
use Composer\Script\PackageEvent;
use Composer\Package\PackageInterface;
use Doctrine\Common\Annotations\AnnotationRegistry;

class ScriptHandler
{
    private static $removableBundles = array();

    /**
     * @deprecated Will be removed in 2.0.0
     */
    public static function preUpdateCommand(CommandEvent $event)
    {
    }

    public static function prePackageInstall(PackageEvent $event)
    {
        self::initAutoload($event->getComposer(), $event->getOperation()->getPackage());
    }

    public static function postPackageInstall(PackageEvent $event)
    {
        self::getRecorder($event)->addBundles(self::getBundles($event));
    }

    public static function prePackageUpdate(PackageEvent $event)
    {
        self::initAutoload($event->getComposer(), $event->getOperation()->getPackage());
    }

    public static function prePackageUninstall(PackageEvent $event)
    {
        self::initAutoload($event->getComposer());
        self::$removableBundles = self::getBundles($event);
    }

    public static function postPackageUninstall(PackageEvent $event)
    {
        self::getRecorder($event)->removeBundles(static::$removableBundles);
    }

    private static function initAutoload(Composer $composer, PackageInterface $package = null)
    {
        // This method enables autoloading for installed packages and optionally for non-installed
        // packages targeted by an installation operation. It may become superfluous when composer
        // will handle it internally (see https://github.com/composer/composer/issues/187).
        // As for implementation details, see Composer\Script\EventDispatcher#getListeners().

        $rootPackage = $composer->getPackage();
        $generator = $composer->getAutoloadGenerator();
        $packages = $composer->getRepositoryManager()->getLocalRepository()->getCanonicalPackages();

        if ($package) {
            $packages[] = $package;
        }

        $packageMap = $generator->buildPackageMap($composer->getInstallationManager(), $rootPackage, $packages);
        $map = $generator->parseAutoloads($packageMap, $rootPackage);
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

    private static function getBundles(PackageEvent $event)
    {
        $package = $event->getOperation()->getPackage();
        $vendorDir = rtrim($event->getComposer()->getConfig()->get('vendor-dir'), '/');
        $path = realpath(($vendorDir ? $vendorDir . '/' : '') . $package->getPrettyName());
        $detector = new Detector();

        return $detector->detectBundles($path);
    }

    private static function getRecorder(PackageEvent $event)
    {
        $options = array_merge(
            array('bundle-file' => 'app/config/bundles.ini'),
            $event->getComposer()->getPackage()->getExtra()
        );
        $recorder = new Recorder($options['bundle-file']);
        $io = $event->getIO();
        $recorder->setLogger(function ($message) use ($io) {
            $io->write("    {$message}");
        });

        return $recorder;
    }
}

