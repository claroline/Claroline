<?php

namespace Claroline\CoreBundle\API\Serializer\Facet;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Serializer\RoleSerializer;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\CoreBundle\Entity\Facet\PanelFacet;
use Claroline\CoreBundle\Entity\Facet\PanelFacetRole;

class PanelFacetSerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;
    /** @var RoleSerializer */
    private $roleSerializer;
    /** @var FieldFacetSerializer */
    private $ffSerializer;

    public function __construct(ObjectManager $om, RoleSerializer $roleSerializer, FieldFacetSerializer $ffSerializer)
    {
        $this->om = $om;
        $this->roleSerializer = $roleSerializer;
        $this->ffSerializer = $ffSerializer;
    }

    public function getName()
    {
        return 'panel_facet';
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
            //deprecated
            'display' => [
                'collapsed' => $panel->isDefaultCollapsed(),
            ],
            'defaultOpened' => true,
            'fields' => array_map(function (FieldFacet $fieldFacet) use ($options) { // todo check user rights
                return $this->ffSerializer->serialize($fieldFacet, $options);
            }, $panel->getFieldsFacet()->toArray()),
        ];
    }

    private function serializeRoles(array $panelRoles = [])
    {
        return array_map(function (PanelFacetRole $panelRole) {
            return [
                'edit' => $panelRole->canEdit(),
                'open' => $panelRole->canOpen(),
                'role' => $this->roleSerializer->serialize($panelRole->getRole(), [Options::SERIALIZE_MINIMAL]),
            ];
        }, $panelRoles);
    }

    /**
     * @return array - the serialized representation of the field facet
     */
    public function deserialize(array $data, PanelFacet $panel, array $options = [])
    {
        $this->sipe('id', 'setUuid', $data, $panel);
        $this->sipe('title', 'setName', $data, $panel);
        $this->sipe('position', 'setPosition', $data, $panel);

        if (isset($data['roles'])) {
            $this->deserializeRoles($data['roles'], $panel);
        }

        if (isset($data['fields']) && in_array(Options::DEEP_DESERIALIZE, $options)) {
            $panel->resetFieldFacets();
            $i = 0;

            foreach ($data['fields'] as $field) {
                if (!isset($field['restrictions']['order'])) {
                    $field['restrictions']['order'] = $i;
                }
                ++$i;
                $fieldFacet = $this->om->getObject($field, FieldFacet::class) ?? new FieldFacet();
                $fieldFacet = $this->ffSerializer->deserialize($field, $fieldFacet, $options);
                $fieldFacet->setPanelFacet($panel);
            }
        }
    }

    private function deserializeRoles(array $roleData, PanelFacet $panel)
    {
        // TODO : implement
    }
}
