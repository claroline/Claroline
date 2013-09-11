<?php

namespace Claroline\BundleRecorder;

use Composer\Composer;
use Composer\Package\PackageInterface;

class Recorder
{
    private $bundleFile;
    private $logger;

    public function __construct($bundleFile)
    {
        $this->bundleFile = $bundleFile;

        if (!file_exists($this->bundleFile)) {
            touch($this->bundleFile);
        }
    }

    public function setLogger(\Closure $logger)
    {
        $this->logger = $logger;
    }

    public function addBundles(array $bundlesFqcns)
    {
        $this->updateBundleFile($bundlesFqcns, 'add');
    }

    public function removeBundles(array $bundlesFqcns)
    {
        $this->updateBundleFile($bundlesFqcns, 'remove');
    }

    private function updateBundleFile(array $bundlesFqcns, $action)
    {
        $recordedBundles = parse_ini_file($this->bundleFile);
        $hasChanges = false;

        foreach ($bundlesFqcns as $bundleFqcn) {
            $fqcnParts = explode('\\', $bundleFqcn);
            $bundleName = array_pop($fqcnParts);

            if ($action === 'add' && !isset($recordedBundles[$bundleFqcn])) {
                $this->log("Adding {$bundleName} to the bundle file..." );
                $recordedBundles[$bundleFqcn] = true;
                $hasChanges = true;
            } elseif ($action === 'remove' && isset($recordedBundles[$bundleFqcn])) {
                $this->log("Removing {$bundleName} from the bundle file..." );
                unset($recordedBundles[$bundleFqcn]);
                $hasChanges = true;
            }
        }

        if ($hasChanges) {
            $content = '';

            foreach ($recordedBundles as $bundle => $isEnabled) {
                $isEnabled = $isEnabled ? 'true' : 'false';
                $content .= "{$bundle} = {$isEnabled}" . PHP_EOL;
            }

            file_put_contents($this->bundleFile, $content);
        }
    }

    private function log($message)
    {
        if ($log = $this->logger) {
            $log($message);
        }
    }
}