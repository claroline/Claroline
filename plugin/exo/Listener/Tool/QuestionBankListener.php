<?php

namespace UJM\ExoBundle\Listener\Tool;

use Claroline\CoreBundle\Event\DisplayToolEvent;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("ujm_exo.listener.question_bank")
 */
class QuestionBankListener
{
    /**
     * @DI\Observe("open_tool_desktop_ujm_questions")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayDesktop(DisplayToolEvent $event)
    {
        $event->stopPropagation();
    }
}
