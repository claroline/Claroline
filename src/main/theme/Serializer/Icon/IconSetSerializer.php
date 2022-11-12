<?php

namespace Claroline\ThemeBundle\Serializer\Icon;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\ThemeBundle\Entity\Icon\IconSet;

class IconSetSerializer
{
    use SerializerTrait;

    /**
     * @return string
     */
    public function getClass()
    {
        return IconSet::class;
    }

    /**
     * Serializes an IconSet entity for the JSON api.
     */
    public function serialize(IconSet $iconSet): array
    {
        return [
            'id' => $iconSet->getUuid(),
            'name' => $iconSet->getName(),
            'type' => $iconSet->getType(),
            'default' => $iconSet->isDefault(),
            'restrictions' => [
                'locked' => $iconSet->isLocked(),
            ],
        ];
    }

    /**
     * Deserializes IconSet data into entities.
     */
    public function deserialize(array $data, IconSet $iconSet): IconSet
    {
        $this->sipe('id', 'setUuid', $data, $iconSet);
        $this->sipe('name', 'setName', $data, $iconSet);
        $this->sipe('type', 'setType', $data, $iconSet);
        $this->sipe('active', 'setActive', $data, $iconSet);
        $this->sipe('editable', 'setEditable', $data, $iconSet);

        return $iconSet;
    }
}
