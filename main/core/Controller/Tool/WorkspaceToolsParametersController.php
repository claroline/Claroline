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

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Form\WorkspaceOptionsType;
use Claroline\CoreBundle\Form\WorkspaceOrderToolEditType;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\RightsManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CoreBundle\Manager\ToolRightsManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class WorkspaceToolsParametersController extends AbstractParametersController
{
    private $formFactory;
    private $om;
    private $request;
    private $resourceManager;
    private $rightsManager;
    private $roleManager;
    private $router;
    private $toolManager;
    private $toolRightsManager;
    private $workspaceManager;

    /**
     * @DI\InjectParams({
     *     "om"                = @DI\Inject("claroline.persistence.object_manager"),
     *     "formFactory"       = @DI\Inject("form.factory"),
     *     "request"           = @DI\Inject("request"),
     *     "resourceManager"   = @DI\Inject("claroline.manager.resource_manager"),
     *     "rightsManager"     = @DI\Inject("claroline.manager.rights_manager"),
     *     "roleManager"       = @DI\Inject("claroline.manager.role_manager"),
     *     "router"            = @DI\Inject("router"),
     *     "toolManager"       = @DI\Inject("claroline.manager.tool_manager"),
     *     "toolRightsManager" = @DI\Inject("claroline.manager.tool_rights_manager"),
     *     "workspaceManager"  = @DI\Inject("claroline.manager.workspace_manager")
     * })
     */
    public function __construct(
        FormFactory $formFactory,
        ObjectManager $om,
        Request $request,
        ResourceManager $resourceManager,
        RightsManager $rightsManager,
        RoleManager $roleManager,
        RouterInterface $router,
        ToolManager $toolManager,
        ToolRightsManager $toolRightsManager,
        WorkspaceManager $workspaceManager
    ) {
        $this->formFactory = $formFactory;
        $this->om = $om;
        $this->request = $request;
        $this->resourceManager = $resourceManager;
        $this->rightsManager = $rightsManager;
        $this->roleManager = $roleManager;
        $this->router = $router;
        $this->toolManager = $toolManager;
        $this->toolRightsManager = $toolRightsManager;
        $this->workspaceManager = $workspaceManager;
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/tools",
     *     name="claro_workspace_tools_roles"
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\parameters:toolRoles.html.twig")
     *
     * @param Workspace $workspace
     *
     * @return array
     */
    public function workspaceToolsRolesAction(Workspace $workspace)
    {
        $this->checkAccess($workspace);
        $toolsDatas = $this->toolManager->getWorkspaceToolsConfigurationArray($workspace);
        $pwsToolConfigs = $this->toolManager->getPersonalWorkspaceToolConfigForCurrentUser();

        return [
            'roles' => $this->roleManager->getWorkspaceConfigurableRoles($workspace),
            'workspace' => $workspace,
            'toolPermissions' => $toolsDatas['existingTools'],
            'maskDecoders' => $toolsDatas['maskDecoders'],
            'pwsToolConfigs' => $pwsToolConfigs,
        ];
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/tools/{tool}/editform",
     *     name="claro_workspace_order_tool_edit_form"
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\parameters:toolNameModalForm.html.twig")
     *
     * @param Workspace $workspace
     * @param Tool      $tool
     *
     * @return Response
     */
    public function workspaceOrderToolEditFormAction(Workspace $workspace, Tool $tool)
    {
        $this->checkAccess($workspace);
        $ot = $this->toolManager->getOneByWorkspaceAndTool($workspace, $tool);

        return [
            'form' => $this->formFactory->create(new WorkspaceOrderToolEditType(), $ot)->createView(),
            'workspace' => $workspace,
            'wot' => $ot,
        ];
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/tools/{workspaceOrderTool}/edit",
     *     name="claro_workspace_order_tool_edit"
     * )
     * @EXT\Method("POST")
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\parameters:workspaceOrderToolEdit.html.twig")
     *
     * @param Workspace   $workspace
     * @param OrderedTool $workspaceOrderTool
     *
     * @return Response
     */
    public function workspaceOrderToolEditAction(Workspace $workspace, OrderedTool $workspaceOrderTool)
    {
        $this->checkAccess($workspace);
        $form = $this->formFactory->create(new WorkspaceOrderToolEditType(), $workspaceOrderTool);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->toolManager->editOrderedTool($form->getData());

            return new JsonResponse(
                [
                    'tool_id' => $workspaceOrderTool->getTool()->getId(),
                    'ordered_tool_id' => $workspaceOrderTool->getId(),
                    'name' => $workspaceOrderTool->getName(),
                ]
            );
        }

        return [
            'form' => $form->createView(),
            'workspace' => $workspace,
            'wot' => $workspaceOrderTool,
        ];
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/tools/order/update/tool/{orderedTool}/with/{otherOrderedTool}/mode/{mode}/type/{type}",
     *     name="claro_workspace_update_ordered_tool_order",
     *     defaults={"type"=0},
     *     options={"expose"=true}
     * )
     *
     * @param Workspace   $workspace
     * @param OrderedTool $orderedTool
     * @param OrderedTool $otherOrderedTool
     * @param string      $mode
     * @param int type
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateWorkspaceOrderedToolOrderAction(
        Workspace $workspace,
        OrderedTool $orderedTool,
        OrderedTool $otherOrderedTool,
        $mode,
        $type = 0
    ) {
        $this->checkAccess($workspace);

        if ($orderedTool->getWorkspace() === $workspace &&
            $otherOrderedTool->getWorkspace() === $workspace) {
            $order = $orderedTool->getOrder();
            $otherOrder = $otherOrderedTool->getOrder();

            if ('previous' === $mode) {
                if ($otherOrder > $order) {
                    $newOrder = $otherOrder;
                } else {
                    $newOrder = $otherOrder + 1;
                }
            } elseif ('next' === $mode) {
                if ($otherOrder > $order) {
                    $newOrder = $otherOrder - 1;
                } else {
                    $newOrder = $otherOrder;
                }
            } else {
                return new Response('Bad Request', 400);
            }

            $this->toolManager->updateWorkspaceOrderedToolOrder(
                $orderedTool,
                $newOrder,
                $type
            );

            return new Response('success', 204);
        } else {
            throw new AccessDeniedException();
        }
    }

    /**
     * @EXT\Route(
     *     "/ordered/tool/{orderedTool}/role/{role}/action/{action}/inverse",
     *     name="claro_workspace_inverse_ordered_tool_right",
     *     options={"expose"=true}
     * )
     *
     * @param OrderedTool $orderedTool
     * @param Role        $role
     * @param string      $action
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function inverseWorkspaceOrderedToolRightAction(
        OrderedTool $orderedTool,
        Role $role,
        $action
    ) {
        $workspace = $orderedTool->getWorkspace();
        $this->checkAccess($workspace);

        $this->toolRightsManager->inverseActionValue($orderedTool, $role, $action);

        return new Response('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/display/edit/form",
     *     name="claro_workspace_display_edit_form"
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\parameters:workspaceDisplayEditForm.html.twig")
     *
     * @param Workspace $workspace
     */
    public function workspaceDisplayEditFormAction(Workspace $workspace)
    {
        $this->checkAccess($workspace);
        $workspaceOptions = $this->workspaceManager->getWorkspaceOptions($workspace);
        $form = $this->formFactory->create(new WorkspaceOptionsType($workspaceOptions));

        return [
            'form' => $form->createView(),
            'workspace' => $workspace,
        ];
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/display/edit",
     *     name="claro_workspace_display_edit"
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\parameters:workspaceDisplayEditForm.html.twig")
     *
     * @param Workspace $workspace
     */
    public function workspaceDisplayEditAction(Workspace $workspace)
    {
        $this->checkAccess($workspace);
        $workspaceOptions = $this->workspaceManager->getWorkspaceOptions($workspace);
        $details = $workspaceOptions->getDetails();
        $form = $this->formFactory->create(new WorkspaceOptionsType($workspaceOptions));
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $color = $form->get('backgroundColor')->getData();
            $hideToolsMenu = $form->get('hideToolsMenu')->getData();
            $hideBreadcrumb = $form->get('hideBreadcrumb')->getData();
            $useDefaultResource = $form->get('useWorkspaceOpeningResource')->getData();
            $defaultResource = $form->get('workspaceOpeningResource')->getData();
            $details['background_color'] = $color;
            $details['hide_tools_menu'] = $hideToolsMenu;
            $details['hide_breadcrumb'] = $hideBreadcrumb;
            $details['use_workspace_opening_resource'] = $useDefaultResource;
            $details['workspace_opening_resource'] = empty($defaultResource) ?
                null :
                $defaultResource->getId();
            $workspaceOptions->setDetails($details);
            $this->workspaceManager->persistworkspaceOptions($workspaceOptions);

            return new RedirectResponse(
                $this->router->generate(
                    'claro_workspace_open_tool',
                    ['workspaceId' => $workspace->getId(), 'toolName' => 'parameters']
                )
            );
        } else {
            return [
                'form' => $form->createView(),
                'workspace' => $workspace,
            ];
        }
    }
}
