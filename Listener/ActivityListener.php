<?php

namespace HeVinci\CompetencyBundle\Listener;

use Claroline\CoreBundle\Event\ActivityEvaluationEvent;
use HeVinci\CompetencyBundle\Manager\ProgressManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Listens to activity evaluations produced by the core bundle.
 *
 * @DI\Service("hevinci.competency.activity_listener")
 */
class ActivityListener
{
    private $manager;

    /**
     * @DI\InjectParams({
     *     "manager" = @DI\Inject("hevinci.competency.progress_manager")
     * })
     *
     * @param ProgressManager $manager
     */
    public function __construct(ProgressManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @DI\Observe("activity_evaluation")
     *
     * @param ActivityEvaluationEvent $event
     */
    public function onActivityEvaluation(ActivityEvaluationEvent $event)
    {
        $this->manager->handleEvaluation($event->getEvaluation());
    }
}
