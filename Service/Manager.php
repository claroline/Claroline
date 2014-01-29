<?php

namespace Icap\NotificationBundle\Service;

use Icap\NotificationBundle\Entity\FollowerResource;
use Icap\NotificationBundle\Entity\NotifiableInterface;
use Icap\NotificationBundle\Entity\Notification;
use Icap\NotificationBundle\Entity\NotificationViewer;
use Doctrine\ORM\EntityManager;
use Icap\NotificationBundle\Event\Notification\NotificationCreateDelegateViewEvent;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\SecurityContext;
use Icap\NotificationBundle\Entity\ColorChooser;
use Symfony\Component\DependencyInjection\Container;

class Manager
{
    protected $em;
    protected $security;
    protected $container;

    /**
     * @return Icap\NotificationBundle\Entity\Notification repository
     */
    protected function getNotificationRepository()
    {
        return $this->getEntityManager()->getRepository('IcapNotificationBundle:Notification');
    }

    /**
     * @return Icap\NotificationBundle\Entity\NotificationViewer repository
     */
    protected function getNotificationViewerRepository()
    {
        return $this->getEntityManager()->getRepository('IcapNotificationBundle:NotificationViewer');
    }

    /**
     * @return Icap\NotificationBundle\Entity\FollowerResource repository
     */
    protected function getFollowerResourceRepository()
    {
        return $this->getEntityManager()->getRepository('IcapNotificationBundle:FollowerResource');
    }

    /**
     * Constructor
     *
     * @param Doctrine\ORM\EntityManager
     * @param Symfony\Component\Form\FormFactory
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->em = $container->get('doctrine.orm.entity_manager');
        $this->security = $container->get('security.context');
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->em;
    }

    /**
     * Get Hash for a given object which must implement notifiable interface
     *
     * @param Icap\NotificationBundle\Entity\NotifiableInterface $notifiable
     * @return string The generated hash
     */
    public function getHash($resourceId, $resourceClass)
    {
        $raw = sprintf('%s_%s',
            $resourceClass,
            $resourceId
        );

        return md5($raw);
    }

    /**
     * @param $resourceId
     * @return mixed
     */
    public function getFollowersByResourceIdAndClass($resourceId, $resourceClass)
    {
        $followerResults = $this->getFollowerResourceRepository()->
            findFollowersByResourceIdAndClass($resourceId, $resourceClass);
        $followerIds = array();
        foreach ($followerResults as $followerResult) {
            array_push($followerIds, $followerResult['id']);
        }
        return $followerIds;
    }

    /**
     * Create new Tag given its name
     *
     * @param String $name
     * @return Tag the generated Tag
     */
    public function createNotification(NotifiableInterface $notifiable)
    {
        $notification = new Notification();
        $notification->setActionKey($notifiable->getActionKey());
        $notification->setIconKey($notifiable->getIconKey());
        $resourceId = null;
        if ($notifiable->getResource() !== null) {
            $resourceId = $notifiable->getResource()->getId();
        }
        $notification->setResourceId($resourceId);
        $details = $notifiable->getNotificationDetails();
        $doer = $notifiable->getDoer();
        $doerId = null;

        if ($doer === null) {
            $doer = $this->security->getToken()->getUser();
        }
        if ($doer !== null) {
            $doerId = $doer->getId();
        }
        if (!isset($details['doer']) && !empty($doerId)) {
            $details['doer'] = array(
                'id' => $doerId,
                'firstName' =>  $doer->getFirstName(),
                'lastName' => $doer->getLastName(),
                'avatar' => $doer->getPicture()
            );
        }
        $notification->setDetails($details);
        $notification->setUserId($doerId);

        $this->getEntityManager()->persist($notification);
        $this->getEntityManager()->flush();

        return $notification;
    }

    /**
     * Creates a notification viewer for every user in the list of people to be notified
     *
     * @param Notification $notification
     * @param NotifiableInterface $notifiable
     *
     */
    public function notifyUsers (Notification $notification, NotifiableInterface $notifiable)
    {
        $userIds = array();
        if ($notifiable->getSendToFollowers() && $notifiable->getResource() !== null) {
            $userIds = $this->getFollowersByResourceIdAndClass(
                $notifiable->getResource()->getId(),
                $notifiable->getResource()->getClass()
            );
        }

        if (!empty($notifiable->getIncludeUserIds())) {
            $userIds = array_merge($userIds, $notifiable->getIncludeUserIds());
        }

        $userIds = array_unique($userIds);

        if (!empty($notifiable->getExcludeUserIds())) {
            $userIds = array_diff($userIds, $notifiable->getExcludeUserIds());
        }

        //Remove doer from user list as long as the logged user
        $loggedUser = $this->security->getToken()->getUser();
        $removeUserIds = array($notification->getUserId());
        if (!empty($loggedUser) && $loggedUser->getId() != $notification->getUserId()) {
            array_push($removeUserIds, $loggedUser->getId());
        }
        $userIds = array_diff($userIds, $removeUserIds);

        if (count($userIds)>0) {
            foreach ($userIds as $userId) {
                $notificationViewer = new NotificationViewer();
                $notificationViewer->setNotification($notification);
                $notificationViewer->setViewerId($userId);
                $notificationViewer->setStatus(false);

                $this->getEntityManager()->persist($notificationViewer);
            }
        } else {
            $this->getEntityManager()->remove($notification);
        }
        $this->getEntityManager()->flush();

        return $notification;
    }

