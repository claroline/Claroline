<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

class Builder extends ContainerAware
{
    public function topBarRightMenu(FactoryInterface $factory, array $options)
    {
        $translator = $this->container->get('translator');
        $securityContext = $this->container->get('security.context');
        $hasRoleExtension = $this->container->get('claroline.core_bundle.twig.has_role_extension');

        $menu = $factory->createItem('root')
            ->setChildrenAttribute('class', 'dropdown-menu')
            ->setChildrenAttribute('role', 'menu');

        $menu->addChild($translator->trans('my_profile', array(), 'platform'), array('route' => 'claro_profile_view'))
            ->setAttribute('class', 'dropdown')
            ->setAttribute('role', 'presentation')
            ->setExtra('icon', 'fa fa-user');

        $this->addDivider($menu, '1');

        $user = $securityContext->getToken()->getUser();

        if ($user instanceof \Claroline\CoreBundle\Entity\User) {
            $pws = $user->getPersonalWorkspace();
        } else {
            $pws = null;
        }

        if ($pws) {
            $menu->addChild(
                $translator->trans('my_workspace', array(), 'platform'),
                array(
                    'route' => 'claro_workspace_open_tool',
                    'routeParameters' => array(
                        'workspaceId' => $pws->getId(),
                        'toolName' => 'home'
                        )
                    )
                )
            ->setAttribute('class', 'dropdown')
            ->setAttribute('role', 'presentation')
            ->setExtra('icon', 'fa fa-book');
        }

        $menu->addChild($translator->trans('my_agenda', array(), 'platform'), array('route' => 'claro_profile_view'))
            ->setAttribute('class', 'dropdown')
            ->setAttribute('role', 'presentation')
            ->setExtra('icon', 'fa fa-calendar');

        $menu->addChild(
            $translator->trans('my_resources', array(), 'platform'),
            array(
                'route' => 'claro_desktop_open_tool',
                'routeParameters' => array('toolName' => 'resourceManager')
            )
        )
            ->setAttribute('class', 'dropdown')
            ->setAttribute('role', 'presentation')
            ->setExtra('icon', 'fa fa-folder')
            ->setExtra('uri-add', '#/resource/0');

        $menu->addChild($translator->trans('my_badges', array(), 'platform'), array('route' => 'claro_profile_view_badges'))
            ->setAttribute('class', 'dropdown')
            ->setAttribute('role', 'presentation')
            ->setExtra('icon', 'fa fa-trophy');

        //allowing the menu to be extended
        $this->container->get('event_dispatcher')->dispatch(
            'claroline_top_bar_right_menu_configure',
            new ConfigureMenuEvent($factory, $menu)
        );

        $this->addDivider($menu, '2');

        //logout
        if ($hasRoleExtension->isImpersonated()) {
            $route = array(
                'route' => 'claro_desktop_open',
                'routeParameters' => array('_switch' => 'exit')
            );
        } else {
            $route = array('route' => 'claro_security_logout');
        }

        $menu->addChild($translator->trans('logout', array(), 'platform'), $route)
            ->setAttribute('class', 'dropdown')
            ->setAttribute('role', 'presentation')
            ->setAttribute('name', 'logout')
            ->setAttribute('id', 'btn-logout')
            ->setExtra('icon', 'fa fa-power-off');

        return $menu;
    }

    public function topBarLeftMenu(FactoryInterface $factory, array $options)
    {
        $translator = $this->container->get('translator');
        $securityContext = $this->container->get('security.context');
        $configHandler = $this->container->get('claroline.config.platform_config_handler');

        $menu = $factory->createItem('root')
            ->setChildrenAttribute('class', 'nav navbar-nav');

         if ($configHandler->getParameter('name') == "" && $configHandler->getParameter('logo') == "") {
             $menu->addChild($translator->trans('home', array(), 'platform'), array('route' => 'claro_index'))
                ->setExtra('icon', 'fa fa-home');
         }

        $menu->addChild($translator->trans('my_profile', array(), 'platform'), array('route' => 'claro_desktop_open'))
            ->setAttribute('role', 'presentation')
            ->setExtra('icon', 'fa fa-briefcase')
            ->setExtra('title', $translator->trans('desktop', array(), 'platform'));

        $token = $securityContext->getToken();
        $tools = $this->container->get('claroline.manager.tool_manager')
            ->getAdminToolsByRoles($token->getRoles());
        $canAdministrate = count($tools) > 0;

        if ($canAdministrate) {
            $menu->addChild($translator->trans('administration', array(), 'platform'), array('route' => 'claro_admin_index'))
                ->setExtra('icon', 'fa fa-cog')
                ->setExtra('title', $translator->trans('administration', array(), 'platform'));
        }

        if ($token) {
            $user = $token->getUser();
            $roles = $this->container->get('claroline.security.utilities')->getRoles($token);
        } else {
            $roles = array('ROLE_ANONYMOUS');
        }

        if (!in_array('ROLE_ANONYMOUS', $roles)) {
            $countUnreadMessages = $this->container->get('claroline.manager.message_manager')->getNbUnreadMessages($user);
            $messageTitle = $translator->trans('new_message_alert', array('%count%' => $countUnreadMessages), 'platform');

            $menu->addChild($translator->trans('messages', array(), 'platform'), array('route' => 'claro_message_list_received'))
                ->setExtra('icon', 'fa fa-envelope')
                ->setExtra('title', $messageTitle)
                ->setExtra('badge', $countUnreadMessages);
        }

        //allowing the menu to be extended
        $this->container->get('event_dispatcher')->dispatch(
            'claroline_top_bar_left_menu_configure',
            new ConfigureMenuEvent($factory, $menu)
        );

        return $menu;
    }

    public function addDivider($menu, $name)
    {
        $menu->addChild($name)
            ->setAttribute('class', 'divider')
            ->setAttribute('role', 'presentation');

    }
}
