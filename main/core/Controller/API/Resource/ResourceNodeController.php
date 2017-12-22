<?php

namespace Claroline\CoreBundle\Controller\API\Resource;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Claroline\CoreBundle\Manager\Resource\ResourceNodeManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
        ResourceNodeManager $resourceNodeManager
    ) {
        $this->authorization = $authorization;
        $this->resourceNodeManager = $resourceNodeManager;
    }

    /**
     * Get a resourceNode properties.
     *
     * @EXT\Route("", name="claro_resource_node_get")
     * @EXT\Method("GET")
     *
     * @param ResourceNode $resourceNode
     *
     * @return JsonResponse
     */
    public function getResourceNodeAction(ResourceNode $resourceNode)
    {
        $this->assertHasPermission('OPEN', $resourceNode);

        return new JsonResponse($this->resourceNodeManager->serialize($resourceNode));
    }

    /**
     * Updates a resource node properties.
     *
     * @EXT\Route("", name="claro_resource_node_update")
     * @EXT\Method("PUT")
     *
     * @param ResourceNode $resourceNode
     * @param Request      $request
     *
     * @return JsonResponse
     */
    public function updateAction(ResourceNode $resourceNode, Request $request)
    {
        $this->assertHasPermission('ADMINISTRATE', $resourceNode);

        $this->resourceNodeManager->update(json_decode($request->getContent(), true), $resourceNode);

        return new JsonResponse(
            $this->resourceNodeManager->serialize($resourceNode)
        );
    }

    /**
     * Publishes a resource node.
     *
     * @EXT\Route("/publish", name="claro_resource_node_publish")
     * @EXT\Method("PUT")
     *
     * @todo to be merge with ResourceController::publishAction (works with ids)
     *
     * @param ResourceNode $resourceNode
     *
     * @return JsonResponse
     */
    public function publishAction(ResourceNode $resourceNode)
    {
        $this->assertHasPermission('ADMINISTRATE', $resourceNode);

        $this->resourceNodeManager->publish($resourceNode);

        return new JsonResponse(null, 204);
    }

    /**
     * Unpublishes a resource node.
     *
     * @EXT\Route("/unpublish", name="claro_resource_node_unpublish")
     * @EXT\Method("PUT")
     *
     * @todo to be merge with ResourceController::unpublishAction (works with ids)
     *
     * @param ResourceNode $resourceNode
     *
     * @return JsonResponse
     */
    public function unpublishAction(ResourceNode $resourceNode)
    {
        $this->assertHasPermission('ADMINISTRATE', $resourceNode);

        $this->resourceNodeManager->unpublish($resourceNode);

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

        $this->resourceNodeManager->delete($resourceNode);

        return new JsonResponse(null, 204);
    }

    /**
     * Unlocks a resource node.
     *
     * @todo for security, code should not be passed in the URL
     *
     * @EXT\Route("/unlock/{code}", name="claro_resource_unlock")
     * @EXT\Method("POST")
     *
     * @param ResourceNode $resourceNode
     * @param mixed        $code
     *
     * @return JsonResponse
     */
    public function unlock(ResourceNode $resourceNode, $code)
    {
        return new JsonResponse($this->resourceNodeManager->unlock($resourceNode, $code));
    }

    private function assertHasPermission($permission, ResourceNode $resourceNode)
    {
        $collection = new ResourceCollection([$resourceNode]);

        if (!$this->authorization->isGranted($permission, $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }
    }
}
