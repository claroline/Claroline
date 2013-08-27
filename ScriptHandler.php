<?php

namespace Claroline\BundleRecorder;

use Composer\Script\PackageEvent;

class ScriptHandler
{
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
}