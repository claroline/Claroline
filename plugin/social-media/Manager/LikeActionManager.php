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

namespace Icap\SocialmediaBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Icap\SocialmediaBundle\Entity\LikeAction;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class LikeActionManager.
 *
 * @DI\Service("icap_socialmedia.manager.like_action")
 */
class LikeActionManager
{
    protected $em;

    /**
     * @var \Icap\SocialmediaBundle\Repository\LikeActionRepository
     */
    protected $likeActionRepository;

    /**
     * @var \Claroline\CoreBundle\Repository\ResourceNodeRepository
     */
    protected $resourceNodeRepository;

    /**
     * @var WallItemManager
     */
    protected $wallItemManager;

    /**
     * @DI\InjectParams({
     *      "em"                = @DI\Inject("doctrine.orm.entity_manager"),
     *      "wallItemManager"   = @DI\Inject("icap_socialmedia.manager.wall_item")
     * })
     *
     * @param EntityManager   $em
     * @param WallItemManager $wallItemManager
     */
    public function __construct(EntityManager $em, WallItemManager $wallItemManager)
    {
        $this->em = $em;
        $this->wallItemManager = $wallItemManager;
        $this->likeActionRepository = $em->getRepository('IcapSocialmediaBundle:LikeAction');
        $this->resourceNodeRepository = $em->getRepository('ClarolineCoreBundle:Resource\ResourceNode');
    }

    /**
     * @param User    $user
     * @param Request $request
     * @param array   $criteria
     *
     * @return null|object
     */
    public function getLikeBy(User $user, Request $request = null, $criteria = array())
    {
        $criteria = $this->getCriteriaFromRequest($request, $user, $criteria);

        return $this->likeActionRepository->findOneBy($criteria);
    }

    /**
     * @param Request $request
     * @param array   $criteria
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getLikesForPagination(Request $request = null, $criteria = array())
    {
        $criteria = $this->getCriteriaFromRequest($request, null, $criteria);

        return $this->likeActionRepository->findLikesForPagination($criteria);
    }

    public function countLikes(Request $request)
    {
        $criteria = $this->getCriteriaFromRequest($request, null);

        return $this->likeActionRepository->countLikes($criteria);
    }

    public function createLike(Request $request, LikeAction $like)
    {
        $resourceId = $request->get('resourceId');
        if ($resourceId === null) {
            $url = $request->get('url');
            $title = $request->get('title');
            $like->setUrl($url);
            $like->setTitle($title);
        } else {
            $resourceNode = $this->resourceNodeRepository->find($resourceId);
            $like->setResource($resourceNode);
        }
        $this->em->persist($like);
        $this->wallItemManager->createWallItem($like);

        $this->em->flush();

        return $like;
    }

    public function removeLike(LikeAction $like)
    {
        $this->em->remove($like);
        $this->em->flush();
    }

    private function getCriteriaFromRequest(Request $request = null, User $user = null, $criteria = array())
    {
        if ($user !== null) {
            $criteria['user'] = $user;
        }

        if ($request !== null) {
            $resourceId = $request->get('resourceId');
            if ($resourceId == null) {
                $resourceId = $request->get('resource');
            }
            if ($resourceId !== null) {
                $criteria['resource'] = $resourceId;
            } else {
                $url = $request->get('url');
                $criteria['url'] = $url;
            }
        }

        return $criteria;
    }
}
