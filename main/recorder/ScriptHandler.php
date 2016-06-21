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
use Composer\Script\Event;
use Claroline\BundleRecorder\Detector\Detector;
use Claroline\BundleRecorder\Handler\BundleHandler;

class ScriptHandler
{
    /**
     * @var \Claroline\BundleRecorder\Recorder
     */
    private static $recorder;

    /**
     * Writes the list of available bundles, based on currently installed packages.
     *
     * Should occur on "post-install-cmd" and "post-update-cmd" events.
     *
     * @param Event $event
     */
    public static function buildBundleFile(Event $event)
    {
        static::getRecorder($event)->buildBundleFile();
    }

    public static function removeBupIniFile(Event $event)
    {
        static::getRecorder($event)->removeBupIniFile();
    }

    /**
     * @param Event $event
     *
     * @return Recorder
     */
    private static function getRecorder(Event $event)
    {
        if (!isset(static::$recorder)) {
            $vendorDir = realpath(rtrim($event->getComposer()->getConfig()->get('vendor-dir'), '/'));
            $configDir = realpath($vendorDir.'/../app/config');
            $logger = new ConsoleIoLogger($event->getIO());
            $manager = $event->getComposer()->getRepositoryManager();
            $rootPackage = $event->getComposer()->getPackage();

            static::$recorder = new Recorder(
                $manager->getLocalRepository(),
                new Detector($vendorDir),
                new BundleHandler($configDir, $logger),
                $rootPackage->getAliases(),
                $vendorDir
            );
        }

        return static::$recorder;
    }
}
