<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\BundleRecorder\Detector;

use Claroline\AppBundle\Log\LoggableTrait;
use Psr\Log\LoggerAwareInterface;

class Detector implements LoggerAwareInterface
{
    use LoggableTrait;

    private $baseDir;

    public function __construct($baseDir = null)
    {
        $this->baseDir = $baseDir;
    }

    public function detectBundles($path)
    {

    }
}
