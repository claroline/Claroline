<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener\Tool;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\CoreBundle\Manager\EventManager;
use Claroline\CoreBundle\Manager\ProgressionManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
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

    /** @var SerializerProvider */
    private $serializer;

    /** @var TwigEngine */
    private $templating;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * DashboardListener constructor.
     *
     * @DI\InjectParams({
     *     "eventManager"       = @DI\Inject("claroline.event.manager"),
     *     "progressionManager" = @DI\Inject("claroline.manager.progression_manager"),
     *     "serializer"         = @DI\Inject("claroline.api.serializer"),
     *     "templating"         = @DI\Inject("templating"),
     *     "tokenStorage"       = @DI\Inject("security.token_storage")
     * })
     *
     * @param EventManager          $eventManager
     * @param ProgressionManager    $progressionManager
     * @param SerializerProvider    $serializer
     * @param TwigEngine            $templating
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        EventManager $eventManager,
        ProgressionManager $progressionManager,
        SerializerProvider $serializer,
        TwigEngine $templating,
        TokenStorageInterface $tokenStorage
    ) {
        $this->eventManager = $eventManager;
        $this->progressionManager = $progressionManager;
        $this->serializer = $serializer;
        $this->templating = $templating;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Displays dashboard on Workspace.
     *
     * @DI\Observe("open_tool_workspace_dashboard")
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

        $content = $this->templating->render(
            'ClarolineCoreBundle:tool\workspace:dashboard.html.twig', [
                'workspace' => $workspace,
                // For logs tool
                'actions' => $this->eventManager->getEventsForApiFilter(LogGenericEvent::DISPLAYED_WORKSPACE),
                // For progression tool
                'context' => [
                    'type' => 'workspace',
                    'data' => $this->serializer->serialize($workspace),
                ],
                'items' => $items,
                'levelMax' => null,    // how deep to process children recursively
            ]
        );

        $event->setContent($content);
        $event->stopPropagation();
    }
}
