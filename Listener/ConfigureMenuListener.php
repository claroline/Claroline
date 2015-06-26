<?php

namespace Icap\OAuthBundle\Listener;

use Claroline\CoreBundle\Menu\ConfigureMenuEvent;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service()
 */
class ConfigureMenuListener
{
    /**
     * @DI\Observe("claroline_external_authentication_menu_configure")
     *
     * @param \Claroline\CoreBundle\Menu\ConfigureMenuEvent $event
     * @return \Knp\Menu\ItemInterface $menu
     */
    public function onTopBarLeftMenuConfigure(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu();
        $menu->addChild(
            'Facebook',
            array('route' => 'claro_admin_facebook_form')
        )->setExtra('name', 'Facebook');

        return $menu;
    }
}
