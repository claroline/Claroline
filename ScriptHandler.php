<?php

namespace Claroline\BundleRecorder;

use Composer\Script\PackageEvent;
use Claroline\BundleRecorder\Operation;
use Claroline\BundleRecorder\Detector\Detector;
use Claroline\BundleRecorder\Handler\BundleHandler;
use Claroline\BundleRecorder\Handler\OperationHandler;

class ScriptHandler
{
    private static $recorder;
    private static $removablePackages = array();

    public static function postPackageInstall(PackageEvent $event)
    {
        static::getRecorder($event)->record(
            Operation::INSTALL,
            $event->getOperation()->getPackage()
        );
    }

    public static function postPackageUpdate(PackageEvent $event)
    {
        static::getRecorder($event)->record(
            Operation::UPDATE,
            $event->getOperation()->getTargetPackage(),
            $event->getOperation()->getInitialPackage()
        );
    }

    public static function prePackageUninstall(PackageEvent $event)
    {
        static::doPackageUninstall($event, 'preUninstall');
    }

    public static function postPackageUninstall(PackageEvent $event)
    {
        static::doPackageUninstall($event, 'postUninstall');
    }

    private static function getRecorder(PackageEvent $event)
    {
        if (!isset(static::$recorder)) {
            $io = $event->getIO();
            $vendorDir = realpath(rtrim($event->getComposer()->getConfig()->get('vendor-dir'), '/'));
            $configDir = realpath($vendorDir . '/../app/config');
            $logger = function ($message) use ($io) {
                $io->write("    {$message}");
            };
            static::$recorder = new Recorder(
                new Detector($vendorDir),
                new BundleHandler($configDir . '/bundles.ini', $logger),
                new OperationHandler($configDir . '/operations.xml', $logger)
            );
        }

        return static::$recorder;
    }

    private static function doPackageUninstall(PackageEvent $event, $action)
    {
        $package = $event->getOperation()->getPackage()->getPrettyName();
        $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
        $packagePath = realpath(rtrim($vendorDir), '/') . '/' . $package;

        if ($action === 'preUninstall') {
            static::$removablePackages[] = $package;
        } elseif (in_array($package, static::$removablePackages) && !is_dir($packagePath)) {
            static::getRecorder($event)->record(
                Operation::UNINSTALL,
                $event->getOperation()->getPackage()
            );
        }
    }
}
