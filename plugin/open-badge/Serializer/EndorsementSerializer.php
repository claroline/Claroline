<?php

namespace Claroline\OpenBadgeBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\OpenBadgeBundle\Entity\Endorsement;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * @DI\Service()
 * @DI\Tag("claroline.serializer")
 */
class EndorsementSerializer
{
    use SerializerTrait;

    /**
     * @DI\InjectParams({
     *     "router" = @DI\Inject("router")
     * })
     *
     * @param Router $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function serialize(Endorsement $endorsement)
    {
        return  [
            'id' => $this->router->generate('apiv2_open_badge__endorsement', ['endorsement' => $endorsement->getUuid()], UrlGeneratorInterface::ABSOLUTE_URL),
        ];
    }

    public function getClass()
    {
        return Endorsement::class;
    }
}
