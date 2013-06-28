<?php

namespace Claroline\CoreBundle\Controller\Tool;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Entity\Tool\DesktopTool;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Library\Event\ConfigureDesktopToolEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormFactoryInterface;
use Claroline\CoreBundle\Manager\ToolManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;

class DesktopParametersController extends Controller
{
    private $request;
    private $router;
    private $formFactory;
    private $toolManager;

    /**
     * @DI\InjectParams({
     *     "request"        = @DI\Inject("request"),
     *     "urlGenerator"   = @DI\Inject("router"),
     *     "formFactory"    = @DI\Inject("form.factory"),
     *     "toolManager"        = @DI\Inject("claroline.manager.tool_manager")
     * })
     */
    public function __construct(
        Request $request,
        UrlGeneratorInterface $router,
        FormFactoryInterface $formFactory,
        ToolManager $toolManager
    )
    {
        $this->request = $request;
        $this->router = $router;
        $this->formFactory = $formFactory;
        $this->toolManager = $toolManager;
    }

    /**
     * @EXT\Route(
     *     "/tools",
     *     name="claro_tool_properties"
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\desktop\parameters:toolProperties.html.twig")
     *
     * Displays the tools configuration page.
     *
     * @return Response
     */
    public function desktopConfigureToolAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $orderedToolList = array();
        $desktopTools = $em->getRepository('ClarolineCoreBundle:Tool\OrderedTool')->findBy(array('user' => $user));

        foreach ($desktopTools as $desktopTool) {
            $desktopTool->getTool()->setVisible(true);
            $orderedToolList[$desktopTool->getOrder()] = $desktopTool->getTool();
        }

        $undisplayedTools = $em->getRepository('ClarolineCoreBundle:Tool\Tool')
            ->findDesktopUndisplayedToolsByUser($user);

        foreach ($undisplayedTools as $tool) {
            $tool->setVisible(false);
        }

        $tools = $this->get('claroline.utilities.misc')->arrayFill($orderedToolList, $undisplayedTools);

        return array('tools' => $tools);
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
     *
     * Add a tool to the desktop.
     *
     * @param Tool $tool
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function desktopAddToolAction(Tool $tool, $position)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $user = $this->get('security.context')->getToken()->getUser();
        $switchTool = $em->getRepository('ClarolineCoreBundle:Tool\DesktopTool')
            ->findOneBy(array('user' => $user, 'order' => $position));
        if ($switchTool != null) {
            throw new \RuntimeException('A tool already exists at this position');
        }
        $desktopTool = new DesktopTool();
        $desktopTool->setUser($user);
        $desktopTool->setTool($tool);
        $desktopTool->setOrder($position);
        $em->persist($desktopTool);
        $em->flush();

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "/move/tool/{tool}/position/{position}",
     *     name="claro_tool_desktop_move",
     *     options={"expose"=true}
     * )
     * @EXT\Method("POST")
     *
     * This method switch the position of a tool with an other one.
     *
     * @param Tool $tool
     * @param integer $position
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function desktopMoveToolAction(Tool $tool, $position)
    {
         $em = $this->get('doctrine.orm.entity_manager');
         $user = $this->get('security.context')->getToken()->getUser();
         $movingTool = $em->getRepository('ClarolineCoreBundle:Tool\DesktopTool')
            ->findOneBy(array('user' => $user, 'tool' => $tool));
         $switchTool = $em->getRepository('ClarolineCoreBundle:Tool\DesktopTool')
            ->findOneBy(array('user' => $user, 'order' => $position));

        //if a tool is already at this position, he must go "far away"
        if ($switchTool !== null) {
            //go far away ! Integrety constraints.
            $switchTool->setOrder('99');
            $em->persist($switchTool);
        }

        $em->flush();

        //the tool must exists
        if ($movingTool !== null) {
            $newPosition = $movingTool->getOrder();
            $movingTool->setOrder(intval($position));
            $em->persist($movingTool);
        }

         //put the original tool back.
        if ($switchTool !== null) {
            $switchTool->setOrder($newPosition);
            $em->persist($switchTool);
        }

        $em->flush();

        return new Response('<body>success</body>');
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
        $this->get('event_dispatcher')->dispatch($eventName, $event);

        if (is_null($event->getContent())) {
            throw new \Exception(
                "Tool '{$tool->getName()}' didn't return any Response for tool event '{$eventName}'."
            );
        }

        return new Response($event->getContent());
    }
}