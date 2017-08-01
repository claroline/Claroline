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

use Claroline\CoreBundle\API\FinderProvider;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Form\WorkspaceType;
use Claroline\CoreBundle\Library\Utilities\ClaroUtilities;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\FOSRestController;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

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
    private $finder;

    /**
     * @DI\InjectParams({
     *     "formFactory"      = @DI\Inject("form.factory"),
     *     "om"               = @DI\Inject("claroline.persistence.object_manager"),
     *     "request"          = @DI\Inject("request"),
     *     "roleManager"      = @DI\Inject("claroline.manager.role_manager"),
     *     "defaultTemplate"  = @DI\Inject("%claroline.param.default_template%"),
     *     "tokenStorage"     = @DI\Inject("security.token_storage"),
     *     "utilities"        = @DI\Inject("claroline.utilities.misc"),
     *     "workspaceManager" = @DI\Inject("claroline.manager.workspace_manager"),
     *     "finder"           = @DI\Inject("claroline.api.finder")
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
        WorkspaceManager $workspaceManager,
        FinderProvider $finder
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
        $this->finder = $finder;
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
     * @Get("/workspaces", name="get_connected_user_workspaces", options={ "method_prefix" = false })
     * @ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    public function getConnectedUserWorkspacesAction(User $user)
    {
        return array_map(function ($workspace) {
            return $this->workspaceManager->exportWorkspace($workspace);
        }, $this->workspaceManager->getWorkspacesByUser($user));
    }

    /**
     * @View(serializerGroups={"api_workspace"})
     * @SEC\PreAuthorize("canOpenAdminTool('workspace_management')")
     */
    public function getWorkspacesAction()
    {
        return $this->workspaceRepo->findAll();
    }

    /**
     * @View(serializerGroups={"api_workspace"})
     * @SEC\PreAuthorize("canOpenAdminTool('workspace_management')")
     */
    public function getWorkspaceAction(Workspace $workspace)
    {
        return $workspace;
    }

    /**
     * @View(serializerGroups={"api_workspace"})
     * @SEC\PreAuthorize("canOpenAdminTool('workspace_management')")
     */
    public function getWorkspaceAdditionalDatasAction(Workspace $workspace)
    {
        $datas = [];
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
     * @SEC\PreAuthorize("canOpenAdminTool('workspace_management')")
     */
    public function getNonPersonalWorkspacesAction()
    {
        return $this->workspaceRepo->findNonPersonalWorkspaces();
    }

    /**
     * @View(serializerGroups={"api_workspace"})
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
     * @View(serializerGroups={"api_workspace"})
     * @Put("workspace/{workspace}", name="put_workspace", options={ "method_prefix" = false })
     * @SEC\PreAuthorize("canOpenAdminTool('workspace_management')")
     * @ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    public function putWorkspaceAction(Workspace $workspace, User $user)
    {
        $workspaceType = new WorkspaceType($user);
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

    /**
     * @Get("/workspace/page/{page}/limit/{limit}/search", name="get_search_workspaces", options={ "method_prefix" = false })
     */
    public function getSearchWorkspacesAction($page, $limit)
    {
        return $this->finder->search(
          'Claroline\CoreBundle\Entity\Workspace\Workspace',
          $page,
          $limit,
          $this->container->get('request')->query->all()
        );
    }

    /**
     * @View(serializerGroups={"api_workspace"})
     */
    public function copyWorkspacesAction($isModel)
    {
        $workspaces = $this->container->get('claroline.manager.api_manager')->getParameters('ids', 'Claroline\CoreBundle\Entity\Workspace\Workspace');
        $newWorkspaces = [];
        $isModel = $isModel === 'true' ? 1 : 0;
        $this->om->startFlushSuite();

        foreach ($workspaces as $workspace) {
            $newWorkspace = new Workspace();
            $newWorkspace->setName($isModel ? '[MODEL] - '.$workspace->getName() : '[COPY] - '.$workspace->getName());
            $newWorkspace->setIsModel($isModel);
            $newWorkspace->setCode($isModel ? '[MODEL] - '.$workspace->getCode() : '[COPY] - '.$workspace->getCode());
            $newWorkspace = $this->workspaceManager->copy($workspace, $newWorkspace);
            $newWorkspaces[] = $newWorkspace;
        }

        $this->om->endFlushSuite();

        return $newWorkspaces;
    }

    /**
     * @View()
     * @SEC\PreAuthorize("canOpenAdminTool('workspace_management')")
     */
    public function deleteWorkspaceAction(Workspace $workspace)
    {
        $this->workspaceManager->deleteWorkspace($workspace);

        return ['success'];
    }

    public function deleteWorkspacesAction()
    {
        $workspaces = $this->container->get('claroline.manager.api_manager')->getParameters('ids', 'Claroline\CoreBundle\Entity\Workspace\Workspace');

        $this->om->startFlushSuite();

        foreach ($workspaces as $workspace) {
            $this->container->get('claroline.event.event_dispatcher')->dispatch('log', 'Log\LogWorkspaceDelete', [$workspace]);
            $this->workspaceManager->deleteWorkspace($workspace);
        }

        $this->om->endFlushSuite();

        return ['success'];
    }
}
