<?php

namespace Icap\NotificationBundle\Listener;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Menu\ConfigureMenuEvent;
use Icap\NotificationBundle\Manager\NotificationManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service()
 */
class ConfigureMenuListener
{
    private $translator;
    private $notificationManager;
    private $templating;
    private $tokenStorage;
    private $ch;

    /**
     * @DI\InjectParams({
     *     "translator"          = @DI\Inject("translator"),
     *     "notificationManager" = @DI\Inject("icap.notification.manager"),
     *     "templating"          = @DI\Inject("templating"),
     *     "tokenStorage"        = @DI\Inject("security.token_storage"),
     *     "ch"                  = @DI\Inject("claroline.config.platform_config_handler")
     * })
     */
    public function __construct(
        TranslatorInterface $translator,
        NotificationManager $notificationManager,
        TwigEngine $templating,
        TokenStorageInterface $tokenStorage,
        PlatformConfigurationHandler $ch
    ) {
        $this->translator = $translator;
        $this->notificationManager = $notificationManager;
        $this->templating = $templating;
        $this->tokenStorage = $tokenStorage;
        $this->ch = $ch;
    }

    /**
     * @DI\Observe("claroline_top_bar_left_menu_configure")
     *
     * @param \Claroline\CoreBundle\Menu\ConfigureMenuEvent $event
     *
     * @return \Knp\Menu\ItemInterface $menu
     */
    public function onTopBarLeftMenuConfigure(ConfigureMenuEvent $event)
    {
        $user = $this->tokenStorage->getToken()->getUser();

        //this is still configurable in the core bundle and should be changed...
        $isActive = $this->ch->getParameter('is_notification_active');

        if ($user !== 'anon.' && $isActive) {
            $countUnviewedNotifications = $this->notificationManager->countUnviewedNotifications($user);

            $end = $this->templating->render(
                'IcapNotificationBundle:Notification:dropdownScript.html.twig',
                ['notificationElementId' => 'notification-topbar-item']
            );

            $menu = $event->getMenu();
            $countUnviewedNotificationsMenuLink = $menu->addChild(
                $this->translator->trans('notifications', [], 'platform'),
                ['route' => 'icap_notification_view']
            )
                ->setExtra('icon', 'fa fa-bell')
                ->setExtra('title', $this->translator->trans('notifications', [], 'platform'))
                ->setAttribute('id', 'notification-topbar-item')
                ->setExtra('close', $end);

            if (0 < $countUnviewedNotifications) {
                $countUnviewedNotificationsMenuLink
                    ->setExtra('badge', $countUnviewedNotifications);
            }

            return $menu;
        }
    }

    /**
     * @DI\Observe("claroline_desktop_parameters_menu_configure")
     *
     * @param \Claroline\CoreBundle\Menu\ConfigureMenuEvent $event
     *
     * @return \Knp\Menu\ItemInterface $menu
     */
    public function onDesktopParametersMenuConfigure(ConfigureMenuEvent $event)
    {
        $user = $this->tokenStorage->getToken()->getUser();

        if ($user !== 'anon.') {
            $menu = $event->getMenu();
            $menu->addChild(
                $this->translator->trans('notifications', [], 'platform'),
                ['route' => 'icap_notification_user_parameters']
            );

            return $menu;
        }
    }
}
