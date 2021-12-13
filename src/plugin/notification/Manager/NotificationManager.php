<?php

namespace Icap\NotificationBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Log\NotifiableInterface;
use Icap\NotificationBundle\Entity\FollowerResource;
use Icap\NotificationBundle\Entity\Notification;
use Icap\NotificationBundle\Entity\NotificationViewer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class NotificationManager
{
    /** @var ObjectManager */
    private $om;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var NotificationUserParametersManager */
    private $notificationParametersManager;

    /**
     * NotificationManager constructor.
     */
    public function __construct(
        ObjectManager $om,
        TokenStorageInterface $tokenStorage,
        EventDispatcherInterface $eventDispatcher,
        NotificationUserParametersManager $notificationParametersManager
    ) {
        $this->om = $om;
        $this->tokenStorage = $tokenStorage;
        $this->eventDispatcher = $eventDispatcher;
        $this->notificationParametersManager = $notificationParametersManager;
    }

    private function getLoggedUser()
    {
        $doer = null;

        $securityToken = $this->tokenStorage->getToken();

        if (null !== $securityToken) {
            $doer = $securityToken->getUser();
        }

        return $doer;
    }

    /**
     * @return \Icap\NotificationBundle\Repository\NotificationViewerRepository
     */
    protected function getNotificationViewerRepository()
    {
        return $this->om->getRepository('IcapNotificationBundle:NotificationViewer');
    }

    /**
     * @return \Icap\NotificationBundle\Repository\FollowerResourceRepository
     */
    protected function getFollowerResourceRepository()
    {
        return $this->om->getRepository('IcapNotificationBundle:FollowerResource');
    }

    protected function getUsersToNotifyForNotifiable(NotifiableInterface $notifiable)
    {
        $userIds = [];
        if ($notifiable->getSendToFollowers() && null !== $notifiable->getResource()) {
            $userIds = $this->getFollowersByResourceIdAndClass(
                $notifiable->getResource()->getId(),
                $notifiable->getResource()->getClass()
            );
        }

        $includeUserIds = $notifiable->getIncludeUserIds();
        if (!empty($includeUserIds)) {
            $userIds = array_merge($userIds, $includeUserIds);
        }

        $userIds = array_unique($userIds);
        $excludeUserIds = $notifiable->getExcludeUserIds();
        $removeUserIds = [];

        if (!empty($excludeUserIds)) {
            $userIds = array_diff($userIds, $excludeUserIds);
        }

        $doer = $notifiable->getDoer();
        if (!empty($doer) && is_a($doer, 'Claroline\CoreBundle\Entity\User')) {
            array_push($removeUserIds, $doer->getId());
        }

        $userIds = array_diff($userIds, $removeUserIds);

        return $userIds;
    }

    /**
     * Get Hash for a given object which must implement notifiable interface.
     *
     * @param int    $resourceId
     * @param string $resourceClass
     *
     * @return string The generated hash
     */
    public function getHash($resourceId, $resourceClass)
    {
        $raw = sprintf(
            '%s_%s',
            $resourceClass,
            $resourceId
        );

        return md5($raw);
    }

    /**
     * @param int    $resourceId
     * @param string $resourceClass
     *
     * @return mixed
     */
    public function getFollowersByResourceIdAndClass($resourceId, $resourceClass)
    {
        $followerResults = $this->getFollowerResourceRepository()->
            findFollowersByResourceIdAndClass($resourceId, $resourceClass);
        $followerIds = [];
        foreach ($followerResults as $followerResult) {
            array_push($followerIds, $followerResult['id']);
        }

        return $followerIds;
    }

    /**
     * Create new Tag given its name.
     *
     * @param string      $actionKey
     * @param string      $iconKey
     * @param int|null    $resourceId
     * @param array       $details
     * @param object|null $doer
     *
     * @internal param \Icap\NotificationBundle\Entity\NotifiableInterface $notifiable
     *
     * @return Notification
     */
    public function createNotification($actionKey, $iconKey, $resourceId = null, $details = [], $doer = null)
    {
        $notification = new Notification();
        $notification->setActionKey($actionKey);
        $notification->setIconKey($iconKey);
        $notification->setResourceId($resourceId);

        $doerId = null;

        if (null === $doer) {
            $doer = $this->getLoggedUser();
        }

        if (is_a($doer, 'Claroline\CoreBundle\Entity\User')) {
            $doerId = $doer->getId();
        }

        if (!isset($details['doer']) && !empty($doerId)) {
            $details['doer'] = [
                'id' => $doerId,
                'firstName' => $doer->getFirstName(),
                'lastName' => $doer->getLastName(),
                'avatar' => $doer->getPicture(),
                'username' => $doer->getUsername(),
            ];
        }
        $notification->setDetails($details);
        $notification->setUserId($doerId);

        $this->om->persist($notification);
        $this->om->flush();

        return $notification;
    }

    /**
     * Creates a notification viewer for every user in the list of people to be notified.
     *
     * @param $userIds
     *
     * @internal param \Icap\NotificationBundle\Entity\NotifiableInterface $notifiable
     *
     * @return \Icap\NotificationBundle\Entity\Notification
     */
    public function notifyUsers(Notification $notification, $userIds)
    {
        if (count($userIds) > 0) {
            foreach ($userIds as $userId) {
                if (null !== $userId && $notification->getUserId() !== $userId) {
                    $notificationViewer = new NotificationViewer();
                    $notificationViewer->setNotification($notification);
                    $notificationViewer->setViewerId($userId);
                    $notificationViewer->setStatus(false);
                    $this->om->persist($notificationViewer);
                }
            }
        }
        $this->om->flush();

        return $notification;
    }

    /**
     * Creates a notification and notifies the concerned users.
     *
     * @return Notification
     */
    public function createNotificationAndNotify(NotifiableInterface $notifiable)
    {
        $userIds = $this->getUsersToNotifyForNotifiable($notifiable);

        $notification = null;
        if (count($userIds) > 0) {
            $resourceId = null;
            if (null !== $notifiable->getResource()) {
                $resourceId = $notifiable->getResource()->getId();
            }

            $notification = $this->createNotification(
                $notifiable->getActionKey(),
                $notifiable->getIconKey(),
                $resourceId,
                $notifiable->getNotificationDetails(),
                $notifiable->getDoer()
            );
            $this->notifyUsers($notification, $userIds);
        }

        return $notification;
    }

    public function markAllNotificationsAsViewed($userId)
    {
        $this->getNotificationViewerRepository()->markAllAsViewed($userId);
    }

    /**
     * @param int    $userId
     * @param int    $resourceId
     * @param string $resourceClass
     *
     * @return object|null
     */
    public function getFollowerResource($userId, $resourceId, $resourceClass)
    {
        $followerResource = $this->getFollowerResourceRepository()->findOneBy([
            'followerId' => $userId,
            'hash' => $this->getHash($resourceId, $resourceClass),
        ]);

        return $followerResource;
    }

    /**
     * @param ResourceNode[] $resourceNodes
     * @param string         $mode
     */
    public function toggleFollowResources(User $user, array $resourceNodes, $mode)
    {
        if (0 < count($resourceNodes)) {
            $this->om->startFlushSuite();

            switch ($mode) {
                case 'create':
                    foreach ($resourceNodes as $resourceNode) {
                        $userId = $user->getId();
                        $resourceId = $resourceNode->getId();
                        $resourceClass = $resourceNode->getClass();
                        $follower = $this->getFollowerResource($userId, $resourceId, $resourceClass);

                        if (empty($follower)) {
                            $this->followResource($userId, $resourceId, $resourceClass);
                        }
                    }
                    break;
                case 'delete':
                    foreach ($resourceNodes as $resourceNode) {
                        $this->unfollowResource($user->getId(), $resourceNode->getId(), $resourceNode->getClass());
                    }
                    break;
            }
            $this->om->endFlushSuite();
        }
    }

    /**
     * @param $userId
     * @param $resourceId
     * @param $resourceClass
     *
     * @return FollowerResource
     */
    public function followResource($userId, $resourceId, $resourceClass)
    {
        $followerResource = new FollowerResource();
        $followerResource->setFollowerId($userId);
        $followerResource->setResourceId($resourceId);
        $followerResource->setHash($this->getHash($resourceId, $resourceClass));
        $followerResource->setResourceClass($resourceClass);

        $this->om->persist($followerResource);
        $this->om->flush();

        return $followerResource;
    }

    /**
     * @param $userId
     * @param $resourceId
     * @param $resourceClass
     *
     * @return mixed
     */
    public function unfollowResource($userId, $resourceId, $resourceClass)
    {
        $followerResource = $this->getFollowerResource($userId, $resourceId, $resourceClass);

        if (!empty($followerResource)) {
            $this->om->remove($followerResource);
            $this->om->flush();
        }

        return $followerResource;
    }

    /**
     * @param $notificationViewIds
     */
    public function markNotificationsAsViewed($notificationViewIds)
    {
        if (!empty($notificationViewIds)) {
            $this->getNotificationViewerRepository()->markAsViewed($notificationViewIds);
        }
    }

    /**
     * @param $notificationViewIds
     */
    public function markNotificationsAsUnviewed($notificationViewIds)
    {
        if (!empty($notificationViewIds)) {
            $this->getNotificationViewerRepository()->markAsUnviewed($notificationViewIds);
        }
    }

    /**
     * @param User $viewer
     *
     * @return int
     */
    public function countUnviewedNotifications(User $viewer = null)
    {
        if (empty($viewer)) {
            $viewer = $this->tokenStorage->getToken()->getUser();
        }
        $notificationParameters = $this->notificationParametersManager->getParametersByUser($viewer);

        return intval($this->getNotificationViewerRepository()->countUnviewedNotifications($viewer->getId(), $notificationParameters->getDisplayEnabledTypes())['total']);
    }
}
