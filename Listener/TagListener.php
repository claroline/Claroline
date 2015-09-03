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
     * @DI\Observe("claroline_tag_object")
     *
     * @param GenericDatasEvent $event
     */
    public function onObjectTag(GenericDatasEvent $event)
    {
        $taggedObject = null;
        $datas = $event->getDatas();

        if (is_array($datas) && isset($datas['tag']) && isset($datas['object'])) {
            $user = isset($datas['user']) ? $datas['user'] : null;
            $taggedObject = $this->tagManager->tagObject($datas['tag'], $datas['object'], $user);
        }
        $event->setResponse($taggedObject);
    }

    /**
     * @DI\Observe("claroline_retrieve_tagged_objects")
     *
     * @param GenericDatasEvent $event
     */
    public function onRetrieveObjectsByTag(GenericDatasEvent $event)
    {
        $taggedObjects = array();
        $datas = $event->getDatas();

        if (is_array($datas)) {
            $search = isset($datas['tag']) ? $datas['tag'] : '';
            $user = isset($datas['user']) ? $datas['user'] : null;
            $withPlatform = isset($datas['with_platform']) && $datas['with_platform'];

            $objects = $this->tagManager->getTaggedObjects($user, $withPlatform, $search);

            foreach ($objects as $object) {
                $datas = array();
                $datas['class'] = $object->getObjectClass();
                $datas['objectId'] = $object->getObjectId();
                $taggedObjects[] = $datas;
            }
        }
        $event->setResponse($taggedObjects);
    }
}
