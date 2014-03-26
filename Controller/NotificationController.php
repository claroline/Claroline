<?php

namespace Icap\NotificationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Icap\NotificationBundle\Entity\ColorChooser;

use Symfony\Component\HttpFoundation\Request;

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

        $result = $this->get("icap.notification.manager")
            ->getUserNotificationsList($user->getId(), $page, $maxResult);

        if ($request->isXMLHttpRequest()) {
            $unviewedNotifications = $this->get('icap.notification.manager')->countUnviewedNotifications($user->getId());
            $result['unviewedNotifications'] = $unviewedNotifications;

            return $this->render(
                'IcapNotificationBundle:Templates:notificationDropdownList.html.twig',
                $result
            );
        } else {
            $defaultLayout = $this->container->getParameter('icap_notification.default_layout');
            $systemName = $this->container->getParameter('icap_notification.system_name');
            $result['layout'] = $defaultLayout;
            $result['systemName'] = $systemName;


            return $result;
        }
    }
}