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
        $this->bundleHandler->writeBundleFile();
    }
}
