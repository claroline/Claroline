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
use Claroline\CommunityBundle\Serializer\GroupSerializer;
use Claroline\CursusBundle\Entity\Registration\AbstractGroupRegistration;
use Claroline\CursusBundle\Entity\Registration\SessionGroup;
use Claroline\CursusBundle\Serializer\SessionSerializer;

class SessionGroupSerializer extends AbstractGroupSerializer
{
    use SerializerTrait;

    /** @var SessionSerializer */
    private $sessionSerializer;

    public function __construct(GroupSerializer $groupSerializer, SessionSerializer $sessionSerializer)
    {
        parent::__construct($groupSerializer);

        $this->sessionSerializer = $sessionSerializer;
    }

    public function getClass()
    {
        return SessionGroup::class;
    }

    /**
     * @param SessionGroup $sessionGroup
     */
    public function serialize(AbstractGroupRegistration $sessionGroup, array $options = []): array
    {
        return array_merge(parent::serialize($sessionGroup, $options), [
            'session' => $this->sessionSerializer->serialize($sessionGroup->getSession(), [Options::SERIALIZE_MINIMAL]),
        ]);
    }
}
