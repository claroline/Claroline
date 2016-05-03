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
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class WallItemManager.
 *
 * @DI\Service("icap_socialmedia.manager.wall_item")
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
     * @DI\InjectParams({
     *      "em"    = @DI\Inject("doctrine.orm.entity_manager")
     * })
     *
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
        $wallItem = $this->wallItemRepository->findOneBy(array('id' => $itemId, 'user' => $user));

        if ($wallItem !== null) {
            $this->em->remove($wallItem);
            $this->em->flush();
        }
    }

    public function createWallItem(ActionBase $action)
    {
        $wallItem = new WallItem();
        $wallItem->setUser($action->getUser());
        $actionClass = get_class($action);
        if (strpos($actionClass, 'LikeAction') !== false) {
            $wallItem->setLike($action);
        } elseif (strpos($actionClass, 'ShareAction') !== false) {
            $wallItem->setShare($action);
        } elseif (strpos($actionClass, 'CommentAction') !== false) {
            $wallItem->setComment($action);
        }
        $this->em->persist($wallItem);

        return $wallItem;
    }
}
