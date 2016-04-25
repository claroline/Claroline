<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\SurveyBundle\Event\Log;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\SurveyBundle\Entity\Survey;

class LogSurveyAnswer extends LogGenericEvent
{
    const ACTION = 'resource-claroline_survey-answer';

    public function __construct(
        Survey $survey,
        User $user = null
    ) {
        parent::__construct(
            self::ACTION,
            array(),
            $user,
            null,
            $survey->getResourceNode(),
            null,
            $survey->getResourceNode()->getWorkspace()
        );
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return array(self::DISPLAYED_WORKSPACE);
    }
}
