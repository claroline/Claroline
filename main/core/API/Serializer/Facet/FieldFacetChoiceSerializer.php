<?php

namespace Claroline\CoreBundle\API\Serializer\Facet;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\Entity\Facet\FieldFacetChoice;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.field_facet_choice")
 * @DI\Tag("claroline.serializer")
 */
class FieldFacetChoiceSerializer
{
    use SerializerTrait;

    /**
     * Serializes a FieldFacetChoice entity for the JSON api.
     *
     * @param FieldFacetChoice $choice  - the choice to serialize
     * @param array            $options - a list of serialization options
     *
     * @return array - the serialized representation of the field facet
     */
    public function serialize(FieldFacetChoice $choice, array $options = [])
    {
        $serialized = [
          'id' => $choice->getUuid(),
          'name' => $choice->getName(),
          'label' => $choice->getName(),
          'value' => $choice->getValue(),
          'position' => $choice->getPosition(),
        ];

        if (!empty($choice->getChildren())) {
            $serialized['children'] = [];

            foreach ($choice->getChildren() as $child) {
                $serialized['children'][] = $this->serialize($child);
            }
        }

        return $serialized;
    }

    public function deserialize(array $data, FieldFacetChoice $choice, array $options = [])
    {
        $this->sipe('name', 'setName', $data, $choice);
        $this->sipe('position', 'setPosition', $data, $choice);

        return $choice;
    }
}
