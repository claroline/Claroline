<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 *
 * Date: 4/22/15
 */

namespace Icap\SocialmediaBundle\Controller;

use Claroline\CoreBundle\API\Serializer\Resource\ResourceNodeSerializer;
use Claroline\CoreBundle\Entity\User;
use Icap\SocialmediaBundle\Entity\LikeAction;
use Icap\SocialmediaBundle\Manager\LikeActionManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @todo rewrite using the new resource action system
 */
class LikeActionController
{
    /** @var ResourceNodeSerializer */
    private $serializer;
    /** @var LikeActionManager */
    private $likeActionManager;

    public function __construct(
        ResourceNodeSerializer $serializer,
        LikeActionManager $likeActionManager
    ) {
        $this->serializer = $serializer;
        $this->likeActionManager = $likeActionManager;
    }

    /**
     * @Route("/like", name="icap_socialmedia_like", options={"expose"=true}, methods={"POST"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function likeAction(Request $request, User $user): JsonResponse
    {
        $like = new LikeAction();
        $like->setUser($user);
        $like = $this->likeActionManager->createLike($request, $like);

        return new JsonResponse(
            $this->serializer->serialize($like->getResource())
        );
    }

    /**
     * @Route("/unlike", name="icap_socialmedia_unlike", options={"expose"=true})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     */
    public function unlikeAction(Request $request, User $user): JsonResponse
    {
        $like = $this->likeActionManager->getLikeBy($user, $request);
        if (null !== $like) {
            $this->likeActionManager->removeLike($like);
        }

        return new JsonResponse(
            $this->serializer->serialize($like->getResource())
        );
    }
}
