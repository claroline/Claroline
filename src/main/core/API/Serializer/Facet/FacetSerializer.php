<?php

namespace Claroline\CoreBundle\API\Serializer\Facet;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Facet\Facet;
use Claroline\CoreBundle\Entity\Facet\PanelFacet;

class FacetSerializer
{
    use SerializerTrait;

    private ObjectManager $om;
    private PanelFacetSerializer $pfSerializer;

    public function __construct(ObjectManager $om, PanelFacetSerializer $pfSerializer)
    {
        $this->om = $om;
        $this->pfSerializer = $pfSerializer;
    }

    public function getClass(): string
    {
        return Facet::class;
    }

    public function getSchema(): string
    {
        return '#/main/core/facet.json';
    }

    public function getName(): string
    {
        return 'facet';
    }

    public function serialize(Facet $facet, ?array $options = []): array
    {
        return [
            'id' => $facet->getUuid(),
            'title' => $facet->getName(),
            'display' => [
                'order' => $facet->getOrder(),
                'icon' => $facet->getIcon(),
                'creation' => $facet->getForceCreationForm(),
            ],
            'meta' => [
                'main' => $facet->isMain(),
            ],
            'sections' => array_map(function ($panel) use ($options) {
                return $this->pfSerializer->serialize($panel, $options);
            }, $facet->getPanelFacets()->toArray()),
        ];
    }

    public function deserialize(array $data, Facet $facet, array $options = []): Facet
    {
        $this->sipe('id', 'setUuid', $data, $facet);
        $this->sipe('title', 'setName', $data, $facet);
        $this->sipe('meta.main', 'setMain', $data, $facet);
        $this->sipe('display.order', 'setOrder', $data, $facet);
        $this->sipe('display.icon', 'setIcon', $data, $facet);
        $this->sipe('display.creation', 'setForceCreationForm', $data, $facet);

        if (array_key_exists('sections', $data)) {
            $facet->resetPanelFacets();

            foreach ($data['sections'] as $section) {
                //check if section exists first
                $panelFacet = $this->om->getObject($section, PanelFacet::class) ?? new PanelFacet();
                $this->pfSerializer->deserialize($section, $panelFacet, $options);
                $panelFacet->setFacet($facet);
            }
        }

        return $facet;
    }
}
