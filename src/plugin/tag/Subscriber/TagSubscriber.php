<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\TagBundle\Subscriber;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Event\SearchObjectsEvent;
use Claroline\TagBundle\Entity\TaggedObject;
use Claroline\TagBundle\Manager\TagManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TagSubscriber implements EventSubscriberInterface
{
    /**
     * TagListener constructor.
     */
    public function __construct(
        private readonly ObjectManager $om,
        private readonly TagManager $manager
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'objects.search' => 'onSearchObjects',
            'claroline_tag_multiple_data' => 'onDataTag',
            'claroline_retrieve_used_tags_by_class_and_ids' => 'onRetrieveUsedTagsByClassAndIds',
            'claroline_retrieve_used_tags_object_by_class_and_ids' => 'onRetrieveUsedTagsObjectByClassAndIds',
        ];
    }

    public function onSearchObjects(SearchObjectsEvent $event): void
    {
        // checks if there are filters managed by tag plugin in query
        $filters = $event->getFilters();

        if (!empty($filters) && !empty($filters['tags'])) {
            $tags = is_string($filters['tags']) ? [$filters['tags']] : $filters['tags'];

            // generate query for tags filter
            $tagQueryBuilder = $this->om->createQueryBuilder();
            $tagQueryBuilder
                ->select('to.id')
                ->from(TaggedObject::class, 'to')
                ->innerJoin('to.tag', 't')
                ->where('to.objectClass = :objectClass')
                ->andWhere("to.objectId = {$event->getObjectAlias()}.uuid") // this makes the UUID required on tagged objects
                ->andWhere('(t.uuid IN (:tagIds) OR t.name IN (:tagNames))')
                ->groupBy('to.objectId')
                ->having('COUNT(to.id) = :expectedCount'); // this permits to make a AND between tags

            // append sub query to the original one
            $queryBuilder = $event->getQueryBuilder();
            $queryBuilder->andWhere($queryBuilder->expr()->exists($tagQueryBuilder->getDql()))
                ->setParameter('objectClass', $event->getObjectClass())
                ->setParameter('tagIds', $tags)
                ->setParameter('tagNames', $tags)
                ->setParameter('expectedCount', count($tags));

            $event->setFilters($filters);
        }
    }

    public function onDataTag(GenericDataEvent $event): void
    {
        $taggedObject = null;
        $data = $event->getData();

        if (is_array($data) && isset($data['tags']) && isset($data['data'])) {
            $replace = isset($data['replace']) && $data['replace'];
            $taggedObject = $this->manager->tagData($data['tags'], $data['data'], $replace);
        }
        $event->setResponse($taggedObject);
    }

    /**
     * Used by serializers to retrieves tags.
     */
    public function onRetrieveUsedTagsByClassAndIds(GenericDataEvent $event): void
    {
        $tags = [];
        $data = $event->getData();

        if (is_array($data) && isset($data['class']) && !empty($data['ids'])) {
            /** @var TaggedObject[] $taggedObjects */
            $taggedObjects = $this->manager->getTaggedObjects($data['class'], $data['ids']);

            if (isset($data['frequency']) && $data['frequency']) {
                // array [tagName => frequency]
                foreach ($taggedObjects as $taggedObject) {
                    $tag = $taggedObject->getTag();
                    if (!array_key_exists($tag->getName(), $tags)) {
                        $tags[$tag->getName()] = 0;
                    }
                    ++$tags[$tag->getName()];
                }
            } else {
                // array [tagName]
                foreach ($taggedObjects as $taggedObject) {
                    $tag = $taggedObject->getTag();
                    $tags[$tag->getId()] = $tag->getName();
                }
                $tags = array_values($tags);
            }
        }
        $event->setResponse($tags);
    }

    /**
     * Used by serializers to retrieves tags object.
     */
    public function onRetrieveUsedTagsObjectByClassAndIds(GenericDataEvent $event): void
    {
        $tags = [];
        $data = $event->getData();

        if (is_array($data) && isset($data['class']) && !empty($data['ids'])) {
            /** @var TaggedObject[] $taggedObjects */
            $taggedObjects = $this->manager->getTaggedObjects($data['class'], $data['ids']);

            if (isset($data['frequency']) && $data['frequency']) {
                // array [tagName => frequency]
                foreach ($taggedObjects as $taggedObject) {
                    $tag = $taggedObject->getTag();
                    if (!array_key_exists($tag->getName(), $tags)) {
                        $tags[$tag->getName()] = 0;
                    }
                    ++$tags[$tag->getName()];
                }
            } else {
                // array [tagName]
                foreach ($taggedObjects as $taggedObject) {
                    $tag = $taggedObject->getTag();
                    $tags[$tag->getId()] = [
                        'id' => $tag->getUuid(),
                        'name' => $tag->getName(),
                    ];
                }
                $tags = array_values($tags);
            }
        }
        $event->setResponse($tags);
    }
}
