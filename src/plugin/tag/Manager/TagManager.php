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

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\TagBundle\Entity\Tag;
use Claroline\TagBundle\Entity\TaggedObject;
use Claroline\TagBundle\Repository\TaggedObjectRepository;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class TagManager
{
    private TaggedObjectRepository $taggedObjectRepo;

    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly ObjectManager $om
    ) {
        $this->taggedObjectRepo = $om->getRepository(TaggedObject::class);
    }

    /**
     * @param string[] $tags
     *
     * @return TaggedObject[]
     */
    public function tagData(array $tags, array $data, ?bool $replace = false): array
    {
        $taggedObjects = [];
        $uniqueTags = [];

        foreach ($tags as $tag) {
            $value = trim($tag);

            if (!empty($value)) {
                $uniqueTags[strtolower($value)] = $value;
            }
        }
        $tagsList = [];

        foreach ($uniqueTags as $tagName) {
            $tag = $this->getOrCreateTag($tagName);
            if (!empty($tag)) {
                $tagsList[$tagName] = $tag;
            }
        }

        foreach ($data as $objectData) {
            $objectId = $objectData['id'];
            $objectClass = $objectData['class'];
            $objectName = isset($objectData['name']) ? $objectData['name'] : null;

            if ($replace) {
                $this->removeUnusedTags($objectClass, $objectId, $tags);
            }

            foreach ($uniqueTags as $tagName) {
                if (empty($tagsList[$tagName])) {
                    continue;
                }

                $tag = $tagsList[$tagName];
                $taggedObject = null;

                if ($tag->getId()) {
                    $taggedObject = $this->taggedObjectRepo->findOneTaggedObjectByTagAndObject($tag, $objectId, $objectClass);
                }

                if (is_null($taggedObject)) {
                    $taggedObject = new TaggedObject();
                }

                $taggedObject->setTag($tag);
                $taggedObject->setObjectId($objectId);
                $taggedObject->setObjectClass($objectClass);
                $tag->addTaggedObject($taggedObject);

                if ($objectName) {
                    $taggedObject->setObjectName($objectName);
                }

                $this->om->persist($taggedObject);
                $taggedObjects[] = $taggedObject;
            }
        }

        $this->om->flush();

        return $taggedObjects;
    }

    public function removeTaggedObjectsByClassAndIds($class, array $ids): void
    {
        if (!empty($class) && !empty($ids)) {
            $objects = $this->taggedObjectRepo->findTaggedObjectsByClassAndIds($class, $ids);
            $nbObjects = count($objects);

            foreach ($objects as $object) {
                $this->om->remove($object);
            }

            if ($nbObjects > 0) {
                $this->om->flush();
            }
        }
    }

    public function removeTagFromObjects(Tag $tag, array $objects = []): void
    {
        foreach ($objects as $object) {
            $taggedObject = $this->taggedObjectRepo->findOneTaggedObjectByTagAndObject($tag, $object['id'], $object['class']);
            if ($taggedObject) {
                $this->om->remove($taggedObject);
            }
        }

        $this->om->flush();
    }

    public function getTaggedObjects($class, array $ids = [])
    {
        return $this->taggedObjectRepo->findTaggedObjectsByClassAndIds($class, $ids);
    }

    private function removeUnusedTags($objectClass, $objectId, array $tags): void
    {
        $objects = $this->taggedObjectRepo->findTaggedObjectsByClassAndIds($objectClass, [$objectId]);

        foreach ($objects as $object) {
            if (!in_array($object->getTag()->getName(), $tags)) {
                $this->om->remove($object);
            }
        }
    }

    private function getOrCreateTag($tagName): ?Tag
    {
        // search for a platform tag first
        $tag = $this->getUowScheduledTag($tagName);
        if (empty($tag)) {
            $tag = $this->om->getRepository(Tag::class)->findOneBy(['name' => $tagName]);
        }

        // no tag found, we create a new one if the current user has correct rights
        if (empty($tag)) {
            $tag = new Tag();
            $tag->setName($tagName);

            if (!$this->authorization->isGranted('CREATE', $tag)) {
                return null;
            }

            $this->om->persist($tag);
            $this->om->flush();
        }

        return $tag;
    }

    /**
     * Avoids duplicate creation of a tag when inside a flushSuite.
     * The only way to do that is to search in the current UOW if a tag with the same name
     * is already scheduled for insertion.
     */
    private function getUowScheduledTag(string $name): ?Tag
    {
        $scheduledForInsert = $this->om->getUnitOfWork()->getScheduledEntityInsertions();
        foreach ($scheduledForInsert as $entity) {
            /** @var Tag $entity */
            if ($entity instanceof Tag && $name === $entity->getName()) {
                return $entity;
            }
        }

        return null;
    }
}
