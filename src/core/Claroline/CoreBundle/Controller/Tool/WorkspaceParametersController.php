<?php

namespace Claroline\CoreBundle\Controller\Tool;

use Claroline\CoreBundle\Event\StrictDispatcher;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use JMS\DiExtraBundle\Annotation as DI;

class WorkspaceParametersController extends Controller
{
    private $workspaceManager;
    private $roleManager;
    private $resourceManager;
    private $security;
    private $eventDispatcher;
    private $formFactory;
    private $router;
    private $request;

    /**
     * @DI\InjectParams({
     *     "workspaceManager" = @DI\Inject("claroline.manager.workspace_manager"),
     *     "roleManager"      = @DI\Inject("claroline.manager.role_manager"),
     *     "resourceManager"  = @DI\Inject("claroline.manager.resource_manager"),
     *     "security"         = @DI\Inject("security.context"),
     *     "eventDispatcher"  = @DI\Inject("claroline.event.event_dispatcher"),
     *     "formFactory"      = @DI\Inject("claroline.form.factory"),
     *     "router"           = @DI\Inject("router"),
     *     "request"          = @DI\Inject("request")
     * })
     */
    public function __construct(
        WorkspaceManager $workspaceManager,
        RoleManager $roleManager,
        ResourceManager $resourceManager,
        SecurityContextInterface $security,
        StrictDispatcher $eventDispatcher,
        FormFactory $formFactory,
        UrlGeneratorInterface $router,
        Request $request
    )
    {
        $this->workspaceManager = $workspaceManager;
        $this->roleManager = $roleManager;
        $this->resourceManager = $resourceManager;
        $this->security = $security;
        $this->eventDispatcher = $eventDispatcher;
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->request = $request;
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/form/export",
     *     name="claro_workspace_export_form"
     * )
     * @EXT\Method("GET")
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\parameters:template.html.twig")
     *
     * @param AbstractWorkspace $workspace
     *
     * @return Response
     */
    public function workspaceExportFormAction(AbstractWorkspace $workspace)
    {
        $this->checkAccess($workspace);
        $form = $this->formFactory->create(FormFactory::TYPE_WORKSPACE_TEMPLATE);

        return array(
            'form' => $form->createView(),
            'workspace' => $workspace
        );
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/export",
     *     name="claro_workspace_export"
     * )
     * @EXT\Method("POST")
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\parameters:template.html.twig")
     *
     * @param AbstractWorkspace $workspace
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function workspaceExportAction(AbstractWorkspace $workspace)
    {
        $this->checkAccess($workspace);
        $form = $this->formFactory->create(FormFactory::TYPE_WORKSPACE_TEMPLATE);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $name = $form->get('name')->getData();
            $this->workspaceManager->export($workspace, $name);
            $route = $this->router->generate(
                'claro_workspace_open_tool',
                array('toolName' => 'parameters', 'workspaceId' => $workspace->getId())
            );

            return new RedirectResponse($route);
        }

