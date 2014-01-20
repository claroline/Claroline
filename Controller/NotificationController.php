<?php

namespace Icap\NotificationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
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
        $systemName = $this->container->getParameter('icap_notification.system_name');
        $notificationListQuery = $this->get('icap_notification.manager')->getUserNotificationsQuery($user->getId());
        $adapter = new DoctrineORMAdapter($notificationListQuery);
        $pager   = new PagerFanta($adapter);
        if ($request->isXMLHttpRequest()) {
            $pager->setMaxPerPage($this->container->getParameter('icap_notification.dropdown_items'));

            try {
                $pager->setCurrentPage(1);
            } catch (NotValidCurrentPageException $exception) {
                throw new NotFoundHttpException();
            }
        } else {
            $pager->setMaxPerPage($this->container->getParameter('icap_notification.max_per_page'));

            try {
                $pager->setCurrentPage($page);
            } catch (NotValidCurrentPageException $exception) {
                throw new NotFoundHttpException();
            }
        }

        $notificationViews = $pager->getCurrentPageResults();
        $userIds = array();
        $resourceIds = array();
        $notificationViewIds = array();
        $colorChooser = new ColorChooser();
        foreach ($notificationViews as $notificationView) {
            $notificationTmp = $notificationView->getNotification();
            $userId = $notificationTmp->getUserId();
            $resourceId = $notificationTmp->getResourceId();
            $iconKey = $notificationTmp->getIconKey();
            if (!empty($iconKey)) {
                $notificationColor = $colorChooser->getColorForName($iconKey);
                $notificationTmp->setIconColor($notificationColor);
            }
            if (!empty($userId)) {
                array_push($userIds, $userId);
            }
            if (!empty($resourceId)) {
                array_push($resourceIds, $resourceId);
            }

            if ($notificationView->getStatus() == false) array_push($notificationViewIds, $notificationView->getId());
        }
        $users = $this->get('icap_notification.manager')->getObjectsByClassAndIds (
            $userIds,
            $this->container->getParameter('icap_notification.user_class')
        );
        $resources = $this->get('icap_notification.manager')->getObjectsByClassAndIds (
            $resourceIds,
            $this->container->getParameter('icap_notification.resource_class')
        );

        $this->get('icap_notification.manager')->markNotificationsAsViewed($notificationViewIds);

        if ($request->isXMLHttpRequest()) {
            $unviewedNotifications = $this->get('icap_notification.manager')->countUnviewedNotifications($user->getId());
            return $this->render(
                'IcapNotificationBundle:Templates:notificationDropdownList.html.twig',
                array(
                    'pager' => $pager,
                    'users' => $users,
                    'resources' => $resources,
                    'systemName' => $systemName,
                    'unviewedNotifications' => $unviewedNotifications
                )
            );
        } else {
            $defaultLayout = $this->container->getParameter('icap_notification.default_layout');

            return array(
                'layout' => $defaultLayout,
                'pager' => $pager,
                'users' => $users,
                'resources' => $resources,
                'systemName' => $systemName
            );
        }
    }
}