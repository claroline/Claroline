<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Library\Installation\Updater;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater120000 extends Updater
{
    /** @var PlatformConfigurationHandler */
    private $pch;

    public function __construct(ContainerInterface $container)
    {
        $this->pch = $container->get('claroline.config.platform_config_handler');
    }

    public function postUpdate()
    {
        $this->updatePlatformOptions();
    }

    private function updatePlatformOptions()
    {
        if (!$this->pch->hasParameter('cursus')) {
            $this->log('Updating cursus options in platform configuration file...');
            $cursusOptions = [
                'disable_certificates' => $this->pch->hasParameter('cursus_disable_certificates') ?
                    $this->pch->getParameter('cursus_disable_certificates') :
                    false,
                'disable_invitations' => $this->pch->hasParameter('disable_invitations') ?
                    $this->pch->getParameter('disable_invitations') :
                    false,
                'disable_session_event_registration' => $this->pch->hasParameter('disable_session_event_registration') ?
                    $this->pch->getParameter('disable_session_event_registration') :
                    false,
                'display_user_events_in_desktop_agenda' => $this->pch->hasParameter('display_user_events_in_desktop_agenda') ?
                    $this->pch->getParameter('display_user_events_in_desktop_agenda') :
                    false,
                'enable_courses_profile_tab' => $this->pch->hasParameter('enable_courses_profile_tab') ?
                    $this->pch->getParameter('enable_courses_profile_tab') :
                    false,
                'enable_ws_in_courses_profile_tab' => $this->pch->hasParameter('enable_ws_in_courses_profile_tab') ?
                    $this->pch->getParameter('enable_ws_in_courses_profile_tab') :
                    false,
                'session_default_duration' => $this->pch->hasParameter('session_default_duration') ?
                    $this->pch->getParameter('session_default_duration') :
                    1,
                'session_default_total' => $this->pch->hasParameter('session_default_total') ?
                    $this->pch->getParameter('session_default_total') :
                    null,
            ];
            $this->pch->setParameter('cursus', $cursusOptions);
            $this->log('Cursus options updated.');
        }
    }
}
