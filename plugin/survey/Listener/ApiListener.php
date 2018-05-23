<?php

namespace Claroline\SurveyBundle\Listener;

use Claroline\CoreBundle\Event\User\MergeUsersEvent;
use Claroline\SurveyBundle\Manager\SurveyManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class ApiListener.
 *
 * @DI\Service
 */
class ApiListener
{
    /** @var SurveyManager */
    private $manager;

    /**
     * @DI\InjectParams({
     *     "manager" = @DI\Inject("claroline.manager.survey_manager")
     * })
     *
     * @param SupportManager $manager
     */
    public function __construct(SurveyManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @DI\Observe("merge_users")
     *
     * @param MergeUsersEvent $event
     */
    public function onMerge(MergeUsersEvent $event)
    {
        // Replace user of SurveyAnswer nodes
        $surveyAnswerCount = $this->manager->replaceSurveyAnswerUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[ClarolineSurveyBundle] updated SurveyAnswer count: $surveyAnswerCount");
    }
}
