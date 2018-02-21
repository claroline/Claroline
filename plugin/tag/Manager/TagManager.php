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
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Pager\PagerFactory;
use Claroline\TagBundle\Entity\ResourcesTagsWidgetConfig;
use Claroline\TagBundle\Entity\Tag;
use Claroline\TagBundle\Entity\TaggedObject;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.tag_manager")
 */
class TagManager
{
    private $om;
    private $pagerFactory;
    private $resWidgetConfigRepo;
    private $taggedObjectRepo;
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
        $this->resWidgetConfigRepo =
            $om->getRepository('ClarolineTagBundle:ResourcesTagsWidgetConfig');
        $this->taggedObjectRepo = $om->getRepository('ClarolineTagBundle:TaggedObject');
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

    public function tagExists($name)
    {
        $tag = $this->getOneTagByName($name);

        return !is_null($tag);
    }

    public function getOrCreatePlatformTag($name)
    {
        $tag = $this->getUowScheduledTag($name);
        if (empty($tag)) {
            $tag = $this->getOnePlatformTagByName($name);
        }

        if (empty($tag)) {
            $tag = new Tag();
            $tag->setName($name);
            $this->persistTag($tag);
        }

        return $tag;
    }

    public function getOrCreateUserTag(User $user, $name)
    {
        $tag = $this->getUowScheduledTag($name, $user);
        if (empty($tag)) {
            $tag = $this->getOneUserTagByName($user, $name);
        }

        if (empty($tag)) {
            $tag = new Tag();
            $tag->setUser($user);
            $tag->setName($name);
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
            $this->om->startFlushSuite();
            $objectId = $object->getId();
            $objectClass = str_replace('Proxies\\__CG__\\', '', get_class($object));
            $tagsList = [];

            foreach ($uniqueTags as $tagName) {
                $tag = is_null($user) ? $this->getOrCreatePlatformTag($tagName) : $this->getOrCreateUserTag($user, $tagName);
                $tagsList[$tagName] = $tag;
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
                    $this->persistTaggedObject($taggedObject);
                    $taggedObjects[] = $taggedObject;
                }
            }
            $this->om->endFlushSuite();
        }

