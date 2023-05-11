<?php

namespace Claroline\ThemeBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\ThemeBundle\Entity\ColorCollection;

class ColorCollectionSerializer
{
    use SerializerTrait;

    public function getClass(): string
    {
        return ColorCollection::class;
    }

    public function serialize(ColorCollection $colorCollection): array
    {
        return [
            'id' => $colorCollection->getUuid(),
            'autoId' => $colorCollection->getId(),
            'name' => $colorCollection->getName(),
            'colors' => $colorCollection->getColors(),
        ];
    }

    public function deserialize(array $data, ColorCollection $colorCollection): ColorCollection
    {
        $this->sipe('id', 'setUuid', $data, $colorCollection);
        $this->sipe('name', 'setName', $data, $colorCollection);

        if (isset($data['colors']) && is_array($data['colors'])) {
            $this->sipe('colors', 'setColors', $data, $colorCollection);
        }

        return $colorCollection;
    }

}
