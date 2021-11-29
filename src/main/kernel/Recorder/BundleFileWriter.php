<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\KernelBundle\Recorder;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Writes the list of Bundles registered in the Claroline Kernel to an INI file.
 */
class BundleFileWriter implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private $fs;
    private $sourceFile;
    private $targetFile;

    public function __construct(Filesystem $fs, string $sourceFile, string $targetFile, LoggerInterface $logger = null)
    {
        if (!file_exists($sourceFile)) {
            throw new \LogicException(sprintf('Source file "%s" does not exist.', $sourceFile));
        }

        $this->fs = $fs;
        $this->sourceFile = $sourceFile;
        $this->targetFile = $targetFile;

        if ($logger) {
            $this->setLogger($logger);
        }
    }

    public function writeBundleFile()
    {
        $this->logger->info('Writing bundle file...');

        if ($this->fs->exists($this->targetFile)) {
            // checks if there are new plugins in the main distribution
            $currentBundles = parse_ini_file($this->targetFile);
            $sourceBundles = parse_ini_file($this->sourceFile);

            foreach ($sourceBundles as $bundle => $bundleEnabled) {
                if (!isset($currentBundles[$bundle])) {
                    // new bundle found
                    $currentBundles[$bundle] = $bundleEnabled;
                }
            }

            // dump new bundles list
            $bundles = '';
            foreach ($currentBundles as $bundle => $bundleEnabled) {
                $bundles .= $bundle.'='.($bundleEnabled ? 'true' : 'false').PHP_EOL;
            }

            $this->fs->dumpFile($this->targetFile, $bundles);

            return;
        }

        $this->fs->copy($this->sourceFile, $this->targetFile);
    }
}
