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

use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\SurveyBundle\Entity\Answer\SurveyAnswer;

class LogSurveyAnswerDelete extends LogGenericEvent
{
    const ACTION = 'resource-claroline_survey-answer-delete';

    public function __construct(SurveyAnswer $surveyAnswer)
    {
        $survey = $surveyAnswer->getSurvey();
        $resourceNode = $survey->getResourceNode();
        $user = $surveyAnswer->getUser();
        $details = [
            'answer_date' => $surveyAnswer->getAnswerDate(),
            'nb_answers' => $surveyAnswer->getNbAnswers(),
        ];

        parent::__construct(
            self::ACTION,
            $details,
            $user,
            null,
            $resourceNode,
            null,
            $resourceNode->getWorkspace()
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
