<?php

namespace Claroline\ThemeBundle\Serializer\Icon;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\ThemeBundle\Entity\Icon\IconItem;

class IconItemSerializer
{
    use SerializerTrait;

    /** @var IconSetSerializer */
    private $iconSetSerializer;

    /**
     * IconItemSerializer constructor.
     *
     * @param IconSetSerializer $iconSetSerializer
     */
    public function __construct(IconSetSerializer $iconSetSerializer)
    {
        $this->iconSetSerializer = $iconSetSerializer;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return IconItem::class;
    }

    /**
     * Serializes an IconItem entity for the JSON api.
     *
     * @param IconItem $iconItem
     * @param array    $options
     *
     * @return array
     */
    public function serialize(IconItem $iconItem, array $options = [])
    {
        $serialized = [
            'id' => $iconItem->getUuid(),
            'mimeType' => $iconItem->getMimeType(),
            'relativeUrl' => $iconItem->getRelativeUrl(),
            'name' => $iconItem->getName(),
            'class' => $iconItem->getClass(),
        ];

        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $serialized['iconSet'] = $this->iconSetSerializer->serialize($iconItem->getIconSet());
        }

        return $serialized;
    }
}
