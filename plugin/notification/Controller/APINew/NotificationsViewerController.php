<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace  Icap\NotificationBundle\Controller\APINew;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Icap\NotificationBundle\Entity\NotificationViewer;
use Icap\NotificationBundle\Manager\NotificationManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @EXT\Route("/notifications")
 */
class NotificationsViewerController
{
    use RequestDecoderTrait;

    /** @var ObjectManager */
    private $om;

    /** @var FinderProvider */
    private $finder;

    /** @var Crud */
    private $crud;

    /** @var NotificationManager */
    private $notificationManager;

    /**
     * NotificationsViewerController constructor.
     *
     * @DI\InjectParams({
     *     "om"                  = @DI\Inject("claroline.persistence.object_manager"),
     *     "finder"              = @DI\Inject("claroline.api.finder"),
     *     "crud"                = @DI\Inject("claroline.api.crud"),
     *     "notificationManager" = @DI\Inject("icap.notification.manager")
     * })
     *
     * @param ObjectManager       $om
     * @param FinderProvider      $finder
     * @param Crud                $crud
     * @param NotificationManager $notificationManager
     */
    public function __construct(
        ObjectManager $om,
        FinderProvider $finder,
        Crud $crud,
        NotificationManager $notificationManager
    ) {
        $this->om = $om;
        $this->finder = $finder;
        $this->crud = $crud;
        $this->notificationManager = $notificationManager;
    }

    /**
     * @EXT\Route("", name="apiv2_user_notifications_list")
     * @EXT\ParamConverter("user", converter="current_user")
     * @EXT\Method("GET")
     *
     * @param User    $user
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listAction(User $user, Request $request)
    {
        return new JsonResponse(
            $this->finder->search(NotificationViewer::class, array_merge($request->query->all(), [
                'hiddenFilters' => ['user' => $user->getId()],
            ]))
        );
    }

    /**
     * @EXT\Route("/unread/count", name="apiv2_user_notifications_count")
     * @EXT\ParamConverter("user", converter="current_user")
     *
     * @param User $user
     *
     * @return JsonResponse
     */
    public function countUnreadAction(User $user)
    {
        return new JsonResponse(
            $this->notificationManager->countUnviewedNotifications($user)
        );
    }

    /**
     * @EXT\Route("/read", name="apiv2_user_notifications_read")
     * @EXT\ParamConverter("user", converter="current_user")
     * @EXT\Method("PUT")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function markAsReadAction(Request $request)
    {
        $this->notificationManager->markNotificationsAsViewed($request->query->get('ids'));

        return new JsonResponse(null, 204);
    }

    /**
     * @EXT\Route("/unread", name="apiv2_user_notifications_unread")
     * @EXT\ParamConverter("user", converter="current_user")
     * @EXT\Method("PUT")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function markAsUnreadAction(Request $request)
    {
        $this->notificationManager->markNotificationsAsUnviewed($request->query->get('ids'));

        return new JsonResponse(null, 204);
    }

    /**
     * @EXT\Route("/", name="apiv2_user_notifications_delete")
     * @EXT\ParamConverter("user", converter="current_user")
     * @EXT\Method("DELETE")
     *
     * @param User    $user
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function deleteAction(User $user, Request $request)
    {
        /** @var NotificationViewer[] $notifications */
        $notifications = $this->decodeIdsString($request, NotificationViewer::class);
        foreach ($notifications as $notification) {
            if ($notification->getViewerId() === $user->getId()) {
                $this->om->remove($notification);
            }
        }

        $this->om->flush();

        return new JsonResponse(null, 204);
    }
}
