<?php

namespace Claroline\OpenBadgeBundle\Serializer;

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
    /** @var ProfileSerializer */
    private $profileSerializer;
    /** @var RouterInterface */
    private $router;
    /** @var VerificationObjectSerializer */
    private $verificationObjectSerializer;
    /** @var IdentityObjectSerializer */
    private $identityObjectSerializer;

    /**
     * AssertionSerializer constructor.
     *
     * @param UserSerializer               $userSerializer
     * @param BadgeClassSerializer         $badgeSerializer
     * @param ProfileSerializer            $profileSerializer
     * @param RouterInterface              $router
     * @param VerificationObjectSerializer $verificationObjectSerializer
     * @param IdentityObjectSerializer     $identityObjectSerializer
     */
    public function __construct(
        UserSerializer $userSerializer,
        BadgeClassSerializer $badgeSerializer,
        ProfileSerializer $profileSerializer,
        RouterInterface $router,
        VerificationObjectSerializer $verificationObjectSerializer,
        IdentityObjectSerializer $identityObjectSerializer
    ) {
        $this->userSerializer = $userSerializer;
        $this->badgeSerializer = $badgeSerializer;
        $this->profileSerializer = $profileSerializer;
        $this->router = $router;
        $this->verificationObjectSerializer = $verificationObjectSerializer;
        $this->identityObjectSerializer = $identityObjectSerializer;
    }

    /**
     * Serializes a Assertion entity.
     *
     * @param Assertion $assertion
     * @param array     $options
     *
     * @return array
     */
    public function serialize(Assertion $assertion, array $options = [])
    {
        if (in_array(Options::ENFORCE_OPEN_BADGE_JSON, $options)) {
            $data = [
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
                //no implementation right now
                'revoked' => false,
            ];
        } else {
            $data = [
                'id' => $assertion->getUuid(),
                'user' => $this->userSerializer->serialize($assertion->getRecipient()),
                'badge' => $this->badgeSerializer->serialize($assertion->getBadge()),
                'issuedOn' => DateNormalizer::normalize($assertion->getIssuedOn()),
            ];
        }

        return $data;
    }

    public function getExpireDate(Assertion $assertion)
    {
        $badge = $assertion->getBadge();
        $date = $assertion->getIssuedOn();
        $date->modify('+ '.$badge->getDurationValidation().' day');

        return $date->format('Y-m-d');
    }

    public function getClass()
    {
        return Assertion::class;
    }
}
