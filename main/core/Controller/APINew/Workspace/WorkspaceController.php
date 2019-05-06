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

use Claroline\AppBundle\Annotations\ApiDoc;
use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\AppBundle\Logger\JsonLogger;
use Claroline\AppBundle\Manager\File\TempFileManager;
use Claroline\CoreBundle\Controller\APINew\Model\HasGroupsTrait;
use Claroline\CoreBundle\Controller\APINew\Model\HasOrganizationsTrait;
use Claroline\CoreBundle\Controller\APINew\Model\HasRolesTrait;
use Claroline\CoreBundle\Controller\APINew\Model\HasUsersTrait;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Security\Utilities;
use Claroline\CoreBundle\Library\Utilities\FileUtilities;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CoreBundle\Manager\Workspace\TransferManager;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/workspace")
 */
class WorkspaceController extends AbstractCrudController
{
    use HasOrganizationsTrait;
    use HasRolesTrait;
    use HasUsersTrait;
    use HasGroupsTrait;

    private $tokenStorage;
    private $authorization;
    protected $resourceManager;
    protected $translator;
    protected $roleManager;
    protected $workspaceManager;
    private $utils;
    private $logDir;
    private $fileUtils;
    private $toolManager;

    /**
     * WorkspaceController constructor.
     *
     * @DI\InjectParams({
     *     "tokenStorage"     = @DI\Inject("security.token_storage"),
     *     "authorization"    = @DI\Inject("security.authorization_checker"),
     *     "resourceManager"  = @DI\Inject("claroline.manager.resource_manager"),
     *     "roleManager"      = @DI\Inject("claroline.manager.role_manager"),
     *     "toolManager"      = @DI\Inject("claroline.manager.tool_manager"),
     *     "translator"       = @DI\Inject("translator"),
     *     "workspaceManager" = @DI\Inject("claroline.manager.workspace_manager"),
     *     "utils"            = @DI\Inject("claroline.security.utilities"),
     *     "fileUtils"        = @DI\Inject("claroline.utilities.file"),
     *     "importer"         = @DI\Inject("claroline.manager.workspace.transfer"),
     *     "logDir"           = @DI\Inject("%claroline.param.workspace_log_dir%"),
     *     "tempFileManager"  = @DI\Inject("claroline.manager.temp_file")
     * })
     *
     * @param TokenStorageInterface         $tokenStorage
     * @param AuthorizationCheckerInterface $authorization
     * @param ResourceManager               $resourceManager
     * @param TranslatorInterface           $translator
     * @param RoleManager                   $roleManager
     * @param WorkspaceManager              $workspaceManager
     * @param TransferManager               $importer
     * @param Utilities                     $utils
     * @param FileUtilities                 $fileUtils
     * @param ToolManager                   $toolManager
     * @param TempFileManager               $tempFileManager
     * @param string                        $logDir
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorization,
        ResourceManager $resourceManager,
        TranslatorInterface $translator,
        RoleManager $roleManager,
        ToolManager $toolManager,
        WorkspaceManager $workspaceManager,
        Utilities $utils,
        FileUtilities $fileUtils,
        TransferManager $importer,
        TempFileManager $tempFileManager,
        $logDir
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authorization = $authorization;
        $this->importer = $importer;
        $this->resourceManager = $resourceManager;
        $this->translator = $translator;
        $this->roleManager = $roleManager;
        $this->workspaceManager = $workspaceManager;
        $this->toolManager = $toolManager;
        $this->utils = $utils;
        $this->logDir = $logDir;
        $this->fileUtils = $fileUtils;
        $this->tempFileManager = $tempFileManager;
    }

    public function getName()
    {
        return 'workspace';
    }

    /**
     * @ApiDoc(
     *     description="Create a forum",
     *     body={
     *         "schema":"$schema"
     *     }
     * )
     *
     * @param Request $request
     * @param string  $class
     *
     * @return JsonResponse
     */
    public function createAction(Request $request, $class)
    {
        $data = $this->decodeRequest($request);

        //THIS IS FOR WORKSPACE IMPORT FROM ARCHIVE. IT'S CURRENTLY NOT TESTED (AND THEREFORE BROKEN)
        if (isset($data['archive'])) {
            $workspace = $this->importer->create($data, new Workspace());
            $this->toolManager->addMissingWorkspaceTools($workspace);

            return new JsonResponse(
                $this->serializer->serialize($workspace, $this->options['get']),
                201
            );
        }

        /** @var Workspace $workspace */
        $workspace = $this->crud->create(
            $class,
            $data,
            [Options::LIGHT_COPY]
        );

        if (is_array($workspace)) {
            return new JsonResponse($workspace, 400);
        }

        $model = $workspace->getWorkspaceModel();
        $logFile = $this->getLogFile($workspace);
        $logger = new JsonLogger($logFile);
        $this->workspaceManager->setLogger($logger);
        $workspace = $this->workspaceManager->copy($model, $workspace, false);
        $workspace = $this->serializer->get(Workspace::class)->deserialize($data, $workspace);
        $logger->end();

        return new JsonResponse(
            $this->serializer->serialize($workspace, $this->options['get']),
            201
        );
    }

