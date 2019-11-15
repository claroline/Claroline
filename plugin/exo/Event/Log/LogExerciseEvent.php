<?php

namespace UJM\ExoBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use UJM\ExoBundle\Entity\Exercise;

class LogExerciseEvent extends AbstractLogResourceEvent
{
    /**
     * @param Exercise $exercise
     * @param array    $details
     */
    public function __construct($action, Exercise $exercise, $details)
    {
        $this->action = $action;

        parent::__construct($exercise->getResourceNode(), $details);
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return [LogGenericEvent::DISPLAYED_WORKSPACE];
    }
}
