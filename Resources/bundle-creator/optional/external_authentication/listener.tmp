<?php

namespace [[Vendor]]\[[Bundle]]Bundle\Listener;

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
            '[[external_authentication]]',
            array('route' => '[[vendor]]_[[external_authentication]]_form')
        )->setExtra('name', '[[external_authentication]]');

        return $menu;
    }
}
