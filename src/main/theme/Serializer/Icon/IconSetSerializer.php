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
     *
     * @return array
     */
    public function serialize(IconSet $iconSet)
    {
        return [
            'id' => $iconSet->getUuid(),
            'name' => $iconSet->getName(),
            'type' => $iconSet->getType(),
            'active' => $iconSet->isActive(),
            'default' => $iconSet->isDefault(),
            'editable' => $iconSet->isEditable(),
        ];
    }

    /**
     * Deserializes IconSet data into entities.
     *
     * @param array $data
     *
     * @return IconSet
     */
    public function deserialize($data, IconSet $iconSet)
    {
        $this->sipe('id', 'setUuid', $data, $iconSet);
        $this->sipe('name', 'setName', $data, $iconSet);
        $this->sipe('type', 'setType', $data, $iconSet);
        $this->sipe('active', 'setActive', $data, $iconSet);
        $this->sipe('editable', 'setEditable', $data, $iconSet);

        return $iconSet;
    }
}
