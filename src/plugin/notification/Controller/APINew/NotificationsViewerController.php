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
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/notifications")
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
     * @Route("", name="apiv2_user_notifications_list", methods={"GET"})
     * @EXT\ParamConverter("user", converter="current_user")
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
     * @Route("/unread/count", name="apiv2_user_notifications_count")
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
     * @Route("/read", name="apiv2_user_notifications_read", methods={"PUT"})
     * @EXT\ParamConverter("user", converter="current_user")
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
     * @Route("/unread", name="apiv2_user_notifications_unread", methods={"PUT"})
     * @EXT\ParamConverter("user", converter="current_user")
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
     * @Route("/", name="apiv2_user_notifications_delete", methods={"DELETE"})
     * @EXT\ParamConverter("user", converter="current_user")
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
