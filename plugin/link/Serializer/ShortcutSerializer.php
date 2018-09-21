<?php

namespace Claroline\LinkBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceNodeSerializer;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\LinkBundle\Entity\Resource\Shortcut;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.resource_shortcut")
 * @DI\Tag("claroline.serializer")
 */
class ShortcutSerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;

    /** @var ResourceNodeSerializer */
    private $resourceNodeSerializer;

    /**
     * ShortcutSerializer constructor.
     *
     * @DI\InjectParams({
     *     "om"                     = @DI\Inject("claroline.persistence.object_manager"),
     *     "resourceNodeSerializer" = @DI\Inject("claroline.serializer.resource_node")
     * })
     *
     * @param ObjectManager          $om
     * @param ResourceNodeSerializer $resourceNodeSerializer
     */
    public function __construct(
        ObjectManager $om,
        ResourceNodeSerializer $resourceNodeSerializer)
    {
        $this->om = $om;
        $this->resourceNodeSerializer = $resourceNodeSerializer;
    }

    public function getClass()
    {
        return Shortcut::class;
    }

    /**
     * Serializes a Shortcut resource entity for the JSON api.
     *
     * @param Shortcut $shortcut
     * @param array    $options
     *
     * @return array
     */
    public function serialize(Shortcut $shortcut, array $options = [])
    {
        return [
            'target' => $this->resourceNodeSerializer->serialize($shortcut->getTarget(), array_merge($options, [Options::SERIALIZE_MINIMAL])),
        ];
    }

    /**
     * Deserializes shortcut data into an Entity.
     *
     * @param array    $data
     * @param Shortcut $shortcut
     *
     * @return Shortcut
     */
    public function deserialize(array $data, Shortcut $shortcut)
    {
        if (!empty($data['target']) &&
            !empty($data['target']['id']) &&
            (!$shortcut->getTarget() || $data['target']['id'] !== $shortcut->getTarget()->getUuid())
        ) {
            // the target is specified and as changed
            /** @var ResourceNode $target */
            $target = $this->om
                ->getRepository('ClarolineCoreBundle:Resource\ResourceNode')
                ->findOneBy(['uuid' => $data['target']['id']]);

            $shortcut->setTarget($target);
        }
    }
}
