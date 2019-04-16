<?php

namespace UJM\ExoBundle\Serializer\Content;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\Text;
use Claroline\CoreBundle\Manager\ResourceManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Routing\RouterInterface;

/**
 * Serializer for resource content.
 *
 * @DI\Service("ujm_exo.serializer.resource_content")
 * @DI\Tag("claroline.serializer")
 */
class ResourceContentSerializer
{
    use SerializerTrait;

    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var string
     */
    private $fileDir;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var ResourceManager
     */
    private $resourceManager;

    /**
     * ResourceContentSerializer constructor.
     *
     * @DI\InjectParams({
     *     "om"              = @DI\Inject("claroline.persistence.object_manager"),
     *     "fileDir"         = @DI\Inject("%claroline.param.files_directory%"),
     *     "router"          = @DI\Inject("router"),
     *     "resourceManager" = @DI\Inject("claroline.manager.resource_manager")
     * })
     *
     * @param ObjectManager   $om
     * @param string          $fileDir
     * @param RouterInterface $router
     * @param ResourceManager $resourceManager
     */
    public function __construct(
        ObjectManager $om,
        $fileDir,
        RouterInterface $router,
        ResourceManager $resourceManager
    ) {
        $this->om = $om;
        $this->fileDir = $fileDir;
        $this->router = $router;
        $this->resourceManager = $resourceManager;
    }

    /**
     * @param ResourceNode $node
     * @param array        $options
     *
     * @return array
     */
    public function serialize(ResourceNode $node, array $options = [])
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
                $serialized['url'] = $this->router->generate(
                    'claro_resource_open',
                    ['resourceType' => $resourceType, 'node' => $node->getId()]
                );
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
     * @param array        $data
     * @param ResourceNode $resourceNode
     * @param array        $options
     *
     * @return mixed
     */
    public function deserialize($data, ResourceNode $resourceNode = null, array $options = [])
    {
        if (empty($resourceNode)) {
            $id = method_exists($data, 'getId') ? $data->getId() : $data['id'];
            $resourceNode = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->find($id);
        }

        return $resourceNode;
    }
}
