<?php

namespace Icap\NotificationBundle\Controller\API;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Icap\NotificationBundle\Manager\NotificationManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/notificationfollower")
 */
class FollowerResourceController extends AbstractCrudController
{
    /** @var NotificationManager */
    private $manager;

    /**
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
     * @Route("resources/toggle/{mode}", name="icap_notification_follower_resources_toggle")
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
