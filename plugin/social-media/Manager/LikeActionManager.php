<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 * Date: 4/22/15
 */

namespace Icap\SocialmediaBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Icap\SocialmediaBundle\Entity\LikeAction;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class LikeActionManager.
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
     * @return LikeAction|null
     */
    public function getLikeBy(User $user, Request $request = null, $criteria = [])
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
    public function getLikesForPagination(Request $request = null, $criteria = [])
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
        $decodedRequest = json_decode($request->getContent(), true);
        $resourceId = isset($decodedRequest['resourceId']) ? $decodedRequest['resourceId'] : null;

        if (null === $resourceId) {
            $url = isset($decodedRequest['url']) ? $decodedRequest['url'] : null;
            $title = isset($decodedRequest['title']) ? $decodedRequest['title'] : null;
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

    private function getCriteriaFromRequest(Request $request = null, User $user = null, $criteria = [])
    {
        if (null !== $user) {
            $criteria['user'] = $user;
        }

        if (null !== $request) {
            $decodedRequest = json_decode($request->getContent(), true);
            $resourceId = isset($decodedRequest['resourceId']) ? $decodedRequest['resourceId'] : null;

            if (empty($resourceId)) {
                $resourceId = isset($decodedRequest['resource']) ? $decodedRequest['resource'] : null;
            }
            if (null !== $resourceId) {
                $criteria['resource'] = $resourceId;
            } else {
                $url = isset($decodedRequest['url']) ? $decodedRequest['url'] : null;
                $criteria['url'] = $url;
            }
        }

        return $criteria;
    }
}
