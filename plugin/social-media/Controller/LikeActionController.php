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

use Claroline\CoreBundle\Entity\User;
use Icap\SocialmediaBundle\Entity\LikeAction;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class LikeActionController extends Controller
{
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
        $criteria = array('resource' => $resourceId);
        $userLike = $likeManager->getLikeBy($user, null, $criteria);
        $likesQB = $likeManager->getLikesForPagination(null, $criteria);
        $pager = $this->paginateQuery($likesQB, 1);

        return array('resourceId' => $resourceId, 'pager' => $pager, 'userLike' => $userLike);
    }

    /**
     * @Route("/like", name="icap_socialmedia_like")
     * @Method({"POST"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param User                                      $user
     *
     * @return bool
     */
    public function likeAction(Request $request, User $user)
    {
        $like = new LikeAction();
        $like->setUser($user);
        $like = $this->getLikeActionManager()->createLike($request, $like);
        $this->dispatchLikeEvent($like);
        $jsonResponse = new JsonResponse(true);

        return $jsonResponse;
    }

    /**
     * @Route("/unlike", name="icap_socialmedia_unlike")
     * @ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param User                                      $user
     *
     * @return bool
     */
    public function unlikeAction(Request $request, User $user)
    {
        $likeActionManager = $this->getLikeActionManager();
        $like = $likeActionManager->getLikeBy($user, $request);
        if ($like !== null) {
            $likeActionManager->removeLike($like);
        }
        $jsonResponse = new JsonResponse(true);

        return $jsonResponse;
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

        return array('pager' => $pager, 'parameters' => $parameters);
    }
}
