<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\BigBlueButtonBundle\Configuration;

use Claroline\CoreBundle\Library\Configuration\ParameterProviderInterface;

class PlatformDefaults implements ParameterProviderInterface
{
    public function getDefaultParameters(): array
    {
        return [
            'bbb' => [
                'allow_records' => false,
                'max_meetings' => 0,
                'max_meeting_participants' => 0,
                'max_participants' => 0,
                'servers' => [],
            ],
        ];
    }
}
