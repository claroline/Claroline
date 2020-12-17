<?php

namespace Claroline\OpenBadgeBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\OpenBadgeBundle\Entity\Endorsement;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class EndorsementSerializer
{
    use SerializerTrait;

    /**
     * @param RouterInterface $router
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

    public function getName()
    {
        return 'open_badge_endorsement';
    }

    public function getClass()
    {
        return Endorsement::class;
    }
}
