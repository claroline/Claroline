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

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Controller\Tool\AbstractParametersController;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CoreBundle\Manager\ToolRightsManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\RightsManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;

class WorkspaceToolsParametersController extends AbstractParametersController
{
    private $toolManager;
    private $toolRightsManager;
    private $roleManager;
    private $rightsManager;
    private $resourceManager;
    private $formFactory;
    private $request;
    private $om;

    /**
     * @DI\InjectParams({
     *     "toolManager"       = @DI\Inject("claroline.manager.tool_manager"),
     *     "toolRightsManager" = @DI\Inject("claroline.manager.tool_rights_manager"),
     *     "roleManager"       = @DI\Inject("claroline.manager.role_manager"),
     *     "rightsManager"     = @DI\Inject("claroline.manager.rights_manager"),
     *     "resourceManager"   = @DI\Inject("claroline.manager.resource_manager"),
     *     "formFactory"       = @DI\Inject("claroline.form.factory"),
     *     "request"           = @DI\Inject("request"),
     *     "om"                = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(
        ToolManager $toolManager,
        ToolRightsManager $toolRightsManager,
        RoleManager $roleManager,
        RightsManager $rightsManager,
        ResourceManager $resourceManager,
        FormFactory $formFactory,
        Request $request,
        ObjectManager $om
    )
    {
        $this->toolManager       = $toolManager;
        $this->toolRightsManager = $toolRightsManager;
        $this->roleManager       = $roleManager;
        $this->rightsManager     = $rightsManager;
        $this->resourceManager   = $resourceManager;
        $this->formFactory       = $formFactory;
        $this->request           = $request;
        $this->om                = $om;
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/tools",
     *     name="claro_workspace_tools_roles"
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\parameters:toolRoles.html.twig")
     *
     * @param Workspace $workspace
     * @return array
     */
    public function workspaceToolsRolesAction(Workspace $workspace)
    {
        $this->checkAccess($workspace);
        $toolsDatas = $this->toolManager->getWorkspaceToolsConfigurationArray($workspace);
        $pwsToolConfigs = $this->toolManager->getPersonalWorkspaceToolConfigForCurrentUser();

        return array(
            'roles' => $this->roleManager->getWorkspaceConfigurableRoles($workspace),
            'workspace' => $workspace,
            'toolPermissions' => $toolsDatas['existingTools'],
            'maskDecoders' => $toolsDatas['maskDecoders'],
            'pwsToolConfigs' => $pwsToolConfigs
        );
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
     * @param Tool              $tool
     *
     * @return Response
     */
    public function workspaceOrderToolEditFormAction(Workspace $workspace, Tool $tool)
    {
        $this->checkAccess($workspace);
        $ot = $this->toolManager->getOneByWorkspaceAndTool($workspace, $tool);

        return array(
            'form' => $this->formFactory->create(FormFactory::TYPE_ORDERED_TOOL, array(), $ot->getContent())->createView(),
            'workspace' => $workspace,
            'wot' => $ot
        );
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
     * @param Workspace $workspace
     * @param OrderedTool $ot
     *
     * @return Response
     */
    public function workspaceOrderToolEditAction(Workspace $workspace, OrderedTool $workspaceOrderTool)
    {
        $this->checkAccess($workspace);
        $form = $this->formFactory->create(FormFactory::TYPE_ORDERED_TOOL, array(), $workspaceOrderTool->getContent());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            //I know it's not that great but I couldn't find an other way
            $formData = $this->request->request->get('workspace_order_tool_edit_form');
            $this->toolManager->renameOrderedTool(
                $formData['content'],
                $workspaceOrderTool
            );

            return new JsonResponse(
                array(
                    'tool_id' => $workspaceOrderTool->getTool()->getId(),
                    'ordered_tool_id' => $workspaceOrderTool->getId(),
                    'name' => $workspaceOrderTool->getContent()->getTitle()
                )
            );
        }

        return array(
            'form' => $form->createView(),
            'workspace' => $workspace,
            'wot' => $workspaceOrderTool
        );
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/tools/order/update/tool/{orderedTool}/with/{otherOrderedTool}/mode/{mode}/type/{type}",
     *     name="claro_workspace_update_ordered_tool_order",
     *     defaults={"type"=0},
     *     options={"expose"=true}
     * )
     * @param Workspace $workspace
     * @param OrderedTool $orderedTool
     * @param OrderedTool $otherOrderedTool
     * @param string $mode
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
    )
    {
        $this->checkAccess($workspace);

        if ($orderedTool->getWorkspace() === $workspace &&
            $otherOrderedTool->getWorkspace() === $workspace) {

            $order = $orderedTool->getOrder();
            $otherOrder = $otherOrderedTool->getOrder();

            if ($mode === 'previous') {

                if ($otherOrder > $order) {
                    $newOrder = $otherOrder;
                } else {
                    $newOrder = $otherOrder + 1;
                }
            } elseif ($mode === 'next') {

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
     * @param OrderedTool $orderedTool
     * @param Role $role
     * @param string $action
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function inverseWorkspaceOrderedToolRightAction(
        OrderedTool $orderedTool,
        Role $role,
        $action
    )
    {
        $workspace = $orderedTool->getWorkspace();
        $this->checkAccess($workspace);

        $this->toolRightsManager->inverseActionValue($orderedTool, $role, $action);

        return new Response('success', 200);
    }
}
