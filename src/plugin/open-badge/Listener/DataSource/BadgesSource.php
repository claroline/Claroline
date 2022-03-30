<?php

namespace Claroline\OpenBadgeBundle\Listener\DataSource;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\Entity\DataSource;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\DataSource\GetDataEvent;
use Claroline\OpenBadgeBundle\Entity\BadgeClass;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class BadgesSource
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
        $options = $event->getOptions() ? $event->getOptions() : [];
        $options['hiddenFilters']['meta.enabled'] = true;

        if (DataSource::CONTEXT_WORKSPACE === $event->getContext()) {
            $options['hiddenFilters']['workspace'] = $event->getWorkspace()->getUuid();
        } elseif (!$this->authorization->isGranted('ROLE_ADMIN')) {
            // only display badges for the current user organization
            $options['hiddenFilters']['organizations'] = [];

            $user = $this->tokenStorage->getToken()->getUser();
            if ($user instanceof User) {
                $options['hiddenFilters']['organizations'] = array_map(function (Organization $organization) {
                    return $organization->getUuid();
                }, $user->getOrganizations());
            }
        }

        $event->setData(
            $this->finder->search(BadgeClass::class, $options)
        );

        $event->stopPropagation();
    }
}
