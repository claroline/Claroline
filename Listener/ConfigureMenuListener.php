<?php

namespace Icap\OAuthBundle\Listener;

use Claroline\CoreBundle\Menu\ConfigureMenuEvent;
use Icap\OAuthBundle\Model\Configuration;
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
        foreach (Configuration::resourceOwners() as $resourceOwner) {
            $menu->addChild(
                $resourceOwner,
                array(
                    'route' => 'claro_admin_oauth_form',
                    'routeParameters' => array('service' => strtolower($resourceOwner))
                )
            )->setExtra('name', $resourceOwner);
        }

        return $menu;
    }
}
