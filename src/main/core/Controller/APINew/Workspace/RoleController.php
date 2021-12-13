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
use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Workspace\Shortcuts;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\Tool\ToolManager;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Claroline\CoreBundle\Repository\Tool\OrderedToolRepository;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/workspace/{workspace}/role")
 * @EXT\ParamConverter("workspace", class="ClarolineCoreBundle:Workspace\Workspace", options={"mapping": {"workspace": "uuid"}})
 */
class RoleController
{
    use PermissionCheckerTrait;
    use RequestDecoderTrait;

    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var FinderProvider */
    private $finder;
    /** @var SerializerProvider */
    private $serializer;
    /** @var WorkspaceManager */
    private $workspaceManager;
    /** @var ToolManager */
    private $toolManager;
    /** @var OrderedToolRepository */
    private $orderedToolRepo;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om,
        FinderProvider $finder,
        SerializerProvider $serializer,
        WorkspaceManager $workspaceManager,
        ToolManager $toolManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authorization = $authorization;
        $this->finder = $finder;
        $this->serializer = $serializer;
        $this->workspaceManager = $workspaceManager;
        $this->toolManager = $toolManager;

        $this->orderedToolRepo = $om->getRepository(OrderedTool::class);
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
     * @Route("/configurable", name="apiv2_workspace_list_roles_configurable", methods={"GET"})
     */
    public function listConfigurableAction(Workspace $workspace, Request $request): JsonResponse
    {
        $this->checkPermission('OPEN', $workspace, [], true);

        return new JsonResponse(
            $this->finder->search(Role::class, array_merge(
                $request->query->all(),
                ['hiddenFilters' => ['workspaceConfigurable' => [$workspace->getUuid()]]]
            ))
        );
    }

    /**
     * Manages workspace tools accesses for a Role.
     *
     * @Route("/{role}/tools", name="apiv2_workspace_tools_set", methods={"PUT"})
     * @EXT\ParamConverter("role", class="ClarolineCoreBundle:Role", options={"mapping": {"role": "uuid"}})
     */
    public function setToolRightsAction(Workspace $workspace, Role $role, Request $request): JsonResponse
    {
        $this->checkPermission(['community', 'edit'], $workspace, [], true);

        $rightsData = $this->decodeRequest($request);

        if ($rightsData) {
            foreach ($rightsData as $toolName => $toolRights) {
                $orderedTool = $this->orderedToolRepo->findOneByNameAndWorkspace($toolName, $workspace);
                if ($orderedTool) {
                    $this->toolManager->setPermissions($toolRights, $orderedTool, $role);
                }
            }
        }

        return new JsonResponse();
    }

    /**
     * @Route("/{role}/shortcuts", name="apiv2_workspace_shortcuts_list", methods={"GET"})
     * @EXT\ParamConverter("role", class="ClarolineCoreBundle:Role", options={"mapping": {"role": "uuid"}})
     */
    public function listShortcutsAction(Workspace $workspace, Role $role): JsonResponse
    {
        $this->checkPermission('OPEN', $workspace, [], true);

        $roleShortcuts = $this->workspaceManager->getShortcuts($workspace, [$role->getName()]);

        return new JsonResponse(array_map(function (Shortcuts $shortcuts) {
            return $this->serializer->serialize($shortcuts);
        }, $roleShortcuts));
    }

    /**
     * @Route("/{role}/shortcuts/add", name="apiv2_workspace_shortcuts_add", methods={"PUT"})
     * @EXT\ParamConverter("role", class="ClarolineCoreBundle:Role", options={"mapping": {"role": "uuid"}})
     * @ApiDoc(
     *     description="Adds shortcuts to a workspace for a given role.",
     *     parameters={
     *         {"name": "workspace", "type": {"string"}, "description": "The workspace uuid"},
     *         {"name": "role", "type": {"string"}, "description": "The role uuid"}
     *     }
     * )
     */
    public function addShortcutsAction(Workspace $workspace, Role $role, Request $request): JsonResponse
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
     * @Route("/{role}/shortcuts/remove", name="apiv2_workspace_shortcut_remove", methods={"PUT"})
     * @EXT\ParamConverter("role", class="ClarolineCoreBundle:Role", options={"mapping": {"role": "uuid"}})
     * @ApiDoc(
     *     description="Removes a shortcut from a workspace for a given role.",
     *     parameters={
     *         {"name": "workspace", "type": {"string"}, "description": "The workspace uuid"},
     *         {"name": "role", "type": {"string"}, "description": "The role uuid"}
     *     }
     * )
     */
    public function removeShortcutsAction(Workspace $workspace, Role $role, Request $request): JsonResponse
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
}
