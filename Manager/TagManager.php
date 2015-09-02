<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\TagBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Pager\PagerFactory;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\TagBundle\Entity\TaggedItem;
use Claroline\TagBundle\Entity\Tag;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.tag_manager")
 */
class TagManager
{
    private $om;
    private $pagerFactory;

    private $taggedItemRepo;
    private $tagRepo;

    /**
     * @DI\InjectParams({
     *     "om"           = @DI\Inject("claroline.persistence.object_manager"),
     *     "pagerFactory" = @DI\Inject("claroline.pager.pager_factory")
     * })
     */
    public function __construct(ObjectManager $om, PagerFactory $pagerFactory)
    {
        $this->om = $om;
        $this->pagerFactory = $pagerFactory;

        $this->taggedItemRepo = $om->getRepository('ClarolineTagBundle:TaggedItem');
        $this->tagRepo = $om->getRepository('ClarolineTagBundle:Tag');
    }
    
    public function persistTag(Tag $tag)
    {
        $this->om->persist($tag);
        $this->om->flush();
    }
    
    public function deleteTag(Tag $tag)
    {
        $this->om->remove($tag);
        $this->om->flush();
    }

    public function persistTaggedItem(TaggedItem $taggedItem)
    {
        $this->om->persist($taggedItem);
        $this->om->flush();
    }

    public function deleteTaggedItem(TaggedItem $taggedItem)
    {
        $this->om->remove($taggedItem);
        $this->om->flush();
    }

    public function tagExists($name)
    {
        $tag = $this->getOneTagByName($name);

        return !is_null($tag);
    }

    public function getOrCreatePlatformTag($name)
    {
        $tag = $this->getOnePlatformTagByName($name);

        if (is_null($tag)) {
            $tag = new Tag();
            $tag->setName($name);
            $this->persistTag($tag);
        }

        return $tag;
    }

    public function getOrCreateUserTag(User $user, $name)
    {
        $tag = $this->getOneUserTagByName($user, $name);

        if (is_null($tag)) {
            $tag = new Tag();
            $tag->setUser($user);
            $tag->setName($name);
            $this->persistTag($tag);
        }

        return $tag;
    }

    public function tagItem($tagName, $item, User $user = null)
    {
        if (method_exists($item, 'getId')) {
            $itemId = $item->getId();
            $itemClass = get_class($item);
            $tag = is_null($user) ?
                $this->getOrCreatePlatformTag($tagName) :
                $this->getOrCreateUserTag($user, $tagName);

            $taggedItem = $this->getOneTaggedItemByTagAndItem($tag, $itemId, $itemClass);

            if (is_null($taggedItem)) {
                $taggedItem = new TaggedItem();
                $taggedItem->setTag($tag);
                $taggedItem->setItemId($itemId);
                $taggedItem->setItemClass($itemClass);
                $this->persistTaggedItem($taggedItem);
            }

            return $taggedItem;
        } else {

            return null;
        }
    }


    /***********************************
     * Access to TagRepository methods *
     ***********************************/

    public function getPlatformTags(
        $search = '',
        $orderedBy = 'name',
        $order = 'ASC',
        $withPager = false,
        $page = 1,
        $max = 50
    )
    {
        $tags = empty($search) ?
            $this->tagRepo->findAllPlatformTags($orderedBy, $order) :
            $this->tagRepo->findSearchedPlatformTags($search, $orderedBy, $order);

        return $withPager ?
            $this->pagerFactory->createPagerFromArray($tags, $page, $max) :
            $tags;
    }

    public function getUserTags(
        User $user,
        $search = '',
        $withPlatform = false,
        $orderedBy = 'name',
        $order = 'ASC',
        $withPager = false,
        $page = 1,
        $max = 50
    )
    {
        $tags = empty($search) ?
            $this->tagRepo->findAllUserTags(
                $user,
                $withPlatform,
                $orderedBy,
                $order
            ) :
            $this->tagRepo->findSearchedUserTags(
                $user,
                $search,
                $withPlatform,
                $orderedBy,
                $order
            );

        return $withPager ?
            $this->pagerFactory->createPagerFromArray($tags, $page, $max) :
            $tags;
    }

    public function getOnePlatformTagByName($name)
    {
        return $this->tagRepo->findOnePlatformTagByName($name);
    }

    public function getOneUserTagByName(User $user, $name)
    {
        return $this->tagRepo->findOneUserTagByName($user, $name);
    }


    /******************************************
     * Access to TaggedItemRepository methods *
     ******************************************/

    public function getSearchedPlatformTaggedItems(
        $search,
        $withPager = false,
        $page = 1,
        $max = 50
    )
    {
        $items = empty($search) ?
            array() :
            $this->taggedItemRepo->findSearchedPlatformTaggedItems($search);

        return $withPager ?
            $this->pagerFactory->createPagerFromArray($items, $page, $max) :
            $items;
    }

    public function getSearchedUserTaggedItems(
        User $user,
        $search,
        $withPlatform = false,
        $withPager = false,
        $page = 1,
        $max = 50
    )
    {
        $items = empty($search) ?
            array() :
            $this->taggedItemRepo->findSearchedUserTaggedItems($user, $search, $withPlatform);

        return $withPager ?
            $this->pagerFactory->createPagerFromArray($items, $page, $max) :
            $items;
    }

    public function getOneTaggedItemByTagAndItem(Tag $tag, $itemId, $itemClass)
    {
        return $this->taggedItemRepo->findOneTaggedItemByTagAndItem(
            $tag,
            $itemId,
            $itemClass
        );
    }
}
