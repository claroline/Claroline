<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\BundleRecorder\Handler;

class BundleHandler extends BaseHandler
{
    private $registeredBundles;

    public function __construct($bundleFile, \Closure $logger = null)
    {
        parent::__construct($bundleFile, $logger);
        $this->registeredBundles = parse_ini_file($this->targetFile);
    }

    public function addBundles(array $bundlesFqcns)
    {
        $this->updateBundleFile($bundlesFqcns, 'add');
    }

    public function removeBundles(array $bundlesFqcns)
    {
        $this->updateBundleFile($bundlesFqcns, 'remove');
    }

    public function reorderBundles(array $bundleFqcns)
    {
        $orderedList = array();

        foreach ($bundleFqcns as $bundleFqcn) {
            $orderedList[$bundleFqcn] = $this->registeredBundles[$bundleFqcn];
        }

        if ($this->registeredBundles !== $orderedList) {
            $this->log('Reordering bundles...', '');
            $this->registeredBundles = $orderedList;
            $this->writeBundleFile();
        }
    }

    private function updateBundleFile(array $bundlesFqcns, $action)
    {
        $hasChanges = false;

        foreach ($bundlesFqcns as $bundleFqcn) {
            $fqcnParts = explode('\\', $bundleFqcn);
            $bundleName = array_pop($fqcnParts);

            if ($action === 'add' && !isset($this->registeredBundles[$bundleFqcn])) {
                $this->log("Adding {$bundleName} to the bundle file..." );
                $this->registeredBundles[$bundleFqcn] = true;
                $hasChanges = true;
            } elseif ($action === 'remove' && isset($this->registeredBundles[$bundleFqcn])) {
                $this->log("Removing {$bundleName} from the bundle file..." );
                unset($this->registeredBundles[$bundleFqcn]);
                $hasChanges = true;
            }
        }

        if ($hasChanges) {
            $this->writeBundleFile();
        }
    }

    private function writeBundleFile()
    {
        $content = '';

        foreach ($this->registeredBundles as $bundle => $isEnabled) {
            $isEnabled = $isEnabled ? 'true' : 'false';
            $content .= "{$bundle} = {$isEnabled}" . PHP_EOL;
        }

        file_put_contents($this->targetFile, $content);
    }
}
