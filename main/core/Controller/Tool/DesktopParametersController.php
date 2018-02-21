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

use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\UserOptions;
use Claroline\CoreBundle\Form\UserOptionsType;
use Claroline\CoreBundle\Manager\ToolManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @todo if user has ROLE_ANONYMOUS, a 403 should be returned (otherise he'll get a 500)
 */
class DesktopParametersController extends Controller
{
    private $formFactory;
    private $request;
    private $router;
    private $toolManager;
    private $om;

    /**
     * @DI\InjectParams({
     *     "formFactory"  = @DI\Inject("form.factory"),
     *     "request"      = @DI\Inject("request"),
     *     "urlGenerator" = @DI\Inject("router"),
     *     "toolManager"  = @DI\Inject("claroline.manager.tool_manager"),
     *     "ed"           = @DI\Inject("claroline.event.event_dispatcher"),
     *     "om"           = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        Request $request,
        UrlGeneratorInterface $router,
        ToolManager $toolManager,
        StrictDispatcher $ed,
        ObjectManager $om
    ) {
        $this->formFactory = $formFactory;
        $this->request = $request;
        $this->router = $router;
        $this->toolManager = $toolManager;
        $this->ed = $ed;
        $this->om = $om;
    }

    /**
     * @EXT\Route(
     *     "/tools/parameters/menu",
     *     name="claro_desktop_parameters_menu"
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\desktop\parameters:desktopParametersMenu.html.twig")
     * @EXT\ParamConverter("user", options={"authenticatedUser"=true})
     *
     * Displays the desktop tools configuration menu page.
     *
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return Response
     */
    public function desktopParametersMenuAction()
    {
        return [];
    }

    /**
     * @EXT\Route(
     *     "/tools/type/{type}",
     *     name="claro_tool_properties",
     *     defaults={"type"=0}
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\desktop\parameters:toolProperties.html.twig")
     * @EXT\ParamConverter("user", options={"authenticatedUser"=true})
     *
     * Displays the tools configuration page.
     *
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return Response
     */
    public function desktopConfigureToolAction(User $user, $type = 0)
    {
        $menuType = intval($type);
        $tools = $this->toolManager->getDesktopToolsConfigurationArray($user, $menuType);
        $adminOrderedTools = $this->toolManager
            ->getLockedConfigurableDesktopOrderedToolsByTypeForAdmin($menuType);

        $toolNames = [];

        foreach ($adminOrderedTools as $adminOrderedTool) {
            $toolNames[] = $adminOrderedTool->getTool()->getName();
        }
        $orderedTools = $this->toolManager
            ->getConfigurableDesktopOrderedToolsByUser($user, $toolNames, $menuType);

        return [
            'tools' => $tools,
            'adminOrderedTools' => $adminOrderedTools,
            'orderedTools' => $orderedTools,
            'type' => $menuType,
        ];
    }

    /**
     * @EXT\Route(
     *     "/tools/edit/type/{type}",
     *     name="claro_desktop_tools_roles_edit",
     *     defaults={"type"=0},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser"=true})
     * @EXT\Method("POST")
     *
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return Response
     */
    public function editToolsRolesAction(User $user, $type = 0)
    {
        $parameters = $this->request->request->all();
        $this->om->startFlushSuite();
        //moving tools;
        foreach ($parameters as $parameter => $value) {
            if (0 === strpos($parameter, 'tool-')) {
                $toolId = (int) str_replace('tool-', '', $parameter);
                $tool = $this->toolManager->getToolById($toolId);
                $this->toolManager->setToolPosition($tool, $value, $user, null, $type);
            }
        }

        //reset the visiblity for every tool
        $this->toolManager->resetToolsVisiblity($user, null, $type);

        //set tool visibility
        foreach ($parameters as $parameter => $value) {
            if (0 === strpos($parameter, 'chk-')) {
                //regex are evil
                $matches = [];
                preg_match('/tool-(.*)/', $parameter, $matches);
                $tool = $this->toolManager->getToolById((int) $matches[1]);
                $this->toolManager->setDesktopToolVisible($tool, $user, $type);
            }
        }

        $this->om->endFlushSuite();

        return new Response();
    }

    /**
     * @EXT\Route(
     *     "tool/{tool}/config",
     *     name="claro_desktop_tool_config"
     * )
     *
     * @param Tool $tool
     *
     * @return Response
     */
    public function openDesktopToolConfig(Tool $tool)
    {
        $event = $this->ed->dispatch(
            strtolower('configure_desktop_tool_'.$tool->getName()),
            'ConfigureDesktopTool',
            [$tool]
        );

        return new Response($event->getContent());
    }

    /**
     * @EXT\Route(
     *     "/tools/order/update/tool/{orderedTool}/type/{type}/next/{nextOrderedToolId}",
     *     name="claro_desktop_update_ordered_tool_order",
     *     defaults={"type"=0},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser"=true})
     *
     * @param OrderedTool $orderedTool
     * @param int type
     * @param int nextOrderedToolId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateDesktopOrderedToolOrderAction(
        User $user,
        OrderedTool $orderedTool,
        $nextOrderedToolId,
        $type = 0
    ) {
        if ($orderedTool->getUser() === $user &&
            $orderedTool->getType() === intval($type)) {
            $this->toolManager->reorderDesktopOrderedTool(
                $user,
                $orderedTool,
                $nextOrderedToolId,
                $type
            );

            return new Response('success', 200);
        } else {
            throw new AccessDeniedException();
        }
    }

    /**
     * @EXT\Route(
     *     "/user/options/edit/form",
     *     name="claro_user_options_edit_form"
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\desktop\parameters:userOptionsEditForm.html.twig")
     * @EXT\ParamConverter("user", options={"authenticatedUser"=true})
     *
     * Displays the user options form page.
     *
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return Response
     */
    public function desktopParametersUserOptionsEditFormAction(User $user)
    {
        $options = $user->getOptions();

        if (is_null($options)) {
            $options = new UserOptions();
            $options->setUser($user);
            $user->setOptions($options);
            $this->om->persist($options);
            $this->om->persist($user);
            $this->om->flush();
        }

        $form = $this->formFactory->create(
            new UserOptionsType(),
            $options
        );

        return ['form' => $form->createView(), 'options' => $options];
    }

    /**
     * @EXT\Route(
     *     "/user/options/{options}/edit",
     *     name="claro_user_options_edit"
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\desktop\parameters:userOptionsEditForm.html.twig")
     * @EXT\ParamConverter("user", options={"authenticatedUser"=true})
     *
     * Edit user options.
     *
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return Response
     */
    public function desktopParametersUserOptionsEditAction(UserOptions $options)
    {
        $form = $this->formFactory->create(
            new UserOptionsType(),
            $options
        );
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->om->persist($options);
            $this->om->flush();

            return new RedirectResponse(
                $this->router->generate('claro_desktop_parameters_menu')
            );
        } else {
            return ['form' => $form->createView(), 'options' => $options];
        }
    }
}
