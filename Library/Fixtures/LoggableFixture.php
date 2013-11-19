<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;

abstract class LoggableFixture extends AbstractFixture
{
    protected $logger;

    public function setLogger(\Closure $logger)
    {
        $this->logger = $logger;
    }

    protected function log($message)
    {
        if (is_callable($this->logger)) {
            call_user_func_array($this->logger, array($message));
        }
    }
}
