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
use HeVinci\CompetencyBundle\Entity\Level;

class LevelSerializer
{
    use SerializerTrait;

    /**
     * @param Level $level
     * @param array $options
     *
     * @return array
     */
    public function serialize(Level $level, array $options = [])
    {
        $serialized = [
            'id' => $level->getUuid(),
            'name' => $level->getName(),
            'value' => $level->getValue(),
        ];

        return $serialized;
    }

    public function getName()
    {
        return 'competency_level';
    }
}