        return array(
            'form' => $form->createView(),
            'workspace' => $workspace
        );
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/editform",
     *     name="claro_workspace_edit_form"
     * )
     * @EXT\Method("GET")
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\parameters:workspaceEdit.html.twig")
     *
     * @param AbstractWorkspace $workspace
     *
     * @return Response
     */
    public function workspaceEditFormAction(AbstractWorkspace $workspace)
    {
        $this->checkAccess($workspace);
        $form = $this->formFactory->create(FormFactory::TYPE_WORKSPACE_EDIT, array(), $workspace);

        return array(
            'form' => $form->createView(),
            'workspace' => $workspace
        );
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/edit",
     *     name="claro_workspace_edit"
     * )
     * @EXT\Method("POST")
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\parameters:workspaceEdit.html.twig")
     *
     * @param AbstractWorkspace $workspace
     *
     * @return Response
     */
    public function workspaceEditAction(AbstractWorkspace $workspace)
    {
        if (!$this->security->isGranted('parameters', $workspace)) {
            throw new AccessDeniedException();
        }

        $wsRegisteredName = $workspace->getName();
        $wsRegisteredCode = $workspace->getCode();
        $form = $this->formFactory->create(FormFactory::TYPE_WORKSPACE_EDIT, array(), $workspace);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->workspaceManager->createWorkspace($workspace);

            return $this->redirect(
                $this->generateUrl(
                    'claro_workspace_open_tool',
                    array(
                        'workspaceId' => $workspace->getId(),
                        'toolName' => 'parameters'
                    )
                )
            );
        } else {
            $workspace->setName($wsRegisteredName);
            $workspace->setCode($wsRegisteredCode);
        }

        return array(
            'form' => $form->createView(),
            'workspace' => $workspace
        );
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/tool/{tool}/config",
     *     name="claro_workspace_tool_config"
     * )
     * @EXT\Method("GET")
     *
     * @param AbstractWorkspace $workspace
     * @param Tool              $tool
     *
     * @return Response
     */
    public function openWorkspaceToolConfig(AbstractWorkspace $workspace, Tool $tool)
    {
        $this->checkAccess($workspace);
        $event = $this->eventDispatcher->dispatch(
            strtolower('configure_workspace_tool_' . $tool->getName()),
            'ConfigureWorkspaceTool',
            array($tool,$workspace)
        );

        return new Response($event->getContent());
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/roles/config",
     *     name="claro_workspace_roles"
     * )
     * @EXT\Method("GET")
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\parameters:roles.html.twig")
     */
    public function configureRolePageAction(AbstractWorkspace $workspace)
    {
        $this->checkAccess($workspace);
        $roles = $this->roleManager->getRolesByWorkspace($workspace);

        return array('workspace' => $workspace, 'roles' => $roles);
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/roles/create/form",
     *     name="claro_workspace_role_create_form"
     * )
     * @EXT\Method("GET")
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\parameters:roleCreation.html.twig")
     */
    public function createRoleFormAction(AbstractWorkspace $workspace)
    {
        $this->checkAccess($workspace);
        $form = $this->formFactory->create(FormFactory::TYPE_WORKSPACE_ROLE);

        return array('workspace' => $workspace, 'form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/roles/create",
     *     name="claro_workspace_role_create"
     * )
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\parameters:roleCreation.html.twig")
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function createRoleAction(AbstractWorkspace $workspace, User $user)
    {
        $this->checkAccess($workspace);
        $form = $this->formFactory->create(FormFactory::TYPE_WORKSPACE_ROLE);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $name = $form->get('translationKey')->getData();
            $requireDir = $form->get('requireDir')->getData();
            $role = $this->roleManager
                ->createWorkspaceRole('ROLE_WS_' . strtoupper($name) . '_' . $workspace->getGuid(), $name, $workspace);

            if ($requireDir) {
                $resourceTypes = $this->resourceManager->getAllResourceTypes();
                $creations = array();

                foreach ($resourceTypes as $resourceType) {
                    $creations[] = array('name' => $resourceType->getName());
                }


                $this->resourceManager->create(
                    $this->resourceManager->createResource(
                        'Claroline\CoreBundle\Entity\Resource\Directory',
                        $name
                    ),
                    $this->resourceManager->getResourceTypeByName('directory'),
                    $user,
                    $workspace,
                    $this->resourceManager->getWorkspaceRoot($workspace),
                    null,
                    array(
                        'ROLE_WS_' .  strtoupper($name) => array(
                            'canOpen' => true,
                            'canEdit' => true,
                            'canCopy' => true,
                            'canDelete' => true,
                            'canExport' => true,
                            'canCreate' => $creations,
                            'role' => $role
                        ),
                        'ROLE_WS_MANAGER' => array(
                            'canOpen' => true,
                            'canEdit' => true,
                            'canCopy' => true,
                            'canDelete' => true,
                            'canExport' => true,
                            'canCreate' => $creations,
                            'role' => $this->roleManager->getManagerRole($workspace)
                        )
                    )
                );
            }

            $route = $this->router->generate(
                'claro_workspace_roles',
                array('workspace' => $workspace->getId())
            );

            return new RedirectResponse($route);
        }

        return array('form' => $form->createView(), 'workspace' => $workspace);
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/role/{role}/remove",
     *     name="claro_workspace_role_remove"
     * )
     * @EXT\Method("GET")
     */
    public function removeRoleAction(AbstractWorkspace $workspace, Role $role)
    {
        $this->checkAccess($workspace);
        $this->roleManager->remove($role);

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/role/{role}/parameters",
     *     name="claro_workspace_role_parameters"
     * )
     * @EXT\Method("GET")
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\parameters:roleParameters.html.twig")
     */
    public function roleParametersAction(Role $role, AbstractWorkspace $workspace)
    {
        $this->checkAccess($workspace);

        return array('role' => $role, 'workspace' => $workspace);
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/role/{role}/edit/form",
     *     name="claro_workspace_role_edit_form"
     * )
     * @EXT\Method("GET")
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\parameters:roleEdit.html.twig")
     */
    public function editRoleFormAction(Role $role, AbstractWorkspace $workspace)
    {
        $this->checkAccess($workspace);
        $form = $this->formFactory->create(FormFactory::TYPE_ROLE_TRANSLATION, array(), $role);

        return array('workspace' => $workspace, 'form' => $form->createView(), 'role' => $role);
    }

    /**
     * @EXT\Route(
     *     "/{workspace}/role/{role}/edit",
     *     name="claro_workspace_role_edit"
     * )
     * @EXT\Method("POST")
     */
    public function editRoleAction(Role $role, AbstractWorkspace $workspace)
    {
        $this->checkAccess($workspace);
        $form = $this->formFactory->create(FormFactory::TYPE_ROLE_TRANSLATION, array(), $role);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->roleManager->edit($role);
            $route = $this->router->generate(
                'claro_workspace_role_parameters',
                array('role' => $role->getId(), 'workspace' => $workspace->getId())
            );

            return new RedirectResponse($route);
        }

        return array('workspace' => $workspace, 'form' => $form->createView(), 'role' => $role);
    }

    public function listGroupsForRoleAction(Role $role, AbstractWorkspace $workspace)
    {
        
    }

    public function listUsersForRoleAction(Role $role, AbstractWorkspace $workspace)
    {

    }

    private function checkAccess(AbstractWorkspace $workspace)
    {
        if (!$this->security->isGranted('parameters', $workspace)) {
            throw new AccessDeniedException();
        }
    }
}
