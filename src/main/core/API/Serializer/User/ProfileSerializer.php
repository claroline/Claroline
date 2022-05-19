<?php

namespace Claroline\CoreBundle\API\Serializer\User;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\Facet\FacetSerializer;
use Claroline\CoreBundle\Entity\Facet\Facet;
use Claroline\CoreBundle\Repository\Facet\FacetRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ProfileSerializer
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var FacetRepository */
    private $repository;

    /** @var FacetSerializer */
    private $facetSerializer;

    /**
     * ProfileSerializer constructor.
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        ObjectManager $om,
        FacetSerializer $facetSerializer
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->repository = $om->getRepository(Facet::class);
        $this->facetSerializer = $facetSerializer;
    }

    public function getName()
    {
        return 'profile';
    }

    /**
     * Serializes the profile configuration.
     *
     * @return array
     */
    public function serialize(array $options = [])
    {
        $facets = $this->repository
            ->findVisibleFacets($this->tokenStorage->getToken(), in_array(Options::REGISTRATION, $options));

        return array_map(function (Facet $facet) {
            return $this->facetSerializer->serialize($facet);
        }, $facets);
    }
}
