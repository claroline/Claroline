<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Event\DisplayToolEvent;
use Claroline\CoreBundle\Manager\ToolManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * User desktop.
 *
 * @EXT\Route("/desktop", options={"expose"=true})
 *
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("hasRole('ROLE_USER')")
 */
class DesktopController
{
    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var UrlGeneratorInterface */
    private $router;

    /** @var SessionInterface */
    private $session;

    /** @var ToolManager */
    private $toolManager;

    /**
     * DesktopController constructor.
     *
     * @DI\InjectParams({
     *     "eventDispatcher" = @DI\Inject("event_dispatcher"),
     *     "router"          = @DI\Inject("router"),
     *     "session"         = @DI\Inject("session"),
     *     "toolManager"     = @DI\Inject("claroline.manager.tool_manager")
     * })
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param UrlGeneratorInterface    $router
     * @param SessionInterface         $session
     * @param ToolManager              $toolManager
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        UrlGeneratorInterface $router,
        SessionInterface $session,
        ToolManager $toolManager)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->router = $router;
        $this->session = $session;
        $this->toolManager = $toolManager;
    }

    /**
     * Opens the desktop.
     *
     * @EXT\Route("/open", name="claro_desktop_open")
     *
     * @return Response
     */
    public function openAction()
    {
        return new RedirectResponse(
            $this->router->generate('claro_desktop_open_tool', [
                'toolName' => 'home',
            ])
        );
    }

    /**
     * Opens a tool.
     *
     * @EXT\Route("/tool/open/{toolName}", name="claro_desktop_open_tool")
     *
     * @param string $toolName
     *
     * @return Response
     */
    public function openToolAction($toolName)
    {
        /** @var DisplayToolEvent $event */
        $event = $this->eventDispatcher->dispatch('open_tool_desktop_'.$toolName, new DisplayToolEvent());

        if ($toolName === 'resource_manager') {
            // FIXME : but why ?
            $this->session->set('isDesktop', true);
        }

        return new Response($event->getContent());
    }
}
