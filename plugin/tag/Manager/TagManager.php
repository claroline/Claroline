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
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Repository\UserRepository;
use Claroline\TagBundle\Entity\Tag;
use Claroline\TagBundle\Entity\TaggedObject;
use Claroline\TagBundle\Repository\TaggedObjectRepository;
use Claroline\TagBundle\Repository\TagRepository;

class TagManager
{
    /** @var ObjectManager */
    private $om;

    /** @var TaggedObjectRepository */
    private $taggedObjectRepo;

    /** @var TagRepository */
    private $tagRepo;

    /** @var UserRepository */
    private $userRepo;

    /**
     * TagManager constructor.
     *
     * @param ObjectManager $om
     */
    public function __construct(
        ObjectManager $om
    ) {
        $this->om = $om;
        $this->taggedObjectRepo = $om->getRepository('ClarolineTagBundle:TaggedObject');
        $this->tagRepo = $om->getRepository('ClarolineTagBundle:Tag');
        $this->userRepo = $om->getRepository(User::class);
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

    public function persistTaggedObject(TaggedObject $taggedObject)
    {
        $this->om->persist($taggedObject);
        $this->om->flush();
    }

    public function deleteTaggedObject(TaggedObject $taggedObject)
    {
        $this->om->remove($taggedObject);
        $this->om->flush();
    }

    private function getOrCreateTag($tagName, $user = null)
    {
        // search for a platform tag first
        $tag = $this->getUowScheduledTag($tagName);
        if (empty($tag)) {
            $tag = $this->getOnePlatformTagByName($tagName);
        }

        // search for a user tag
        if (empty($tag) && !empty($user)) {
            $tag = $this->getUowScheduledTag($tagName, $user);
            if (empty($tag)) {
                $tag = $this->getOneUserTagByName($user, $tagName);
            }
        }

        // no tag found, we create a new one
        if (empty($tag)) {
            $tag = new Tag();
            $tag->setName($tagName);
            $tag->setUser($user);
            $this->persistTag($tag);
        }

        return $tag;
    }

    /**
     * Avoids duplicate creation of a tag when inside a flushSuite.
     * The only way to do that is to search in the current UOW if a tag with the same name
     * is already scheduled for insertion.
     *
     * @param string $name
     * @param User   $user
     *
     * @return Tag|null
     */
    private function getUowScheduledTag($name, User $user = null)
    {
        $scheduledForInsert = $this->om->getUnitOfWork()->getScheduledEntityInsertions();
        foreach ($scheduledForInsert as $entity) {
            /** @var Tag $entity */
            if ($entity instanceof Tag
                && $name === $entity->getName()
                && $user === $entity->getUser()) {
                return $entity;
            }
        }

        return null;
    }

    public function tagObject(array $tags, $object, User $user = null)
    {
        $taggedObjects = [];
        $uniqueTags = [];

        foreach ($tags as $tag) {
            $value = trim($tag);

            if (!empty($value)) {
                $uniqueTags[strtolower($value)] = $value;
            }
        }

        if (method_exists($object, 'getId')) {
            $objectId = $object->getId();
            $objectClass = str_replace('Proxies\\__CG__\\', '', get_class($object));
            $tagsList = [];

            foreach ($uniqueTags as $tagName) {
                $tagsList[$tagName] = $this->getOrCreateTag($tagName, $user);
            }

            foreach ($uniqueTags as $tagName) {
                $tag = $tagsList[$tagName];

                $taggedObject = null;
                //if tag is scheduled for insertion it's new so no need to search for it
                if (!$this->getUowScheduledTag($tagName)) {
                    $taggedObject = $this->getOneTaggedObjectByTagAndObject($tag, $objectId, $objectClass);
                }

                if (is_null($taggedObject)) {
                    $taggedObject = new TaggedObject();
                    $taggedObject->setTag($tag);
                    $taggedObject->setObjectId($objectId);
                    $taggedObject->setObjectClass($objectClass);

                    if (method_exists($object, '__toString')) {
                        $taggedObject->setObjectName((string) $object);
                    }

                    $this->om->persist($taggedObject);
                    $taggedObjects[] = $taggedObject;
                }
            }
            $this->om->flush();
        }

        return $taggedObjects;
    }

    /**
     * @param string[]  $tags
     * @param array     $data
     * @param User|null $user
     * @param bool      $replace
     *
     * @return TaggedObject[]
     */
    public function tagData(array $tags, $data, User $user = null, $replace = false)
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
            $tagsList[$tagName] = $this->getOrCreateTag($tagName, $user);
        }

        foreach ($data as $objectData) {
            $objectId = $objectData['id'];
            $objectClass = $objectData['class'];
            $objectName = isset($objectData['name']) ? $objectData['name'] : null;

            if ($replace) {
                $this->removeUnusedTags($objectClass, $objectId, $tags);
            }

            foreach ($uniqueTags as $tagName) {
                $tag = $tagsList[$tagName];
                $taggedObject = null;

                if ($tag->getId()) {
                    $taggedObject = $this->getOneTaggedObjectByTagAndObject($tag, $objectId, $objectClass);
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

    public function removeUnusedTags($objectClass, $objectId, array $tags)
    {
        $objects = $this->taggedObjectRepo->findTaggedObjectsByClassAndIds($objectClass, [$objectId]);

        foreach ($objects as $object) {
            if (!in_array($object->getTag()->getName(), $tags)) {
                $this->om->remove($object);
            }
        }
    }

    public function getObjectsByClassAndIds($class, array $ids, $orderedBy = 'id', $order = 'ASC')
    {
        $objects = [];

        if (count($ids) > 0) {
            $objects = $this->taggedObjectRepo->findObjectsByClassAndIds($class, $ids, $orderedBy, $order);
        }

        return $objects;
    }

    public function getTaggedWorkspacesByRoles(User $user, $tag, $orderedBy = 'id', $order = 'ASC', $type = 0)
    {
        $roles = $user->getEntityRoles();

        return count($roles) > 0 ? $this->taggedObjectRepo->findTaggedWorkspacesByRoles($tag, $roles, $orderedBy, $order, $type) : [];
    }

    public function removeTaggedObjectsByClassAndIds($class, array $ids)
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

    public function removeTagFromObjects(Tag $tag, array $objects = [])
    {
        foreach ($objects as $object) {
            $taggedObject = $this->getOneTaggedObjectByTagAndObject($tag, $object['id'], $object['class']);
            if ($taggedObject) {
                $this->om->remove($taggedObject);
            }
        }

        $this->om->flush();
    }

    /***********************************
     * Access to TagRepository methods *
     ***********************************/

    public function getOnePlatformTagByName($name)
    {
        return $this->tagRepo->findOnePlatformTagByName($name);
    }

    public function getOneUserTagByName(User $user, $name)
    {
        return $this->tagRepo->findOneUserTagByName($user, $name);
    }

    public function getUserTagByNameAndUserId($name, $userId)
    {
        $user = $this->userRepo->findOneBy(['uuid' => $userId]);

        return $user ? $this->getOneUserTagByName($user, $name) : null;
    }

    /******************************************
     * Access to TaggedObjectRepository methods *
     ******************************************/

    public function getTaggedObjects($class, array $ids = [])
    {
        return $this->taggedObjectRepo->findTaggedObjectsByClassAndIds($class, $ids);
    }

    public function getOneTaggedObjectByTagAndObject(Tag $tag, $objectId, $objectClass)
    {
        return $this->taggedObjectRepo->findOneTaggedObjectByTagAndObject($tag, $objectId, $objectClass);
    }
}
