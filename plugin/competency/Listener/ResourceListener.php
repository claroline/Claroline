<?php

namespace HeVinci\CompetencyBundle\Listener;

use Claroline\CoreBundle\Event\ResourceEvaluationEvent;
use HeVinci\CompetencyBundle\Manager\ProgressManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Listens to resource evaluations produced by the core bundle.
 *
 * @DI\Service("hevinci.competency.resource_listener")
 */
class ResourceListener
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
     * @DI\Observe("resource_evaluation")
     *
     * @param ResourceEvaluationEvent $event
     */
    public function onResourceEvaluation(ResourceEvaluationEvent $event)
    {
        $this->manager->handleEvaluation($event->getEvaluation());
    }
}