    /**
     * @ApiDoc(
     *     description="Copy an array of object of class $class.",
     *     queryString={
     *         {"name": "ids[]", "type": {"string", "integer"}, "description": "The object id or uuid."}
     *     }
     * )
     *
     * @param Request $request
     * @param string  $class
     *
     * @return JsonResponse
     */
    public function copyBulkAction(Request $request, $class)
    {
        //add params for the copy here
        $isModel = 1 === (int) $request->query->get('model') || 'true' === $request->query->get('model') ? true : false;

        $serializer = $this->serializer;
        $copies = [];

        foreach ($this->decodeIdsString($request, $class) as $workspace) {
            $new = new Workspace();
            $new->setCode($workspace->getCode().uniqid());
            $copies[] = $this->workspaceManager->copy($workspace, $new, $isModel);
        }

        return new JsonResponse(array_map(function ($copy) use ($serializer) {
            return $serializer->serialize($copy, $this->options['get']);
        }, $copies), 200);
    }

    /**
     * Gets the main workspace menu for the current user.
     *
     * @Route("/menu", name="apiv2_workspace_menu")
     * @Method("GET")
     *
     * @return JsonResponse
     */
    public function menuAction()
    {
        $workspaces = [];
        $personalWs = null;

        $user = null;
        $token = $this->tokenStorage->getToken();
        if ($token) {
            $user = $token->getUser();
        }

        if ($user instanceof User) {
            $personalWs = $user->getPersonalWorkspace();
            $workspaces = $this->workspaceManager->getRecentWorkspaceForUser($user, $this->utils->getRoles($token));
        }

        return new JsonResponse([
            'creatable' => $this->authorization->isGranted('CREATE', new Workspace()),
            'personal' => $personalWs ? $this->serializer->serialize($personalWs, [Options::SERIALIZE_MINIMAL]) : null,
            'history' => array_map(function (Workspace $workspace) {
                return $this->serializer->serialize($workspace, [Options::SERIALIZE_MINIMAL]);
            }, $workspaces),
        ]);
    }

    /**
     * @ApiDoc(
     *     description="List the user registration pending for workspace.",
     *     queryString={
     *         "$finder=Claroline\CoreBundle\Entity\User",
     *         {"name": "page", "type": "integer", "description": "The queried page."},
     *         {"name": "limit", "type": "integer", "description": "The max amount of objects per page."},
     *         {"name": "sortBy", "type": "string", "description": "Sort by the property if you want to."}
     *     },
     *     parameters={
     *         "id": {
     *              "type": {"string", "integer"},
     *              "description": "The workspace id or uuid"
     *          }
     *     }
     * )
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
     * @ApiDoc(
     *     description="Export the workspace as a zip archive.",
     *     parameters={
     *         "id": {
     *              "type": {"string", "integer"},
     *              "description": "The workspace id or uuid"
     *          }
     *     }
     * )
     * @Route(
     *    "/{id}/export",
     *    name="apiv2_workspace_export"
     * )
     * @Method("GET")
     * @ParamConverter("workspace", options={"mapping": {"id": "id"}})
     *
     * @param Request   $request
     * @param Workspace $workspace
     *
     * @return JsonResponse
     */
    public function exportAction(Request $request, Workspace $workspace)
    {
        $pathArch = $this->importer->export($workspace);
        $response = new BinaryFileResponse($pathArch);
        $response->headers->set('Content-Type', 'application/zip');

        return $response;
    }

