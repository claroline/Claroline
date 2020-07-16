<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Configuration;

use Claroline\CoreBundle\Library\Configuration\ParameterProviderInterface;

class PlatformDefaults implements ParameterProviderInterface
{
    public function getDefaultParameters()
    {
        return [
            'cursus' => [
                'disable_certificates' => false,
                'disable_invitations' => false,
                'disable_session_event_registration' => false,
                'disable_session_registration' => false,
                'session_default_duration' => 1,
                'session_default_total' => null,
            ],
        ];
    }
}
