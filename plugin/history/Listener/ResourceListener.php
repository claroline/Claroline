<?php

namespace Claroline\HistoryBundle\Listener;

use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\HistoryBundle\Manager\HistoryManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @DI\Service()
 */
class ResourceListener
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var HistoryManager */
    private $manager;

    /**
     * ResourceListener constructor.
     *
     * @DI\InjectParams({
     *     "tokenStorage" = @DI\Inject("security.token_storage"),
     *     "manager"      = @DI\Inject("claroline.manager.history")
     * })
     *
     * @param TokenStorageInterface $tokenStorage
     * @param HistoryManager        $manager
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        HistoryManager $manager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->manager = $manager;
    }

    /**
     * @DI\Observe("resource.load")
     *
     * @param LoadResourceEvent $event
     */
    public function onLoad(LoadResourceEvent $event)
    {
        $user = $this->tokenStorage->getToken()->getUser();

        if (!$event->isEmbedded() && 'anon.' !== $user) {
            $this->manager->addResource($event->getResourceNode(), $user);
        }
    }
}
