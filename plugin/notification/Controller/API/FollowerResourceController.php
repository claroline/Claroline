<?php

namespace Icap\NotificationBundle\Controller\API;

use Claroline\AppBundle\Annotations\ApiMeta;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
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
 *
 * @todo rewrite using the new resource action system
 */
class FollowerResourceController extends AbstractCrudController
{
    protected $manager;

    /**
     * @DI\InjectParams({
     *     "manager"    = @DI\Inject("icap.notification.manager")
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
     * @EXT\Route("resources/toggle/{mode}", name="icap_notification_follower_resources_toggle")
     * @EXT\Method("PUT")
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param string  $mode
     * @param User    $user
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function followerResourcesToggleAction($mode, User $user, Request $request)
    {
        $nodes = $this->decodeIdsString($request, 'Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $this->manager->toggleFollowResources($user, $nodes, $mode);

        return new JsonResponse(array_map(function (ResourceNode $resourceNode) {
            return $this->serializer->serialize($resourceNode);
        }, $nodes));
    }
}
