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

use Claroline\InstallationBundle\Log\LoggableTrait;
use Psr\Log\LoggerInterface;

class BaseHandler
{
    use LoggableTrait;

    /**
     * @var string
     */
    protected $targetFile;

    /**
     * @param                 $targetFile
     * @param LoggerInterface $logger
     */
    public function __construct($targetFile, LoggerInterface $logger = null)
    {
        if (!file_exists($targetFile)) {
            touch($targetFile);
        }

        $this->targetFile = $targetFile;
        $this->setLogger($logger);
    }

    /**
     * @param string $message
     * @param string $indent
     */
    public function log($message, $indent = '    ')
    {
        $this->logger->log(LogLevel::INFO, $indent . $message);
    }

    /**
     * @return bool
     */
    public function isFileEmpty()
    {
        return '' === trim(file_get_contents($this->targetFile));
    }
}
