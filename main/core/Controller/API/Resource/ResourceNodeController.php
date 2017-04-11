<?php

namespace Claroline\CoreBundle\Controller\API;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Claroline\CoreBundle\Manager\ResourceNodeManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * JSON API for resource node management.
 *
 * @EXT\Route("resources/{id}", options={"expose"=true})
 * @EXT\ParamConverter("resourceNode", class="ClarolineCoreBundle:Resource\ResourceNode", options={"mapping": {"id": "guid"}})
 */
class ResourceNodeController
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorization;

    /**
     * ResourceNodeController constructor.
     *
     * @DI\InjectParams({
     *     "authorization"       = @DI\Inject("security.authorization_checker"),
     *     "resourceNodeManager" = @DI\Inject("claroline.manager.resource_node")
     * })
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param ResourceNodeManager           $resourceNodeManager
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ResourceNodeManager $resourceNodeManager)
    {
        $this->authorization = $authorization;
        $this->resourceNodeManager = $resourceNodeManager;
    }

    /**
     * Updates a resource node properties.
     *
     * @EXT\Route("", name="claro_resource_update")
     * @EXT\Method("PUT")
     * @EXT\ParamConverter("currentUser", converter="current_user", options={"allowAnonymous"=true})
     *
     * @param ResourceNode $resourceNode
     * @param User         $currentUser
     *
     * @return JsonResponse
     */
    public function updateAction(ResourceNode $resourceNode, User $currentUser)
    {
        $this->assertHasPermission('EDIT', $resourceNode);

        return new JsonResponse(
            $this->resourceNodeManager->serialize($resourceNode, $currentUser)
        );
    }

    /**
     * Publishes a resource node.
     *
     * @EXT\Route("/publish", name="claro_resource_publish")
     * @EXT\Method("PUT")
     *
     * @param ResourceNode $resourceNode
     *
     * @return JsonResponse
     */
    public function publishAction(ResourceNode $resourceNode)
    {
        $this->assertHasPermission('EDIT', $resourceNode);

        return new JsonResponse(null, 204);
    }

    /**
     * Unpublishes a resource node.
     *
     * @EXT\Route("/unpublish", name="claro_resource_unpublish")
     * @EXT\Method("PUT")
     *
     * @param ResourceNode $resourceNode
     *
     * @return JsonResponse
     */
    public function unpublishAction(ResourceNode $resourceNode)
    {
        $this->assertHasPermission('EDIT', $resourceNode);

        return new JsonResponse(null, 204);
    }

    /**
     * Exports a resource node in the Claroline export format.
     *
     * @EXT\Route("/export", name="claro_resource_export")
     * @EXT\Method("GET")
     *
     * @param ResourceNode $resourceNode
     */
    public function exportAction(ResourceNode $resourceNode)
    {
        $this->assertHasPermission('EXPORT', $resourceNode);
    }

    /**
     * Deletes a resource node.
     *
     * @EXT\Route("", name="claro_resource_delete")
     * @EXT\Method("DELETE")
     *
     * @param ResourceNode $resourceNode
     *
     * @return JsonResponse
     */
    public function deleteAction(ResourceNode $resourceNode)
    {
        $this->assertHasPermission('DELETE', $resourceNode);

        return new JsonResponse(null, 204);
    }

    private function assertHasPermission($permission, ResourceNode $resourceNode)
    {
        $collection = new ResourceCollection([$resourceNode]);

        if (!$this->authorization->isGranted($permission, $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }
    }
}