    /**
     * @ApiDoc(
     *     description="Validate user registration pending for workspace.",
     *     queryString={
     *         {"name": "ids", "type": "array", "description": "the list of user uuids."}
     *     },
     *     parameters={
     *         "id": {
     *              "type": {"string", "integer"},
     *              "description": "The workspace uuid"
     *          }
     *     }
     * )
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
            $this->container->get('claroline.manager.workspace_user_queue_manager')->removeRegistration($pending);
        }

        return new JsonResponse($this->finder->search(
            'Claroline\CoreBundle\Entity\Workspace\WorkspaceRegistrationQueue',
            array_merge($request->query->all(), ['hiddenFilters' => ['workspace' => $workspace->getUuid()]])
        ));
    }

    /**
     * @ApiDoc(
     *     description="Remove user registration pending for workspace.",
     *     queryString={
     *         {"name": "ids", "type": "array", "description": "the list of user uuids."}
     *     },
     *     parameters={
     *         "id": {
     *              "type": {"string", "integer"},
     *              "description": "The workspace uuid"
     *          }
     *     }
     * )
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
            $this->container->get('claroline.manager.workspace_user_queue_manager')->removeRegistration($pending);
        }

        return new JsonResponse($this->finder->search(
            'Claroline\CoreBundle\Entity\Workspace\WorkspaceRegistrationQueue',
            array_merge($request->query->all(), ['hiddenFilters' => ['workspace' => $workspace->getUuid()]])
        ));
    }

    /**
     * @ApiDoc(
     *     description="Unregister users from workspace.",
     *     queryString={
     *         {"name": "ids", "type": "array", "description": "the list of user uuids."}
     *     },
     *     parameters={
     *         "id": {
     *              "type": {"string", "integer"},
     *              "description": "The workspace uuid"
     *          }
     *     }
     * )
     * @Route(
     *    "/{id}/users/unregistrate",
     *    name="apiv2_workspace_unregister_users"
     * )
     * @Method("DELETE")
     * @ParamConverter("workspace", options={"mapping": {"id": "uuid"}})
     *
     * @param Request   $request
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
     * @ApiDoc(
     *     description="Unregister groups from workspace.",
     *     queryString={
     *         {"name": "ids", "type": "array", "description": "the list of group uuids."}
     *     },
     *     parameters={
     *         "id": {
     *              "type": {"string", "integer"},
     *              "description": "The workspace uuid"
     *          }
     *     }
     * )
     * @Route(
     *    "/{id}/groups/unregistrate",
     *    name="apiv2_workspace_unregister_groups"
     * )
     * @Method("DELETE")
     * @ParamConverter("workspace", options={"mapping": {"id": "uuid"}})
     *
     * @param Request   $request
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
     * @ApiDoc(
     *     description="Remove workspaces.",
     *     queryString={
     *         {"name": "ids", "type": "array", "description": "the list of workspace uuids."}
     *     }
     * )
     *
     * @param Request $request
     * @param string  $class
     *
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, $class)
    {
        /** @var Workspace[] $workspaces */
        $workspaces = parent::decodeIdsString($request, Workspace::class);
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
            parent::deleteBulkAction($request, Workspace::class);

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
     * @ApiDoc(
     *     description="The manager list of a workspace.",
     *     queryString={
     *         "$finder=Claroline\CoreBundle\Entity\User&!role",
     *         {"name": "page", "type": "integer", "description": "The queried page."},
     *         {"name": "limit", "type": "integer", "description": "The max amount of objects per page."},
     *         {"name": "sortBy", "type": "string", "description": "Sort by the property if you want to."}
     *     },
     *     parameters={
     *         "id": {
     *              "type": {"string"},
     *              "description": "The workspace uuid"
     *          }
     *     }
     * )
     * @Route(
     *    "/{id}/managers",
     *    name="apiv2_workspace_list_managers"
     * )
     * @Method("GET")
     * @ParamConverter("workspace", options={"mapping": {"id": "uuid"}})
     *
     * @param Workspace $workspace
     * @param Request   $request
     *
     * @return JsonResponse
     */
    public function listManagersAction(Workspace $workspace, Request $request)
    {
        /** @var Role $role */
        $role = $this->container->get('claroline.manager.role_manager')->getManagerRole($workspace);

        return new JsonResponse($this->finder->search(
            'Claroline\CoreBundle\Entity\User',
            array_merge($request->query->all(), ['hiddenFilters' => ['role' => $role->getUuid()]]),
            [Options::IS_RECURSIVE]
        ));
    }

