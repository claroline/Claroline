<?php

namespace Claroline\OpenBadgeBundle\Serializer;

use Claroline\AppBundle\API\Options as ApiOptions;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\OpenBadgeBundle\Entity\Assertion;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class AssertionSerializer
{
    use SerializerTrait;

    /** @var UserSerializer */
    private $userSerializer;
    /** @var BadgeClassSerializer */
    private $badgeSerializer;
    /** @var RouterInterface */
    private $router;
    /** @var VerificationObjectSerializer */
    private $verificationObjectSerializer;

    public function __construct(
        UserSerializer $userSerializer,
        BadgeClassSerializer $badgeSerializer,
        RouterInterface $router,
        VerificationObjectSerializer $verificationObjectSerializer
    ) {
        $this->userSerializer = $userSerializer;
        $this->badgeSerializer = $badgeSerializer;
        $this->router = $router;
        $this->verificationObjectSerializer = $verificationObjectSerializer;
    }

    public function getName()
    {
        return 'open_badge_assertion';
    }

    public function getClass()
    {
        return Assertion::class;
    }

    public function serialize(Assertion $assertion, array $options = []): array
    {
        if (in_array(Options::ENFORCE_OPEN_BADGE_JSON, $options)) {
            return [
                'uid' => $assertion->getUuid(),
                'id' => $this->router->generate('apiv2_open_badge__assertion', ['assertion' => $assertion->getUuid()], UrlGeneratorInterface::ABSOLUTE_URL),
                'type' => 'Assertion',
                'verify' => $this->verificationObjectSerializer->serialize($assertion),
                //doc is uncomplete ? verify works but not verification. For mozilla backpack.
                //'verification' => $this->verificationObjectSerializer->serialize($assertion),
                //'recipient' => $this->identityObjectSerializer->serialize($assertion->getRecipient(), [Options::ENFORCE_OPEN_BADGE_JSON]),
                //they don't follow their owndock ? it's for mozilla backpack
                'recipient' => $assertion->getRecipient()->getEmail(),
                'badge' => $this->badgeSerializer->serialize($assertion->getBadge(), [Options::ENFORCE_OPEN_BADGE_JSON]),
                'issuedOn' => $assertion->getIssuedOn()->format('Y-m-d'),
                'expires' => $this->getExpireDate($assertion),
                'revoked' => $assertion->getRevoked(),
            ];
        }

        return [
            'id' => $assertion->getUuid(),
            'user' => $this->userSerializer->serialize($assertion->getRecipient(), [ApiOptions::SERIALIZE_MINIMAL]),
            'badge' => $this->badgeSerializer->serialize($assertion->getBadge(), [ApiOptions::SERIALIZE_MINIMAL]),
            'issuedOn' => DateNormalizer::normalize($assertion->getIssuedOn()),
            'expires' => $this->getExpireDate($assertion),
        ];
    }

    public function getExpireDate(Assertion $assertion)
    {
        $badge = $assertion->getBadge();
        $date = $assertion->getIssuedOn();
        $date->modify('+ '.$badge->getDurationValidation().' day');

        return $date->format('Y-m-d');
    }
}
