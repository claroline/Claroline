<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\BundleRecorder;

use Composer\Script\CommandEvent;
use Composer\Script\Event;
use Composer\Script\PackageEvent;
use Claroline\BundleRecorder\Operation;
use Claroline\BundleRecorder\Detector\Detector;
use Claroline\BundleRecorder\Handler\BundleHandler;
use Claroline\BundleRecorder\Handler\OperationHandler;

class ScriptHandler
{
    private static $recorder;

    public static function prePlatformInstall(CommandEvent $event)
    {
        static::getRecorder($event)->checkForPreviousOperations();
    }

    public static function prePlatformUpdate(CommandEvent $event)
    {
        static::getRecorder($event)->checkForPreviousOperations();
    }

    public static function postPlatformUpdate(CommandEvent $event)
    {
        static::getRecorder($event)->updateBundlesOrder();
    }

    public static function postPackageInstall(PackageEvent $event)
    {
        static::getRecorder($event)->install($event->getOperation()->getPackage());
    }

    public static function postPackageUpdate(PackageEvent $event)
    {
        static::getRecorder($event)->update(
            $event->getOperation()->getTargetPackage(),
            $event->getOperation()->getInitialPackage()
        );
    }

    public static function prePackageUninstall(PackageEvent $event)
    {
        static::getRecorder($event)->addRemovablePackage($event->getOperation()->getPackage());
    }

    public static function postPackageUninstall(PackageEvent $event)
    {
        static::getRecorder($event)->uninstall($event->getOperation()->getPackage());
    }

    /**
     * @param Event $event
     *
     * @return Recorder
     */
    private static function getRecorder(Event $event)
    {
        if (!isset(static::$recorder)) {
            $io = $event->getIO();
            $vendorDir = realpath(rtrim($event->getComposer()->getConfig()->get('vendor-dir'), '/'));
            $configDir = realpath($vendorDir . '/../app/config');
            $logger = function ($message, $indent = '    ') use ($io) {
                $io->write($indent . $message);
            };
            static::$recorder = new Recorder(
                new Detector($vendorDir),
                new BundleHandler($configDir . '/bundles.ini', $logger),
                new OperationHandler($configDir . '/operations.xml', $logger),
                $vendorDir
            );
        }

        return static::$recorder;
    }
}
