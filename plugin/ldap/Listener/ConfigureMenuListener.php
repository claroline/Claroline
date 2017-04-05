<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\LdapBundle\Listener;

use Claroline\CoreBundle\Menu\ConfigureMenuEvent;
use JMS\DiExtraBundle\Annotation\Observe;
use JMS\DiExtraBundle\Annotation\Service;

/**
 * @Service()
 */
class ConfigureMenuListener
{
    /**
     * @Observe("claroline_external_authentication_menu_configure", priority=2)
     *
     * @param ConfigureMenuEvent $event
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function onKernelRequest(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu();
        $menu->addChild(
            'LDAP',
            [
                'route' => 'claro_admin_ldap',
            ]
        )->setExtra('name', 'LDAP');

        return $menu;
    }
}
