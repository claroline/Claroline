<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icap\NotificationBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\View;
use JMS\DiExtraBundle\Annotation as DI;
use Icap\NotificationBundle\Manager\NotificationManager;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Claroline\CoreBundle\Entity\Oauth\ClarolineAccess;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;


class ApiNotificationController extends FOSRestController
{
    private $notificationManager;
    private $tokenStorage;

    /**
     * @DI\InjectParams({
     *     "notificationManager" = @DI\Inject("icap.notification.manager"),
     *     "tokenStorage"        = @DI\Inject("security.token_storage")
     * })
     */
    public function __construct(
        NotificationManager $notificationManager,
         $tokenStorage)
    {
        $this->notificationManager = $notificationManager;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @Route("/notifications.{_format}", name="icap_notifications", defaults={"_format":"json"})
     * @View(serializerGroups={"api_notification"})
     */
    public function getNotificationsAction()
    {
        $user = $this->tokenStorage->getToken()->getUser();

        return $this->notificationManager->getUserNotifications($user->getId());
    }

   /**
    * Mark all notifications as read
    * @Route("/notifications/read.{_format}", name="icap_notifications_read", defaults={"_format":"json"})
    * @View(serializerGroups={"api_notification"})
    */
   public function getNotificationsReadAction(){
         $user = $this->tokenStorage->getToken()->getUser();
         $this->notificationManager->markAllNotificationsAsViewed($user->getId());

         return $this->notificationManager->getUserNotifications($user->getId());
   }
}
