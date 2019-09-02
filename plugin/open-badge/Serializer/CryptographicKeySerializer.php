<?php

namespace Claroline\OpenBadgeBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\Entity\Cryptography\CryptographicKey;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class CryptographicKeySerializer
{
    use SerializerTrait;

    public function __construct(RouterInterface $router, ProfileSerializer $profileSerializer)
    {
        $this->router = $router;
        $this->profileSerializer = $profileSerializer;
    }

    public function serialize(CryptographicKey $crypto)
    {
        return  [
            'type' => 'CryptographicKey',
            'id' => $this->router->generate('apiv2_open_badge__profile', ['profile' => $crypto->getOrganization()->getUuid()], UrlGeneratorInterface::ABSOLUTE_URL),
            'publicKeyParam' => $crypto->getPublicKeyParam(),
            'id' => $this->router->generate('apiv2_open_badge__cryptographic_key', ['key' => $crypto->getUuid()], UrlGeneratorInterface::ABSOLUTE_URL),
        ];
    }

    public function getClass()
    {
        return CryptographicKey::class;
    }
}
