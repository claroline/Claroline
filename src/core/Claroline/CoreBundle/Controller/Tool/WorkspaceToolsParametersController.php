<?php

namespace Claroline\CoreBundle\Controller\Tool;

use Symfony\Component\HttpFoundation\Response;
use Claroline\CoreBundle\Controller\Tool\AbstractParametersController;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;

class WorkspaceToolsParametersController extends AbstractParametersController
{
    private $toolManager;
    private $roleManager;
    private $formFactory;
    private $request;

    /**
     * @DI\InjectParams({
     *     "toolManager" = @DI\Inject("claroline.manager.tool_manager"),
     *     "roleManager" = @DI\Inject("claroline.manager.role_manager"),
     *     "formFactory" = @DI\Inject("claroline.form.factory"),
     *     "request"     = @DI\Inject("request")
     * })
     */
    public function __construct(
        ToolManager $toolManager,
        RoleManager $roleManager,
        FormFactory $formFactory,
        Request $request
    )
    {
        $this->toolManager = $toolManager;
        $this->roleManager = $roleManager;
        $this->formFactory = $formFactory;
        $this->request = $request;
    }
    /**
     * @EXT\Route(
     *     "/{workspaceId}/tools",
     *     name="claro_workspace_tools_roles"
     * )
     * @EXT\Method("GET")
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\parameters:toolRoles.html.twig")
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     *
     * @param AbstractWorkspace $workspace
     */
    public function workspaceToolsRolesAction(AbstractWorkspace $workspace)
    {
        return array(
            'roles' => $this->roleManager->getWorkspaceRoles($workspace),
            'workspace' => $workspace,
            'toolPermissions' => $this->toolManager->getWorkspaceToolsConfigurationArray($workspace)
        );
    }

    /**
     * @EXT\Route(
     *     "/remove/tool/{toolId}/workspace/{workspaceId}/role/{roleId}",
     *     name="claro_tool_workspace_remove",
     *     options={"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *     "role",
     *     class="ClarolineCoreBundle:Role",
     *     options={"id" = "roleId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *     "tool",
     *     class="ClarolineCoreBundle:Tool\Tool",
     *     options={"id" = "toolId", "strictId" = true}
     * )
     *
     * Remove a tool from a role in a workspace.
     *
     * @param Tool              $tool
     * @param Role              $role
     * @param AbstractWorkspace $workspace
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function removeRoleFromTool(Tool $tool, Role $role, AbstractWorkspace $workspace)
    {
        $this->toolManager->removeRole($tool, $role, $workspace);

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "/add/tool/{toolId}/workspace/{workspaceId}/role/{roleId}",
     *     name="claro_tool_workspace_add",
     *     options={"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *     "role",
     *     class="ClarolineCoreBundle:Role",
     *     options={"id" = "roleId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *     "tool",
     *     class="ClarolineCoreBundle:Tool\Tool",
     *     options={"id" = "toolId", "strictId" = true}
     * )
     *
     * Adds a tool to a role in a workspace.
     *
     * @param Tool              $tool
     * @param Role              $role
     * @param AbstractWorkspace $workspace
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function addRoleToTool(Tool $tool, Role $role, AbstractWorkspace $workspace)
    {
        $this->toolManager->addRole($tool, $role, $workspace);

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "/move/tool/{toolId}/position/{position}/workspace/{workspaceId}",
     *     name="claro_tool_workspace_move",
     *     options={"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *     "tool",
     *     class="ClarolineCoreBundle:Tool\Tool",
     *     options={"id" = "toolId", "strictId" = true}
     * )
     *
     * This method switch the position of a tool with an other one.
     *
     * @param Tool              $tool
     * @param integer           $position
     * @param AbstractWorkspace $workspace
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function move(Tool $tool, $position, AbstractWorkspace $workspace)
    {
        $this->toolManager->move($tool, $position, null, $workspace);

        return new Response('success');
    }

    /**
     * @EXT\Route(
     *     "/{workspaceId}/tools/{toolId}/editform",
     *     name="claro_workspace_order_tool_edit_form"
     * )
     * @EXT\Method("GET")
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\parameters:workspaceOrderToolEdit.html.twig")
     *
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *      "tool",
     *      class="ClarolineCoreBundle:Tool\Tool",
     *      options={"id" = "toolId", "strictId" = true}
     * )
     *
     * @param AbstractWorkspace $workspace
     * @param Tool              $tool
     *
     * @return Response
     */
    public function workspaceOrderToolEditFormAction(AbstractWorkspace $workspace, Tool $tool)
    {
        $ot = $this->toolManager->getOneByWorkspaceAndTool($workspace, $tool);

        return array(
            'form' => $this->formFactory->create(FormFactory::TYPE_ORDERED_TOOL, array(), $ot)->createView(),
            'workspace' => $workspace,
            'wot' => $ot
        );
    }

    /**
     * @EXT\Route(
     *     "/{workspaceId}/tools/{workspaceOrderToolId}/edit",
     *     name="claro_workspace_order_tool_edit"
     * )
     * @EXT\Method("POST")
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\parameters:workspaceOrderToolEdit.html.twig")
     *
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *      "ot",
     *      class="ClarolineCoreBundle:Tool\OrderedTool",
     *      options={"id" = "workspaceOrderToolId", "strictId" = true}
     * )
     *
     * @param AbstractWorkspace $workspace
     * @param OrderedTool       $ot
     *
     * @return Response
     */
    public function workspaceOrderToolEditAction(AbstractWorkspace $workspace, OrderedTool $ot)
    {

        $form = $this->formFactory->create(FormFactory::TYPE_ORDERED_TOOL, array(), $ot);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->toolManager->editOrderedTool($form->getData());

            return $this->redirect(
                $this->generateUrl(
                    'claro_workspace_tools_roles',
                    array('workspaceId' => $workspace->getId())
                )
            );
        }

        return array(
            'form' => $form->createView(),
            'workspace' => $workspace,
            'wot' => $ot
        );
    }
}
