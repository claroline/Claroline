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

use Psr\Log\LoggerInterface;

class BundleHandler extends BaseHandler
{
    private $registeredBundles;

    public function __construct($bundleFile, LoggerInterface $logger = null)
    {
        parent::__construct($bundleFile, $logger);
        $this->registeredBundles = parse_ini_file($this->targetFile);
    }

    public function writeBundleFile(array $bundleFqcns)
    {
        $bundles = array();

        foreach ($bundleFqcns as $bundleFqcn) {
            $isEnabled = true;

            if (isset($this->registeredBundles[$bundleFqcn])) {
                $isEnabled = $this->registeredBundles[$bundleFqcn];
            }

            $bundles[$bundleFqcn] = $isEnabled;
        }

        $this->registeredBundles = $bundles;
        $this->doWriteBundleFile();
    }

    private function doWriteBundleFile()
    {
        $this->log('Writing bundle file...', '');

        $content = '';

        foreach ($this->registeredBundles as $bundle => $isEnabled) {
            $isEnabled = $isEnabled ? 'true' : 'false';
            $content .= "{$bundle} = {$isEnabled}" . PHP_EOL;
        }

        file_put_contents($this->targetFile, $content);
    }
}
