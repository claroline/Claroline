<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Library\Configuration;

use Claroline\CoreBundle\Library\Configuration\ParameterProviderInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service()
 * @DI\Tag("claroline.configuration")
 */
class DefaultCursusPlatformConfiguration implements ParameterProviderInterface
{
    public function getDefaultParameters()
    {
        return [
            'cursus' => [
                'disable_certificates' => false,
                'disable_invitations' => false,
                'disable_session_event_registration' => false,
                'display_user_events_in_desktop_agenda' => false,
                'enable_courses_profile_tab' => false,
                'enable_ws_in_courses_profile_tab' => false,
                'session_default_duration' => 1,
                'session_default_total' => null,
            ],
        ];
    }
}
