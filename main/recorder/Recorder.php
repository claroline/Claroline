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

use Claroline\BundleRecorder\Handler\BundleHandler;

class Recorder
{
    private $bundleHandler;
    private $vendorDir;

    public function __construct(BundleHandler $bundleHandler, string $vendorDir)
    {
        $this->bundleHandler = $bundleHandler;
        $this->vendorDir = $vendorDir;
    }

    public function buildBundleFile(): void
    {
        $this->bundleHandler->writeBundleFile($this->getBundles());
    }

    private function getBundles(): array
    {
        $path = "$this->vendorDir/claroline/distribution";

        //look for a bundle list in the composer.json for - packages
        if (!file_exists($path.'/composer.json')) {
            throw new \LogicException(sprintf('File "%s/composer.json" does not exist.', $path));
        }

        $json = json_decode(file_get_contents($path.'/composer.json'), true);

        if (!array_key_exists('extra', $json) && array_key_exists('bundles', $json['extra'])) {
            throw new \LogicException(sprintf('Missing key "extra.bundles" in "%s/composer.json".', $path));
        }

        $bundles = [];

        foreach ($json['extra']['bundles'] as $bundle) {
            $bundles[] = $bundle;
        }

        return array_unique($bundles);
    }
}
