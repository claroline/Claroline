<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\HomeBundle\Listener\Tool;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Tool\OpenToolEvent;
use Claroline\HomeBundle\Manager\HomeManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Home tool.
 */
class HomeListener
{
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var HomeManager */
    private $manager;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        HomeManager $manager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->manager = $manager;
    }

    /**
     * Displays home on Desktop.
     */
    public function onDisplayDesktop(OpenToolEvent $event)
    {
        $user = null;
        if ($this->tokenStorage->getToken()->getUser() instanceof User) {
            $user = $this->tokenStorage->getToken()->getUser();
        }

        $event->setData([
            'tabs' => $this->manager->getDesktopTabs($user),
        ]);

        $event->stopPropagation();
    }

    /**
     * Displays home on Workspace.
     */
    public function onDisplayWorkspace(OpenToolEvent $event)
    {
        $event->setData([
            'tabs' => $this->manager->getWorkspaceTabs($event->getWorkspace()),
        ]);

        $event->stopPropagation();
    }

    /**
     * Displays home administration tool.
     */
    public function onDisplayAdministration(OpenToolEvent $event)
    {
        $event->setData([
            'administration' => true,
            'tabs' => $this->manager->getAdministrationTabs(),
        ]);

        $event->stopPropagation();
    }
}
