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
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @todo rewrite using the new resource action system
 */
class LikeActionController extends Controller
{
    /** @var ResourceNodeSerializer */
    private $serializer;

    /**
     * LikeActionController constructor.
     *
     * @DI\InjectParams({
     *     "serializer" = @DI\Inject("Claroline\CoreBundle\API\Serializer\Resource\ResourceNodeSerializer")
     * })
     *
     * @param ResourceNodeSerializer $serializer
     */
    public function __construct(ResourceNodeSerializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @Route("/like/form/{resourceId}", name = "icap_socialmedia_like_form")
     * @ParamConverter("user", options={"authenticatedUser" = true})
     * @Template()
     *
     * @param int  $resourceId
     * @param User $user
     *
     * @return array
     */
    public function formAction($resourceId, User $user)
    {
        $likeManager = $this->getLikeActionManager();
        $criteria = ['resource' => $resourceId];
        $userLike = $likeManager->getLikeBy($user, null, $criteria);
        $likesQB = $likeManager->getLikesForPagination(null, $criteria);
        $pager = $this->paginateQuery($likesQB, 1);

        return ['resourceId' => $resourceId, 'pager' => $pager, 'userLike' => $userLike];
    }

    /**
     * @Route("/like", name="icap_socialmedia_like", options={"expose"=true})
     * @Method({"POST"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param User                                      $user
     *
     * @return JsonResponse
     */
    public function likeAction(Request $request, User $user)
    {
        $like = new LikeAction();
        $like->setUser($user);
        $like = $this->getLikeActionManager()->createLike($request, $like);
        $this->dispatchLikeEvent($like);

        return new JsonResponse(
            $this->serializer->serialize($like->getResource())
        );
    }

    /**
     * @Route("/unlike", name="icap_socialmedia_unlike", options={"expose"=true})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param User                                      $user
     *
     * @return JsonResponse
     */
    public function unlikeAction(Request $request, User $user)
    {
        $likeActionManager = $this->getLikeActionManager();
        $like = $likeActionManager->getLikeBy($user, $request);
        if (null !== $like) {
            $likeActionManager->removeLike($like);
        }

        return new JsonResponse(
            $this->serializer->serialize($like->getResource())
        );
    }

    /**
     * @Route("/like/list/{page}", name="icap_socialmedia_likelist", defaults={"page" = "1"})
     * @Method({"GET"})
     *
     * @param Request $request
     * @Template()
     *
     * @param $page
     *
     * @return array
     */
    public function likeListAction(Request $request, $page)
    {
        $likesQB = $this->getLikeActionManager()->getLikesForPagination($request);
        $pager = $this->paginateQuery($likesQB, $page);
        $parameters = $request->query->all();
        $parameters['page'] = ($pager->hasNextPage()) ? $pager->getNextPage() : 0;

        return ['pager' => $pager, 'parameters' => $parameters];
    }
}
