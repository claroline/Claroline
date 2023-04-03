<?php

namespace Claroline\CoreBundle\API\Serializer\Facet;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\CoreBundle\Entity\Facet\PanelFacet;

class PanelFacetSerializer
{
    use SerializerTrait;

    private ObjectManager $om;
    private FieldFacetSerializer $ffSerializer;

    public function __construct(ObjectManager $om, FieldFacetSerializer $ffSerializer)
    {
        $this->om = $om;
        $this->ffSerializer = $ffSerializer;
    }

    public function getName(): string
    {
        return 'facet_panel';
    }

    public function getClass(): string
    {
        return PanelFacet::class;
    }

    /**
     * Serializes a FieldFacet entity for the JSON api.
     *
     * @param PanelFacet $panel   - the field facet to serialize
     * @param array      $options - a list of serialization options
     *
     * @return array - the serialized representation of the field facet
     */
    public function serialize(PanelFacet $panel, ?array $options = []): array
    {
        return [
            'id' => $panel->getUuid(),
            'title' => $panel->getName(),
            'help' => $panel->getHelp(),
            'meta' => [
                'description' => $panel->getDescription(),
            ],
            'display' => [
                'order' => $panel->getOrder(),
                'icon' => $panel->getIcon(),
            ],
            'fields' => array_map(function (FieldFacet $fieldFacet) use ($options) {
                return $this->ffSerializer->serialize($fieldFacet, $options);
            }, $panel->getFieldsFacet()->toArray()),
        ];
    }

    public function deserialize(array $data, PanelFacet $panel, ?array $options = []): PanelFacet
    {
        $this->sipe('id', 'setUuid', $data, $panel);
        $this->sipe('title', 'setName', $data, $panel);
        $this->sipe('meta.description', 'setDescription', $data, $panel);
        $this->sipe('display.order', 'setOrder', $data, $panel);
        $this->sipe('display.icon', 'setIcon', $data, $panel);
        $this->sipe('help', 'setHelp', $data, $panel);

        if (array_key_exists('fields', $data)) {
            $panel->resetFieldFacets();

            foreach ($data['fields'] as $i => $field) {
                if (!isset($field['display']['order'])) {
                    $field['display']['order'] = $i;
                }

                $fieldFacet = $this->om->getObject($field, FieldFacet::class) ?? new FieldFacet();
                $fieldFacet = $this->ffSerializer->deserialize($field, $fieldFacet, $options);
                $fieldFacet->setPanelFacet($panel);
            }
        }

        return $panel;
    }
}
