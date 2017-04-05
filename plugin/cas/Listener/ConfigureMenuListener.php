<?php

namespace Claroline\CasBundle\Listener;

use Claroline\CoreBundle\Menu\ConfigureMenuEvent;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service()
 */
class ConfigureMenuListener
{
    /**
     * @DI\Observe("claroline_external_authentication_menu_configure", priority=3)
     *
     * @param \Claroline\CoreBundle\Menu\ConfigureMenuEvent $event
     *
     * @return \Knp\Menu\ItemInterface $menu
     */
    public function onExternalAuthenticationMenuConfigure(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu();
        $menu->addChild(
            'CAS',
            [
                'route' => 'claro_admin_cas_server_config_form',
            ]
        )->setExtra('name', 'CAS');

        return $menu;
    }
}
