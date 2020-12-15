<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 *
 * Date: 4/29/15
 */

namespace Icap\SocialmediaBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Icap\SocialmediaBundle\Entity\ActionBase;
use Icap\SocialmediaBundle\Entity\WallItem;

/**
 * Class WallItemManager.
 */
class WallItemManager
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Icap\SocialmediaBundle\Repository\WallItemRepository;
     */
    protected $wallItemRepository;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->wallItemRepository = $em->getRepository('IcapSocialmediaBundle:WallItem');
    }

    public function getWallItemsForPagination($userId, $isOwner)
    {
        return $this->wallItemRepository->findItemsForPagination($userId, $isOwner);
    }

    public function removeItem($itemId, User $user)
    {
        $wallItem = $this->wallItemRepository->findOneBy(['id' => $itemId, 'user' => $user]);

        if (null !== $wallItem) {
            $this->em->remove($wallItem);
            $this->em->flush();
        }
    }

    public function createWallItem(ActionBase $action)
    {
        $wallItem = new WallItem();
        $wallItem->setUser($action->getUser());
        $actionClass = get_class($action);
        if (false !== strpos($actionClass, 'LikeAction')) {
            $wallItem->setLike($action);
        } elseif (false !== strpos($actionClass, 'ShareAction')) {
            $wallItem->setShare($action);
        } elseif (false !== strpos($actionClass, 'CommentAction')) {
            $wallItem->setComment($action);
        }
        $this->em->persist($wallItem);

        return $wallItem;
    }
}
