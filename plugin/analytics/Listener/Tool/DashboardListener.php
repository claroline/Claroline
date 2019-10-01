<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AnalyticsBundle\Listener\Tool;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Log\Connection\LogConnectWorkspace;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\CoreBundle\Manager\EventManager;
use Claroline\CoreBundle\Manager\ProgressionManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @DI\Service
 */
class DashboardListener
{
    /** @var EventManager */
    private $eventManager;

    /** @var ProgressionManager */
    private $progressionManager;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    private $logConnectWSRepo;

    /**
     * DashboardListener constructor.
     *
     * @param EventManager          $eventManager
     * @param ObjectManager         $om
     * @param ProgressionManager    $progressionManager
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        EventManager $eventManager,
        ObjectManager $om,
        ProgressionManager $progressionManager,
        TokenStorageInterface $tokenStorage
    ) {
        $this->eventManager = $eventManager;
        $this->progressionManager = $progressionManager;
        $this->tokenStorage = $tokenStorage;

        $this->logConnectWSRepo = $om->getRepository(LogConnectWorkspace::class);
    }

    /**
     * Displays dashboard on Workspace.
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayWorkspace(DisplayToolEvent $event)
    {
        $workspace = $event->getWorkspace();
        $levelMax = 1;
        $authenticatedUser = $this->tokenStorage->getToken()->getUser();
        $user = 'anon.' !== $authenticatedUser ? $authenticatedUser : null;
        $items = $this->progressionManager->fetchItems($workspace, $user, $levelMax);
        $workspaceConnections = $this->logConnectWSRepo->findBy(['workspace' => $workspace]);
        $event->setData([
            'actions' => $this->eventManager->getEventsForApiFilter(LogGenericEvent::DISPLAYED_WORKSPACE),
            'items' => $items,
            'levelMax' => null,    // how deep to process children recursively
            'nbConnections' => count($workspaceConnections),
        ]);
        $event->stopPropagation();
    }
}
