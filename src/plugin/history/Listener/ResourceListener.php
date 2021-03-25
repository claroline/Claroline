<?php

namespace Claroline\HistoryBundle\Listener;

use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\HistoryBundle\Manager\HistoryManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ResourceListener
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var HistoryManager */
    private $manager;

    /**
     * ResourceListener constructor.
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        HistoryManager $manager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->manager = $manager;
    }

    public function onLoad(LoadResourceEvent $event)
    {
        $user = $this->tokenStorage->getToken()->getUser();

        if (!$event->isEmbedded() && 'anon.' !== $user) {
            $this->manager->addResource($event->getResourceNode(), $user);
        }
    }
}
