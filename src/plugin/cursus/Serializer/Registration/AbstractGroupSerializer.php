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
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CursusBundle\Entity\Registration\AbstractGroupRegistration;

abstract class AbstractGroupSerializer
{
    use SerializerTrait;

    /** @var GroupSerializer */
    private $groupSerializer;

    public function __construct(GroupSerializer $groupSerializer)
    {
        $this->groupSerializer = $groupSerializer;
    }

    public function serialize(AbstractGroupRegistration $groupRegistration, array $options = []): array
    {
        return [
            'id' => $groupRegistration->getUuid(),
            'type' => $groupRegistration->getType(),
            'date' => DateNormalizer::normalize($groupRegistration->getDate()),
            'group' => $this->groupSerializer->serialize($groupRegistration->getGroup(), [Options::SERIALIZE_MINIMAL]),
        ];
    }
}
