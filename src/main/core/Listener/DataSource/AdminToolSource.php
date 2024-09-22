<?php

namespace Claroline\CoreBundle\Listener\DataSource;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\Component\Context\AdministrationContext;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Event\DataSource\GetDataEvent;
use Claroline\CoreBundle\Security\PlatformRoles;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AdminToolSource
{
    public function __construct(
        private readonly FinderProvider $finder,
        private readonly TokenStorageInterface $tokenStorage
    ) {
    }

    public function getData(GetDataEvent $event): void
    {
        $options = $event->getOptions();

        if (!in_array(PlatformRoles::ADMIN, $this->tokenStorage->getToken()?->getRoleNames() ?? [PlatformRoles::ANONYMOUS])) {
            $options['hiddenFilters']['roles'] = $this->tokenStorage->getToken()?->getRoleNames() ?? [PlatformRoles::ANONYMOUS];
        }

        $options['hiddenFilters']['context'] = AdministrationContext::getName();

        $event->setData($this->finder->search(OrderedTool::class, $options));
        $event->stopPropagation();
    }
}
