<?php

namespace Claroline\OpenBadgeBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\Entity\Cryptography\CryptographicKey;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * @DI\Service()
 * @DI\Tag("claroline.serializer")
 */
class CryptographicKeySerializer
{
    use SerializerTrait;

    /**
     * @DI\InjectParams({
     *     "router"             = @DI\Inject("router"),
     *     "profileSerializer"  = @DI\Inject("claroline.serializer.open_badge.profile")
     * })
     *
     * @param Router $router
     */
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
