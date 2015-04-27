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

namespace Icap\SocialmediaBundle\Manager;

use Doctrine\ORM\EntityManager;
use Icap\SocialmediaBundle\Entity\LikeAction;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class LikeActionManager
 * @package Icap\SocialmediaBundle\Manager
 *
 * @DI\Service("icap.social_media.manager.like_action")
 */
class LikeActionManager 
{
    protected $em;

    protected $likeActionRepository;

    /**
     * @DI\InjectParams({
     *      "em"    = @DI\Inject("doctrine.orm.entity_manager")
     * })
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->likeActionRepository = $em->getRepository("IcapSocialmediaBundle:LikeAction");
    }

    /**
     * @param $request
     * @param $user
     * @return null|\Icap\SocialmediaBundle\Entity\LikeAction
     */
    public function getLikeBy($request, $user)
    {
        $criteria = $this->getCriteriaFromRequest($request, $user);

        return $this->likeActionRepository->findOneBy($criteria);
    }

    /**
     * @param $criteria
     * @return array Icap\SocialmediaBundle\Entity\LikeAction
     */
    public function getLikesBy($criteria)
    {
        return $this->likeActionRepository->findBy($criteria);
    }

    public function countLikes(Request $request)
    {
        $criteria = $this->getCriteriaFromRequest($request, null);

        return $this->likeActionRepository->countLikes($criteria);
    }

    public function createLike(Request $request, LikeAction $like)
    {
        $resourceId = $request->get("resourceId");
        $like->setResource($resourceId);
        if ($resourceId === null) {
            $url = $request->get("url");
            $title = $request->get("title");
            $like->setUrl($url);
            $like->setTitle($title);
        }

        $this->em->persist($like);
        $this->em->flush();
    }

    public function removeLike(LikeAction $like)
    {
        $this->em->remove($like);
        $this->em->flush();
    }

    private function getCriteriaFromRequest(Request $request, User $user = null)
    {
        if ($user !== null) {
            $criteria = array("user" => $user);
        }
        $resourceId = $request->get("resourceId");
        if ($resourceId !== null) {
            $criteria["resource"] = $resourceId;
        }
        $url = $request->get("url");
        if ($url !== null) {
            $criteria["url"] = $url;
        }
        $title = $request->get("title");
        if ($title !== null) {
            $criteria["title"] = $title;
        }

        return $criteria;
    }
} 