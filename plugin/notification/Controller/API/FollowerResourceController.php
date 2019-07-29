<?php

namespace Icap\NotificationBundle\Controller\API;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Icap\NotificationBundle\Manager\NotificationManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @EXT\Route("/notificationfollower")
 */
class FollowerResourceController extends AbstractCrudController
{
    /** @var NotificationManager */
    private $manager;

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

    public function getClass()
    {
        return 'HeVinci\FavouriteBundle\Entity\Favourite';
    }

    public function getIgnore()
    {
        return ['create', 'update', 'deleteBulk', 'exist', 'list', 'copyBulk', 'schema', 'find', 'get'];
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
        $nodes = $this->decodeIdsString($request, ResourceNode::class);
        $this->manager->toggleFollowResources($user, $nodes, $mode);

        return new JsonResponse(array_map(function (ResourceNode $resourceNode) {
            return $this->serializer->serialize($resourceNode);
        }, $nodes));
    }
}
