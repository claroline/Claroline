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
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Controller\Tool\AbstractParametersController;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\RightsManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;

class WorkspaceToolsParametersController extends AbstractParametersController
{
    private $toolManager;
    private $roleManager;
    private $rightsManager;
    private $resourceManager;
    private $formFactory;
    private $request;
    private $objectManager;

    /**
     * @DI\InjectParams({
     *     "toolManager"     = @DI\Inject("claroline.manager.tool_manager"),
     *     "roleManager"     = @DI\Inject("claroline.manager.role_manager"),
     *     "rightsManager"   = @DI\Inject("claroline.manager.rights_manager"),
     *     "resourceManager" = @DI\Inject("claroline.manager.resource_manager"),
     *     "formFactory"     = @DI\Inject("claroline.form.factory"),
     *     "request"         = @DI\Inject("request"),
     *     "om"              = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(
        ToolManager $toolManager,
        RoleManager $roleManager,
        RightsManager $rightsManager,
        ResourceManager $resourceManager,
        FormFactory $formFactory,
        Request $request,
        ObjectManager $om
    )
    {
        $this->toolManager     = $toolManager;
        $this->roleManager     = $roleManager;
        $this->rightsManager   = $rightsManager;
        $this->resourceManager = $resourceManager;
        $this->formFactory     = $formFactory;
        $this->request         = $request;
        $this->om              = $om;
    }
    /**
     * @EXT\Route(
     *     "/{workspace}/tools",
     *     name="claro_workspace_tools_roles"
     * )
     * @EXT\Method("GET")
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\parameters:toolRoles.html.twig")
     *
     * @param Workspace $workspace
     * @return array
     */
    public function workspaceToolsRolesAction(Workspace $workspace)
    {
        $this->checkAccess($workspace);

        return array(
            'roles' => $this->roleManager->getWorkspaceConfigurableRoles($workspace),
            'workspace' => $workspace,
            'toolPermissions' => $this->toolManager->getWorkspaceToolsConfigurationArray($workspace)
        );
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/tools/edit",
     *     name="claro_workspace_tools_roles_edit",
     *     options={"expose"=true}
     * )
     * @EXT\Method("POST")
     */
    public function editToolsRolesAction(Workspace $workspace)
    {
        $this->checkAccess($workspace);
        $parameters = $this->request->request->all();
        $this->om->startFlushSuite();
        //moving tools;
        foreach ($parameters as $parameter => $value) {
            if (strpos($parameter, 'tool-') === 0) {
                $toolId = (int) str_replace('tool-', '', $parameter);
                $tool = $this->toolManager->getToolById($toolId);
                $this->toolManager->setToolPosition($tool, $value, null, $workspace);
            }
        }

        //reset the visiblity for every tool
        $this->toolManager->resetToolsVisiblity(null, $workspace);

        //set tool visibility
        foreach ($parameters as $parameter => $value) {
            if (strpos($parameter, 'chk-') === 0) {
                //regex are evil
                $matches = array();
                preg_match('/tool-(.*?)-/', $parameter, $matches);
                $tool = $this->toolManager->getToolById((int) $matches[1]);
                preg_match('/role-(.*)/', $parameter, $matches);
                $role = $this->roleManager->getRole($matches[1]);
                $this->toolManager->addRole($tool, $role, $workspace);
            }
        }

        $this->om->endFlushSuite();

        return new Response();
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
     *      class="ClarolineCoreBundle:Workspace\Workspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *      "tool",
     *      class="ClarolineCoreBundle:Tool\Tool",
     *      options={"id" = "toolId", "strictId" = true}
     * )
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
     *      class="ClarolineCoreBundle:Workspace\Workspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *      "ot",
     *      class="ClarolineCoreBundle:Tool\OrderedTool",
     *      options={"id" = "workspaceOrderToolId", "strictId" = true}
     * )
     *
     * @param Workspace $workspace
     * @param OrderedTool       $ot
     *
     * @return Response
     */
    public function workspaceOrderToolEditAction(Workspace $workspace, OrderedTool $ot)
    {
        $this->checkAccess($workspace);
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