    /**
     * @ApiDoc(
     *     description="The list of registerable workspaces for the current security token.",
     *     queryString={
     *         "$finder",
     *         {"name": "page", "type": "integer", "description": "The queried page."},
     *         {"name": "limit", "type": "integer", "description": "The max amount of objects per page."},
     *         {"name": "sortBy", "type": "string", "description": "Sort by the property if you want to."}
     *     }
     * )
     * @Route(
     *    "/list/registerable",
     *    name="apiv2_workspace_list_registerable"
     * )
     * @Method("GET")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listRegisterableAction(Request $request)
    {
        return new JsonResponse($this->finder->search(
            'Claroline\CoreBundle\Entity\Workspace\Workspace',
            array_merge($request->query->all(), ['hiddenFilters' => [
                'displayable' => true,
                'model' => false,
                'selfRegistration' => true,
                'sameOrganization' => true,
            ]]),
            $this->getOptions()['list']
        ));
    }

    /**
     * @ApiDoc(
     *     description="The list of registered workspaces for the current security token.",
     *     queryString={
     *         "$finder=Claroline\CoreBundle\Entity\Workspace\Workspace&!user",
     *         {"name": "page", "type": "integer", "description": "The queried page."},
     *         {"name": "limit", "type": "integer", "description": "The max amount of objects per page."},
     *         {"name": "sortBy", "type": "string", "description": "Sort by the property if you want to."}
     *     }
     * )
     * @Route(
     *    "/list/registered",
     *    name="apiv2_workspace_registered_list"
     * )
     * @Method("GET")
     *
     * @param Request $request
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
     * @ApiDoc(
     *     description="The list of administrated workspaces for the current security token.",
     *     queryString={
     *         "$finder=Claroline\CoreBundle\Entity\Workspace\Workspace&!administrated",
     *         {"name": "page", "type": "integer", "description": "The queried page."},
     *         {"name": "limit", "type": "integer", "description": "The max amount of objects per page."},
     *         {"name": "sortBy", "type": "string", "description": "Sort by the property if you want to."}
     *     }
     * )
     * @Route(
     *    "/list/administrated",
     *    name="apiv2_administrated_list"
     * )
     * @Method("GET")
     *
     * @param Request $request
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
     * @ApiDoc(
     *     description="Register users in different workspaces.",
     *     queryString={
     *         {"name": "users", "type": "array", "description": "The list of user uuids."},
     *         {"name": "workspaces", "type": "array", "description": "The list of workspace uuids."},
     *     },
     *     parameters={
     *         "role": {
     *              "type": {"string"},
     *              "description": "The role translation key"
     *          }
     *     }
     * )
     * @Route(
     *    "/users/register/bulk/{role}",
     *    name="apiv2_workspace_bulk_register_users"
     * )
     * @Method("PATCH")
     *
     * @param string  $role
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function bulkRegisterUsersAction($role, Request $request)
    {
        $workspaces = $this->decodeQueryParam($request, 'Claroline\CoreBundle\Entity\Workspace\Workspace', 'workspaces');
        $users = $this->decodeQueryParam($request, 'Claroline\CoreBundle\Entity\User', 'users');

        foreach ($workspaces as $workspace) {
            $roleEntity = $this->om->getRepository(Role::class)
              ->findOneBy(['translationKey' => $role, 'workspace' => $workspace]);

            $this->crud->patch($roleEntity, 'user', Crud::COLLECTION_ADD, $users);
        }

        return new JsonResponse(array_map(function ($workspace) {
            return $this->serializer->serialize($workspace);
        }, $workspaces));
    }

    /**
     * @ApiDoc(
     *     description="Register users in different workspaces.",
     *     queryString={
     *         {"name": "groups", "type": "array", "description": "The list of group uuids."},
     *         {"name": "workspaces", "type": "array", "description": "The list of workspace uuids."},
     *     },
     *     parameters={
     *         "role": {
     *              "type": {"string"},
     *              "description": "The role translation key"
     *          }
     *     }
     * )
     * @Route(
     *    "/groups/register/bulk/{role}",
     *    name="apiv2_workspace_bulk_register_groups"
     * )
     * @Method("PATCH")
     *
     * @param string  $role
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function bulkRegisterGroupsAction($role, Request $request)
    {
        $workspaces = $this->decodeQueryParam($request, 'Claroline\CoreBundle\Entity\Workspace\Workspace', 'workspaces');
        $groups = $this->decodeQueryParam($request, 'Claroline\CoreBundle\Entity\Group', 'groups');

        foreach ($workspaces as $workspace) {
            $roleEntity = $this->om->getRepository(Role::class)
                ->findOneBy(['translationKey' => $role, 'workspace' => $workspace]);

            $this->crud->patch($roleEntity, 'group', Crud::COLLECTION_ADD, $groups);
        }

        return new JsonResponse(array_map(function ($workspace) {
            return $this->serializer->serialize($workspace);
        }, $workspaces));
    }

    /**
     * @ApiDoc(
     *     description="Get the list of common role translation keys between 2 workspaces.",
     *     queryString={
     *         {"name": "workspaces", "type": "array", "description": "The list of workspace uuids."},
     *     }
     * )
     * @Route(
     *    "/roles/common",
     *    name="apiv2_workspace_roles_common"
     * )
     * @Method("GET")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getCommonRolesAction(Request $request)
    {
        /** @var Workspace[] $workspaces */
        $workspaces = $this->decodeQueryParam($request, 'Claroline\CoreBundle\Entity\Workspace\Workspace', 'workspaces');

