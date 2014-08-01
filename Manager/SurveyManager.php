<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\SurveyBundle\Manager;

use Claroline\SurveyBundle\QuestionTypeHandler\AbstractQuestionTypeHandler;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.survey_manager")
 */
class SurveyManager
{
    private $handlers;

    public function addQuestionTypeHandler(AbstractQuestionTypeHandler $handler)
    {
        $this->handlers[$handler->getSupportedType()] = $handler;
    }

    public function getQuestionTypeHandlerFor($type)
    {
        if (isset($this->handlers[$type])) {
            return $this->handlers[$type];
        }

        throw new \Exception("No handler registered for type '{$type}'");
    }
}
