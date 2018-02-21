<?php

namespace Claroline\CoreBundle\API\Serializer\Facet;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\CoreBundle\Entity\Facet\PanelFacet;
use Claroline\CoreBundle\Entity\Facet\PanelFacetRole;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.panel_facet")
 * @DI\Tag("claroline.serializer")
 */
class PanelFacetSerializer
{
    use SerializerTrait;

    /** @var SerializerProvider */
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
     * @param PanelFacet $panel   - the field facet to serialize
     * @param array      $options - a list of serialization options
     *
     * @return array - the serialized representation of the field facet
     */
    public function serialize(PanelFacet $panel, array $options = [])
    {
        return [
            'id' => $panel->getUuid(),
            'title' => $panel->getName(),
            'position' => $panel->getPosition(),
            'roles' => $this->serializeRoles($panel->getPanelFacetsRole()->toArray()),
            'display' => [
                'collapsed' => $panel->isDefaultCollapsed(),
            ],
            'fields' => array_map(function (FieldFacet $fieldFacet) use ($options) { // todo check user rights
                return $this->serializer->serialize($fieldFacet, $options);
            }, $panel->getFieldsFacet()->toArray()),
        ];
    }

    private function serializeRoles(array $panelRoles = [])
    {
        return array_map(function (PanelFacetRole $panelRole) {
            return [
                'edit' => $panelRole->canEdit(),
                'open' => $panelRole->canOpen(),
                'role' => $this->serializer->serialize($panelRole->getRole(), [Options::SERIALIZE_MINIMAL]),
            ];
        }, $panelRoles);
    }

    /**
     * @param array      $data
     * @param PanelFacet $panel
     * @param array      $options
     *
     * @return array - the serialized representation of the field facet
     */
    public function deserialize(array $data, PanelFacet $panel = null, array $options = [])
    {
        $this->sipe('id', 'setUuid', $data, $panel);
        $this->sipe('title', 'setName', $data, $panel);
        $this->sipe('position', 'setPosition', $data, $panel);

        if (isset($data['roles'])) {
            $this->deserializeRoles($data['roles'], $panel);
        }

        if (isset($data['fields']) && in_array(Options::DEEP_DESERIALIZE, $options)) {
            $panel->resetFieldFacets();

            foreach ($data['fields'] as $field) {
                $field = $this->serializer->deserialize('Claroline\CoreBundle\Entity\Facet\FieldFacet', $field, $options);
                $field->setPanelFacet($panel);
            }
        }
    }

    private function deserializeRoles(array $roleData, PanelFacet $panel)
    {
    }
}
