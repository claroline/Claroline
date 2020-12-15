<?php

namespace Claroline\OpenBadgeBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\OpenBadgeBundle\Entity\Assertion;
use Symfony\Component\Routing\RouterInterface;

class VerificationObjectSerializer
{
    use SerializerTrait;

    /**
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function getName()
    {
        return 'open_badge_verification_object';
    }

    public function serialize(Assertion $assertion)
    {
        return [
          'type' => 'SignedBadge',
          //is a link to a cryptographic key
          'creator' => null,
        ];
    }
}
