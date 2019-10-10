<?php

namespace HeVinci\CompetencyBundle\Listener;

use Claroline\CoreBundle\Event\Resource\ResourceEvaluationEvent;
use HeVinci\CompetencyBundle\Manager\ProgressManager;

/**
 * Listens to resource evaluations produced by the core bundle.
 */
class ResourceListener
{
    private $manager;

    /**
     * @param ProgressManager $manager
     */
    public function __construct(ProgressManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param ResourceEvaluationEvent $event
     */
    public function onResourceEvaluation(ResourceEvaluationEvent $event)
    {
        $this->manager->handleEvaluation($event->getEvaluation());
    }
}
