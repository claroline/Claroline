<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\TagBundle\Listener;

use Claroline\CoreBundle\Event\GenericDatasEvent;
use Claroline\TagBundle\Manager\TagManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service
 */
class TagListener
{
    private $tagManager;

    /**
    * @DI\InjectParams({
    *     "tagManager" = @DI\Inject("claroline.manager.tag_manager")
    * })
    */
    public function __construct(TagManager $tagManager)
    {
        $this->tagManager = $tagManager;
    }

    /**
     * @DI\Observe("claroline_tag_item")
     *
     * @param GenericDatasEvent $event
     */
    public function onItemTag(GenericDatasEvent $event)
    {
        $taggedItem = null;
        $datas = $event->getDatas();

        if (is_array($datas) && isset($datas['tag']) && isset($datas['item'])) {
            $user = isset($datas['user']) ? $datas['user'] : null;
            $taggedItem = $this->tagManager->tagItem($datas['tag'], $datas['item'], $user);
        }
        $event->setResponse($taggedItem);
    }

    /**
     * @DI\Observe("claroline_retrieve_tagged_items")
     *
     * @param GenericDatasEvent $event
     */
    public function onRetrieveItemsByTag(GenericDatasEvent $event)
    {
        $taggedItems = array();
        $datas = $event->getDatas();

        if (is_array($datas) && isset($datas['tag'])) {

            if (isset($datas['user'])) {
                $withPlatform = isset($datas['with_platform']) && $datas['with_platform'];
                $items = $this->tagManager->getSearchedUserTaggedItems(
                    $datas['user'],
                    $datas['tag'],
                    $withPlatform
                );
            } else {
                $items = $this->tagManager->getSearchedPlatformTaggedItems($datas['tag']);
            }

            foreach ($items as $item) {
                $datas = array();
                $datas['class'] = $item->getItemClass();
                $datas['itemId'] = $item->getItemId();
                $taggedItems[] = $datas;
            }
        }
        $event->setResponse($taggedItems);
    }
}
