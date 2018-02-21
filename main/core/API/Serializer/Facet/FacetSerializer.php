<?php

namespace Claroline\CoreBundle\API\Serializer\Facet;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\Facet\Facet;
use Claroline\CoreBundle\Entity\Role;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.facet")
 * @DI\Tag("claroline.serializer")
 */
class FacetSerializer
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
     * @return string
     */
    public function getSchema()
    {
        return '#/main/core/facet.json';
    }

    /**
     * @param Facet $facet
     * @param array $options
     *
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
              return $this->serializer->serialize($role, [Options::SERIALIZE_MINIMAL]);
          }, $facet->getRoles()->toArray()),
          'meta' => [
              'main' => $facet->isMain(),
          ],
          'sections' => array_map(function ($panel) use ($options) { // todo check user rights
              return $this->serializer->serialize($panel, $options);
          }, $facet->getPanelFacets()->toArray()),
        ];
    }

    /**
     * @param array $data
     * @param Facet $facet
     * @param array $options
     */
    public function deserialize(array $data, Facet $facet = null, array $options = [])
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
                $panelFacet = $this->serializer->deserialize('Claroline\CoreBundle\Entity\Facet\PanelFacet', $section, $options);
                $panelFacet->setFacet($facet);
            }
        }

        // todo deserialize roles here too
    }
}
