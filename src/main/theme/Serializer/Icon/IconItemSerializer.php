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

    public function __construct(IconSetSerializer $iconSetSerializer)
    {
        $this->iconSetSerializer = $iconSetSerializer;
    }

    public function getClass(): string
    {
        return IconItem::class;
    }

    /**
     * Serializes an IconItem entity for the JSON api.
     */
    public function serialize(IconItem $iconItem, ?array $options = []): array
    {
        $serialized = [
            'id' => $iconItem->getUuid(),
            'name' => $iconItem->getName(),
            'mimeType' => $iconItem->getMimeType(),
            'relativeUrl' => $iconItem->getRelativeUrl(),
        ];

        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $serialized['iconSet'] = $this->iconSetSerializer->serialize($iconItem->getIconSet());
        }

        return $serialized;
    }
}
