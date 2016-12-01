<?php

namespace Icap\NotificationBundle\Manager;

use Claroline\CoreBundle\Event\Log\NotifiableInterface;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;
use Icap\NotificationBundle\Entity\FollowerResource;
use Icap\NotificationBundle\Entity\Notification;
use Icap\NotificationBundle\Entity\NotificationPluginConfiguration;
use Icap\NotificationBundle\Entity\NotificationViewer;
use Icap\NotificationBundle\Event\Notification\NotificationCreateDelegateViewEvent;
use Icap\NotificationBundle\Library\ColorChooser;
use JMS\DiExtraBundle\Annotation as DI;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Pagerfanta\Pagerfanta;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class NotificationManager.
 *
 * @DI\Service("icap.notification.manager")
 */
class NotificationManager
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;
    /**
     * @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface
     */
    protected $tokenStorage;
    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;
    /**
     * @var string
     */
    protected $platformName;
    /**
     * @var NotificationUserParametersManager
     */
    protected $notificationParametersManager;
    /**
     * @var NotificationPluginConfigurationManager
     */
    protected $notificationPluginConfigurationManager;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *      "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *      "tokenStorage" = @DI\Inject("security.token_storage"),
     *      "eventDispatcher" = @DI\Inject("event_dispatcher"),
     *      "configHandler" = @DI\Inject("claroline.config.platform_config_handler"),
     *      "notificationParametersManager" = @DI\Inject("icap.notification.manager.notification_user_parameters"),
     *      "notificationPluginConfigurationManager" = @DI\Inject("icap.notification.manager.plugin_configuration")
     * })
     */
    public function __construct(
        EntityManager $em,
        TokenStorageInterface $tokenStorage,
        EventDispatcherInterface $eventDispatcher,
        PlatformConfigurationHandler $configHandler,
        NotificationUserParametersManager $notificationParametersManager,
        NotificationPluginConfigurationManager $notificationPluginConfigurationManager
    ) {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
        $this->eventDispatcher = $eventDispatcher;
        $this->platformName = $configHandler->getParameter('name');
        if ($this->platformName === null || empty($this->platformName)) {
            $this->platformName = 'Claroline';
        }
        $this->notificationParametersManager = $notificationParametersManager;
        $this->notificationPluginConfigurationManager = $notificationPluginConfigurationManager;
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

    private function getConfigurationAndPurge()
    {
        $config = $this->notificationPluginConfigurationManager->getConfigOrEmpty();
        if ($config->getPurgeEnabled()) {
            $this->purgeNotifications($config);
        }

        return $config;
    }

    private function purgeNotifications(NotificationPluginConfiguration $config)
    {
        $lastPurgeDate = $config->getLastPurgeDate();
        $today = (new \DateTime())->setTime(0, 0, 0);
        if ($lastPurgeDate === null || $today > $lastPurgeDate) {
            $purgeBeforeDate = clone $today;
            $purgeBeforeDate->sub(new \DateInterval('P'.$config->getPurgeAfterDays().'D'));
            $this->getNotificationRepository()->deleteNotificationsBeforeDate($purgeBeforeDate);

            $config->setLastPurgeDate($today);
            $this->em->persist($config);
            $this->em->flush();
        }
    }

    protected function buildColorChooser()
    {
        $iconKeys = $this->getNotificationRepository()->findAllDistinctIconKeys();
        $colorChooser = new ColorChooser();
        foreach ($iconKeys as $key) {
            $colorChooser->getColorForName($key['iconKey']);
        }

        return $colorChooser;
    }

    /**
     * @return \Icap\NotificationBundle\Repository\NotificationRepository
     */
    protected function getNotificationRepository()
    {
        return $this->getEntityManager()->getRepository('IcapNotificationBundle:Notification');
    }

    /**
     * @return \Icap\NotificationBundle\Repository\NotificationViewerRepository
     */
    protected function getNotificationViewerRepository()
    {
        return $this->getEntityManager()->getRepository('IcapNotificationBundle:NotificationViewer');
    }

    /**
     * @return \Icap\NotificationBundle\Repository\FollowerResourceRepository
     */
    protected function getFollowerResourceRepository()
    {
        return $this->getEntityManager()->getRepository('IcapNotificationBundle:FollowerResource');
    }

    protected function getUsersToNotifyForNotifiable(NotifiableInterface $notifiable)
    {
        $userIds = [];
        if ($notifiable->getSendToFollowers() && $notifiable->getResource() !== null) {
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

    protected function renderNotifications($notificationsViews)
    {
        $views = [];
        $colorChooser = $this->buildColorChooser();
        $unviewedNotificationIds = [];
        foreach ($notificationsViews as $notificationView) {
            $notification = $notificationView->getNotification();
            $iconKey = $notification->getIconKey();
            if (!empty($iconKey)) {
                $notificationColor = $colorChooser->getColorForName($iconKey);
                $notification->setIconColor($notificationColor);
            }
            $eventName = 'create_notification_item_'.$notification->getActionKey();
            $event = new NotificationCreateDelegateViewEvent($notificationView, $this->platformName);

            /* @var EventDispatcher $eventDispatcher */
            if ($this->eventDispatcher->hasListeners($eventName)) {
                $event = $this->eventDispatcher->dispatch($eventName, $event);
                $views[$notificationView->getId().''] = $event->getResponseContent();
            }
            if ($notificationView->getStatus() === false) {
                array_push(
                    $unviewedNotificationIds,
                    $notificationView->getId()
                );
            }
        }
        $this->markNotificationsAsViewed($unviewedNotificationIds);

        return ['views' => $views, 'colors' => $colorChooser->getColorObjectArray()];
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->em;
    }

    /**
     * @return mixed
     */
    public function getPlatformName()
    {
        return $this->platformName;
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

        if ($doer === null) {
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
                'publicUrl' => $doer->getPublicUrl(),
            ];
        }
        $notification->setDetails($details);
        $notification->setUserId($doerId);

        $this->getEntityManager()->persist($notification);
        $this->getEntityManager()->flush();

        return $notification;
    }

    /**
     * Creates a notification viewer for every user in the list of people to be notified.
     *
     * @param Notification $notification
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
                if ($userId !== null && $notification->getUserId() !== $userId) {
                    $notificationViewer = new NotificationViewer();
                    $notificationViewer->setNotification($notification);
                    $notificationViewer->setViewerId($userId);
                    $notificationViewer->setStatus(false);
                    $this->getEntityManager()->persist($notificationViewer);
                }
            }
        }
        $this->getEntityManager()->flush();

        return $notification;
    }

    /**
     * Creates a notification and notifies the concerned users.
     *
     * @param NotifiableInterface $notifiable
     *
     * @return Notification
     */
    public function createNotificationAndNotify(NotifiableInterface $notifiable)
    {
        $userIds = $this->getUsersToNotifyForNotifiable($notifiable);
        $notification = null;
        if (count($userIds) > 0) {
            $resourceId = null;
            if ($notifiable->getResource() !== null) {
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

    public function getDropdownNotifications($userId)
    {
        $config = $this->getConfigurationAndPurge();

        return $this->getUserNotificationsList($userId, 1, $config->getDropdownItems());
    }

    public function getPaginatedNotifications($userId, $page = 1, $category = null)
    {
        $config = $this->getConfigurationAndPurge();

        return $this->getUserNotificationsList($userId, $page, $config->getMaxPerPage(), false, null, $category);
    }

    public function markAllNotificationsAsViewed($userId)
    {
        $this->getNotificationViewerRepository()->markAllAsViewed($userId);
    }

    /**
     * Retrieves the notifications list.
     *
     * @param int  $userId
     * @param int  $page
     * @param int  $maxResult
     * @param bool $isRss
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return mixed
     */
    public function getUserNotificationsList($userId, $page = 1, $maxResult = -1, $isRss = false, $notificationParameters = null, $category = null)
    {
        $query = $this->getUserNotifications($userId, $page, $maxResult, $isRss, $notificationParameters, false, $category);
        $adapter = new DoctrineORMAdapter($query, false);
        $pager = new Pagerfanta($adapter);
        $pager->setMaxPerPage($maxResult);

        try {
            $pager->setCurrentPage($page);
        } catch (NotValidCurrentPageException $e) {
            throw new NotFoundHttpException();
        }
        $notifications = $this->renderNotifications($pager->getCurrentPageResults());

        return [
            'pager' => $pager,
            'notificationViews' => $notifications['views'],
            'colors' => $notifications['colors'],
        ];
    }

    public function getUserNotifications($userId, $page = 1, $maxResult = -1, $isRss = false, $notificationParameters = null, $executeQuery = true, $category = null)
    {
        if (is_null($notificationParameters)) {
            $notificationParameters = $this
                ->notificationParametersManager
                ->getParametersByUserId($userId);
        }

        if ($isRss) {
            $visibleTypes = $notificationParameters->getRssEnabledTypes();
        } else {
            $visibleTypes = $notificationParameters->getDisplayEnabledTypes();
        }

        $query = $this
            ->getNotificationViewerRepository()
            ->findUserNotificationsQuery($userId, $visibleTypes, $category);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function getUserNotificationsListRss($rssId)
    {
        $config = $this->getConfigurationAndPurge();
        $notificationUserParameters = $this
            ->notificationParametersManager
            ->getParametersByRssId($rssId);
        if ($notificationUserParameters === null) {
            throw new NoResultException();
        }

        return $this->getUserNotificationsList(
            $notificationUserParameters->getUserId(),
            1,
            $config->getMaxPerPage(),
            true,
            $notificationUserParameters
        );
    }

    /**
     * @param int    $userId
     * @param int    $resourceId
     * @param string $resourceClass
     *
     * @return null|object
     */
    public function getFollowerResource($userId, $resourceId, $resourceClass)
    {
        $followerResource = $this->getFollowerResourceRepository()->findOneBy(
            [
                'followerId' => $userId,
                'hash' => $this->getHash($resourceId, $resourceClass),
            ]
        );

        return $followerResource;
    }

    public function getTaggedUsersFromText($text)
    {
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

        $this->getEntityManager()->persist($followerResource);
        $this->getEntityManager()->flush();

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
            $this->getEntityManager()->remove($followerResource);
            $this->getEntityManager()->flush();
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
     * @param null $viewerId
     *
     * @return int
     */
    public function countUnviewedNotifications($viewerId = null)
    {
        if (empty($viewerId)) {
            $viewerId = $this->tokenStorage->getToken()->getUser()->getId();
        }
        $notificationParameters = $this->notificationParametersManager->getParametersByUserId($viewerId);

        return intval($this->getNotificationViewerRepository()->countUnviewedNotifications($viewerId, $notificationParameters->getDisplayEnabledTypes())['total']);
    }
}
