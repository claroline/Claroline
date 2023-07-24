<?php

namespace Claroline\CommunityBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\Facet\FacetSerializer;
use Claroline\CoreBundle\Entity\Facet\Facet;

class ProfileSerializer
{
    private FacetSerializer $facetSerializer;

    private $repository;

    public function __construct(
        ObjectManager $om,
        FacetSerializer $facetSerializer
    ) {
        $this->repository = $om->getRepository(Facet::class);
        $this->facetSerializer = $facetSerializer;
    }

    public function getName(): string
    {
        return 'profile';
    }

    /**
     * Serializes the profile configuration.
     */
    public function serialize(?array $options = []): array
    {
        if (in_array(Options::REGISTRATION, $options)) {
            // only get facets configured to be displayed in the registration form
            $facets = $this->repository->findBy(['forceCreationForm' => true]);
        } else {
            $facets = $this->repository->findAll();
        }

        return array_map(function (Facet $facet) use ($options) {
            return $this->facetSerializer->serialize($facet, $options);
        }, $facets);
    }
}
