<?php

namespace Claroline\BundleRecorder;

use Composer\Script\Event;
use Composer\Script\CommandEvent;
use Composer\Script\PackageEvent;

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
        // will force autoloader registering
        $event->getComposer()->getEventDispatcher()->dispatch($scriptName);
    }
}