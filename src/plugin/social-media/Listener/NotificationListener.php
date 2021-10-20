<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 *
 * Date: 5/18/15
 */

namespace Icap\SocialmediaBundle\Listener;

use Claroline\CoreBundle\Event\Notification\NotificationUserParametersEvent;
use Icap\NotificationBundle\Event\Notification\NotificationCreateDelegateViewEvent;
use Icap\SocialmediaBundle\Event\Log\LogSocialmediaLikeEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class NotificationListener
{
    /** @var TranslatorInterface */
    private $translator;
    /** @var RouterInterface */
    private $router;

    public function __construct(TranslatorInterface $translator, RouterInterface $router)
    {
        $this->translator = $translator;
        $this->router = $router;
    }

    public function onCreateNotificationItem(NotificationCreateDelegateViewEvent $event)
    {
        $notificationView = $event->getNotificationView();
        $notification = $notificationView->getNotification();
        $slug = isset($notification->getDetails()['resource']['slug']) ? $notification->getDetails()['resource']['slug'] : $notification->getDetails()['id'];

        $primaryAction = [
            'url' => 'claro_index',
            'parameters' => ['#' => '/desktop/open/'.$slug.'/resources/'.$slug],
        ];

        $text = '';
        switch ($notification->getActionKey()) {
            case LogSocialmediaLikeEvent::ACTION:
                $text .= $this->translator->trans('liked', [], 'icap_socialmedia');
                break;
        }

        $text .= ' '.$notification->getDetails()['resource']['name'];

        $event->setText($text);
        $event->setPrimaryAction($primaryAction);
    }

    public function onGetTypesForParameters(NotificationUserParametersEvent $event)
    {
        $event->addTypes('icap_socialmedia');
    }
}
