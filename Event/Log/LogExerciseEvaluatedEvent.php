<?php

namespace UJM\ExoBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use UJM\ExoBundle\Entity\Exercise;

class LogExerciseEvaluatedEvent extends AbstractLogResourceEvent {

    const ACTION = 'resource-ujm_exercise-exercise_evaluated';

    /**
     * @param Exercise $exercise
     * @param string   $grade
     */
    public function __construct(Exercise $exercise, $grade)
    {
        $details = array(
            'exercise'  => array(
                'id'    => $exercise->getId(),
                'name'  => $exercise->getName(),
                'title' => $exercise->getTitle()
            ),
            'result'      => $grade['scorePaper'],
            'resultMax' => $grade['maxExoScore']
        );

        parent::__construct($exercise->getResourceNode(), $details);
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return array(LogGenericEvent::DISPLAYED_WORKSPACE);
    }
}
