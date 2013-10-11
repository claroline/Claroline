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

    public static function postPackageInstall(PackageEvent $event)
    {
        static::getRecorder($event)->record(
            Operation::INSTALL,
            $event->getOperation()->getPackage()
        );
    }

    public static function prePackageUpdate(PackageEvent $event)
    {
        static::getRecorder($event)->record(
            Operation::UPDATE,
            $event->getOperation()->getTargetPackage(),
            $event->getOperation()->getInitialPackage()
        );
    }

    public static function prePackageUninstall(PackageEvent $event)
    {
        static::getRecorder($event)->record(
            Operation::UNINSTALL,
            $event->getOperation()->getPackage()
        );
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
}
