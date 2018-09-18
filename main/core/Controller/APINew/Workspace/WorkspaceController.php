<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\APINew\Workspace;

use Claroline\AppBundle\Annotations\ApiMeta;
use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\AppBundle\Logger\JsonLogger;
use Claroline\CoreBundle\Controller\APINew\Model\HasGroupsTrait;
use Claroline\CoreBundle\Controller\APINew\Model\HasOrganizationsTrait;
use Claroline\CoreBundle\Controller\APINew\Model\HasRolesTrait;
use Claroline\CoreBundle\Controller\APINew\Model\HasUsersTrait;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @ApiMeta(class="Claroline\CoreBundle\Entity\Workspace\Workspace", ignore={})
 * @Route("/workspace")
 */
class WorkspaceController extends AbstractCrudController
{
    use HasOrganizationsTrait;
    use HasRolesTrait;
    use HasUsersTrait;
    use HasGroupsTrait;

    protected $resourceManager;
    protected $translator;
    protected $roleManager;
    protected $workspaceManager;

    /**
     * @DI\InjectParams({
     *     "resourceManager"  = @DI\Inject("claroline.manager.resource_manager"),
     *     "roleManager"      = @DI\Inject("claroline.manager.role_manager"),
     *     "translator"       = @DI\Inject("translator"),
     *     "workspaceManager" = @DI\Inject("claroline.manager.workspace_manager"),
     *     "logDir"           = @DI\Inject("%claroline.param.workspace_log_dir%")
     * })
     *
     * @param ResourceManager $resourceManager
     */
    public function __construct(
        ResourceManager $resourceManager,
        TranslatorInterface $translator,
        RoleManager $roleManager,
        WorkspaceManager $workspaceManager,
        $logDir
    ) {
        $this->resourceManager = $resourceManager;
        $this->translator = $translator;
        $this->roleManager = $roleManager;
        $this->workspaceManager = $workspaceManager;
        $this->logDir = $logDir;
    }

    public function getName()
    {
        return 'workspace';
    }

    /**
     * @param Request $request
     * @param string  $class
     *
     * @return JsonResponse
     */
    public function createAction(Request $request, $class)
    {
        $data = $this->decodeRequest($request);

        if (isset($data['model'])) {
            $modelId = $data['model'];
        } else {
            $modelId = 0;
        }

        $workspace = $this->crud->create(
            $class,
            $data,
            [Options::LIGHT_COPY]
        );

        if (is_array($workspace)) {
            return new JsonResponse($workspace, 400);
        }

        $model = $this->om->getRepository(Workspace::class)->findOneBy(['uuid' => $modelId]);

        if (!$model) {
            $model = $this->workspaceManager->getDefaultModel();
        }

        $logFile = $this->getLogFile($workspace);
        $logger = new JsonLogger($logFile);
        $this->workspaceManager->setLogger($logger);

        $this->workspaceManager->duplicateWorkspaceRoles($model, $workspace, $workspace->getCreator());
        $this->workspaceManager->duplicateOrderedTools($model, $workspace);
        $rootNode = $this->workspaceManager->duplicateRoot($model, $workspace, $workspace->getCreator());
        $resourceNodes = $this->resourceManager->getWorkspaceRoot($model)->getChildren()->toArray();
        $workspaceRoles = $this->workspaceManager->getArrayRolesByWorkspace($workspace);
        $resourceInfos = ['copies' => []];
        $this->workspaceManager->duplicateResources($resourceNodes, $workspaceRoles, $workspace->getCreator(), $rootNode, $resourceInfos);
        $homeTabs = $this->container->get('claroline.manager.home_tab_manager')->getHomeTabByWorkspace($model);

        $this->workspaceManager->duplicateHomeTabs($workspace, $homeTabs, $resourceInfos);

        $logger->end();

        return new JsonResponse(
            $this->serializer->serialize($workspace, $this->options['get']),
            201
        );
    }

    /**
     * @param Request $request
     * @param string  $class
     *
     * @return JsonResponse
     */
    public function copyBulkAction(Request $request, $class)
    {
        //add params for the copy here
        $this->options['copyBulk'] = 1 === (int) $request->query->get('model') || 'true' === $request->query->get('model') ?
          [Options::WORKSPACE_MODEL] : [];

        return parent::copyBulkAction($request, $class);
    }

