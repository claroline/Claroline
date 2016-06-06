<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\API\Workspace;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Form\WorkspaceType;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Put;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Symfony\Component\HttpFoundation\File\File;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Claroline\CoreBundle\Library\Utilities\ClaroUtilities;

/**
 * @NamePrefix("api_")
 */
class WorkspaceController extends FOSRestController
{
    private $formFactory;
    private $om;
    private $request;
    private $roleManager;
    private $defaultTemplate;
    private $tokenStorage;
    private $utilities;
    private $workspaceManager;
    private $workspaceRepo;

    /**
     * @DI\InjectParams({
     *     "formFactory"      = @DI\Inject("form.factory"),
     *     "om"               = @DI\Inject("claroline.persistence.object_manager"),
     *     "request"          = @DI\Inject("request"),
     *     "roleManager"      = @DI\Inject("claroline.manager.role_manager"),
     *     "defaultTemplate"  = @DI\Inject("%claroline.param.default_template%"),
     *     "tokenStorage"     = @DI\Inject("security.token_storage"),
     *     "utilities"        = @DI\Inject("claroline.utilities.misc"),
     *     "workspaceManager" = @DI\Inject("claroline.manager.workspace_manager")
     * })
     */
    public function __construct(
        FormFactory $formFactory,
        ObjectManager $om,
        Request $request,
        RoleManager $roleManager,
        $defaultTemplate,
        TokenStorageInterface $tokenStorage,
        ClaroUtilities $utilities,
        WorkspaceManager $workspaceManager
    ) {
        $this->formFactory = $formFactory;
        $this->om = $om;
        $this->request = $request;
        $this->roleManager = $roleManager;
        $this->defaultTemplate = $defaultTemplate;
        $this->tokenStorage = $tokenStorage;
        $this->utilities = $utilities;
        $this->workspaceManager = $workspaceManager;
        $this->workspaceRepo = $this->om->getRepository('ClarolineCoreBundle:Workspace\Workspace');
    }

    /**
     * @View(serializerGroups={"api_workspace"})
     * @Get("/user/{user}/workspaces", name="get_user_workspaces", options={ "method_prefix" = false })
     * @SEC\PreAuthorize("hasRole('ROLE_ADMIN')")
     */
    public function getUserWorkspacesAction(User $user)
    {
        return $this->workspaceManager->getWorkspacesByUser($user);
    }

    /**
     * @View(serializerGroups={"api_workspace"})
     * @ApiDoc(
     *     description="Returns the workspaces list",
     *     views = {"workspace"}
     * )
     * @SEC\PreAuthorize("canOpenAdminTool('workspace_management')")
     */
    public function getWorkspacesAction()
    {
        return $this->workspaceRepo->findAll();
    }

    /**
     * @View(serializerGroups={"api_workspace"})
     * @ApiDoc(
     *     description="Returns a workspace",
     *     views = {"workspace"}
     * )
     * @SEC\PreAuthorize("canOpenAdminTool('workspace_management')")
     */
    public function getWorkspaceAction(Workspace $workspace)
    {
        return $workspace;
    }

    /**
     * @View(serializerGroups={"api_workspace"})
     * @ApiDoc(
     *     description="Returns a workspace with additional datas",
     *     views = {"workspace"}
     * )
     * @SEC\PreAuthorize("canOpenAdminTool('workspace_management')")
     */
    public function getWorkspaceAdditionalDatasAction(Workspace $workspace)
    {
        $datas = array();
        $nbUsers = $this->workspaceManager->countUsers($workspace, true);
        $usedStorage = $this->workspaceManager->getUsedStorage($workspace);
        $nbUsedStorage = $this->utilities->formatFileSize($usedStorage);
        $nbResources = $this->workspaceManager->countResources($workspace);
        $datas['used_storage'] = $nbUsedStorage;
        $datas['nb_users'] = $nbUsers;
        $datas['nb_resources'] = $nbResources;

        return new JsonResponse($datas);
    }

    /**
     * @View(serializerGroups={"api"})
     * @ApiDoc(
     *     description="Returns the non-personal workspaces list",
     *     views = {"workspace"}
     * )
     * @SEC\PreAuthorize("canOpenAdminTool('workspace_management')")
     */
    public function getNonPersonalWorkspacesAction()
    {
        return $this->workspaceRepo->findNonPersonalWorkspaces();
    }

    /**
     * @View(serializerGroups={"api_workspace"})
     * @ApiDoc(
     *     description="Create a workspace",
     *     views = {"workspace"},
     *     input="Claroline\CoreBundle\Form\WorkspaceType"
     * )
     * @Post("workspace/user/{user}", name="post_workspace", options={ "method_prefix" = false })
     * @SEC\PreAuthorize("canOpenAdminTool('workspace_management')")
     */
    public function postWorkspaceUserAction(User $user)
    {
        $workspaceType = new WorkspaceType($user);
        $workspaceType->enableApi();
        $form = $this->formFactory->create($workspaceType, new Workspace());
        $form->submit($this->request);

        if ($form->isValid()) {
            $workspace = $form->getData();
            $workspace->setCreator($user);
            $template = new File($this->defaultTemplate);
            $workspace = $this->workspaceManager->create($workspace, $template);

            return $workspace;
        }

        return $form;
    }

    /**
     * @View()
     * @ApiDoc(
     *     description="Removes a workspace",
     *     views = {"workspace"}
     * )
     * @SEC\PreAuthorize("canOpenAdminTool('workspace_management')")
     */
    public function deleteWorkspaceAction(Workspace $workspace)
    {
        $this->workspaceManager->deleteWorkspace($workspace);

        return array('success');
    }

    /**
     * @View(serializerGroups={"api_workspace"})
     * @ApiDoc(
     *     description="Update a workspace",
     *     views = {"workspace"},
     *     input="Claroline\CoreBundle\Form\WorkspaceType"
     * )
     * @Put("workspace/{workspace}", name="put_workspace", options={ "method_prefix" = false })
     * @SEC\PreAuthorize("canOpenAdminTool('workspace_management')")
     */
    public function putWorkspaceAction(Workspace $workspace)
    {
        $workspaceType = new WorkspaceType();
        $workspaceType->enableApi();
        $form = $this->formFactory->create($workspaceType, $workspace);
        $form->submit($this->request);

        if ($form->isValid()) {
            $this->workspaceManager->editWorkspace($workspace);

            return $workspace;
        }

        return $form;
    }

    /**
     * @View(serializerGroups={"api_workspace"})
     * @ApiDoc(
     *     description="Update a workspace owner",
     *     views = {"workspace"}
     * )
     * @ParamConverter("user", class="ClarolineCoreBundle:User", options={"repository_method" = "findForApi"})
     * @SEC\PreAuthorize("canOpenAdminTool('workspace_management')")
     */
    public function putWorkspaceOwnerAction(Workspace $workspace, User $user)
    {
        $currentCreator = $workspace->getCreator();

        if ($currentCreator->getId() !== $user->getId()) {
            $this->om->startFlushSuite();
            $role = $this->roleManager->getManagerRole($workspace);
            $this->roleManager->associateRole($user, $role);
            $this->roleManager->dissociateRole($currentCreator, $role);
            $workspace->setCreator($user);
            $this->workspaceManager->editWorkspace($workspace);
            $this->om->endFlushSuite();
        }

        return $workspace;
    }
}
