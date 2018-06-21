<?php

namespace Icap\NotificationBundle\Controller\API;

use Claroline\AppBundle\Annotations\ApiMeta;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\User;
use Icap\NotificationBundle\Manager\NotificationManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @ApiMeta(
 *     class="HeVinci\FavouriteBundle\Entity\Favourite",
 *     ignore={"create", "update", "deleteBulk", "exist", "list", "copyBulk", "schema", "find", "get"}
 * )
 * @EXT\Route("/notificationfollower")
 */
class FollowerResourceController extends AbstractCrudController
{
    protected $manager;

    /**
     * @DI\InjectParams({
     *     "manager" = @DI\Inject("icap.notification.manager")
     * })
     *
     * @param NotificationManager $manager
     */
    public function __construct(NotificationManager $manager)
    {
        $this->manager = $manager;
    }

    public function getName()
    {
        return 'notificationfollower';
    }

    /**
     * Follows or unfollows resources.
     *
     * @EXT\Route("resources/toggle", name="icap_notification_follower_resources_toggle")
     * @EXT\Method("PUT")
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param User    $user
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function followerResourcesToggleAction(User $user, Request $request)
    {
        $nodes = $this->decodeIdsString($request, 'Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $this->manager->toggleFollowResources($user, $nodes);

        return new JsonResponse(null, 204);
    }
}