    /**
     * @Route(
     *    "/{id}/user/pending",
     *    name="apiv2_workspace_list_pending"
     * )
     * @Method("GET")
     * @ParamConverter("workspace", options={"mapping": {"id": "uuid"}})
     *
     * @param Request   $request
     * @param Workspace $workspace
     *
     * @return JsonResponse
     */
    public function listPendingAction(Request $request, Workspace $workspace)
    {
        return new JsonResponse($this->finder->search(
            'Claroline\CoreBundle\Entity\Workspace\WorkspaceRegistrationQueue',
            array_merge($request->query->all(), ['hiddenFilters' => ['workspace' => $workspace->getUuid()]])
        ));
    }

    /**
     * @Route(
     *    "/{id}/registration/validate",
     *    name="apiv2_workspace_registration_validate"
     * )
     * @Method("PATCH")
     * @ParamConverter("workspace", options={"mapping": {"id": "uuid"}})
     *
     * @param Request   $request
     * @param Workspace $workspace
     *
     * @return JsonResponse
     */
    public function validateRegistrationAction(Request $request, Workspace $workspace)
    {
        $query = $request->query->all();
        $users = $this->om->findList('Claroline\CoreBundle\Entity\User', 'uuid', $query['ids']);

        foreach ($users as $user) {
            $pending = $this->om->getRepository('Claroline\CoreBundle\Entity\Workspace\WorkspaceRegistrationQueue')
              ->findOneBy(['user' => $user, 'workspace' => $workspace]);
            //maybe use the crud instead ? I don't know yet
            $this->container->get('claroline.manager.workspace_user_queue_manager')->validateRegistration($pending);
            $this->container->get('claroline.manager.workspace_user_queue_manager')->removeRegistrationQueue($pending);
        }

        return new JsonResponse($this->finder->search(
            'Claroline\CoreBundle\Entity\Workspace\WorkspaceRegistrationQueue',
            array_merge($request->query->all(), ['hiddenFilters' => ['workspace' => $workspace->getUuid()]])
        ));
    }

    /**
     * @Route(
     *    "/{id}/registration/remove",
     *    name="apiv2_workspace_registration_remove"
     * )
     * @Method("DELETE")
     * @ParamConverter("workspace", options={"mapping": {"id": "uuid"}})
     *
     * @param Request   $request
     * @param Workspace $workspace
     *
     * @return JsonResponse
     */
    public function removeRegistrationAction(Request $request, Workspace $workspace)
    {
        $query = $request->query->all();
        $users = $this->om->findList('Claroline\CoreBundle\Entity\User', 'uuid', $query['ids']);

        foreach ($users as $user) {
            $pending = $this->om->getRepository('Claroline\CoreBundle\Entity\Workspace\WorkspaceRegistrationQueue')
              ->findOneBy(['user' => $user, 'workspace' => $workspace]);
            $this->container->get('claroline.manager.workspace_user_queue_manager')->removeRegistrationQueue($pending);
        }

        return new JsonResponse($this->finder->search(
            'Claroline\CoreBundle\Entity\Workspace\WorkspaceRegistrationQueue',
            array_merge($request->query->all(), ['hiddenFilters' => ['workspace' => $workspace->getUuid()]])
        ));
    }

    /**
     * @Route(
     *    "/{id}/users/unregistrate",
     *    name="apiv2_workspace_unregister_users"
     * )
     * @Method("DELETE")
     * @ParamConverter("workspace", options={"mapping": {"id": "uuid"}})
     *
     * @param Workspace $workspace
     *
     * @return JsonResponse
     */
    public function unregisterUsersAction(Request $request, Workspace $workspace)
    {
        $query = $request->query->all();
        $users = $this->om->findList('Claroline\CoreBundle\Entity\User', 'uuid', $query['ids']);

        $this->om->startFlushSuite();

        foreach ($users as $user) {
            $this->container->get('claroline.manager.workspace_manager')->unregister($user, $workspace);
        }

        $this->om->endFlushSuite();

        return new JsonResponse('success');
    }

    /**
     * @Route(
     *    "/{id}/groups/unregistrate",
     *    name="apiv2_workspace_unregister_groups"
     * )
     * @Method("DELETE")
     * @ParamConverter("workspace", options={"mapping": {"id": "uuid"}})
     *
     * @param Workspace $workspace
     *
     * @return JsonResponse
     */
    public function unregisterGroupsAction(Request $request, Workspace $workspace)
    {
        $query = $request->query->all();
        $groups = $this->om->findList('Claroline\CoreBundle\Entity\Group', 'uuid', $query['ids']);

        $this->om->startFlushSuite();

        foreach ($groups as $group) {
            $this->container->get('claroline.manager.workspace_manager')->unregister($group, $workspace);
        }

        $this->om->endFlushSuite();

        return new JsonResponse('success');
    }

