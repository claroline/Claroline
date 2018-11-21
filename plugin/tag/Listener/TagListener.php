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

use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\TagBundle\Entity\TaggedObject;
use Claroline\TagBundle\Manager\TagManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service
 */
class TagListener
{
    /** @var TagManager */
    private $manager;

    /**
     * TagListener constructor.
     *
     * @DI\InjectParams({
     *     "tagManager" = @DI\Inject("claroline.manager.tag_manager")
     * })
     *
     * @param TagManager $tagManager
     */
    public function __construct(
        TagManager $tagManager
    ) {
        $this->manager = $tagManager;
    }

    /**
     * @DI\Observe("claroline_tag_object")
     *
     * @param GenericDataEvent $event
     */
    public function onObjectTag(GenericDataEvent $event)
    {
        $taggedObject = null;
        $data = $event->getData();

        if (is_array($data) && isset($data['tag']) && isset($data['object'])) {
            $user = isset($data['user']) ? $data['user'] : null;
            $taggedObject = $this->manager->tagObject($data['tag'], $data['object'], $user);
        }
        $event->setResponse($taggedObject);
    }

    /**
     * @DI\Observe("claroline_tag_multiple_data")
     *
     * @param GenericDataEvent $event
     */
    public function onDataTag(GenericDataEvent $event)
    {
        $taggedObject = null;
        $data = $event->getData();

        if (is_array($data) && isset($data['tags']) && isset($data['data'])) {
            $user = isset($data['user']) ? $data['user'] : null;
            $replace = isset($data['replace']) && $data['replace'];
            $taggedObject = $this->manager->tagData($data['tags'], $data['data'], $user, $replace);
        }
        $event->setResponse($taggedObject);
    }

    /**
     * @DI\Observe("claroline_retrieve_user_workspaces_by_tag")
     *
     * @param GenericDataEvent $event
     */
    public function onRetrieveUserWorkspacesByTag(GenericDataEvent $event)
    {
        $workspaces = [];
        $data = $event->getData();

        if (is_array($data) && isset($data['user']) && isset($data['tag'])) {
            $user = $data['user'];
            $tag = $data['tag'];
            $orderedBy = isset($data['ordered_by']) ? $data['ordered_by'] : 'id';
            $order = isset($data['order']) ? $data['order'] : 'ASC';
            $workspaces = $this->manager->getTaggedWorkspacesByRoles(
                $user,
                $tag,
                $orderedBy,
                $order
            );
        }
        $event->setResponse($workspaces);
    }

    /**
     * Used by serializers to retrieves tags.
     *
     * @DI\Observe("claroline_retrieve_used_tags_by_class_and_ids")
     *
     * @param GenericDataEvent $event
     */
    public function onRetrieveUsedTagsByClassAndIds(GenericDataEvent $event)
    {
        $tags = [];
        $data = $event->getData();

        if (is_array($data) && isset($data['class']) && !empty($data['ids'])) {
            /** @var TaggedObject[] $taggedObjects */
            $taggedObjects = $this->manager->getTaggedObjects(
                null,
                false,
                $data['class'],
                '',
                false,
                'name',
                'ASC',
                $data['ids']
            );
            if (isset($data['frequency']) && $data['frequency']) {
                //array [tagName => frequency]
                foreach ($taggedObjects as $taggedObject) {
                    $tag = $taggedObject->getTag();
                    if (!array_key_exists($tag->getName(), $tags)) {
                        $tags[$tag->getName()] = 0;
                    }
                    ++$tags[$tag->getName()];
                }
            } else {
                //array [tagName]
                foreach ($taggedObjects as $taggedObject) {
                    $tag = $taggedObject->getTag();
                    $tags[$tag->getId()] = $taggedObject->getTag()->getName();
                }
                $tags = array_values($tags);
            }
        }

        $event->setResponse($tags);
    }
}
