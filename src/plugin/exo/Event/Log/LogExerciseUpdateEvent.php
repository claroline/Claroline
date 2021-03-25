<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UJM\ExoBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use UJM\ExoBundle\Entity\Exercise;

class LogExerciseUpdateEvent extends AbstractLogResourceEvent
{
    const ACTION = 'resource-ujm_exercise-exercise_updated';

    public function __construct(Exercise $exercise, array $details)
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
