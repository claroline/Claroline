<?php

namespace Claroline\LinkBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceNodeSerializer;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\LinkBundle\Entity\Resource\Shortcut;

class ShortcutSerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;

    /** @var ResourceNodeSerializer */
    private $resourceNodeSerializer;

    /**
     * ShortcutSerializer constructor.
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

    public function getName()
    {
        return 'shortcut';
    }

    public function serialize(Shortcut $shortcut, array $options = []): array
    {
        return [
            'id' => $shortcut->getUuid(),
            'target' => $this->resourceNodeSerializer->serialize($shortcut->getTarget(), array_merge($options, [Options::SERIALIZE_MINIMAL])),
        ];
    }

    public function deserialize(array $data, Shortcut $shortcut, array $options = []): Shortcut
    {
        if (!in_array(Options::REFRESH_UUID, $options)) {
            $this->sipe('id', 'setUuid', $data, $shortcut);
        } else {
            $shortcut->refreshUuid();
        }

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
