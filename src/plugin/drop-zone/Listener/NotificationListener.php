<?php

namespace Claroline\DropZoneBundle\Listener;

use Claroline\DropZoneBundle\Event\Log\LogCorrectionReportEvent;
use Claroline\DropZoneBundle\Event\Log\LogDropEndEvent;
use Claroline\DropZoneBundle\Event\Log\LogDropGradeAvailableEvent;
use Claroline\DropZoneBundle\Event\Log\LogDropReportEvent;
use Claroline\DropZoneBundle\Event\Log\LogDropzoneManualStateChangedEvent;
use Icap\NotificationBundle\Event\Notification\NotificationCreateDelegateViewEvent;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class NotificationListener
{
    use ContainerAwareTrait;

    public function onCreateNotificationItem(NotificationCreateDelegateViewEvent $event)
    {
        $translator = $this->container->get('translator');

        $notificationView = $event->getNotificationView();
        $notification = $notificationView->getNotification();

        switch ($notification->getActionKey()) {
            case LogDropReportEvent::ACTION:
            case LogCorrectionReportEvent::ACTION:
            case LogDropGradeAvailableEvent::ACTION:
            case LogDropEndEvent::ACTION:
                if (isset($notification->getDetails()['report']['dropId'])) {
                    $event->setPrimaryAction([
                        'url' => 'claro_dropzone_detail_drop',
                        'parameters' => [
                            'dropzoneId' => $notification->getDetails()['report']['dropzoneId'],
                            'dropId' => $notification->getDetails()['report']['dropId'],
                        ],
                    ]);
                }

                $text = $translator->trans($notification->getActionKey(), ['%dropzone%' => $notification->getDetails()['resource']['name']], 'notification');
                $event->setText($text);

                break;
            case LogDropzoneManualStateChangedEvent::ACTION:
                $event->setPrimaryAction([
                    'url' => 'claro_dropzone_detail_dropzone',
                    'parameters' => [
                        'dropzoneId' => $notification->getDetails()['report']['dropzoneId'],
                    ],
                ]);

                $text = $translator->trans($notification->getActionKey(), [
                    '%dropzone%' => $notification->getDetails()['resource']['name'],
                    '%newState%' => $translator->trans($notification->getDetails()['resource']['newState'], [], 'dropzone'),
                ], 'notification');
                $event->setText($text);

                break;
        }
    }
}
