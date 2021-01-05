<?php

namespace HeVinci\CompetencyBundle\Listener;

use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Event\Resource\EvaluateResourceEvent;
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

    public function onResourceEvaluation(EvaluateResourceEvent $event)
    {
        /** @var ResourceUserEvaluation $evaluation */
        $evaluation = $event->getEvaluation();

        $this->manager->handleEvaluation($evaluation);
    }
}
