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

use Claroline\BundleRecorder\Logger\ConsoleIoLogger;
use Composer\Script\Event as ScriptEvent;
use Composer\EventDispatcher\Event;
use Composer\Installer\PackageEvent;
use Claroline\BundleRecorder\Operation;
use Claroline\BundleRecorder\Detector\Detector;
use Claroline\BundleRecorder\Handler\BundleHandler;
use Claroline\BundleRecorder\Handler\OperationHandler;

class ScriptHandler
{
    /**
     * @var \Claroline\BundleRecorder\Recorder
     */
    private static $recorder;

    /**
     * Blocks the execution of install/update if an operation file is already
     * present (i.e. prevents packages changes if previous updates were not
     * properly performed).
     *
     * Should occur on "pre-install-cmd" and "pre-update-cmd" events.
     *
     * @param ScriptEvent $event
     */
    public static function checkForPendingOperations(ScriptEvent $event)
    {
        static::getRecorder($event)->checkForPendingOperations();
    }

    /**
     * Adds an install instruction to the operation file if the package type
     * is "claroline-core" or "claroline-plugin".
     *
     * Should occur on "post-package-install" event.
     *
     * @param PackageEvent $event
     */
    public static function logInstallOperation(PackageEvent $event)
    {
        static::getRecorder($event)->addInstallOperation($event->getOperation()->getPackage());
    }

    /**
     * Adds an update instruction to the operation file if the package type
     * is "claroline-core" or "claroline-plugin".
     *
     * Should occur on "post-package-update" event.
     *
     * @param PackageEvent $event
     */
    public static function logUpdateOperation(PackageEvent $event)
    {
        static::getRecorder($event)->addUpdateOperation(
            $event->getOperation()->getTargetPackage(),
            $event->getOperation()->getInitialPackage()
        );
    }

    /**
     * Adds a version file of the package type is
     *  "claroline-core" or "claroline-plugin".
     *
     * Should occur on "post-package-update" event.
     *
     * @param PackageEvent $event
     */
    public static function addVersion(PackageEvent $event)
    {
        static::getRecorder($event)->addVersion(
            $event->getOperation()->getTargetPackage()
        );
    }

    /**
     * Keeps track of a package before composer removes it, if the package
     * type is "claroline-core" or "claroline-plugin".
     *
     * Should occur on "pre-package-uninstall" event.
     *
     * @param PackageEvent $event
     */
    public static function prepareUninstallOperation(PackageEvent $event)
    {
        static::getRecorder($event)->addRemovablePackage($event->getOperation()->getPackage());
    }

    /**
     * Adds an uninstall instruction to the operation file if the package type
     * is "claroline-core" or "claroline-plugin".
     *
     * Should occur on "post-package-uninstall" event.
     *
     * @param PackageEvent $event
     */
    public static function logUninstallOperation(PackageEvent $event)
    {
        static::getRecorder($event)->addUninstallOperation($event->getOperation()->getPackage());
    }

    /**
     * Writes the list of available bundles, based on currently installed packages.
     *
     * Should occur on "post-install-cmd" and "post-update-cmd" events.
     *
     * @param ScriptEvent $event
     */
    public static function buildBundleFile(ScriptEvent $event)
    {
        static::getRecorder($event)->buildBundleFile();
    }

    /**
     * @param Event $event
     *
     * @return Recorder
     */
    private static function getRecorder(Event $event)
    {
        if (!isset(static::$recorder)) {
            /** @var \Composer\IO\ConsoleIO $io */
            $io = $event->getIO();
            $vendorDir = realpath(rtrim($event->getComposer()->getConfig()->get('vendor-dir'), '/'));
            $configDir = realpath($vendorDir . '/../app/config');

            $logger = new ConsoleIoLogger($io);

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
