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

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\SurveyBundle\Entity\Question;
use Claroline\SurveyBundle\Entity\Survey;
//use Claroline\SurveyBundle\QuestionTypeHandler\AbstractQuestionTypeHandler;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.survey_manager")
 */
class SurveyManager
{
//    private $handlers;
    private $om;
    private $questionRepo;
    
    /**
     * @DI\InjectParams({
     *     "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
        $this->questionRepo = $om->getRepository('ClarolineSurveyBundle:Question');
    }

    public function persistSurvey(Survey $survey)
    {
        $this->om->persist($survey);
        $this->om->flush();
    }

    public function persistQuestion(Question $question)
    {
        $this->om->persist($question);
        $this->om->flush();
    }


//    public function addQuestionTypeHandler(AbstractQuestionTypeHandler $handler)
//    {
//        $this->handlers[$handler->getSupportedType()] = $handler;
//    }

//    public function getQuestionTypeHandlerFor($type)
//    {
//        if (isset($this->handlers[$type])) {
//            return $this->handlers[$type];
//        }
//
//        throw new \Exception("No handler registered for type '{$type}'");
//    }


    /****************************************
     * Access to QuestionRepository methods *
     ****************************************/

    public function getQuestionsByWorkspace(
        Workspace $workspace,
        $orderedBy = 'title',
        $order = 'ASC',
        $executeQuery = true
    )
    {
        return $this->questionRepo->findQuestionsByWorkspace(
            $workspace,
            $orderedBy,
            $order,
            $executeQuery
        );
    }
}
