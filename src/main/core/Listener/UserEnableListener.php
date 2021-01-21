<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener;

use Claroline\CoreBundle\Event\Log\LogUserEnableEvent;
use Psr\Log\LoggerInterface;

class UserEnableListener
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onUserEnable(LogUserEnableEvent $logUserEnableEvent)
    {
        //todo: do something
    }
}
