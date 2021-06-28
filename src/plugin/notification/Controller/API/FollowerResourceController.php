<?php

namespace Icap\NotificationBundle\Controller\API;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
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
class FollowerResourceController
{
    use RequestDecoderTrait;

    /** @var ObjectManager */
    private $om;
    /** @var SerializerProvider */
    private $serializer;
    /** @var NotificationManager */
    private $manager;

    public function __construct(
        ObjectManager $om,
        SerializerProvider $serializer,
        NotificationManager $manager
    ) {
        $this->om = $om;
        $this->serializer = $serializer;
        $this->manager = $manager;
    }

    /**
     * Follows or unfollows resources.
     *
     * @Route("resources/toggle/{mode}", name="icap_notification_follower_resources_toggle", methods={"PUT"})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    public function followerResourcesToggleAction(string $mode, User $user, Request $request): JsonResponse
    {
        $nodes = $this->decodeIdsString($request, ResourceNode::class);
        $this->manager->toggleFollowResources($user, $nodes, $mode);

        return new JsonResponse(array_map(function (ResourceNode $resourceNode) {
            return $this->serializer->serialize($resourceNode);
        }, $nodes));
    }
}
