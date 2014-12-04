<?php

namespace Icap\NotificationBundle\Listener;

use Claroline\CoreBundle\Menu\ConfigureMenuEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Translation\Translator;
use Icap\NotificationBundle\Manager\NotificationManager;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * @DI\Service()
 */
class ConfigureTopLeftMenuListener
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
        Translator $translator,
        NotificationManager $notificationManager,
        TwigEngine $templating,
        SecurityContext $securityContext
    )
    {
        $this->translator          = $translator;
        $this->notificationManager = $notificationManager;
        $this->templating          = $templating;
        $this->securityContext     = $securityContext;
    }

    /**
     * @DI\Observe("claroline_top_bar_left_menu_configure")
     *
     * @param \Acme\DemoBundle\Event\ConfigureMenuEvent $event
     */
    public function onMenuConfigure(ConfigureMenuEvent $event)
    {
        $user = $this->securityContext->getToken()->getUser();

        if ($user !== 'anon.') {
            $countUnviewedNotifications = $this->notificationManager->countUnviewedNotifications($user->getId());

            $end = $this->templating->render(
                'IcapNotificationBundle:Notification:dropdownScript.html.twig',
                array('notificationElementId' => 'notification-topbar-item')
            );

            $menu = $event->getMenu();
            $menu->addChild($this->translator->trans('notifications', array(), 'platform'), array('route' => 'icap_notification_view'))
                ->setExtra('icon', 'fa fa-bell')
                ->setExtra('title', $this->translator->trans('notifications', array(), 'platform'))
                ->setAttribute('id', 'notification-topbar-item')
                ->setExtra('close', $end)
                ->setExtra('badge', $countUnviewedNotifications);

            return $menu;
        }
    }
}
