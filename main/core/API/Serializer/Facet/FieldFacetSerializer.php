<?php

namespace Claroline\CoreBundle\API\Serializer\Facet;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\CoreBundle\Entity\Facet\FieldFacetChoice;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.field_facet")
 * @DI\Tag("claroline.serializer")
 */
class FieldFacetSerializer
{
    use SerializerTrait;

    private $serializer;

    /**
     * @DI\InjectParams({
     *     "serializer" = @DI\Inject("claroline.api.serializer")
     * })
     *
     * @param SerializerProvider $serializer
     */
    public function __construct(SerializerProvider $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Serializes a FieldFacet entity for the JSON api.
     *
     * @param FieldFacet $fieldFacet - the field facet to serialize
     * @param array      $options    - a list of serialization options
     *
     * @return array - the serialized representation of the field facet
     */
    public function serialize(FieldFacet $fieldFacet, array $options = [])
    {
        if (in_array(Options::PROFILE_SERIALIZE, $options)) {
            $serialized = [
              'id' => $fieldFacet->getUuid(),
              'name' => $fieldFacet->getName(),
              'type' => $fieldFacet->getFieldType(),
              'label' => $fieldFacet->getLabel(),
              'required' => $fieldFacet->isRequired(),
            ];

            if (!empty($fieldFacet->getOptions())) {
                $serialized['options'] = $fieldFacet->getOptions();
            }

            if (in_array($fieldFacet->getType(), [
                FieldFacet::SELECT_TYPE,
                FieldFacet::CHECKBOXES_TYPE,
                FieldFacet::CASCADE_SELECT_TYPE,
            ])) {
                $serialized['options']['choices'] = array_map(function (FieldFacetChoice $choice) {
                    return $this->serializer
                        ->get('Claroline\CoreBundle\Entity\Facet\FieldFacetChoice')
                        ->serialize($choice);
                }, $fieldFacet->getFieldFacetChoices()->toArray());
            }
        } else {
            //could be used by the clacoform. It should change later. The default one should be
            //PROFILE_SERIALIZE. See with @kitan
            $serialized = [
                'id' => $fieldFacet->getId(),
                'name' => $fieldFacet->getLabel(),
                'type' => $fieldFacet->getType(),
                'translationKey' => $fieldFacet->getTypeTranslationKey(),
                'field_facet_choices' => array_map(function (FieldFacetChoice $choice) {
                    return $this->serializer
                        ->get('Claroline\CoreBundle\Entity\Facet\FieldFacetChoice')
                        ->serialize($choice);
                }, $fieldFacet->getFieldFacetChoices()->toArray()),
            ];
        }

        return $serialized;
    }

    public function deserialize(array $data, FieldFacet $field = null, array $options = [])
    {
        $this->sipe('id', 'setUuid', $data, $field);
        $this->sipe('label', 'setLabel', $data, $field);
        $this->sipe('type', 'setType', $data, $field);
        $this->sipe('required', 'setRequired', $data, $field);
        $this->sipe('options', 'setOptions', $data, $field);
    }
}
