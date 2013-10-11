<?php

namespace Claroline\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Claroline\CoreBundle\Entity\Log\Log;

class LogCreateEvent extends Event
{
    const NAME = 'claroline.log.create';

    /** @var \Claroline\CoreBundle\Entity\Log\Log */
    protected $log;

    public function __construct(Log $log)
    {
        $this->log = $log;
    }

    /**
     * @return Log
     */
    public function getLog()
    {
        return $this->log;
    }
}
