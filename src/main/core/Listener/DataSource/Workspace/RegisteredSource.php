<?php

namespace Claroline\CoreBundle\Listener\DataSource\Workspace;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\Options;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\DataSource\GetDataEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * List the workspaces in which the current user is registered.
 */
class RegisteredSource
{
    /** @var FinderProvider */
    private $finder;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(
        FinderProvider $finder,
        TokenStorageInterface $tokenStorage
    ) {
        $this->finder = $finder;
        $this->tokenStorage = $tokenStorage;
    }

    public function getData(GetDataEvent $event)
    {
        $options = $event->getOptions();

        $options['hiddenFilters']['model'] = false;
        $options['hiddenFilters']['user'] = null;
        if ($this->tokenStorage->getToken()->getUser() instanceof User) {
            $options['hiddenFilters']['user'] = $this->tokenStorage->getToken()->getUser()->getId();
        }

        $event->setData(
            $this->finder->search(Workspace::class, $options, [Options::SERIALIZE_LIST])
        );

        $event->stopPropagation();
    }
}
