<?php

namespace Claroline\CoreBundle\Controller\API\Resource;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Claroline\CoreBundle\Manager\Resource\ResourceNodeManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * JSON API for resource node management.
 *
 * @EXT\Route(options={"expose"=true})
 */
class ResourceNodeController
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorization;

    /** @var SerializerProvider */
    private $serializer;

    /**
     * ResourceNodeController constructor.
     *
     * @DI\InjectParams({
     *     "authorization" = @DI\Inject("security.authorization_checker"),
     *     "om"            = @DI\Inject("claroline.persistence.object_manager"),
     *     "serializer"    = @DI\Inject("claroline.api.serializer"),
     *     "resourceManager"     = @DI\Inject("claroline.manager.resource_manager"),
     *     "resourceNodeManager" = @DI\Inject("claroline.manager.resource_node")
     * })
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param ObjectManager                 $om
     * @param SerializerProvider            $serializer
     * @param ResourceManager               $resourceManager
     * @param ResourceNodeManager           $resourceNodeManager
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om,
        SerializerProvider $serializer,
        ResourceManager $resourceManager,
        ResourceNodeManager $resourceNodeManager
    ) {
        $this->authorization = $authorization;
        $this->serializer = $serializer;
        $this->om = $om;
        $this->resourceManager = $resourceManager;
        $this->resourceNodeManager = $resourceNodeManager;
    }

    /**
     * Updates a resource node properties.
     *
     * @EXT\Route("resources/{id}", name="claro_resource_node_update")
     * @EXT\Method("PUT")
     * @EXT\ParamConverter("resourceNode", class="ClarolineCoreBundle:Resource\ResourceNode", options={"mapping": {"id": "uuid"}})
     *
     * @param ResourceNode $resourceNode
     * @param Request      $request
     *
     * @return JsonResponse
     */
    public function updateAction(ResourceNode $resourceNode, Request $request)
    {
        $this->assertHasPermission('ADMINISTRATE', $resourceNode);

        // TODO : use crud
        $updated = $this->serializer->deserialize(json_decode($request->getContent(), true), $resourceNode);

        $this->om->persist($updated);
        $this->om->flush();

        return new JsonResponse(
            $this->serializer->serialize($updated)
        );
    }

    public function updateRightsActions()
    {
        // TODO implement
    }

    /**
     * Publishes a resource node.
     *
     * @EXT\Route("resources/selected/publish", name="claro_resource_node_publish")
     * @EXT\Method("PUT")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function publishAction(Request $request)
    {
        $nodes = $this->decodeIdsString($request, 'Claroline\CoreBundle\Entity\Resource\ResourceNode');

        foreach ($nodes as $node) {
            $this->assertHasPermission('ADMINISTRATE', $node);
        }
        $this->resourceManager->setPublishedStatus($nodes, true);

        return new JsonResponse(null, 204);
    }

    /**
     * Unpublishes a resource node.
     *
     * @EXT\Route("resources/selected/unpublish", name="claro_resource_node_unpublish")
     * @EXT\Method("PUT")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function unpublishAction(Request $request)
    {
        $nodes = $this->decodeIdsString($request, 'Claroline\CoreBundle\Entity\Resource\ResourceNode');

        foreach ($nodes as $node) {
            $this->assertHasPermission('ADMINISTRATE', $node);
        }
        $this->resourceManager->setPublishedStatus($nodes, false);

        return new JsonResponse(null, 204);
    }

    /**
     * Exports a resource node in the Claroline export format.
     *
     * @EXT\Route("resources/{id}/export", name="claro_resource_export")
     * @EXT\Method("GET")
     * @EXT\ParamConverter("resourceNode", class="ClarolineCoreBundle:Resource\ResourceNode", options={"mapping": {"id": "uuid"}})
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
     * @EXT\Route("resources/{id}", name="claro_resource_delete")
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter("resourceNode", class="ClarolineCoreBundle:Resource\ResourceNode", options={"mapping": {"id": "uuid"}})
     *
     * @param ResourceNode $resourceNode
     *
     * @return JsonResponse
     */
    public function deleteAction(ResourceNode $resourceNode)
    {
        $this->assertHasPermission('DELETE', $resourceNode);

        $this->resourceManager->delete($resourceNode);

        return new JsonResponse(null, 204);
    }

    /**
     * Unlocks a resource node.
     *
     * @todo for security, code should not be passed in the URL
     *
     * @EXT\Route("resources/{id}/unlock/{code}", name="claro_resource_unlock")
     * @EXT\Method("POST")
     * @EXT\ParamConverter("resourceNode", class="ClarolineCoreBundle:Resource\ResourceNode", options={"mapping": {"id": "uuid"}})
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

    /**
     * @param Request $request
     * @param string  $class
     */
    private function decodeIdsString(Request $request, $class)
    {
        $ids = $request->query->get('ids');
        $property = is_numeric($ids[0]) ? 'id' : 'uuid';

        return $this->om->findList($class, $property, $ids);
    }

    private function assertHasPermission($permission, ResourceNode $resourceNode)
    {
        $collection = new ResourceCollection([$resourceNode]);

        if (!$this->authorization->isGranted($permission, $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }
    }
}
