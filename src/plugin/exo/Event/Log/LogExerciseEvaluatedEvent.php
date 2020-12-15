<?php

namespace UJM\ExoBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use UJM\ExoBundle\Entity\Exercise;

class LogExerciseEvaluatedEvent extends AbstractLogResourceEvent
{
    const ACTION = 'resource-ujm_exercise-exercise_evaluated';

    /**
     * @param Exercise $exercise
     * @param array    $details
     */
    public function __construct(Exercise $exercise, $details)
    {
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