        $roles = [];
        if (1 === count($workspaces)) {
            $roles = $workspaces[0]->getRoles()->toArray();
        } else {
            $all = [];
            foreach ($workspaces as $workspace) {
                foreach ($workspace->getRoles() as $role) {
                    if (!isset($all[$role->getTranslationKey()])) {
                        $all[$role->getTranslationKey()] = [
                            'count' => 1,
                            'instance' => $role,
                        ];
                    } else {
                        ++$all[$role->getTranslationKey()]['count'];
                    }
                }
            }

            // only grab roles used by multiple ws
            foreach ($all as $role) {
                if (1 < $role['count']) {
                    $roles[] = $role['instance'];
                }
            }
        }

        return new JsonResponse(array_map(function (Role $role) {
            return $this->serializer->serialize($role);
        }, $roles));
    }

    /**
     * @ApiDoc(
     *     description="Unregister a user from a list of workspace.",
     *     queryString={
     *         {"name": "workspaces", "type": "array", "description": "The list of workspace uuids."},
     *     },
     *     parameters={
     *         "user": {
     *              "type": {"string"},
     *              "description": "The user uuid"
     *          }
     *     }
     * )
     * @Route("/unregister/{user}", name="apiv2_workspace_unregister")
     * @Method("DELETE")
     * @ParamConverter("user", class = "ClarolineCoreBundle:User",  options={"mapping": {"user": "uuid"}})
     *
     * @param User    $user
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function unregisterAction(User $user, Request $request)
    {
        $workspaces = $this->decodeQueryParam($request, 'Claroline\CoreBundle\Entity\Workspace\Workspace', 'workspaces');

        foreach ($workspaces as $workspace) {
            $this->workspaceManager->unregister($user, $workspace);
        }

        return new JsonResponse(array_map(function (Workspace $workspace) {
            return $this->serializer->serialize($workspace);
        }, $workspaces));
    }

    /**
     * @ApiDoc(
     *     description="Register a user in a list of workspace.",
     *     queryString={
     *         {"name": "workspaces", "type": "array", "description": "The list of workspace uuids."},
     *     },
     *     parameters={
     *         "user": {
     *              "type": {"string"},
     *              "description": "The user uuid"
     *          }
     *     }
     * )
     * @Route("/register/{user}", name="apiv2_workspace_register")
     * @Method("PATCH")
     * @ParamConverter("user", class = "ClarolineCoreBundle:User",  options={"mapping": {"user": "uuid"}})
     *
     * @param User    $user
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function registerAction(User $user, Request $request)
    {
        // If user is admin or registration validation is disabled, subscribe user
        //see WorkspaceParametersController::userSubscriptionAction
        /** @var Workspace[] $workspaces */
        $workspaces = $this->decodeQueryParam($request, 'Claroline\CoreBundle\Entity\Workspace\Workspace', 'workspaces');

        foreach ($workspaces as $workspace) {
            if ($this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN') || !$workspace->getRegistrationValidation()) {
                $this->workspaceManager->addUserAction($workspace, $user);
            } else {
                // Otherwise add user to validation queue if not already there
                if (!$this->workspaceManager->isUserInValidationQueue($workspace, $user)) {
                    $this->workspaceManager->addUserQueue($workspace, $user);
                }
            }
        }

        return new JsonResponse(array_map(function (Workspace $workspace) {
            return $this->serializer->serialize($workspace);
        }, $workspaces));
    }

    /**
     * @param Workspace $workspace
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
          'get' => ['id' => '^(?!.*(schema|copy|parameters|find|doc|menu\/)).*'],
        ];
    }

    /**
     * @Route(
     *    "/archive/upload",
     *    name="apiv2_workspace_upload_archive"
     * )
     * @Method("POST")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function uploadArchiveAction(Request $request)
    {
        $files = $request->files->all();

        foreach ($files as $file) {
            $object = $this->crud->create(
                PublicFile::class,
                [],
                ['file' => $file]
            );
        }

        $zip = new \ZipArchive();
        $zip->open($this->fileUtils->getPath($object));
        $json = $zip->getFromName('workspace.json');
        $zip->close();

        $data = json_decode($json, true);
        $data['archive'] = $this->serializer->serialize($object);

        return new JsonResponse($data);
    }

    /**
     * @Route(
     *    "/archive/fetch",
     *    name="apiv2_workspace_archive_fetch"
     * )
     * @Method("POST")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function fetchArchiveAction(Request $request)
    {
        $url = $request->request->get('url');

        $file = file_get_contents($url);
        $tmp = @tempnam('claro', '_zip');
        file_put_contents($tmp, $file);
        $file = new File($tmp);
        $object = $this->fileUtils->createFile($file);
        $archive = $this->serializer->serialize($object);
        $zip = new \ZipArchive();
        $zip->open($this->fileUtils->getPath($object));
        $json = $zip->getFromName('workspace.json');
        $zip->close();

        $data = json_decode($json, true);
        $data['archive'] = $archive;

        return new JsonResponse($data);
    }

    /**
     * @ApiDoc(
     *     description="List the roles of a workspace.",
     *     queryString={
     *         "$finder=Claroline\CoreBundle\Entity\Role&!workspace",
     *         {"name": "page", "type": "integer", "description": "The queried page."},
     *         {"name": "limit", "type": "integer", "description": "The max amount of objects per page."},
     *         {"name": "sortBy", "type": "string", "description": "Sort by the property if you want to."}
     *     },
     *     parameters={
     *         "id": {
     *              "type": {"string"},
     *              "description": "The workspace uuid"
     *          }
     *     }
     * )
     * @Route("/{id}/role")
     * @Method("GET")
     *
     * @param string  $id
     * @param string  $class
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listRolesAction($id, $class, Request $request)
    {
        return new JsonResponse(
            $this->finder->search(Role::class, array_merge(
                $request->query->all(),
                ['hiddenFilters' => ['workspace' => [$id]]]
            ))
        );
    }

    /**
     * @ApiDoc(
     *     description="List the configuratble roles of a workspace for the current security token.",
     *     queryString={
     *         "$finder=Claroline\CoreBundle\Entity\Role&!workspaceConfigurable",
     *         {"name": "page", "type": "integer", "description": "The queried page."},
     *         {"name": "limit", "type": "integer", "description": "The max amount of objects per page."},
     *         {"name": "sortBy", "type": "string", "description": "Sort by the property if you want to."}
     *     },
     *     parameters={
     *         "id": {
     *              "type": {"string"},
     *              "description": "The workspace uuid"
     *          }
     *     }
     * )
     * @Route(
     *    "/{id}/role/configurable",
     *    name="apiv2_workspace_list_roles_configurable"
     *)
     * @Method("GET")
     *
     * @param string  $id
     * @param string  $class
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listConfigurableRolesAction($id, Request $request)
    {
        return new JsonResponse(
            $this->finder->search(Role::class, array_merge(
                $request->query->all(),
                ['hiddenFilters' => ['workspaceConfigurable' => [$id]]]
            ))
        );
    }

    public function getClass()
    {
        return Workspace::class;
    }

    public function getOptions()
    {
        return [
            'list' => [Options::SERIALIZE_LIST],
        ];
    }
}
