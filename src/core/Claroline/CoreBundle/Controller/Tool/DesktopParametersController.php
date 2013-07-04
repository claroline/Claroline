<?php

namespace Claroline\CoreBundle\Controller\Tool;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Event\ConfigureDesktopToolEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Claroline\CoreBundle\Manager\ToolManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;

class DesktopParametersController extends Controller
{
    private $request;
    private $router;
    private $toolManager;

    /**
     * @DI\InjectParams({
     *     "request"      = @DI\Inject("request"),
     *     "urlGenerator" = @DI\Inject("router"),
     *     "toolManager"  = @DI\Inject("claroline.manager.tool_manager"),
     *     "ed"           = @DI\Inject("event_dispatcher")
     * })
     */
    public function __construct(
        Request $request,
        UrlGeneratorInterface $router,
        ToolManager $toolManager,
        EventDispatcher $ed
    )
    {
        $this->request = $request;
        $this->router = $router;
        $this->toolManager = $toolManager;
        $this->ed = $ed;
    }

    /**
     * @EXT\Route(
     *     "/tools",
     *     name="claro_tool_properties"
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\desktop\parameters:toolProperties.html.twig")
     * @EXT\ParamConverter("user", options={"authenticatedUser"=true})
     *
     * Displays the tools configuration page.
     *
     * @return Response
     */
    public function desktopConfigureToolAction(User $user)
    {
        return array('tools' => $this->toolManager->getDesktopToolsConfigurationArray($user));
    }

    /**
     * @EXT\Route(
     *     "/remove/tool/{tool}",
     *     name="claro_tool_desktop_remove",
     *     options={"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter("tool", class="ClarolineCoreBundle:Tool\Tool", options={"id"="tool", "strictId"=true})
     * @EXT\ParamConverter("user", options={"authenticatedUser"=true})
     *
     * Remove a tool from the desktop.
     *
     * @param Tool $tool
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function desktopRemoveToolAction(Tool $tool, User $user)
    {
        $this->toolManager->removeDesktopTool($tool, $user);

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "/add/tool/{tool}/position/{position}",
     *     name="claro_tool_desktop_add",
     *     options={"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter("user", options={"authenticatedUser"=true})
     *
     * Add a tool to the desktop.
     *
     * @param Tool $tool
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function desktopAddToolAction(Tool $tool, $position, User $user)
    {
        $this->toolManager->addDesktopTool($tool, $user, $position, $tool->getName());

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "/move/tool/{tool}/position/{position}",
     *     name="claro_tool_desktop_move",
     *     options={"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter("user", options={"authenticatedUser"=true})
     *
     * This method switch the position of a tool with an other one.
     *
     * @param Tool $tool
     * @param integer $position
     * @param User $user
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function desktopMoveToolAction(Tool $tool, $position, User $user)
    {
        $this->toolManager->move($tool, $position, $user, null);

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "tool/{tool}/config",
     *     name="claro_desktop_tool_config"
     * )
     * @EXT\Method("GET")
     *
     * @param Tool $tool
     *
     * @return Response
     */
    public function openDesktopToolConfig(Tool $tool)
    {
        $event = new ConfigureDesktopToolEvent($tool);
        $eventName = strtolower('configure_desktop_tool_' . $tool->getName());
        $this->ed->dispatch($eventName, $event);

        if (is_null($event->getContent())) {
            throw new \Exception(
                "Tool '{$tool->getName()}' didn't return any Response for tool event '{$eventName}'."
            );
        }

        return new Response($event->getContent());
    }
}