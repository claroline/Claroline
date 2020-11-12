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

use Claroline\BundleRecorder\Detector\Detector;
use Claroline\BundleRecorder\Handler\BundleHandler;
use Claroline\BundleRecorder\Logger\ConsoleIoLogger;
use Composer\Script\Event;

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

    private static function getRecorder(Event $event): Recorder
    {
        if (!isset(static::$recorder)) {
            $vendorDir = realpath(rtrim($event->getComposer()->getConfig()->get('vendor-dir'), '/'));
            $bundleFile = realpath($vendorDir.'/../files/config').DIRECTORY_SEPARATOR.'bundles.ini';
            $logger = new ConsoleIoLogger($event->getIO());

            static::$recorder = new Recorder(
                new BundleHandler($bundleFile, $logger),
                $vendorDir
            );
        }

        return static::$recorder;
    }
}