    /**
     * @param Request $request
     * @param string  $class
     *
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, $class)
    {
        $workspaces = parent::decodeIdsString($request, 'Claroline\CoreBundle\Entity\Workspace\Workspace');
        $errors = [];

        foreach ($workspaces as $workspace) {
            $notDeletableResources = $this->resourceManager->getNotDeletableResourcesByWorkspace($workspace);

            if (count($notDeletableResources)) {
                $errors[$workspace->getUuid()] = $this->translator->trans(
                    'workspace_not_deletable_resources_error_message',
                    ['%workspaceName%' => $workspace->getName()],
                    'platform'
                );
            }
        }
        if (empty($errors)) {
            parent::deleteBulkAction($request, 'Claroline\CoreBundle\Entity\Workspace\Workspace');

            return new JsonResponse('success', 200);
        } else {
            $validIds = [];
            $ids = $request->query->get('ids');

            foreach ($ids as $id) {
                if (!isset($errors[$id])) {
                    $validIds[] = $id;
                }
            }
            if (count($validIds) > 0) {
                $request->query->set('ids', $validIds);
                parent::deleteBulkAction($request, 'Claroline\CoreBundle\Entity\Workspace\Workspace');
            }

            return new JsonResponse(['errors' => $errors], 422);
        }
    }

    /**
     * @Route(
     *    "/{id}/managers",
     *    name="apiv2_workspace_list_managers"
     * )
     * @Method("GET")
     * @ParamConverter("workspace", options={"mapping": {"id": "uuid"}})
     *
     * @param Workspace $workspace
     *
     * @return JsonResponse
     */
    public function listManagersAction(Workspace $workspace, Request $request)
    {
        $role = $this->container->get('claroline.manager.role_manager')->getManagerRole($workspace);

        return new JsonResponse($this->finder->search(
            'Claroline\CoreBundle\Entity\User',
            array_merge($request->query->all(), ['hiddenFilters' => ['role' => $role->getUuid()]]),
            [Options::IS_RECURSIVE]
        ));
    }

    /**
     * @Route(
     *    "/list/registerable",
     *    name="apiv2_workspace_list_registerable"
     * )
     * @Method("GET")
     *
     * For anonymous
     *
     * @return JsonResponse
     */
    public function listRegisterableAction(Request $request)
    {
        return new JsonResponse($this->finder->search(
            'Claroline\CoreBundle\Entity\Workspace\Workspace',
            array_merge($request->query->all(), ['hiddenFilters' => ['displayable' => true, 'selfRegistration' => true]]),
            $this->getOptions()['list']
        ));
    }

    /**
     * @Route(
     *    "/list/registerable",
     *    name="apiv2_workspace_displayable_list"
     * )
     * @Method("GET")
     *
     * @return JsonResponse
     */
    public function listDisplaybleAction(Request $request)
    {
        return new JsonResponse($this->finder->search(
            'Claroline\CoreBundle\Entity\Workspace\Workspace',
            array_merge($request->query->all(), ['hiddenFilters' => ['displayable' => true, 'model' => false, 'sameOrganization' => true]]),
            $this->getOptions()['list']
        ));
    }

    /**
     * @Route(
     *    "/list/registered",
     *    name="apiv2_workspace_registered_list"
     * )
     * @Method("GET")
     *
     * @param Workspace $workspace
     *
     * @return JsonResponse
     */
    public function listRegisteredAction(Request $request)
    {
        return new JsonResponse($this->finder->search(
            'Claroline\CoreBundle\Entity\Workspace\Workspace',
            array_merge($request->query->all(), ['hiddenFilters' => ['user' => $this->container->get('security.token_storage')->getToken()->getUser()->getId()]]),
            $this->getOptions()['list']
        ));
    }

    /**
     * @Route(
     *    "/list/administrated",
     *    name="apiv2_administrated_list"
     * )
     * @Method("GET")
     *
     * @return JsonResponse
     */
    public function listAdministratedAction(Request $request)
    {
        return new JsonResponse($this->finder->search(
            'Claroline\CoreBundle\Entity\Workspace\Workspace',
            array_merge($request->query->all(), ['hiddenFilters' => ['administrated' => true]]),
            $this->getOptions()['list']
        ));
    }

    /**
     * @Route(
     *    "/users/register/bulk/{role}",
     *    name="apiv2_workspace_bulk_register_users"
     * )
     * @Method("PATCH")
     *
     * @return JsonResponse
     */
    public function bulkRegisterUsersAction($role, Request $request)
    {
        $workspaces = $this->decodeQueryParam($request, 'Claroline\CoreBundle\Entity\Workspace\Workspace', 'workspaces');
        $users = $this->decodeQueryParam($request, 'Claroline\CoreBundle\Entity\User', 'users');

        foreach ($workspaces as $workspace) {
            if ('collaborator' === $role) {
                $role = $this->roleManager->getCollaboratorRole($workspace);
            } elseif ('manager' === $role) {
                $role = $this->roleManager->getManagerRole($workspace);
            } else {
                throw new \Exception('The role '.$role.' does not exists');
            }

            $this->crud->patch($role, 'user', Crud::COLLECTION_ADD, $users);
        }

        return new JsonResponse(array_map(function ($workspace) {
            return $this->serializer->serialize($workspace);
        }, $workspaces));
    }

