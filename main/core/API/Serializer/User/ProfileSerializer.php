<?php

namespace Claroline\CoreBundle\API\Serializer\User;

use Claroline\CoreBundle\API\Options;
use Claroline\CoreBundle\API\Serializer\Facet\FacetSerializer;
use Claroline\CoreBundle\Entity\Facet\Facet;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Repository\Facet\FacetRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @DI\Service("claroline.serializer.profile")
 */
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
     *
     * @DI\InjectParams({
     *     "tokenStorage"    = @DI\Inject("security.token_storage"),
     *     "om"              = @DI\Inject("claroline.persistence.object_manager"),
     *     "facetSerializer" = @DI\Inject("claroline.serializer.facet")
     * })
     *
     * @param TokenStorageInterface $tokenStorage
     * @param ObjectManager         $om
     * @param FacetSerializer       $facetSerializer
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        ObjectManager $om,
        FacetSerializer $facetSerializer
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->repository = $om->getRepository('ClarolineCoreBundle:Facet\Facet');
        $this->facetSerializer = $facetSerializer;
    }

    /**
     * Serializes the profile configuration.
     *
     * @param array $options
     *
     * @return array
     */
    public function serialize(array $options = [])
    {
        $facets = $this->repository
            ->findVisibleFacets($this->tokenStorage->getToken(), in_array(Options::REGISTRATION, $options));

        return array_map(function (Facet $facet) {
            return $this->facetSerializer->serialize($facet, [Options::PROFILE_SERIALIZE]);
        }, $facets);
    }
}
