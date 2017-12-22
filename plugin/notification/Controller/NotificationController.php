<?php

namespace Icap\NotificationBundle\Controller;

use Doctrine\ORM\NoResultException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NotificationController extends Controller
{
    /**
     * @Route(
     *    "/list/{page}/{markViewed}",
     *    requirements = {
     *        "page" = "\d+",
     *        "markViewed" = "0|1"
     *    },
     *    defaults = {
     *        "page" = 1,
     *        "markViewed" = 0
     *    },
     *    name="icap_notification_view"
     * )
     * @Template()
     * @ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function listAction(Request $request, $user, $page, $markViewed)
    {
        $notificationManager = $this->getNotificationManager();
        $systemName = $notificationManager->getPlatformName();
        if ($request->isXMLHttpRequest()) {
            $result = $notificationManager->getDropdownNotifications($user);
            $result['systemName'] = $systemName;
            $unviewedNotifications = $notificationManager->countUnviewedNotifications(
                $user
            );
            $result['unviewedNotifications'] = $unviewedNotifications;

            return $this->render(
                'IcapNotificationBundle:Templates:notificationDropdownList.html.twig',
                $result
            );
        } else {
            $category = $request->get('category');
            if ($markViewed === true) {
                $notificationManager->markAllNotificationsAsViewed($user->getId());
            }
            $result = $notificationManager->getPaginatedNotifications($user, $page, $category);
            $result['systemName'] = $systemName;
            $result['category'] = $category;

            return $result;
        }
    }

    /**
     * @Route(
     *      "/rss/{rssId}",
     *      defaults={"_format":"xml"},
     *      name="icap_notification_rss"
     * )
     *
     * @param $rssId
     *
     * @return mixed
     */
    public function rssAction($rssId)
    {
        $notificationManager = $this->getNotificationManager();
        try {
            $result = $notificationManager->getUserNotificationsListRss($rssId);
            $result['systemName'] = $notificationManager->getPlatformName();
        } catch (NoResultException $nre) {
            $result = ['error' => 'no_rss_defined'];
        } catch (NotFoundHttpException $nfe) {
            $result = ['error' => 'zero_notifications'];
        }

        return $this->render('IcapNotificationBundle:Notification:rss.xml.twig', $result);
    }

    /**
     * @return \Icap\NotificationBundle\Manager\NotificationManager
     */
    private function getNotificationManager()
    {
        return $this->get('icap.notification.manager');
    }
}
