<?php

namespace Icap\NotificationBundle\Service;

use Icap\NotificationBundle\Entity\FollowerResource;
use Icap\NotificationBundle\Entity\NotifiableInterface;
use Icap\NotificationBundle\Entity\Notification;
use Icap\NotificationBundle\Entity\NotificationViewer;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\SecurityContext;

class Manager
{
    protected $em;
    protected $security;

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
    public function __construct(EntityManager $em, SecurityContext $security)
    {
        $this->em = $em;
        $this->security = $security;
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
        $followerResults = $this->getFollowerResourceRepository()->findFollowersByResourceIdAndClass($resourceId, $resourceClass);
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
        $notification->setTargetUrl($notifiable->getTargetUrl());
        $notification->setResourceId($notifiable->getResourceId());
        $doerId = $notifiable->getDoerId();
        if ($doerId === null) {
            $loggedUser = $this->security->getToken()->getUser();
            if (!empty($loggedUser)) {
                $doerId = $loggedUser->getId();
            }
        }
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
        if ($notifiable->getSendToFollowers()) {
            $userIds = $this->getFollowersByResourceIdAndClass($notifiable->getResourceId(), $notifiable->getResourceClass());
        }

        if (!empty($notifiable->getIncludeUserIds())) {
            $userIds = array_merge($userIds, $notifiable->getIncludeUserIds());
        }

        $userIds = array_unique($userIds);

        if (!empty($notifiable->getExcludeUserIds())) {
            $userIds = array_diff($userIds, $notifiable->getExcludeUserIds());
        }

        //Remove doer from user list
        $loggedUser = $this->security->getToken()->getUser();
        if (!empty($loggedUser)) {
            $userIds = array_diff($userIds, array($loggedUser->getId()));
        }

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
     * Retrieves the notifications for a user
     *
     * @param $userId
     * @return query
     */
    public function getUserNotificationsQuery($userId)
    {
        return $this->getNotificationViewerRepository()->findUserNotificationsQuery($userId);
    }

    /**
     * @param $userId
     * @param $max
     *
     * @return mixed
     */
    public function getUserLatestNotifications($userId, $max)
    {
        return $this->getNotificationViewerRepository()->findUserLatestNotifications($userId, $max);
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
     * @param $objectIds
     * @param $objectClass
     * @return array
     */
    public function getObjectsByClassAndIds ($objectIds, $objectClass)
    {
        $objectIds = array_unique($objectIds);
        $objects = $this->getEntityManager()->getRepository($objectClass)->findBy(array('id'=>$objectIds));

        $objectsHash = array();
        foreach ($objects as $object) {
            $objectsHash[$object->getId()] = $object;
        }

        return $objectsHash;
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