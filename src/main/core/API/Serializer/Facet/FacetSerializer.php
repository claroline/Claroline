<?php

namespace Claroline\CoreBundle\API\Serializer\Facet;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Serializer\RoleSerializer;
use Claroline\CoreBundle\Entity\Facet\Facet;
use Claroline\CoreBundle\Entity\Facet\PanelFacet;
use Claroline\CoreBundle\Entity\Role;

class FacetSerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;
    /** @var RoleSerializer */
    private $roleSerializer;
    /** @var PanelFacetSerializer */
    private $pfSerializer;

    public function __construct(ObjectManager $om, RoleSerializer $roleSerializer, PanelFacetSerializer $pfSerializer)
    {
        $this->om = $om;
        $this->roleSerializer = $roleSerializer;
        $this->pfSerializer = $pfSerializer;
    }

    /**
     * @return string
     */
    public function getSchema()
    {
        return '#/main/core/facet.json';
    }

    public function getName()
    {
        return 'facet';
    }

    /**
     * @return array
     */
    public function serialize(Facet $facet, array $options = [])
    {
        return [
          'id' => $facet->getUuid(),
          'title' => $facet->getName(),
          'position' => $facet->getPosition(),
          'display' => [
            'creation' => $facet->getForceCreationForm(),
          ],
          'roles' => array_map(function (Role $role) {
              return $this->roleSerializer->serialize($role, [Options::SERIALIZE_MINIMAL]);
          }, $facet->getRoles()->toArray()),
          'meta' => [
              'main' => $facet->isMain(),
          ],
          'sections' => array_map(function ($panel) use ($options) { // todo check user rights
              return $this->pfSerializer->serialize($panel, $options);
          }, $facet->getPanelFacets()->toArray()),
        ];
    }

    public function deserialize(array $data, Facet $facet, array $options = [])
    {
        $this->sipe('id', 'setUuid', $data, $facet);
        $this->sipe('title', 'setName', $data, $facet);
        $this->sipe('position', 'setPosition', $data, $facet);
        $this->sipe('meta.main', 'setMain', $data, $facet);
        $this->sipe('display.creation', 'setForceCreationForm', $data, $facet);

        if (isset($data['sections']) && in_array(Options::DEEP_DESERIALIZE, $options)) {
            $facet->resetPanelFacets();

            foreach ($data['sections'] as $section) {
                //check if section exists first
                $panelFacet = $this->om->getObject($section, PanelFacet::class) ?? new PanelFacet();
                $this->pfSerializer->deserialize($section, $panelFacet, $options);
                $panelFacet->setFacet($facet);
            }
        }
    }
}
