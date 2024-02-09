<?php

namespace UJM\ExoBundle\Serializer\Content;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\Text;
use Claroline\CoreBundle\Library\RoutingHelper;
use Claroline\CoreBundle\Manager\ResourceManager;

/**
 * Serializer for resource content.
 */
class ResourceContentSerializer
{
    use SerializerTrait;

    public function __construct(
        private readonly ObjectManager $om,
        private readonly string $fileDir,
        private readonly RoutingHelper $routingHelper,
        private readonly ResourceManager $resourceManager
    ) {
    }

    public function getName(): string
    {
        return 'exo_resource_content';
    }

    public function serialize(ResourceNode $node, array $options = []): array
    {
        // Load Resource from Node
        $resource = $this->resourceManager->getResourceFromNode($node);
        $resourceType = $node->getResourceType()->getName();

        $serialized = ['id' => (string) $node->getId()];

        if ('text' === $resourceType) {
            /* @var Text $resource */
            $serialized = array_merge($serialized, [
                'data' => $resource->getContent(),
                'type' => 'text/html',
            ]);
        } else {
            $serialized['type'] = $node->getMimeType();

            if ('file' === $resourceType && 1 === preg_match('#^([image|audio|video]+\/[^\/]+)$#', $serialized['type'])) {
                // the file is directly understandable by the browser (img, audio, video) return the file URL
                /* @var File $resource */
                $serialized['url'] = $this->fileDir.DIRECTORY_SEPARATOR.$resource->getHashName();
            } else {
                // return the url to access the resource
                $serialized['url'] = $this->routingHelper->resourceUrl($node);
            }
        }

        return $serialized;
    }

    /**
     * Converts raw data into a ResourceNode.
     *
     * The only purpose of this serializer is to expose a common data representation of a resource,
     * it's not made to create/update them so the deserialization only returns an existing ResourceNode
     *
     * @param array $data
     */
    public function deserialize($data, ResourceNode $resourceNode = null, array $options = [])
    {
        if (empty($resourceNode)) {
            $id = method_exists($data, 'getId') ? $data->getId() : $data['id'];
            $resourceNode = $this->om->getRepository(ResourceNode::class)->find($id);
        }

        return $resourceNode;
    }
}
