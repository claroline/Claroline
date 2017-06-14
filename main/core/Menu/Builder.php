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
        $tokenStorage = $this->container->get('security.token_storage');
        $hasRoleExtension = $this->container->get('claroline.core_bundle.twig.has_role_extension');
        $router = $this->container->get('router');
        $dispatcher = $this->container->get('event_dispatcher');
        /** @var \Claroline\CoreBundle\Manager\ToolManager $toolManager */
        $toolManager = $this->container->get('claroline.manager.tool_manager');

        $menu = $factory->createItem('root')
            ->setChildrenAttribute('class', 'dropdown-menu')
            ->setChildrenAttribute('role', 'menu');

        $menu->addChild(
            $translator->trans('my_profile', [], 'platform'),
            ['uri' => $router->generate('claro_public_profile_view', ['publicUrl' => $tokenStorage->getToken()->getUser()->getPublicUrl()])]
        )->setAttribute('class', 'dropdown')
            ->setAttribute('role', 'presentation')
            ->setExtra('icon', 'fa fa-user');
        $menu->addChild(
            $translator->trans('preferences', [], 'platform'),
            ['uri' => $router->generate('claro_desktop_open_tool', ['toolName' => 'parameters'])]
        )->setAttribute('class', 'dropdown')
        ->setAttribute('role', 'presentation')
        ->setExtra('icon', 'fa fa-cogs');

        //allowing the menu to be extended
        $this->container->get('event_dispatcher')->dispatch(
            'claroline_top_bar_right_menu_configure',
            new ConfigureMenuEvent($factory, $menu)
        );

        $this->addDivider($menu, '1');

        $user = $tokenStorage->getToken()->getUser();
        $lockedOrderedTools = $toolManager->getOrderedToolsLockedByAdmin(1);
        $adminTools = [];
        $excludedTools = [];

        foreach ($lockedOrderedTools as $lockedOrderedTool) {
            $lockedTool = $lockedOrderedTool->getTool();

            if ($lockedOrderedTool->isVisibleInDesktop()) {
                $adminTools[] = $lockedTool;
            }
            $excludedTools[] = $lockedTool;
        }
        $desktopTools = $toolManager->getDisplayedDesktopOrderedTools(
            $user,
            1,
            $excludedTools
        );
        /** @var \Claroline\CoreBundle\Entity\Tool\Tool[] $tools */
        $tools = array_merge($adminTools, $desktopTools);

        $countPermanentMenuLinks = $menu->count();

        foreach ($tools as $tool) {
            $toolName = $tool->getName();

            if ($toolName === 'home' || $toolName === 'parameters') {
                continue;
            }
            $event = new ConfigureMenuEvent($factory, $menu, $tool);

            if ($dispatcher->hasListeners('claroline_top_bar_right_menu_configure_desktop_tool_'.$toolName)) {
                $dispatcher->dispatch(
                    'claroline_top_bar_right_menu_configure_desktop_tool_'.$toolName,
                    $event
                );
            } else {
                $dispatcher->dispatch(
                    'claroline_top_bar_right_menu_configure_desktop_tool',
                    $event
                );
            }
        }

        $countAddedToolMenuLinks = $menu->count();

        if ($countPermanentMenuLinks < $countAddedToolMenuLinks) {
            $this->addDivider($menu, '2');
        }

        //logout
        if ($hasRoleExtension->isImpersonated()) {
            $route = [
                'route' => 'claro_desktop_open',
                'routeParameters' => ['_switch' => 'exit'],
            ];
        } else {
            $route = ['route' => 'claro_security_logout'];
        }

        $menu->addChild($translator->trans('logout', [], 'platform'), $route)
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
        $tokenStorage = $this->container->get('security.token_storage');
        $configHandler = $this->container->get('claroline.config.platform_config_handler');

        $menu = $factory->createItem('root')
            ->setChildrenAttribute('class', 'nav navbar-nav');

        if ($configHandler->getParameter('name') === '' && $configHandler->getParameter('logo') === '') {
            $menu->addChild($translator->trans('home', [], 'platform'), ['route' => 'claro_index'])
                ->setExtra('icon', 'fa fa-home');
        }

        $menu->addChild($translator->trans('desktop', [], 'platform'), ['route' => 'claro_desktop_open'])
            ->setAttribute('role', 'presentation')
            ->setExtra('icon', 'fa fa-home')
            ->setExtra('title', $translator->trans('desktop', [], 'platform'));

        $token = $tokenStorage->getToken();

        if ($token) {
            $user = $token->getUser();
            $roles = $this->container->get('claroline.security.utilities')->getRoles($token);
        } else {
            $roles = ['ROLE_ANONYMOUS'];
        }

        if (!in_array('ROLE_ANONYMOUS', $roles)) {
            $dispatcher = $this->container->get('event_dispatcher');
            $toolManager = $this->container->get('claroline.manager.tool_manager');
            $lockedOrderedTools = $toolManager->getOrderedToolsLockedByAdmin();
            $adminTools = [];
            $excludedTools = [];

            foreach ($lockedOrderedTools as $lockedOrderedTool) {
                $lockedTool = $lockedOrderedTool->getTool();

                if ($lockedOrderedTool->isVisibleInDesktop()) {
                    $adminTools[] = $lockedTool;
                }
                $excludedTools[] = $lockedTool;
            }

            $desktopTools = $toolManager->getDisplayedDesktopOrderedTools(
                $user,
                0,
                $excludedTools
            );
            $tools = array_merge($adminTools, $desktopTools);

            foreach ($tools as $tool) {
                $toolName = $tool->getName();

                if ($toolName === 'home') {
                    continue;
                }
                $event = new ConfigureMenuEvent($factory, $menu, $tool);

                if ($dispatcher->hasListeners('claroline_top_bar_left_menu_configure_desktop_tool_'.$toolName)) {
                    $dispatcher->dispatch(
                        'claroline_top_bar_left_menu_configure_desktop_tool_'.$toolName,
                        $event
                    );
                } else {
                    $dispatcher->dispatch(
                        'claroline_top_bar_left_menu_configure_desktop_tool',
                        $event
                    );
                }
            }
        }

        //allowing the menu to be extended
        $this->container->get('event_dispatcher')->dispatch(
            'claroline_top_bar_left_menu_configure',
            new ConfigureMenuEvent($factory, $menu)
        );

        return $menu;
    }

    public function desktopParametersMenu(FactoryInterface $factory, array $options)
    {
        $translator = $this->container->get('translator');

        $menu = $factory->createItem('root')
            ->setChildrenAttribute('class', 'list-group menu desktop-parameters-menu');

        $menu->addChild(
            $translator->trans('menu_bar', [], 'platform'),
            [
                'route' => 'claro_tool_properties',
                'routeParameters' => ['type' => 0],
            ]);

        $menu->addChild(
            $translator->trans('user_menu', [], 'platform'),
            [
                'route' => 'claro_tool_properties',
                'routeParameters' => ['type' => 1],
            ]);

        $menu->addChild(
            $translator->trans('desktop_parameters', [], 'platform'),
            [
                'route' => 'claro_user_options_edit_form',
            ]);

        //allowing the menu to be extended
        $this->container->get('event_dispatcher')->dispatch(
            'claroline_desktop_parameters_menu_configure',
            new ConfigureMenuEvent($factory, $menu)
        );

        return $menu;
    }

    public function externalAuthenticationMenu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('root')
            ->setChildrenAttribute('class', 'nav nav-pills');

        //allowing the menu to be extended
        $this->container->get('event_dispatcher')->dispatch(
            'claroline_external_authentication_menu_configure',
            new ConfigureMenuEvent($factory, $menu)
        );

        return $menu;
    }

    public function externalParametersMenu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('root')
            ->setChildrenAttribute('class', 'nav nav-pills');

        //allowing the menu to be extended
        $this->container->get('event_dispatcher')->dispatch(
            'claroline_external_parameters_menu_configure',
            new ConfigureMenuEvent($factory, $menu)
        );

        return $menu;
    }

    public function contactActionsMenu(FactoryInterface $factory, array $options)
    {
        $user = $options['user'];
        $menu = $factory->createItem($user->getUsername())
            ->setChildrenAttribute('class', 'btn-group menu contact-actions-menu');

        $this->container->get('event_dispatcher')->dispatch(
            'claroline_contact_additional_action',
            new ContactAdditionalActionEvent($factory, $menu, $user)
        );

        return $menu;
    }

    public function groupActionsMenu(FactoryInterface $factory, array $options)
    {
        $group = $options['group'];
        $menu = $factory->createItem($group->getName())
            ->setChildrenAttribute('class', 'btn-group menu group-actions-menu');

        $this->container->get('event_dispatcher')->dispatch(
            'claroline_group_additional_action',
            new GroupAdditionalActionEvent($factory, $menu, $group)
        );

        return $menu;
    }

    public function userActionsMenu(FactoryInterface $factory, array $options)
    {
        $user = $options['user'];
        $menu = $factory->createItem($user->getUsername())
            ->setChildrenAttribute('class', 'btn-group menu user-actions-menu');

        $this->container->get('event_dispatcher')->dispatch(
            'claroline_user_additional_action',
            new UserAdditionalActionEvent($factory, $menu, $user)
        );

        return $menu;
    }

    public function workspaceActionsMenu(FactoryInterface $factory, array $options)
    {
        $workspace = $options['workspace'];
        $menu = $factory->createItem($workspace->getCode())
            ->setChildrenAttribute('class', 'btn-group menu user-actions-menu');

        $this->container->get('event_dispatcher')->dispatch(
            'claroline_workspace_additional_action',
            new WorkspaceAdditionalActionEvent($factory, $menu, $workspace)
        );

        return $menu;
    }

    public function workspaceUsersMenu(FactoryInterface $factory, array $options)
    {
        $user = $options['user'];
        $menu = $factory->createItem($user->getUsername())
            ->setChildrenAttribute('class', 'btn-group menu contact-actions-menu');

        $this->container->get('event_dispatcher')->dispatch(
            'claroline_workspace_users_action',
            new ContactAdditionalActionEvent($factory, $menu, $user)
        );

        return $menu;
    }

    public function exceptionActionsMenu(FactoryInterface $factory, array $options)
    {
        $user = $options['user'];
        $message = $options['message'];
        $exceptionClass = $options['exception_class'];
        $file = $options['file'];
        $line = $options['line'];
        $url = $options['url'];
        $referer = $options['referer'];
        $httpCode = isset($options['httpCode']) ? $options['httpCode'] : null;
        $menu = $factory->createItem('exception-actions')
            ->setChildrenAttribute('class', 'btn-group menu exception-actions-menu');

        $this->container->get('event_dispatcher')->dispatch(
            'claroline_exception_action',
            new ExceptionActionEvent(
                $factory,
                $menu,
                $user,
                $message,
                $exceptionClass,
                $file,
                $line,
                $url,
                $referer,
                $httpCode
            )
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
