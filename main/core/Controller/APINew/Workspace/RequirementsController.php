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

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Requirements;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\Workspace\EvaluationManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/workspace")
 */
class RequirementsController
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var EvaluationManager */
    private $evaluationManager;
    /** @var FinderProvider */
    private $finder;
    /** @var ObjectManager */
    private $om;
    /** @var SerializerProvider */
    private $serializer;

    /**
     * RequirementsController constructor.
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param EvaluationManager             $evaluationManager
     * @param FinderProvider                $finder
     * @param ObjectManager                 $om
     * @param SerializerProvider            $serializer
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        EvaluationManager $evaluationManager,
        FinderProvider $finder,
        ObjectManager $om,
        SerializerProvider $serializer
    ) {
        $this->authorization = $authorization;
        $this->evaluationManager = $evaluationManager;
        $this->finder = $finder;
        $this->om = $om;
        $this->serializer = $serializer;
    }

    /**
     * @Route(
     *    "/{workspace}/requirements/{type}/list",
     *    name="apiv2_workspace_requirements_list"
     * )
     * @Method("GET")
     * @ParamConverter("workspace", options={"mapping": {"workspace": "uuid"}})
     *
     * @param Workspace $workspace
     * @param string    $type
     * @param Request   $request
     *
     * @return JsonResponse
     */
    public function requirementsListAction(Workspace $workspace, $type, Request $request)
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
     * @Route(
     *    "/{workspace}/requirements/{type}/create",
     *    name="apiv2_workspace_requirements_create"
     * )
     * @Method("PUT")
     * @ParamConverter("workspace", options={"mapping": {"workspace": "uuid"}})
     *
     * @param Workspace $workspace
     * @param string    $type
     * @param Request   $request
     *
     * @return JsonResponse
     */
    public function requirementsCreateAction(Workspace $workspace, $type, Request $request)
    {
        if (!$this->authorization->isGranted(['dashboard', 'OPEN'], $workspace)) {
            throw new AccessDeniedException();
        }
        if ('role' === $type) {
            $roles = $this->decodeIdsString($request, Role::class);
            $this->evaluationManager->createRolesRequirements($workspace, $roles);
        } elseif ('user' === $type) {
            $users = $this->decodeIdsString($request, User::class);
            $this->evaluationManager->createUsersRequirements($workspace, $users);
        }

        return new JsonResponse();
    }

    /**
     * @Route(
     *     "/{workspace}/requirements/delete",
     *     name="apiv2_workspace_requirements_delete"
     * )
     * @Method("DELETE")
     * @ParamConverter("workspace", options={"mapping": {"workspace": "uuid"}})
     *
     * @param Workspace $workspace
     * @param Request   $request
     *
     * @return JsonResponse
     */
    public function deleteBulkAction(Workspace $workspace, Request $request)
    {
        if (!$this->authorization->isGranted(['dashboard', 'OPEN'], $workspace)) {
            throw new AccessDeniedException();
        }
        $requirementsToDelete = $this->decodeIdsString($request, Requirements::class);
        $this->evaluationManager->deleteMultipleRequirements($requirementsToDelete);

        return new JsonResponse();
    }

    /**
     * @Route(
     *    "/requirements/{requirements}/fetch",
     *    name="apiv2_workspace_requirements_fetch"
     * )
     * @Method("GET")
     * @ParamConverter("requirements", options={"mapping": {"requirements": "uuid"}})
     *
     * @param Requirements $requirements
     *
     * @return JsonResponse
     */
    public function requirementsFetchAction(Requirements $requirements)
    {
        if (!$this->authorization->isGranted(['dashboard', 'OPEN'], $requirements->getWorkspace())) {
            throw new AccessDeniedException();
        }

        return new JsonResponse($this->serializer->serialize($requirements));
    }

    /**
     * @Route(
     *    "/requirements/resource/{resourceNode}/{type}/list",
     *    name="apiv2_workspace_requirements_resource_list"
     * )
     * @Method("GET")
     * @ParamConverter("resourceNode", options={"mapping": {"resourceNode": "uuid"}})
     *
     * @param ResourceNode $resourceNode
     * @param string       $type
     * @param Request      $request
     *
     * @return JsonResponse
     */
    public function resourceListAction(ResourceNode $resourceNode, $type, Request $request)
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
     * @Route(
     *    "/requirements/{requirements}/resources/add",
     *    name="apiv2_workspace_requirements_resources_add"
     * )
     * @Method("PUT")
     * @ParamConverter("requirements", options={"mapping": {"requirements": "uuid"}})
     *
     * @param Requirements $requirements
     * @param Request      $request
     *
     * @return JsonResponse
     */
    public function resourcesRequirementsAddAction(Requirements $requirements, Request $request)
    {
        if (!$this->authorization->isGranted(['dashboard', 'OPEN'], $requirements->getWorkspace())) {
            throw new AccessDeniedException();
        }
        $resourceNodes = $this->decodeIdsString($request, ResourceNode::class);
        $updatedRequirements = $this->evaluationManager->addResourcesToRequirements($requirements, $resourceNodes);

        return new JsonResponse($this->serializer->serialize($updatedRequirements));
    }

    /**
     * @Route(
     *    "/requirements/{requirements}/resources/remove",
     *    name="apiv2_workspace_requirements_resources_remove"
     * )
     * @Method("DELETE")
     * @ParamConverter("requirements", options={"mapping": {"requirements": "uuid"}})
     *
     * @param Requirements $requirements
     * @param Request      $request
     *
     * @return JsonResponse
     */
    public function resourcesRequirementsRemoveAction(Requirements $requirements, Request $request)
    {
        if (!$this->authorization->isGranted(['dashboard', 'OPEN'], $requirements->getWorkspace())) {
            throw new AccessDeniedException();
        }
        $resourceNodes = $this->decodeIdsString($request, ResourceNode::class);
        $updatedRequirements = $this->evaluationManager->removeResourcesFromRequirements($requirements, $resourceNodes);

        return new JsonResponse($this->serializer->serialize($updatedRequirements));
    }

    /**
     * @Route(
     *    "/requirements/resource/{resourceNode}/remove",
     *    name="apiv2_workspace_requirements_resource_remove"
     * )
     * @Method("DELETE")
     * @ParamConverter("resourceNode", options={"mapping": {"resourceNode": "uuid"}})
     *
     * @param ResourceNode $resourceNode
     * @param Request      $request
     *
     * @return JsonResponse
     */
    public function resourceRequirementsRemoveAction(ResourceNode $resourceNode, Request $request)
    {
        if (!$this->authorization->isGranted(['dashboard', 'OPEN'], $resourceNode->getWorkspace())) {
            throw new AccessDeniedException();
        }
        $selectedRequirements = $this->decodeIdsString($request, Requirements::class);

        foreach ($selectedRequirements as $requirements) {
            $this->evaluationManager->removeResourcesFromRequirements($requirements, [$resourceNode]);
        }

        return new JsonResponse();
    }

    /**
     * @Route(
     *    "/requirements/resource/{resourceNode}/{type}/update",
     *    name="apiv2_workspace_requirements_resource_update"
     * )
     * @Method("PUT")
     * @ParamConverter("resourceNode", options={"mapping": {"resourceNode": "uuid"}})
     *
     * @param ResourceNode $resourceNode
     * @param string       $type
     * @param Request      $request
     *
     * @return JsonResponse
     */
    public function resourceRequirementsUpdateAction(ResourceNode $resourceNode, $type, Request $request)
    {
        $workspace = $resourceNode->getWorkspace();

        if (!$this->authorization->isGranted(['dashboard', 'OPEN'], $workspace)) {
            throw new AccessDeniedException();
        }
        $workspace = $resourceNode->getWorkspace();

        if ('role' === $type) {
            $roles = $this->decodeIdsString($request, Role::class);
            $this->evaluationManager->createRolesRequirements($workspace, $roles, [$resourceNode]);
        } elseif ('user' === $type) {
            $users = $this->decodeIdsString($request, User::class);
            $this->evaluationManager->createUsersRequirements($workspace, $users, [$resourceNode]);
        }

        return new JsonResponse();
    }

    /**
     * @param Request $request
     * @param string  $class
     * @param string  $property
     *
     * @return array
     */
    private function decodeIdsString(Request $request, $class, $property = 'ids')
    {
        $ids = $request->query->get($property);

        $property = is_numeric($ids[0]) ? 'id' : 'uuid';

        return $this->om->findList($class, $property, $ids);
    }
}
