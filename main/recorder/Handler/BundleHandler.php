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

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

class BundleHandler implements LoggerAwareInterface
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
        // The bundle file should not be overwritten, it's versioned.
        if ($this->fs->exists($this->targetFile)) {
            return;
        }

        $this->logger->info('Writing bundle file...');

        $this->fs->copy($this->sourceFile, $this->targetFile);
    }
}
