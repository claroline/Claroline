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
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\AppBundle\Logger\JsonLogger;
use Claroline\AppBundle\Manager\File\TempFileManager;
use Claroline\CoreBundle\Controller\APINew\Model\HasGroupsTrait;
use Claroline\CoreBundle\Controller\APINew\Model\HasOrganizationsTrait;
use Claroline\CoreBundle\Controller\APINew\Model\HasRolesTrait;
use Claroline\CoreBundle\Controller\APINew\Model\HasUsersTrait;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Shortcuts;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Security\Utilities;
use Claroline\CoreBundle\Library\Utilities\FileUtilities;
use Claroline\CoreBundle\Manager\LogConnectManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CoreBundle\Manager\Workspace\TransferManager;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
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
    private $resourceManager;
    private $translator;
    private $roleManager;
    private $workspaceManager;
    private $importer;
    private $utils;
    private $logDir;
    private $fileUtils;
    private $toolManager;
    private $tempFileManager;
    /** @var LogConnectManager */
    private $logConnectManager;

    /**
     * WorkspaceController constructor.
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
     * @param LogConnectManager             $logConnectManager
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
        $logDir,
        LogConnectManager $logConnectManager
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
        $this->logConnectManager = $logConnectManager;
    }

    public function getName()
    {
        return 'workspace';
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
     *    name="apiv2_workspace_list_registered"
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
            array_merge($request->query->all(), ['hiddenFilters' => ['user' => $this->tokenStorage->getToken()->getUser()->getId()]]),
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
     *    name="apiv2_workspace_list_managed"
     * )
     * @Method("GET")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listManagedAction(Request $request)
    {
        return new JsonResponse($this->finder->search(
            Workspace:: class,
            array_merge($request->query->all(), ['hiddenFilters' => ['administrated' => true, 'model' => false]]),
            $this->getOptions()['list']
        ));
    }

    /**
     * @ApiDoc(
     *     description="The list of workspace models for the current security token.",
     *     queryString={
     *         "$finder=Claroline\CoreBundle\Entity\Workspace\Workspace&!model",
     *         {"name": "page", "type": "integer", "description": "The queried page."},
     *         {"name": "limit", "type": "integer", "description": "The max amount of objects per page."},
     *         {"name": "sortBy", "type": "string", "description": "Sort by the property if you want to."}
     *     }
     * )
     * @Route(
     *    "/list/model",
     *    name="apiv2_workspace_list_model"
     * )
     * @Method("GET")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listModelAction(Request $request)
    {
        return new JsonResponse($this->finder->search(
            Workspace:: class,
            array_merge($request->query->all(), ['hiddenFilters' => ['model' => true]]),
            $this->getOptions()['list']
        ));
    }

    /**
     * @ApiDoc(
     *     description="Create a workspace",
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
            /** @var Workspace $workspace */
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

        /** @var Workspace $workspace */
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
     * @ApiDoc(
     *     description="Export the workspace as a zip archive.",
     *     parameters={
     *         {"name": "id", "type": {"string", "integer"},  "description": "The workspace id or uuid"}
     *     }
     * )
     * @Route(
     *    "/{id}/export",
     *    name="apiv2_workspace_export"
     * )
     * @Method("GET")
     * @ParamConverter("workspace", options={"mapping": {"id": "id"}})
     *
     * @param Workspace $workspace
     *
     * @return BinaryFileResponse
     */
    public function exportAction(Workspace $workspace)
    {
        $pathArch = $this->importer->export($workspace);
        $response = new BinaryFileResponse($pathArch);
        $response->headers->set('Content-Type', 'application/zip');

        return $response;
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
            return parent::deleteBulkAction($request, Workspace::class);
        }

        $validIds = [];
        $ids = $request->query->get('ids');

        foreach ($ids as $id) {
            if (!isset($errors[$id])) {
                $validIds[] = $id;
            }
        }
        if (count($validIds) > 0) {
            $request->query->set('ids', $validIds);
            parent::deleteBulkAction($request, Workspace::class);
        }

        return new JsonResponse(['errors' => $errors], 422);
    }

    /**
     * @ApiDoc(
     *     description="Archive workspaces.",
     *     queryString={
     *         {"name": "ids", "type": "array", "description": "the list of workspace uuids."}
     *     }
     * )
     * @Route("/archive", name="apiv2_workspace_archive")
     * @Method("PUT")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function archiveBulkAction(Request $request)
    {
        $processed = [];

        /** @var Workspace[] $workspaces */
        $workspaces = parent::decodeIdsString($request, Workspace::class);
        foreach ($workspaces as $workspace) {
            if ($this->authorization->isGranted('EDIT', $workspace) && !$workspace->isModel() && !$workspace->isArchived()) {
                $processed[] = $this->workspaceManager->archive($workspace);
            }
        }

        $this->om->flush();

        return new JsonResponse(array_map(function (Workspace $workspace) {
            return $this->serializer->serialize($workspace);
        }, $processed));
    }

    /**
     * @ApiDoc(
     *     description="Unarchive workspaces.",
     *     queryString={
     *         {"name": "ids", "type": "array", "description": "the list of workspace uuids."}
     *     }
     * )
     * @Route("/unarchive", name="apiv2_workspace_unarchive")
     * @Method("PUT")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function unarchiveBulkAction(Request $request)
    {
        $processed = [];

        /** @var Workspace[] $workspaces */
        $workspaces = parent::decodeIdsString($request, Workspace::class);
        foreach ($workspaces as $workspace) {
            if ($this->authorization->isGranted('EDIT', $workspace) && !$workspace->isModel() && $workspace->isArchived()) {
                $processed[] = $this->workspaceManager->unarchive($workspace);
            }
        }

        $this->om->flush();

        return new JsonResponse(array_map(function (Workspace $workspace) {
            return $this->serializer->serialize($workspace);
        }, $processed));
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
     *         {"name": "id", "type": {"string", "integer"},  "description": "The workspace id or uuid"}
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
        $workspaces = $this->decodeIdsString($request, 'Claroline\CoreBundle\Entity\Workspace\Workspace', 'workspaces');

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
     * @ApiDoc(
     *     description="List the configurable roles of a workspace for the current security token.",
     *     queryString={
     *         "$finder=Claroline\CoreBundle\Entity\Role&!workspaceConfigurable",
     *         {"name": "page", "type": "integer", "description": "The queried page."},
     *         {"name": "limit", "type": "integer", "description": "The max amount of objects per page."},
     *         {"name": "sortBy", "type": "string", "description": "Sort by the property if you want to."}
     *     },
     *     parameters={
     *         {"name": "id", "type": {"string", "integer"},  "description": "The workspace id or uuid"}
     *     }
     * )
     * @Route(
     *    "/{id}/role/configurable",
     *    name="apiv2_workspace_list_roles_configurable"
     *)
     * @Method("GET")
     *
     * @param string  $id
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

    /**
     * @ApiDoc(
     *     description="Adds shortcuts to a workspace for a given role.",
     *     parameters={
     *         {"name": "workspace", "type": {"string"}, "description": "The workspace uuid"},
     *         {"name": "role", "type": {"string"}, "description": "The role uuid"}
     *     }
     * )
     * @Route(
     *     "/{workspace}/role/{role}/shortcuts/add",
     *     name="apiv2_workspace_shortcuts_add"
     * )
     * @Method("PUT")
     * @ParamConverter(
     *     "workspace",
     *     class = "ClarolineCoreBundle:Workspace\Workspace",
     *     options={"mapping": {"workspace": "uuid"}}
     * )
     * @ParamConverter(
     *     "role",
     *     class = "ClarolineCoreBundle:Role",
     *     options={"mapping": {"role": "uuid"}}
     * )
     *
     * @param Workspace $workspace
     * @param Role      $role
     * @param Request   $request
     *
     * @return JsonResponse
     */
    public function shortcutsAddAction(Workspace $workspace, Role $role, Request $request)
    {
        $data = $this->decodeRequest($request);

        if (isset($data['shortcuts']) && 0 < count($data['shortcuts'])) {
            $this->workspaceManager->addShortcuts($workspace, $role, $data['shortcuts']);
        }
        $shortcuts = array_values(array_map(function (Shortcuts $shortcuts) {
            return $this->serializer->serialize($shortcuts);
        }, $workspace->getShortcuts()->toArray()));

        return new JsonResponse($shortcuts);
    }

    /**
     * @ApiDoc(
     *     description="Removes a shortcut from a workspace for a given role.",
     *     parameters={
     *         {"name": "workspace", "type": {"string"}, "description": "The workspace uuid"},
     *         {"name": "role", "type": {"string"}, "description": "The role uuid"}
     *     }
     * )
     * @Route(
     *     "/{workspace}/role/{role}/shortcut/remove",
     *     name="apiv2_workspace_shortcut_remove"
     * )
     * @Method("PUT")
     * @ParamConverter(
     *     "workspace",
     *     class = "ClarolineCoreBundle:Workspace\Workspace",
     *     options={"mapping": {"workspace": "uuid"}}
     * )
     * @ParamConverter(
     *     "role",
     *     class = "ClarolineCoreBundle:Role",
     *     options={"mapping": {"role": "uuid"}}
     * )
     *
     * @param Workspace $workspace
     * @param Role      $role
     * @param Request   $request
     *
     * @return JsonResponse
     */
    public function shortcutRemoveAction(Workspace $workspace, Role $role, Request $request)
    {
        $data = $this->decodeRequest($request);

        if (isset($data['type']) && 0 < count($data['name'])) {
            $this->workspaceManager->removeShortcut($workspace, $role, $data['type'], $data['name']);
        }
        $shortcuts = array_values(array_map(function (Shortcuts $shortcuts) {
            return $this->serializer->serialize($shortcuts);
        }, $workspace->getShortcuts()->toArray()));

        return new JsonResponse($shortcuts);
    }

    /**
     * @ApiDoc(
     *     description="Dispatches all actions that has to be done when closing a workspace.",
     *     parameters={
     *         {"name": "id", "type": {"string"}, "description": "The workspace uuid"}
     *     }
     * )
     * @Route(
     *     "/{slug}/close",
     *     name="apiv2_workspace_close"
     * )
     * @Method("PUT")
     * @ParamConverter(
     *     "workspace",
     *     class = "ClarolineCoreBundle:Workspace\Workspace",
     *     options={"mapping": {"slug": "slug"}}
     * )
     * @ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     *
     * @param Workspace $workspace
     * @param User      $user
     *
     * @return JsonResponse
     */
    public function closeAction(Workspace $workspace, User $user = null)
    {
        if ($user) {
            $this->logConnectManager->computeWorkspaceDuration($user, $workspace);
        }

        return new JsonResponse();
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

    /**
     * @return array
     */
    protected function getRequirements()
    {
        return [
            'get' => ['id' => '^(?!.*(schema|copy|parameters|find|doc|menu\/)).*'],
        ];
    }
}
