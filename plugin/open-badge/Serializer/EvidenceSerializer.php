<?php

namespace Claroline\OpenBadgeBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceNodeSerializer;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\OpenBadgeBundle\Entity\Evidence;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * @DI\Service("claroline.serializer.open_badge.evidence")
 * @DI\Tag("claroline.serializer")
 */
class EvidenceSerializer
{
    use SerializerTrait;

    /**
     * @DI\InjectParams({
     *     "router"                 = @DI\Inject("router"),
     *     "resourceNodeSerializer" = @DI\Inject("claroline.serializer.resource_node"),
     * })
     *
     * @param Router $router
     */
    public function __construct(RouterInterface $router, ResourceNodeSerializer $resourceNodeSerializer)
    {
        $this->router = $router;
        $this->resourceNodeSerializer = $resourceNodeSerializer;
    }

    /**
     * Serializes a Assertion entity.
     *
     * @param Assertion $assertion
     * @param array     $options
     *
     * @return array
     */
    public function serialize(Evidence $evidence, array $options = [])
    {
        $data = [
          'id' => $evidence->getUuid(),
          'narrative' => $evidence->getNarrative(),
          'name' => $evidence->getName(),
        ];

        if (in_array(Options::ENFORCE_OPEN_BADGE_JSON, $options)) {
            $data['id'] = $this->router->generate('apiv2_open_badge__evidence', ['evidence' => $evidence->getUuid()], UrlGeneratorInterface::ABSOLUTE_URL);
            $data['type'] = 'Evidence';
        } else {
            $data['resources'] = array_map(function (ResourceNode $node) {
                return $this->resourceNodeSerializer->serialize($node);
            }, $evidence->getResourceEvidences()->toArray());
        }

        return $data;
    }

    /**
     * Serializes a Evidence entity.
     *
     * @param array    $data
     * @param Evidence $evidence
     * @param array    $options
     *
     * @return array
     */
    public function deserialize(array $data, Evidence $evidence = null, array $options = [])
    {
        $this->sipe('narrative', 'setNarrative', $data, $evidence);
        $this->sipe('name', 'setName', $data, $evidence);

        if (isset($data['resources'])) {
            $resources = [];
            foreach ($data['resources'] as $resource) {
                $node = $this->_om->getObject($resource, ResourceNode::class);
                $resources[] = $this->resourceNodeSerializer->deserialize($resource, $node);
            }
            $evidence->setResourceEvidences($resources);
        }
    }

    public function getClass()
    {
        return Evidence::class;
    }
}
