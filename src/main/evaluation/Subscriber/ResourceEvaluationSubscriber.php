<?php

namespace Claroline\EvaluationBundle\Subscriber;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\CatalogEvents\ResourceEvents;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\EvaluationBundle\Entity\AbstractEvaluation;
use Claroline\EvaluationBundle\Manager\ResourceEvaluationManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ResourceEvaluationSubscriber implements EventSubscriberInterface
{
    /** @var ResourceEvaluationManager */
    private $manager;

    public function __construct(ResourceEvaluationManager $manager)
    {
        $this->manager = $manager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ResourceEvents::RESOURCE_OPEN => ['open', 10],
        ];
    }

    public function open(LoadResourceEvent $event)
    {
        // Update current user evaluation
        if ($event->getUser() instanceof User) {
            $this->manager->updateUserEvaluation(
                $event->getResourceNode(),
                $event->getUser(),
                ['status' => AbstractEvaluation::STATUS_OPENED]
            );
        }
    }
}