        return $taggedObjects;
    }

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
            $tag = is_null($user) ? $this->getOrCreatePlatformTag($tagName) : $this->getOrCreateUserTag($user, $tagName);
            $tagsList[$tagName] = $tag;
        }
        $this->om->startFlushSuite();

        foreach ($data as $objectData) {
            $objectId = $objectData['id'];
            $objectClass = $objectData['class'];
            $objectName = isset($objectData['name']) ? $objectData['name'] : null;

            if ($replace) {
                $this->removeTaggedObjectsByClassAndIds($objectClass, [$objectId]);
            }

            foreach ($uniqueTags as $tagName) {
                $tag = $tagsList[$tagName];

                $taggedObject = $replace ?
                    null :
                    $this->getOneTaggedObjectByTagAndObject($tag, $objectId, $objectClass);

                if (is_null($taggedObject)) {
                    $taggedObject = new TaggedObject();
                    $taggedObject->setTag($tag);
                    $taggedObject->setObjectId($objectId);
                    $taggedObject->setObjectClass($objectClass);

                    if ($objectName) {
                        $taggedObject->setObjectName($objectName);
                    }
                    $this->persistTaggedObject($taggedObject);
                    $taggedObjects[] = $taggedObject;
                }
            }
        }
        $this->om->endFlushSuite();

        return $taggedObjects;
    }

    public function getObjectsByClassAndIds($class, array $ids, $orderedBy = 'id', $order = 'ASC')
    {
        $objects = [];

        if (count($ids) > 0) {
            $objects = $this->taggedObjectRepo->findObjectsByClassAndIds($class, $ids, $orderedBy, $order);
        }

        return $objects;
    }

    public function getTaggedWorkspacesByRoles(User $user, $tag, $orderedBy = 'id', $order = 'ASC')
    {
        $roles = $user->getEntityRoles();

        return count($roles) > 0 ? $this->taggedObjectRepo->findTaggedWorkspacesByRoles($tag, $roles, $orderedBy, $order) : [];
    }

    public function getTaggedWorkspaces($tag)
    {
        return $this->taggedObjectRepo->findTaggedWorkspaces($tag);
    }

    public function removeTaggedObjectsByResourceAndTag(ResourceNode $resourceNode, Tag $tag)
    {
        $taggedObject = $this->getOneTaggedObjectByTagAndObject(
                $tag, $resourceNode->getId(),
                str_replace('Proxies\\__CG__\\', '', get_class($resourceNode)));
        $this->deleteTaggedObject($taggedObject);
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

    public function getResourcesTagsWidgetConfig(WidgetInstance $widgetInstance)
    {
        $config = $this->resWidgetConfigRepo->findOneByWidgetInstance($widgetInstance);

        if (is_null($config)) {
            $config = new ResourcesTagsWidgetConfig();
            $config->setWidgetInstance($widgetInstance);
            $details = ['nb_tags' => 10];
            $config->setDetails($details);
            $this->persistResourcesTagsWidgetConfig($config);
        }

        return $config;
    }

    public function persistResourcesTagsWidgetConfig(ResourcesTagsWidgetConfig $config)
    {
        $this->om->persist($config);
        $this->om->flush();
    }

    public function removeTaggedObjectByTagNameAndObjectIdAndClass($tagName, $objectId, $objectClass)
    {
        $taggedObjects = $this->getOneTaggedObjectByTagNameAndObject($tagName, $objectId, $objectClass);
        $this->om->startFlushSuite();

        foreach ($taggedObjects as $to) {
            $this->deleteTaggedObject($to);
        }
        $this->om->endFlushSuite();
    }

    /***********************************
     * Access to TagRepository methods *
     ***********************************/

    public function getTags(
        User $user = null,
        $search = '',
        $withPlatform = false,
        $orderedBy = 'name',
        $order = 'ASC',
        $withPager = false,
        $page = 1,
        $max = 50,
        $strictSearch = false
    ) {
        $tags = is_null($user) ?
            $this->getPlatformTags($search, $orderedBy, $order, $withPager, $page, $max, $strictSearch) :
            $this->getUserTags($user, $search, $withPlatform, $orderedBy, $order, $withPager, $page, $max, $strictSearch);

        return $tags;
    }

    public function getPlatformTags(
        $search = '',
        $orderedBy = 'name',
        $order = 'ASC',
        $withPager = false,
        $page = 1,
        $max = 50,
        $strictSearch = false
    ) {
        $tags = empty($search) ?
            $this->tagRepo->findAllPlatformTags($orderedBy, $order) :
            $this->tagRepo->findSearchedPlatformTags($search, $orderedBy, $order, $strictSearch);

        return $withPager ? $this->pagerFactory->createPagerFromArray($tags, $page, $max) : $tags;
    }

    public function getUserTags(
        User $user,
        $search = '',
        $withPlatform = false,
        $orderedBy = 'name',
        $order = 'ASC',
        $withPager = false,
        $page = 1,
        $max = 50,
        $strictSearch = false
    ) {
        $tags = empty($search) ?
            $this->tagRepo->findAllUserTags($user, $withPlatform, $orderedBy, $order) :
            $this->tagRepo->findSearchedUserTags($user, $search, $withPlatform, $orderedBy, $order, $strictSearch);

        return $withPager ? $this->pagerFactory->createPagerFromArray($tags, $page, $max) : $tags;
    }

    public function getOnePlatformTagByName($name)
    {
        return $this->tagRepo->findOnePlatformTagByName($name);
    }

    public function getOneUserTagByName(User $user, $name)
    {
        return $this->tagRepo->findOneUserTagByName($user, $name);
    }

    public function getTagsByObject($object, User $user = null, $withPlatform = false, $orderedBy = 'name', $order = 'ASC')
    {
        return $this->tagRepo->findTagsByObject($object, $user, $withPlatform, $orderedBy, $order);
    }

    /******************************************
     * Access to TaggedObjectRepository methods *
     ******************************************/

    public function getTaggedObjects(
        User $user = null,
        $withPlatform = false,
        $class = null,
        $search = '',
        $strictSearch = false,
        $orderedBy = 'name',
        $order = 'ASC',
        $withPager = false,
        $page = 1,
        $max = 50,
        array $ids = []
    ) {
        $objects = empty($search) ?
            $this->taggedObjectRepo->findAllTaggedObjects(
                $user,
                $withPlatform,
                $class,
                $orderedBy,
                $order,
                $ids
            ) :
            $this->taggedObjectRepo->findSearchedTaggedObjects(
                $search,
                $user,
                $withPlatform,
                $class,
                $orderedBy,
                $order,
                $strictSearch,
                $ids
            );

        return $withPager ? $this->pagerFactory->createPagerFromArray($objects, $page, $max) : $objects;
    }

    public function getOneTaggedObjectByTagAndObject(Tag $tag, $objectId, $objectClass)
    {
        return $this->taggedObjectRepo->findOneTaggedObjectByTagAndObject($tag, $objectId, $objectClass);
    }

    public function getOneTaggedObjectByTagNameAndObject($tagName, $objectId, $objectClass)
    {
        return $this->taggedObjectRepo->findOneTaggedObjectByTagNameAndObject($tagName, $objectId, $objectClass);
    }

    public function getTaggedObjectsByTags(array $tags, $orderedBy = 'name', $order = 'ASC', $withPager = false, $page = 1, $max = 50)
    {
        $objects = count($tags) > 0 ? $this->taggedObjectRepo->findTaggedObjectsByTags($tags, $orderedBy, $order) : [];

        return $withPager ? $this->pagerFactory->createPagerFromArray($objects, $page, $max) : $objects;
    }

    public function getTaggedResourcesByWorkspace(Workspace $workspace, $user = 'anon.', array $roleNames = ['ROLE_ANONYMOUS'])
    {
        return $this->taggedObjectRepo->findTaggedResourcesByWorkspace($workspace, $user, $roleNames);
    }

    public function getTaggedResourcesByRoles($user = 'anon.', array $roleNames = ['ROLE_ANONYMOUS'])
    {
        return $this->taggedObjectRepo->findTaggedResourcesByRoles($user, $roleNames);
    }
}
