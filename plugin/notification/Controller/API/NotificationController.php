<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icap\NotificationBundle\Controller\API;

use Claroline\CoreBundle\Entity\User;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\FOSRestController;
use Icap\NotificationBundle\Manager\NotificationManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class NotificationController extends FOSRestController
{
    private $notificationManager;

    /**
     * @DI\InjectParams({
     *     "notificationManager" = @DI\Inject("icap.notification.manager")
     * })
     */
    public function __construct(NotificationManager $notificationManager)
    {
        $this->notificationManager = $notificationManager;
    }

    /**
     * @Route("/notifications.{_format}", name="icap_notifications", defaults={"_format":"json"})
     * @View(serializerGroups={"api_notification"})
     * @EXT\ParamConverter("user", converter="current_user")
     */
    public function getNotificationsAction(User $user)
    {
        return $this->notificationManager->getUserNotifications($user);
    }

    /**
     * Mark all notifications as read.
     *
     * @Route("/notifications/read.{_format}", name="icap_notifications_read", defaults={"_format":"json"})
     * @View(serializerGroups={"api_notification"})
     * @EXT\ParamConverter("user", converter="current_user")
     */
    public function getNotificationsReadAction(User $user)
    {
        $this->notificationManager->markAllNotificationsAsViewed($user->getId());

        return $this->notificationManager->getUserNotifications($user);
    }
}
