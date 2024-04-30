<?php

namespace Claroline\HistoryBundle\Subscriber;

use Claroline\AppBundle\Manager\SecurityManager;
use Claroline\CoreBundle\Event\CatalogEvents\ResourceEvents;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\HistoryBundle\Manager\HistoryManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ResourceSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly SecurityManager $securityManager,
        private readonly HistoryManager $manager
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ResourceEvents::OPEN => 'onLoad',
        ];
    }

    public function onLoad(LoadResourceEvent $event): void
    {
        if (!$event->isEmbedded() && !$this->securityManager->isAnonymous() && !$this->securityManager->isImpersonated()) {
            $this->manager->addResource($event->getResourceNode(), $this->securityManager->getCurrentUser());
        }
    }
}
