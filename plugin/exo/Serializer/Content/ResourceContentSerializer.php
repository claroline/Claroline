<?php

namespace UJM\ExoBundle\Serializer\Content;

use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\Text;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Routing\RouterInterface;
use UJM\ExoBundle\Library\Serializer\SerializerInterface;

/**
 * Serializer for resource content.
 *
 * @DI\Service("ujm_exo.serializer.resource_content")
 */
class ResourceContentSerializer implements SerializerInterface
{
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
     * @return \stdClass
     */
    public function serialize($node, array $options = [])
    {
        // Load Resource from Node
        $resource = $this->resourceManager->getResourceFromNode($node);
        $resourceType = $node->getResourceType()->getName();

        $resourceData = new \stdClass();
        $resourceData->id = (string) $node->getId();

        if ('text' === $resourceType) {
            /* @var Text $resource */
            $resourceData->data = $resource->getContent();
            $resourceData->type = 'text/html';
        } else {
            $resourceData->type = $node->getMimeType();

            if ('file' === $resourceType
                && 1 === preg_match('#^([image|audio|video]+\/[^\/]+)$#', $resourceData->type)) {
                // the file is directly understandable by the browser (img, audio, video) return the file URL

                /* @var File $resource */
                $resourceData->url = $this->fileDir.DIRECTORY_SEPARATOR.$resource->getHashName();
            } else {
                // return the url to access the resource

                $resourceData->url = $this->router->generate(
                    'claro_resource_open',
                    ['resourceType' => $resourceType, 'node' => $node->getId()]
                );
            }
        }

        return $resourceData;
    }

    /**
     * Converts raw data into a ResourceNode.
     *
     * The only purpose of this serializer is to expose a common data representation of a resource,
     * it's not made to create/update them so the deserialization only returns an existing ResourceNode
     *
     * @param \stdClass    $data
     * @param ResourceNode $resourceNode
     * @param array        $options
     *
     * @return mixed
     */
    public function deserialize($data, $resourceNode = null, array $options = [])
    {
        if (empty($resourceNode)) {
            $id = method_exists($data, 'getId') ? $data->getId() : $data->id;
            $resourceNode = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->find($id);
        }

        return $resourceNode;
    }
}
