<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\KernelBundle;

use Claroline\KernelBundle\Recorder\BundleFileWriter;
use Composer\Script\Event;
use Symfony\Component\Filesystem\Filesystem;

class ScriptHandler
{
    /**
     * @var BundleFileWriter
     */
    private static $handler;

    /**
     * Writes the list of available bundles, based on currently installed packages.
     *
     * Should occur on "post-install-cmd" and "post-update-cmd" events.
     */
    public static function buildBundleFile(Event $event)
    {
        static::getHandler($event)->writeBundleFile();
    }

    private static function getHandler(Event $event): BundleFileWriter
    {
        if (!isset(static::$handler)) {
            $vendorDir = realpath(rtrim($event->getComposer()->getConfig()->get('vendor-dir'), '/'));
            $distBundleFile = realpath($vendorDir.'/../src/main/installation/Resources/config').DIRECTORY_SEPARATOR.'bundles.ini-dist';
            $bundleFile = realpath($vendorDir.'/../files/config').DIRECTORY_SEPARATOR.'bundles.ini';

            static::$handler = new BundleFileWriter(new Filesystem(), $distBundleFile, $bundleFile, $event->getIO());
        }

        return static::$handler;
    }
}