    /**
     * Creates a notification and notifies the concerned users
     *
     * @param NotifiableInterface $notifiable
     * @return Notification
     */
    public function createNotificationAndNotify(NotifiableInterface $notifiable)
    {
        $notification = $this->createNotification($notifiable);
        $this->notifyUsers($notification, $notifiable);

        return $notification;
    }

    /**
     * Retrieves the notifications list
     *
     * @param int $userId
     * @param int $page
     * @param int $maxResult
     * @return query
     */
    public function getUserNotificationsList($userId, $page = 1, $maxResult = -1)
    {
        $query = $this->getNotificationViewerRepository()->findUserNotificationsQuery($userId);
        $adapter = new DoctrineORMAdapter($query);
        $pager   = new Pagerfanta($adapter);
        $pager->setMaxPerPage($maxResult);

        try {
            $pager->setCurrentPage($page);
        } catch (NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }

        $views = $this->renderNotifications($pager->getCurrentPageResults());

        return array(
            'pager' => $pager,
            'notificationViews' => $views
        );
    }

    protected function renderNotifications($notificationsViews)
    {
        $views = array();
        $colorChooser = new ColorChooser();
        $systemName = $this->container->getParameter('icap_notification.system_name');
        $unviewedNotificationIds = array();
        foreach ($notificationsViews as $notificationView) {
            $notification = $notificationView->getNotification();
            $iconKey = $notification->getIconKey();
            if (!empty($iconKey)) {
                $notificationColor = $colorChooser->getColorForName($iconKey);
                $notification->setIconColor($notificationColor);
            }
            $eventName = 'create_notification_item_'.$notification->getActionKey();
            $event     = new NotificationCreateDelegateViewEvent($notificationView, $systemName);

            /** @var EventDispatcher $eventDispatcher */
            $eventDispatcher = $this->container->get('event_dispatcher');
            if ($eventDispatcher->hasListeners($eventName)) {
                $event = $eventDispatcher->dispatch($eventName, $event);
                $views[$notificationView->getId().''] = $event->getResponseContent();
            }
            if ($notificationView->getStatus() == false) array_push($unviewedNotificationIds, $notificationView->getId());
        }
        $this->markNotificationsAsViewed($unviewedNotificationIds);

        return $views;
    }

    /**
     * @param $userId
     * @param $resourceId
     */
    public function getFollowerResource($userId, $resourceId, $resourceClass)
    {
        $followerResource = $this->getFollowerResourceRepository()->findOneBy(
            array(
                'followerId' => $userId,
                'hash' => $this->getHash($resourceId, $resourceClass)
            )
        );

        return $followerResource;
    }

    /**
     * @param $userId
     * @param $resourceId
     * @param $resourceClass
     * @return FollowerResource
     */
    public function followResource($userId, $resourceId, $resourceClass)
    {
        $followerResource = new FollowerResource();
        $followerResource->setFollowerId($userId);
        $followerResource->setResourceId($resourceId);
        $followerResource->setHash($this->getHash($resourceId, $resourceClass));
        $followerResource->setResourceClass($resourceClass);

        $this->getEntityManager()->persist($followerResource);
        $this->getEntityManager()->flush();

        return $followerResource;
    }

    /**
     * @param $userId
     * @param $resourceId
     * @param $resourceClass
     * @return mixed
     */
    public function unfollowResource($userId, $resourceId, $resourceClass)
    {
        $followerResource = $this->getFollowerResource($userId, $resourceId, $resourceClass);

        if (!empty($followerResource)) {
            $this->getEntityManager()->remove($followerResource);
            $this->getEntityManager()->flush();
        }

        return $followerResource;
    }

    /**
     * @param $notificationViewIds
     */
    public function markNotificationsAsViewed ($notificationViewIds)
    {
        if (!empty($notificationViewIds)) {
            $this->getNotificationViewerRepository()->markAsViewed($notificationViewIds);
        }
    }

    /**
     * @param null $viewerId
     * @return int
     */
    public function countUnviewedNotifications ($viewerId = null)
    {
        if (empty($viewerId)) {
            $viewerId = $this->security->getToken()->getUser()->getId();
        }
        return intval($this->getNotificationViewerRepository()->countUnviewedNotifications($viewerId)["total"]);
    }
}