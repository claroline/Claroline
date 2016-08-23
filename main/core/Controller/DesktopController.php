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

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use Claroline\CoreBundle\Manager\ToolManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("hasRole('ROLE_USER')")
 */
class DesktopController extends Controller
{
    private $eventDispatcher;
    private $router;
    private $session;
    private $toolManager;

    /**
     * @DI\InjectParams({
     *     "eventDispatcher" = @DI\Inject("event_dispatcher"),
     *     "router"          = @DI\Inject("router"),
     *     "session"         = @DI\Inject("session"),
     *     "toolManager"     = @DI\Inject("claroline.manager.tool_manager")
     * })
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        UrlGeneratorInterface $router,
        SessionInterface $session,
        ToolManager $toolManager
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->router = $router;
        $this->session = $session;
        $this->toolManager = $toolManager;
    }

    /**
     * @EXT\Template()
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Renders the left tool bar. Not routed.
     *
     * @return Response
     */
    public function renderToolListAction(User $user)
    {
        return ['tools' => $this->toolManager->getDisplayedDesktopOrderedTools($user)];
    }

    /**
     * @EXT\Route(
     *     "tool/open/{toolName}",
     *     name="claro_desktop_open_tool",
     *     options={"expose"=true}
     * )
     *
     * Opens a tool.
     *
     * @param string $toolName
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function openToolAction($toolName)
    {
        $event = $this->eventDispatcher->dispatch('open_tool_desktop_'.$toolName, new DisplayToolEvent());

        if ($toolName === 'resource_manager') {
            $this->session->set('isDesktop', true);
        }

        return new Response($event->getContent());
    }

    /**
     * @EXT\Route(
     *     "/open",
     *     name="claro_desktop_open",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Opens the desktop.
     *
     * @param User $user
     *
     * @return Response
     */
    public function openAction(User $user)
    {
        $route = $this->router->generate(
            'claro_desktop_open_tool',
            ['toolName' => 'home']
        );

        return new RedirectResponse($route);
    }
}
