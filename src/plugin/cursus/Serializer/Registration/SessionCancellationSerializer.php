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
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CursusBundle\Entity\Registration\SessionCancellation;
use Claroline\CursusBundle\Serializer\SessionSerializer;

class SessionCancellationSerializer
{
    use SerializerTrait;

    /** @var SessionSerializer */
    private $sessionSerializer;

    /** @var UserSerializer */
    private $userSerializer;

    public function __construct(UserSerializer $userSerializer, SessionSerializer $sessionSerializer)
    {
        $this->userSerializer = $userSerializer;
        $this->sessionSerializer = $sessionSerializer;
    }

    public function getClass()
    {
        return SessionCancellation::class;
    }

    /**
     * @param SessionCancellation $sessionUser
     */
    public function serialize(SessionCancellation $sessionCancellation, array $options = []): array
    {
        return [
            'id' => $sessionCancellation->getUuid(),
            'date' => DateNormalizer::normalize($sessionCancellation->getDate()),
            'user' => $this->userSerializer->serialize($sessionCancellation->getUser(), [Options::SERIALIZE_MINIMAL]),
            'session' => $this->sessionSerializer->serialize($sessionCancellation->getSession(), [Options::SERIALIZE_MINIMAL]),
        ];
    }
}
