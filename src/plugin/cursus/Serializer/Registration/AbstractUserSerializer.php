<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Serializer\Registration;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CursusBundle\Entity\Registration\AbstractUserRegistration;

abstract class AbstractUserSerializer
{
    use SerializerTrait;

    /** @var UserSerializer */
    private $userSerializer;

    public function __construct(UserSerializer $userSerializer)
    {
        $this->userSerializer = $userSerializer;
    }

    public function serialize(AbstractUserRegistration $userRegistration, array $options = []): array
    {
        return [
            'id' => $userRegistration->getUuid(),
            'type' => $userRegistration->getType(),
            'validated' => $userRegistration->isValidated(),
            'confirmed' => $userRegistration->isConfirmed(),
            'date' => DateNormalizer::normalize($userRegistration->getDate()),
            'user' => $this->userSerializer->serialize($userRegistration->getUser(), [Options::SERIALIZE_MINIMAL]),
        ];
    }
}
