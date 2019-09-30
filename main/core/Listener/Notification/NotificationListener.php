<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener\Notification;

use Claroline\CoreBundle\Event\Log\LogEditResourceTextEvent;
use Claroline\CoreBundle\Event\Log\LogResourceCreateEvent;
use Claroline\CoreBundle\Event\Log\LogResourcePublishEvent;
use Claroline\CoreBundle\Event\Log\LogRoleSubscribeEvent;
use Claroline\CoreBundle\Event\Log\LogWorkspaceRegistrationQueueEvent;
use Claroline\CoreBundle\Event\Log\LogWorkspaceRoleChangeRightEvent;
use Icap\NotificationBundle\Event\Notification\NotificationCreateDelegateViewEvent;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class NotificationListener
{
    use ContainerAwareTrait;

    public function onCreateNotificationItem(NotificationCreateDelegateViewEvent $event)
    {
        $translator = $this->container->get('translator');
        $authStorage = $this->container->get('security.token_storage');
        $current = $authStorage->getToken()->getUser();

        $notificationView = $event->getNotificationView();
        $notification = $notificationView->getNotification();

        //to change later for the workspace open

        switch ($notification->getActionKey()) {
          case LogWorkspaceRoleChangeRightEvent::ACTION:
            if (isset($notification->getDetails()['resource']['id']) && '' !== $notification->getDetails()['resource']['id']) {
                $event->setPrimaryAction([
                  'url' => 'claro_resource_open',
                  'parameters' => [
                    'node' => $notification->getDetails()['resource']['id'],
                    'resourceType' => $notification->getDetails()['resource']['resourceType'],
                  ],
                ]);
            }

              $text = $translator->trans(
                $notification->getActionKey(),
                ['%resource%' => $notification->getDetails()['resource']['name'], '%workspace%' => $notification->getDetails()['workspace']['name']],
                'notification'
              );

              $event->setText($text);
              break;
          case LogRoleSubscribeEvent::ACTION_WORKSPACE_USER:
            if (isset($notification->getDetails()['workspace']['id']) && '' !== $notification->getDetails()['workspace']['id'] && null !== $notification->getDetails()['workspace']['id']) {
                $event->setPrimaryAction([
                'url' => 'claro_workspace_open',
                'parameters' => [
                  'workspaceId' => $notification->getDetails()['workspace']['id'],
                ],
              ]);
            }

            if (isset($notification->getDetails()['receiverUser']['username']) && $notification->getDetails()['receiverUser']['username'] !== $current->getUsername()) {
                $text = $translator->trans('user_subscription_notification_for_admin', ['%workspace%' => $notification->getDetails()['workspace']['name']]);
            } else {
                $text = $translator->trans(
                $notification->getActionKey(),
                ['%role%' => $translator->trans($notification->getDetails()['role']['name']), '%workspace%' => $notification->getDetails()['workspace']['name']],
                'notification'
              );
            }
            $event->setText($text);

            break;
          case LogRoleSubscribeEvent::ACTION_WORKSPACE_GROUP:
            if (isset($notification->getDetails()['workspace']['id']) && '' !== $notification->getDetails()['workspace']['id'] && null !== $notification->getDetails()['workspace']['id']) {
                $event->setPrimaryAction([
                'url' => 'claro_workspace_open',
                'parameters' => [
                  'workspaceId' => $notification->getDetails()['workspace']['id'],
                ],
              ]);
            }

            $text = $translator->trans(
              $notification->getActionKey(),
              ['%role%' => $translator->trans($notification->getDetails()['role']['name']), '%workspace%' => $notification->getDetails()['workspace']['name']],
              'notification'
            );

            $event->setText($text);
            break;
          case LogRoleSubscribeEvent::ACTION_GROUP:
            $text = $translator->trans(
              $notification->getActionKey(),
              ['%role%' => $translator->trans($notification->getDetails()['role']['name'])],
              'notification'
            );
            $event->setText($text);
            break;
          case LogRoleSubscribeEvent::ACTION_USER:
            $text = $translator->trans(
              $notification->getActionKey(),
              ['%role%' => $translator->trans($notification->getDetails()['role']['name'])],
              'notification'
            );
            $event->setText($text);
            break;
          case LogResourceCreateEvent::ACTION:
            if (isset($notification->getDetails()['resource']['guid'])) {
                if ('directory' === $notification->getDetails()['resource']['resourceType'] && isset($notification->getDetails()['workspace']['id'])) {
                    $event->setPrimaryAction([
                    'url' => 'claro_workspace_open_tool',
                    'parameters' => [
                      'workspaceId' => $notification->getDetails()['workspace']['id'],
                      'resourceType' => $notification->getDetails()['resource']['resourceType'],
                      '#' => 'resources/'.$notification->getDetails()['resource']['guid'],
                    ],
                  ]);
                } else {
                    $event->setPrimaryAction([
                    'url' => 'claro_resource_open',
                    'parameters' => [
                      'node' => $notification->getDetails()['resource']['guid'],
                      'resourceType' => $notification->getDetails()['resource']['resourceType'],
                    ],
                  ]);
                }
            }

              $text = $translator->trans(
                'resource_creation_notification_message',
                ['%resource%' => $notification->getDetails()['resource']['name'], '%workspace%' => $notification->getDetails()['workspace']['name']],
                'notification'
              );

              $event->setText($text);
            break;
          case LogResourcePublishEvent::ACTION:
            if (isset($notification->getDetails()['resource']['guid'])) {
                if ('directory' === $notification->getDetails()['resource']['resourceType'] && isset($notification->getDetails()['workspace']['id'])) {
                    $event->setPrimaryAction([
                    'url' => 'claro_workspace_open_tool',
                    'parameters' => [
                      'workspaceId' => $notification->getDetails()['workspace']['id'],
                      'resourceType' => $notification->getDetails()['resource']['resourceType'],
                      '#' => 'resources/'.$notification->getDetails()['resource']['guid'],
                    ],
                  ]);
                } else {
                    $event->setPrimaryAction([
                    'url' => 'claro_resource_open',
                    'parameters' => [
                      'node' => $notification->getDetails()['resource']['guid'],
                      'resourceType' => $notification->getDetails()['resource']['resourceType'],
                    ],
                  ]);
                }
            }

              $text = $translator->trans(
                'resource_publication_notification_message',
                ['%resource%' => $notification->getDetails()['resource']['name'], '%workspace%' => $notification->getDetails()['workspace']['name']],
                'notification'
              );

              $event->setText($text);
            break;
          case LogWorkspaceRegistrationQueueEvent::ACTION:
            if (isset($notification->getDetails()['workspace']['id']) && '' !== $notification->getDetails()['workspace']['id'] && null !== $notification->getDetails()['workspace']['id']) {
                $event->setPrimaryAction([
                'url' => 'claro_workspace_open',
                'parameters' => [
                  'workspaceId' => $notification->getDetails()['workspace']['id'],
                ],
              ]);
            }

            $text = $translator->trans(
              $notification->getActionKey(),
              ['%workspace%' => $notification->getDetails()['workspace']['name']],
              'notification'
            );

            $event->setText($text);
            break;
          case LogEditResourceTextEvent::ACTION:
            if (isset($notification->getDetails()['resource']['guid'])) {
                $event->setPrimaryAction([
                'url' => 'claro_resource_open',
                'parameters' => [
                  'node' => $notification->getDetails()['resource']['guid'],
                  'resourceType' => $notification->getDetails()['resource']['resourceType'],
                ],
              ]);
            } else {
                if (isset($notification->getDetails()['workspace']['id']) && '' !== $notification->getDetails()['workspace']['id'] && null !== $notification->getDetails()['workspace']['id']) {
                    $event->setPrimaryAction([
                  'url' => 'claro_workspace_open',
                  'parameters' => [
                    'workspaceId' => $notification->getDetails()['workspace']['id'],
                  ],
                ]);
                }
            }

            $text = $translator->trans(
              'text_update_notification_message',
              ['%resource%' => $notification->getDetails()['resource']['name'], '%workspace%' => $notification->getDetails()['workspace']['name']],
              'notification'
            );

            $event->setText($text);
            break;
      }

        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:notification:notification_item.html.twig',
            [
                'notification' => $notification,
                'status' => $notificationView->getStatus(),
                'systemName' => $event->getSystemName(),
            ]
        );

        $event->setResponseContent($content);
        $event->stopPropagation();
    }
}
