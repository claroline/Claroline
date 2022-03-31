<?php

namespace Claroline\CoreBundle\Listener\DataSource;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\Entity\DataSource;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\DataSource\GetDataEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UserSource
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var FinderProvider */
    private $finder;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        TokenStorageInterface $tokenStorage,
        FinderProvider $finder
    ) {
        $this->authorization = $authorization;
        $this->tokenStorage = $tokenStorage;
        $this->finder = $finder;
    }

    public function getData(GetDataEvent $event)
    {
        $options = $event->getOptions();
        if (DataSource::CONTEXT_WORKSPACE === $event->getContext()) {
            if (!$this->authorization->isGranted('OPEN', $event->getWorkspace())) {
                throw new AccessDeniedException();
            }

            $options['hiddenFilters']['workspace'] = $event->getWorkspace()->getUuid();
        } elseif (!$this->authorization->isGranted('ROLE_ADMIN')) {
            // only shows users of the same organizations
            $options['hiddenFilters']['recursiveOrXOrganization'] = [];

            $user = $this->tokenStorage->getToken()->getUser();
            if ($user instanceof User) {
                $options['hiddenFilters']['recursiveOrXOrganization'] = array_map(function (Organization $organization) {
                    return $organization->getUuid();
                }, $user->getOrganizations());
            }
        }

        $event->setData(
            $this->finder->search(User::class, $options)
        );

        $event->stopPropagation();
    }
}
