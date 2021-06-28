<?php

namespace Claroline\OpenBadgeBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\Entity\Cryptography\CryptographicKey;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class CryptographicKeySerializer
{
    use SerializerTrait;

    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function serialize(CryptographicKey $crypto)
    {
        return [
            'type' => 'CryptographicKey',
            'publicKeyParam' => $crypto->getPublicKeyParam(),
            'id' => $this->router->generate('apiv2_open_badge__cryptographic_key', ['key' => $crypto->getUuid()], UrlGeneratorInterface::ABSOLUTE_URL),
        ];
    }

    public function getName()
    {
        return 'open_badge_cryptographic_key';
    }

    public function getClass()
    {
        return CryptographicKey::class;
    }
}
