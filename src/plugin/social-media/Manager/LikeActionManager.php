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

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Repository\Resource\ResourceNodeRepository;
use Icap\SocialmediaBundle\Entity\LikeAction;
use Icap\SocialmediaBundle\Event\Log\LogSocialmediaLikeEvent;
use Icap\SocialmediaBundle\Repository\LikeActionRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class LikeActionManager.
 */
class LikeActionManager
{
    /** @var ObjectManager */
    private $om;
    /** @var EventDispatcherInterface */
    private $dispatcher;

    /** @var LikeActionRepository */
    private $likeActionRepository;
    /** @var ResourceNodeRepository */
    private $resourceNodeRepository;

    public function __construct(
        ObjectManager $om,
        EventDispatcherInterface $dispatcher
    ) {
        $this->om = $om;
        $this->dispatcher = $dispatcher;

        $this->likeActionRepository = $om->getRepository(LikeAction::class);
        $this->resourceNodeRepository = $om->getRepository(ResourceNode::class);
    }

    /**
     * @return LikeAction|null
     */
    public function getLikeBy(User $user, Request $request = null, array $criteria = [])
    {
        $criteria = $this->getCriteriaFromRequest($request, $user, $criteria);

        return $this->likeActionRepository->findOneBy($criteria);
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

        $this->om->persist($like);
        $this->om->flush();

        $this->dispatcher->dispatch(new LogSocialmediaLikeEvent($like), 'log');

        return $like;
    }

    public function removeLike(LikeAction $like)
    {
        $this->om->remove($like);
        $this->om->flush();
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
