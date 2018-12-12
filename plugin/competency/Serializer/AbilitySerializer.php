<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HeVinci\CompetencyBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use HeVinci\CompetencyBundle\Entity\Ability;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.ability")
 * @DI\Tag("claroline.serializer")
 */
class AbilitySerializer
{
    use SerializerTrait;

    /**
     * @param Ability $ability
     * @param array   $options
     *
     * @return array
     */
    public function serialize(Ability $ability, array $options = [])
    {
        $serialized = [
            'id' => $ability->getUuid(),
            'name' => $ability->getName(),
            'minResourceCount' => $ability->getMinResourceCount(),
        ];

        return $serialized;
    }
}
