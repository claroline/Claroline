<?php

namespace Icap\NotificationBundle\Listener;

use Claroline\CoreBundle\Menu\ConfigureMenuEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Translation\DataCollectorTranslator;
use Icap\NotificationBundle\Manager\NotificationManager;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * @DI\Service()
 */
class ConfigureMenuListener
{
    private $translator;
    private $notificationManager;
    private $templating;
    private $securityContext;

    /**
     * @DI\InjectParams({
     *     "translator"          = @DI\Inject("translator"),
     *     "notificationManager" = @DI\Inject("icap.notification.manager"),
     *     "templating"          = @DI\Inject("templating"),
     *     "securityContext"     = @DI\Inject("security.context")
     * })
     */
    public function __construct(
        DataCollectorTranslator $translator,
        NotificationManager $notificationManager,
        TwigEngine $templating,
        SecurityContext $securityContext
    ) {
        $this->translator = $translator;
        $this->notificationManager = $notificationManager;
        $this->templating = $templating;
        $this->securityContext = $securityContext;
    }

    /**
     * @DI\Observe("claroline_top_bar_left_menu_configure")
     *
     * @param \Claroline\CoreBundle\Menu\ConfigureMenuEvent $event
     * @return \Knp\Menu\ItemInterface $menu
     */
    public function onTopBarLeftMenuConfigure(ConfigureMenuEvent $event)
    {
        $user = $this->securityContext->getToken()->getUser();

        if ($user !== 'anon.') {
            $countUnviewedNotifications = $this->notificationManager->countUnviewedNotifications($user->getId());

            $end = $this->templating->render(
                'IcapNotificationBundle:Notification:dropdownScript.html.twig',
                array('notificationElementId' => 'notification-topbar-item')
            );

            $menu = $event->getMenu();
            $countUnviewedNotificationsMenuLink = $menu->addChild(
                $this->translator->trans('notifications', array(), 'platform'),
                array('route' => 'icap_notification_view')
            )
                ->setExtra('icon', 'fa fa-bell')
                ->setExtra('title', $this->translator->trans('notifications', array(), 'platform'))
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
     * @return \Knp\Menu\ItemInterface $menu
     */
    public function onDesktopParametersMenuConfigure(ConfigureMenuEvent $event)
    {
        $user = $this->securityContext->getToken()->getUser();

        if ($user !== 'anon.') {
            $menu = $event->getMenu();
            $menu->addChild(
                $this->translator->trans('notifications', array(), 'platform'),
                array('route' => 'icap_notification_user_parameters')
            );

            return $menu;
        }
    }
}
