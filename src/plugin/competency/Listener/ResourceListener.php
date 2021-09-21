<?php

namespace HeVinci\CompetencyBundle\Listener;

use Claroline\EvaluationBundle\Event\ResourceEvaluationEvent;
use HeVinci\CompetencyBundle\Manager\ProgressManager;

/**
 * Listens to resource evaluations produced by the core bundle.
 */
class ResourceListener
{
    /** @var ProgressManager */
    private $manager;

    public function __construct(ProgressManager $manager)
    {
        $this->manager = $manager;
    }

    public function onResourceEvaluation(ResourceEvaluationEvent $event)
    {
        $evaluation = $event->getEvaluation();

        $this->manager->handleEvaluation($evaluation);
    }
}
