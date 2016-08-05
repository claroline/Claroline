<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Tool;

use Claroline\CoreBundle\Controller\Exception\WorkspaceAccessDeniedException;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\DisplayWidgetEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Controller of the workspace/desktop home page.
 */
class HomeController extends Controller
{
    private $authorization;
    private $eventDispatcher;

    /**
     * @DI\InjectParams({
     *     "authorization"   = @DI\Inject("security.authorization_checker"),
     *     "eventDispatcher" = @DI\Inject("event_dispatcher")
     * })
     */
    public function __construct(AuthorizationCheckerInterface $authorization, EventDispatcherInterface $eventDispatcher)
    {
        $this->authorization = $authorization;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @EXT\Route(
     *     "/desktop/home/display/tab/{tabId}",
     *     name="claro_desktop_home_display",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCoreBundle:Tool\desktop\home:desktopHome.html.twig")
     *
     * Displays the desktop
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function desktopHomeDisplayAction($tabId = -1)
    {
        return ['tabId' => $tabId];
    }

    /**
     * @EXT\Route(
     *     "/desktop/tab/{tabId}",
     *     name="claro_display_desktop_home_tab",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @param int $tabId
     */
    public function displayDesktopHomeTabAction($tabId)
    {
        return $this->redirectToRoute('claro_desktop_home_display', ['tabId' => $tabId]);
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/home/display/tab/{tabId}",
     *     name="claro_workspace_home_display",
     *     options = {"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\home:workspaceHome.html.twig")
     *
     * Displays the workspace home tool.
     *
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     *
     * @return array
     */
    public function workspaceHomeDisplayAction(Workspace $workspace, $tabId = -1)
    {
        $this->checkWorkspaceHomeAccess($workspace);
        $canEdit = $this->authorization->isGranted(['home', 'edit'], $workspace);

        return ['workspace' => $workspace, 'canEdit' => $canEdit, 'tabId' => $tabId];
    }

    /**
     * @EXT\Route(
     *     "/widget/instance/{widgetInstance}/content",
     *     name="claro_widget_instance_content",
     *     options={"expose"=true}
     * )
     *
     * Get a widget instance content.
     *
     * @param WidgetInstance $widgetInstance
     *
     * @return JsonResponse
     */
    public function getWidgetInstanceContentAction(WidgetInstance $widgetInstance)
    {
        $event = $this->eventDispatcher->dispatch(
            "widget_{$widgetInstance->getWidget()->getName()}",
            new DisplayWidgetEvent($widgetInstance)
        );

        return new JsonResponse($event->getContent());
    }

    private function checkWorkspaceHomeAccess(Workspace $workspace)
    {
        if (!$this->authorization->isGranted('home', $workspace)) {
            $exception = new WorkspaceAccessDeniedException();
            $exception->setWorkspace($workspace);

            throw $exception;
        }
    }
}