    /**
     * @Route(
     *    "/groups/register/bulk/{role}",
     *    name="apiv2_workspace_bulk_register_groups"
     * )
     * @Method("PATCH")
     *
     * @param Workspace $workspace
     *
     * @return JsonResponse
     */
    public function bulkRegisterGroupsAction($role, Request $request)
    {
        $workspaces = $this->decodeQueryParam($request, 'Claroline\CoreBundle\Entity\Workspace\Workspace', 'workspaces');
        $groups = $this->decodeQueryParam($request, 'Claroline\CoreBundle\Entity\Group', 'groups');

        foreach ($workspaces as $workspace) {
            if ('collaborator' === $role) {
                $role = $this->roleManager->getCollaboratorRole($workspace);
            } elseif ('manager' === $role) {
                $role = $this->roleManager->getManagerRole($workspace);
            } else {
                throw new \Exception('The role '.$role.' does not exists');
            }

            $this->crud->patch($role, 'group', Crud::COLLECTION_ADD, $groups);
        }

        return new JsonResponse(array_map(function ($workspace) {
            return $this->serializer->serialize($workspace);
        }, $workspaces));
    }

    /**
     * @Route("/{workspace}/unregister/{user}", name="apiv2_workspace_unregister")
     * @Method("DELETE")
     * @ParamConverter("user", class = "ClarolineCoreBundle:User",  options={"mapping": {"user": "uuid"}})
     * @ParamConverter("workspace", class = "ClarolineCoreBundle:Workspace\Workspace",  options={"mapping": {"workspace": "uuid"}})
     */
    public function unregisterAction(Workspace $workspace, User $user)
    {
        $this->workspaceManager->unregister($user, $workspace);

        return new JsonResponse($this->serializer->serialize($workspace));
    }

    /**
     * @Route("/{workspace}/register/{user}", name="apiv2_workspace_register")
     * @Method("PATCH")
     * @ParamConverter("user", class = "ClarolineCoreBundle:User",  options={"mapping": {"user": "uuid"}})
     * @ParamConverter("workspace", class = "ClarolineCoreBundle:Workspace\Workspace",  options={"mapping": {"workspace": "uuid"}})
     */
    public function registerAction(Workspace $workspace, User $user)
    {
        // If user is admin or registration validation is disabled, subscribe user
        //see WorkspaceParametersController::userSubscriptionAction

        if ($this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN') || !$workspace->getRegistrationValidation()) {
            $this->workspaceManager->addUserAction($workspace, $user);
        } else {
            // Otherwise add user to validation queue if not already there
            if (!$this->workspaceManager->isUserInValidationQueue($workspace, $user)) {
                $this->workspaceManager->addUserQueue($workspace, $user);
            }
        }

        return new JsonResponse($this->serializer->serialize($workspace));
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    private function getLogFile(Workspace $workspace)
    {
        $fs = new Filesystem();
        $fs->mkDir($this->logDir);

        return $this->logDir.DIRECTORY_SEPARATOR.$workspace->getCode().'.json';
    }

    /**
     * @return array
     */
    protected function getRequirements()
    {
        return [
          'get' => ['id' => '^(?!.*(schema|copy|parameters|find|doc|\/)).*'],
        ];
    }

    /**
     * @Route(
     *    "/{id}/management/roles",
     *    name="apiv2_workspace_management_roles_list"
     * )
     * @Method("GET")
     * @ParamConverter("workspace", options={"mapping": {"id": "uuid"}})
     *
     * @param Request   $request
     * @param Workspace $workspace
     *
     * @return JsonResponse
     */
    public function rolesListAction(Request $request, Workspace $workspace)
    {
        return new JsonResponse(
            $this->finder->search('Claroline\CoreBundle\Entity\Role', array_merge(
                $request->query->all(),
                ['hiddenFilters' => [
                    'workspace' => [$workspace->getUuid()],
                    'roleNames' => ['ROLE_ANONYMOUS', 'ROLE_USER'],
                ]]
            ))
        );
    }

    public function getOptions()
    {
        return [
            'list' => [Options::SERIALIZE_LIST],
        ];
    }
}
