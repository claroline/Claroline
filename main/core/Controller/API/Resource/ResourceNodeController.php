<?php

namespace Claroline\CoreBundle\Controller\API\Resource;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceNodeSerializer;
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
 * @EXT\Route("resources/{id}", options={"expose"=true})
 * @EXT\ParamConverter("resourceNode", class="ClarolineCoreBundle:Resource\ResourceNode", options={"mapping": {"id": "uuid"}})
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
     * @param ObjectManager $om
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
        /*$rights = $data['rights']['all']['permissions'];
        foreach ($rights as $rolePerms) {
            $role = $this->om->getRepository('ClarolineCoreBundle:Role')->find($rolePerms['role']['id']);
            $this->rightsManager->editPerms($rolePerms['permissions'], $role, $resourceNode);
        }*/
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

        if (!$resourceNode->isPublished()) {
            $this->resourceManager->setPublishedStatus([$resourceNode], true);
        }

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

        if ($resourceNode->isPublished()) {
            $this->resourceManager->setPublishedStatus([$resourceNode], false);
        }

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

        $this->resourceManager->delete($resourceNode);

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
