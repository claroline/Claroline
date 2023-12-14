<?php

namespace Claroline\CoreBundle\Listener\DataSource;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\Component\Context\AdministrationContext;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Event\DataSource\GetDataEvent;
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

        if (!in_array('ROLE_ADMIN', $this->tokenStorage->getToken()->getRoleNames())) {
            $options['hiddenFilters']['roles'] = $this->tokenStorage->getToken()->getRoleNames();
        }

        $options['hiddenFilters']['context'] = AdministrationContext::getName();

        $event->setData($this->finder->search(OrderedTool::class, $options));
        $event->stopPropagation();
    }
}
