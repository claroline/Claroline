<?php
/**
 * Created by : Vincent SAISSET
 * Date: 22/08/13
 * Time: 09:30.
 */

namespace Icap\NotificationBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class FollowerResourceController extends Controller
{
    /**
     * @Template("IcapNotificationBundle:follower_resource:follower_resource_form.html.twig")
     * @ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function renderFormAction($resourceId, $resourceClass, $user)
    {
        $followerResource = $this->get('icap.notification.manager')->getFollowerResource(
            $user->getId(),
            $resourceId,
            $resourceClass
        );

        $hasActiveNotifications = false;
        if (!empty($followerResource)) {
            $hasActiveNotifications = true;
        }

        return [
            'hasActiveNotifications' => $hasActiveNotifications,
            'resourceId' => $resourceId,
            'resourceClass' => base64_encode($resourceClass),
            'userId' => $user->getId(),
        ];
    }

    /**
     * @Route(
     *     "/resource/notification/toggle/{resourceId}/{resourceClass}",
     *     name="icap_notification_resource_toggle",
     *     options={"expose"=true}
     * )
     * @ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function resourceNotificationToggleAction($resourceId, $resourceClass, User $user)
    {
        $resourceClass = base64_decode($resourceClass);
        $this->get('icap.notification.manager')->toggleFollowResource($user->getId(), $resourceId, $resourceClass);

        return new JsonResponse(null);
    }

    /**
     * @Route(
     *     "/enableResourceNotification/{resourceId}/{resourceClass}",
     *     name="icap_notification_resource_enable",
     *     options={"expose"=true}
     * )
     * @Template()
     * @ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function enableResourceNotificationAction(Request $request, $resourceId, $resourceClass, $user)
    {
        $resourceClass = base64_decode($resourceClass);
        $this->get('icap.notification.manager')->followResource($user->getId(), $resourceId, $resourceClass);

        return $this->redirect($this->getPreviousUrl($request));
    }

    /**
     * @Route(
     *     "/disableResourceNotification/{resourceId}/{resourceClass}",
     *     name="icap_notification_resource_disable",
     *     options={"expose"=true}
     * )
     * @Template()
     * @ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function disableResourceNotificationAction(Request $request, $resourceId, $resourceClass, $user)
    {
        $resourceClass = base64_decode($resourceClass);
        $this->get('icap.notification.manager')->unfollowResource($user->getId(), $resourceId, $resourceClass);

        return $this->redirect($this->getPreviousUrl($request));
    }

    protected function getPreviousUrl($request)
    {
        $referer = $request->headers->get('referer');

        return $referer;
    }
}
