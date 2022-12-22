<?php

namespace Claroline\CoreBundle\Listener\DataSource\Workspace;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\DataSource\GetDataEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * List the workspaces in which the current user is registered.
 */
class RegisteredSource
{
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var FinderProvider */
    private $finder;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        FinderProvider $finder
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->finder = $finder;
    }

    public function getData(GetDataEvent $event)
    {
        $options = $event->getOptions();

        $options['hiddenFilters']['model'] = false;
        $options['hiddenFilters']['roles'] = $this->tokenStorage->getToken()->getRoleNames();

        $event->setData(
            $this->finder->search(Workspace::class, $options, [SerializerInterface::SERIALIZE_LIST])
        );

        $event->stopPropagation();
    }
}
