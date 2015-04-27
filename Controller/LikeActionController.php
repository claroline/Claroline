<?php
/**
 * This file is part of the Claroline Connect package
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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

class LikeActionController extends Controller
{
    /**
     * @Route("/like.{_format}", name="icap_socialmedia_like", defaults={"_format" = "json"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param User $user
     * @return bool
     */
    public function likeAction(Request $request, User $user)
    {
        $like = new LikeAction();
        $like->setUser($user);
        $this->getLikeActionManager()->createLike($request, $like);

        return true;
    }
    
    /**
     * @Route("/unlike.{_format}", name="icap_socialmedia_unlike", defaults={"_format" = "json"})
     * @ParamConverter("user", options={"authenticatedUser" = true})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param User $user
     * @return bool
     */
    public function unlikeAction(Request $request, User $user)
    {
        $likeActionManager = $this->getLikeActionManager();
        $like = $likeActionManager->getLikeBy($request, $user);
        if ($like !== null) {
            $likeActionManager->removeLike($like);
        }

        return true;
    }

    /**
     * @Route("/form/{resourceId}", name="icap_socialmedia_like_form", )
     * @ParamConverter("user", options={"authenticatedUser" = true})
     * @Template()
     * @param int $resourceId
     * @param User $user
     * @return array
     */
    public function formAction($resourceId, User $user)
    {
        $likes = $this->getLikeActionManager()->getLikesBy(array("resource"=>$resourceId));

        return array("resourceId" => $resourceId, "likes" => $likes);
    }

    /**
     * @Route(
     *  "/likes/count/{resourceId}",
     *  name="icap_socialmedia_count_likes_resource",
     *  requirements={"resourceId" = "\d+"}
     * )
     * @Route(
     *  "/likes/count",
     *  name="icap_socialmedia_count_likes"
     * )
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param $resourceId
     * @return array
     */
    public function countLikesAction(Request $request, $resourceId = null)
    {
        if ($resourceId !== null) {
            $request->request->set("resource", $resourceId);
        }
        $likes = $this->getLikeActionManager()->countLikes($request);

        return array('likes' => $likes);
    }
} 