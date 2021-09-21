<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\EvaluationBundle\Controller;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Requirements;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\EvaluationBundle\Manager\WorkspaceRequirementsManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/workspace")
 *
 * @todo use AbstractCrudController
 */
class WorkspaceRequirementsController
{
    use RequestDecoderTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var ObjectManager */
    private $om;
    /** @var FinderProvider */
    private $finder;
    /** @var SerializerProvider */
    private $serializer;
    /** @var WorkspaceRequirementsManager */
    private $requirementsManager;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om,
        FinderProvider $finder,
        SerializerProvider $serializer,
        WorkspaceRequirementsManager $requirementsManager
    ) {
        $this->om = $om;
        $this->authorization = $authorization;
        $this->finder = $finder;
        $this->serializer = $serializer;
        $this->requirementsManager = $requirementsManager;
    }

    /**
     * @Route("/{workspace}/requirements/{type}/list", name="apiv2_workspace_requirements_list", methods={"GET"})
     * @ParamConverter("workspace", options={"mapping": {"workspace": "uuid"}})
     */
    public function listAction(Workspace $workspace, string $type, Request $request): JsonResponse
    {
        if (!$this->authorization->isGranted(['dashboard', 'OPEN'], $workspace)) {
            throw new AccessDeniedException();
        }
        $params = $request->query->all();

        if (!isset($params['hiddenFilters'])) {
            $params['hiddenFilters'] = [];
        }
        $params['hiddenFilters']['workspace'] = $workspace->getUuid();

        if ('role' === $type) {
            $params['hiddenFilters']['withRole'] = true;
        } elseif ('user' === $type) {
            $params['hiddenFilters']['withUser'] = true;
        }

        return new JsonResponse($this->finder->search(Requirements::class, $params, [Options::SERIALIZE_MINIMAL]));
    }

    /**
     * @Route("/{workspace}/requirements/{type}/create", name="apiv2_workspace_requirements_create", methods={"PUT"})
     * @ParamConverter("workspace", options={"mapping": {"workspace": "uuid"}})
     */
    public function createAction(Workspace $workspace, string $type, Request $request): JsonResponse
    {
        if (!$this->authorization->isGranted(['dashboard', 'OPEN'], $workspace)) {
            throw new AccessDeniedException();
        }
        if ('role' === $type) {
            $roles = $this->decodeIdsString($request, Role::class);
            $this->requirementsManager->createRolesRequirements($workspace, $roles);
        } elseif ('user' === $type) {
            $users = $this->decodeIdsString($request, User::class);
            $this->requirementsManager->createUsersRequirements($workspace, $users);
        }

        return new JsonResponse();
    }

    /**
     * @Route("/{workspace}/requirements/delete", name="apiv2_workspace_requirements_delete", methods={"DELETE"})
     * @ParamConverter("workspace", options={"mapping": {"workspace": "uuid"}})
     */
    public function deleteBulkAction(Workspace $workspace, Request $request): JsonResponse
    {
        if (!$this->authorization->isGranted(['dashboard', 'OPEN'], $workspace)) {
            throw new AccessDeniedException();
        }
        $requirementsToDelete = $this->decodeIdsString($request, Requirements::class);
        $this->requirementsManager->deleteMultipleRequirements($requirementsToDelete);

        return new JsonResponse();
    }

    /**
     * @Route("/requirements/{requirements}/fetch", name="apiv2_workspace_requirements_fetch", methods={"GET"})
     * @ParamConverter("requirements", options={"mapping": {"requirements": "uuid"}})
     */
    public function requirementsFetchAction(Requirements $requirements): JsonResponse
    {
        if (!$this->authorization->isGranted(['dashboard', 'OPEN'], $requirements->getWorkspace())) {
            throw new AccessDeniedException();
        }

        return new JsonResponse($this->serializer->serialize($requirements));
    }

    /**
     * @Route("/requirements/resource/{resourceNode}/{type}/list", name="apiv2_workspace_requirements_resource_list", methods={"GET"})
     * @ParamConverter("resourceNode", options={"mapping": {"resourceNode": "uuid"}})
     */
    public function resourceListAction(ResourceNode $resourceNode, string $type, Request $request): JsonResponse
    {
        $workspace = $resourceNode->getWorkspace();

        if (!$this->authorization->isGranted(['dashboard', 'OPEN'], $workspace)) {
            throw new AccessDeniedException();
        }
        $params = $request->query->all();

        if (!isset($params['hiddenFilters'])) {
            $params['hiddenFilters'] = [];
        }
        $params['hiddenFilters']['workspace'] = $workspace->getUuid();
        $params['hiddenFilters']['resource'] = $resourceNode->getUuid();

        if ('role' === $type) {
            $params['hiddenFilters']['withRole'] = true;
        } elseif ('user' === $type) {
            $params['hiddenFilters']['withUser'] = true;
        }

        return new JsonResponse($this->finder->search(Requirements::class, $params, [Options::SERIALIZE_MINIMAL]));
    }

    /**
     * @Route("/requirements/{requirements}/resources/add", name="apiv2_workspace_requirements_resources_add", methods={"PUT"})
     * @ParamConverter("requirements", options={"mapping": {"requirements": "uuid"}})
     */
    public function resourcesRequirementsAddAction(Requirements $requirements, Request $request): JsonResponse
    {
        if (!$this->authorization->isGranted(['dashboard', 'OPEN'], $requirements->getWorkspace())) {
            throw new AccessDeniedException();
        }
        $resourceNodes = $this->decodeIdsString($request, ResourceNode::class);
        $updatedRequirements = $this->requirementsManager->addResourcesToRequirements($requirements, $resourceNodes);

        return new JsonResponse($this->serializer->serialize($updatedRequirements));
    }

    /**
     * @Route("/requirements/{requirements}/resources/remove", name="apiv2_workspace_requirements_resources_remove", methods={"DELETE"})
     * @ParamConverter("requirements", options={"mapping": {"requirements": "uuid"}})
     */
    public function resourcesRequirementsRemoveAction(Requirements $requirements, Request $request): JsonResponse
    {
        if (!$this->authorization->isGranted(['dashboard', 'OPEN'], $requirements->getWorkspace())) {
            throw new AccessDeniedException();
        }
        $resourceNodes = $this->decodeIdsString($request, ResourceNode::class);
        $updatedRequirements = $this->requirementsManager->removeResourcesFromRequirements($requirements, $resourceNodes);

        return new JsonResponse($this->serializer->serialize($updatedRequirements));
    }

    /**
     * @Route("/requirements/resource/{resourceNode}/remove", name="apiv2_workspace_requirements_resource_remove", methods={"DELETE"})
     * @ParamConverter("resourceNode", options={"mapping": {"resourceNode": "uuid"}})
     */
    public function resourceRequirementsRemoveAction(ResourceNode $resourceNode, Request $request): JsonResponse
    {
        if (!$this->authorization->isGranted(['dashboard', 'OPEN'], $resourceNode->getWorkspace())) {
            throw new AccessDeniedException();
        }
        $selectedRequirements = $this->decodeIdsString($request, Requirements::class);

        foreach ($selectedRequirements as $requirements) {
            $this->requirementsManager->removeResourcesFromRequirements($requirements, [$resourceNode]);
        }

        return new JsonResponse();
    }

    /**
     * @Route("/requirements/resource/{resourceNode}/{type}/update", name="apiv2_workspace_requirements_resource_update", methods={"PUT"})
     * @ParamConverter("resourceNode", options={"mapping": {"resourceNode": "uuid"}})
     */
    public function resourceRequirementsUpdateAction(ResourceNode $resourceNode, string $type, Request $request): JsonResponse
    {
        $workspace = $resourceNode->getWorkspace();

        if (!$this->authorization->isGranted(['dashboard', 'OPEN'], $workspace)) {
            throw new AccessDeniedException();
        }
        $workspace = $resourceNode->getWorkspace();

        if ('role' === $type) {
            $roles = $this->decodeIdsString($request, Role::class);
            $this->requirementsManager->createRolesRequirements($workspace, $roles, [$resourceNode]);
        } elseif ('user' === $type) {
            $users = $this->decodeIdsString($request, User::class);
            $this->requirementsManager->createUsersRequirements($workspace, $users, [$resourceNode]);
        }

        return new JsonResponse();
    }
}
