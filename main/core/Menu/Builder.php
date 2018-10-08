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
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class Builder implements ContainerAwareInterface
{
    use ContainerAwareTrait;

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
