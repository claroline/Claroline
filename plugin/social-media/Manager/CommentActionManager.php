<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 * 
 * Date: 5/7/15
 */

namespace Icap\SocialmediaBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Icap\SocialmediaBundle\Entity\CommentAction;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class CommentActionManager.
 *
 * @DI\Service("icap_socialmedia.manager.comment_action")
 */
class CommentActionManager
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var \Icap\SocialmediaBundle\Repository\CommentActionRepository
     */
    protected $commentActionRepository;

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
        $this->commentActionRepository = $em->getRepository('IcapSocialmediaBundle:CommentAction');
        $this->resourceNodeRepository = $em->getRepository('ClarolineCoreBundle:Resource\ResourceNode');
    }

    public function getComment($commentId, User $user)
    {
        return $this->commentActionRepository->findOneBy(array(
            'id' => $commentId,
            'user' => $user,
        ));
    }

    /**
     * @param $resourceId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getCommentsForPagination($resourceId)
    {
        return $this->commentActionRepository->findCommentsForPagination($resourceId);
    }

    public function createComment($resourceId, CommentAction $comment)
    {
        $resourceNode = $this->resourceNodeRepository->find($resourceId);
        $comment->setResource($resourceNode);
        $this->em->persist($comment);
        $this->wallItemManager->createWallItem($comment);

        $this->em->flush();
    }

    public function getHasCommentedUserIds($resourceId)
    {
        $queryResult = $this->commentActionRepository->findHasCommentedUserIds($resourceId);
        $userIds = array();
        if (!empty($queryResult) && !empty($queryResult)) {
            foreach ($queryResult as $userId) {
                $userIds[] = $userId['id'];
            }
        }

        return $userIds;
    }

    public function removeComment($commentId, User $user)
    {
        $this->commentActionRepository->removeComment($commentId, $user);
    }
}
