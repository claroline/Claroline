<?php

namespace HeVinci\CompetencyBundle\Listener;

use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Event\UserEvaluationEvent;
use HeVinci\CompetencyBundle\Manager\ProgressManager;

/**
 * Listens to resource evaluations produced by the core bundle.
 */
class ResourceListener
{
    /** @var ProgressManager */
    private $manager;

    /**
     * @param ProgressManager $manager
     */
    public function __construct(ProgressManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param UserEvaluationEvent $event
     */
    public function onResourceEvaluation(UserEvaluationEvent $event)
    {
        /** @var ResourceUserEvaluation $evaluation */
        $evaluation = $event->getEvaluation();

        $this->manager->handleEvaluation($evaluation);
    }
}
