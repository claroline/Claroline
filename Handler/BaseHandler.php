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

class BaseHandler
{
    protected $targetFile;
    private $logger;

    public function __construct($targetFile, \Closure $logger = null)
    {
        if (!file_exists($targetFile)) {
            touch($targetFile);
        }

        $this->targetFile = $targetFile;
        $this->logger = $logger;
    }

    public function log($message)
    {
        if ($log = $this->logger) {
            $log($message);
        }
    }
}
