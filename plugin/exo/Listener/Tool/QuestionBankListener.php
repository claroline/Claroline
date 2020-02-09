<?php

namespace UJM\ExoBundle\Listener\Tool;

use Claroline\CoreBundle\Event\Tool\OpenToolEvent;

class QuestionBankListener
{
    /**
     * @param OpenToolEvent $event
     */
    public function onDisplayDesktop(OpenToolEvent $event)
    {
        $event->stopPropagation();
    }
}
