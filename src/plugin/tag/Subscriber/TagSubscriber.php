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

use Claroline\AppBundle\API\Finder\Type\TagType;
use Claroline\AppBundle\Event\Finder\BuildQueryEvent;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\TagBundle\Entity\TaggedObject;
use Claroline\TagBundle\Manager\TagManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TagSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ObjectManager $om,
        private readonly TagManager $manager
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BuildQueryEvent::class => 'onFinderBuildQuery',
            'claroline_tag_multiple_data' => 'onTagObject',
            'claroline_retrieve_used_tags_by_class_and_ids' => 'onGetObjectTags',
        ];
    }

    /**
     * Implements Tag filter for Finders.
     * The FinderType which wants to use the filter by tags must add a placeholder in the buildFinder method:
     *
     *   $builder->add('tags', \Claroline\AppBundle\API\Finder\Type\TagType::class);
     */
    public function onFinderBuildQuery(BuildQueryEvent $event): void
    {
        $finder = $event->getFinder();

        if ($finder->getType() instanceof TagType && !empty($finder->getFilterValue())) {
            $tags = is_string($finder->getFilterValue()) ? [$finder->getFilterValue()] : $finder->getFilterValue();

            // generate query for tags filter
            $tagQueryBuilder = $this->om->createQueryBuilder();
            $tagQueryBuilder
                ->select('to.id')
                ->from(TaggedObject::class, 'to')
                ->innerJoin('to.tag', 't')
                ->andWhere("to.objectId = {$finder->getParent()->getAlias()}.uuid") // this makes the UUID required on tagged objects
                ->andWhere('(t.uuid IN (:tagIds) OR t.name IN (:tagNames))')
                ->groupBy('to.objectId')
                ->having('COUNT(to.id) = :expectedCount'); // this permits making a AND between tags

            // append subquery to the original one
            $queryBuilder = $event->getQueryBuilder();
            $queryBuilder->andWhere($queryBuilder->expr()->exists($tagQueryBuilder->getDql()))
                ->setParameter('tagIds', $tags)
                ->setParameter('tagNames', $tags)
                ->setParameter('expectedCount', count($tags));
        }
    }

    public function onTagObject(GenericDataEvent $event): void
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
     * Used by serializers to retrieve tags.
     */
    public function onGetObjectTags(GenericDataEvent $event): void
    {
        $tags = [];
        $data = $event->getData();

        if (is_array($data) && isset($data['class']) && !empty($data['ids'])) {
            /** @var TaggedObject[] $taggedObjects */
            $taggedObjects = $this->manager->getTaggedObjects($data['class'], $data['ids']);

            // array [tagName]
            foreach ($taggedObjects as $taggedObject) {
                $tag = $taggedObject->getTag();
                $tags[$tag->getId()] = $tag->getName();
            }
            $tags = array_values($tags);
        }
        $event->setResponse($tags);
    }
}
