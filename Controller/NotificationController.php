<?php

namespace Icap\NotificationBundle\Controller;

use Doctrine\ORM\NoResultException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Icap\NotificationBundle\Entity\ColorChooser;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NotificationController extends Controller
{
    /**
     * @Route(
     *    "/list/{page}",
     *    requirements = {
     *        "page" = "\d+"
     *    },
     *    defaults = {
     *        "page" = 1
     *    },
     *    name="icap_notification_view"
     * )
     * @Template()
     * @ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function listAction(Request $request, $user, $page)
    {
        if ($request->isXMLHttpRequest()) {
            $maxResult = $this->container->getParameter('icap_notification.dropdown_items');
            $page = 1;
        } else {
            $maxResult = $this->container->getParameter('icap_notification.max_per_page');
        }

        $notificationManager = $this->getNotificationManager();
        $result = $notificationManager->getUserNotificationsList($user->getId(), $page, $maxResult);
        $systemName = $notificationManager->getPlatformName();
        $result['systemName'] = $systemName;

        if ($request->isXMLHttpRequest()) {
            $unviewedNotifications = $notificationManager->countUnviewedNotifications(
                $user->getId()
            );
            $result['unviewedNotifications'] = $unviewedNotifications;

            return $this->render(
                'IcapNotificationBundle:Templates:notificationDropdownList.html.twig',
                $result
            );
        } else {
            $defaultLayout = $this->container->getParameter('icap_notification.default_layout');
            $result['layout'] = $defaultLayout;

            return $result;
        }
    }

    /**
     * @Route(
     *      "/rss/{rssId}",
     *      defaults={"_format":"xml"},
     *      name="icap_notification_rss"
     * )
     * @Template()
     * @param $rssId
     * @return mixed
     */
    public function rssAction($rssId)
    {
        $notificationManager = $this->getNotificationManager();
        $maxResult = $this->container->getParameter('icap_notification.max_per_page');
        try {
            $result = $notificationManager->getUserNotificationsListRss($rssId, $maxResult);
            $result["systemName"] = $notificationManager->getPlatformName();
        } catch (NoResultException $nre) {
            $result = array("error" => "no_rss_defined");
        } catch (NotFoundHttpException $nfe) {
            $result = array("error" => "zero_notifications");
        }

        return $result;
    }

    /**
     * @return \Icap\NotificationBundle\Manager\NotificationManager
     */
    private function getNotificationManager()
    {
        return $this->get("icap.notification.manager");
    }
}