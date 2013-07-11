<?php

namespace Claroline\CoreBundle\Library\Testing;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Gedmo\Timestampable\TimestampableListener;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceTag;
use Claroline\CoreBundle\Entity\Workspace\RelWorkspaceTag;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceTagHierarchy;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\ResourceRights;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Entity\Resource\ResourceShortcut;
use Claroline\CoreBundle\Entity\Resource\Activity;
use Claroline\CoreBundle\Entity\Resource\ResourceActivity;
use Claroline\CoreBundle\Entity\Resource\Text;
use Claroline\CoreBundle\Entity\Resource\Revision;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Message;
use Claroline\CoreBundle\Entity\UserMessage;
use Claroline\CoreBundle\Entity\Plugin;
use Claroline\CoreBundle\Entity\Logger\Log;

/**
 * Base test case for repository testing. Provides fixture methods intended to be
 * called during the test case class set up, so that all the writing operations are
 * done once per test case. Created objects are stored in a internal collection
 * and can be retrieved in every test using a getter.
 */
abstract class RepositoryTestCase extends WebTestCase
{
    protected static $writer;
    private static $client;
    private static $em;
    private static $references;
    private static $time;

    public static function setUpBeforeClass()
    {
        self::$client = static::createClient();
        self::$em = self::$client->getContainer()->get('doctrine.orm.entity_manager');
        self::$writer = self::$client->getContainer()->get('claroline.database.writer');
        self::$references = array();
        self::$time = new \DateTime();
        self::$client->beginTransaction();
        self::disableTimestampableListener();
    }

    public static function tearDownAfterClass()
    {
        self::$client->shutdown();
    }

    /**
     * Returns the repository associated to an entity class.
     *
     * @param string $entityClass
     *
     * @return EntityRepository
     */
    protected static function getRepository($entityClass)
    {
        return self::$em->getRepository($entityClass);
    }

    /**
     * Returns a reference previously stored by a fixture method.
     *
     * @param string $reference
     *
     * @return object
     *
     * @throws \InvalidArgumentException if the reference is not present in the collection
     */
    protected static function get($reference)
    {
        if (isset(self::$references[$reference])) {
            return self::$references[$reference];
        }

        throw new \InvalidArgumentException("Unknown fixture reference '{$reference}'");
    }

    /**
     * Returns the internal time of the test case. All the fixture methods dealing
     * with dates refer to that time. Default time is the set up time but it may be
     * changed with calls to the "sleep" method.
     *
     * @param string $format
     *
     * @return string|DateTime
     */
    protected static function getTime($format = 'Y-m-d H:i:s')
    {
        if ($format) {
            return self::$time->format($format);
        }

        return self::$time;
    }

    /**
     * Increases the test case internal time by a number of seconds.
     *
     * @param integer $seconds
     */
    protected static function sleep($seconds)
    {
        self::$time->add(new \DateInterval("PT{$seconds}S"));
    }

    protected static function createUser($name, array $roles = array(), AbstractWorkspace $personalWorkspace = null)
    {
        $user = new User();
        $user->setFirstName($name . 'FirstName');
        $user->setLastName($name . 'LastName');
        $user->setUsername($name . 'Username');
        $user->setPlainPassword($name . 'Password');
        $user->setCreationDate(self::$time);

        foreach ($roles as $role) {
            $user->addRole($role);
        }

        if ($personalWorkspace) {
            $user->setPersonalWorkspace($personalWorkspace);
        }

        self::create($name, $user);
    }

    protected static function createGroup($name, array $users = array(), array $roles = array())
    {
        $group = new Group();
        $group->setName($name);

        foreach ($users as $user) {
            $group->addUser($user);
        }

        foreach ($roles as $role) {
            $group->addRole($role);
        }

        self::create($name, $group);
    }

    protected static function createRole($name, AbstractWorkspace $workspace = null)
    {
        $role = new Role();
        $role->setName($name);
        $role->setTranslationKey($name);
        $role->setType(Role::PLATFORM_ROLE);

        if ($workspace) {
            $role->setType(Role::WS_ROLE);
            $role->setWorkspace($workspace);
        }

        self::create($name, $role);
    }

    protected static function createWorkspace($name)
    {
        $workspace = new SimpleWorkspace();
        $workspace->setName($name);
        $workspace->setCode($name . 'Code');
        $workspace->setGuid(self::$client->getContainer()->get('claroline.utilities.misc')->generateGuid());
        self::create($name, $workspace);
    }

    protected static function createResourceType($name, $isExportable = true, Plugin $plugin = null)
    {
        $type = new ResourceType();
        $type->setName($name);
        $type->setClass($name . 'Class');
        $type->setExportable($isExportable);

        if ($plugin) {
            $type->setPlugin($plugin);
        }

        self::create($name, $type);
    }

    protected static function createDirectory(
        $name,
        ResourceType $type,
        User $creator,
        AbstractWorkspace $workspace,
        Directory $parent = null
    )
    {
        $directory = self::prepareResource(new Directory(), $type, $creator, $workspace, $parent);
        $directory->setName($name);
        $directory->setMimeType('directory/mime');
        self::create($name, $directory);
    }

    protected static function createFile($name, ResourceType $type, User $creator, Directory $parent)
    {
        $file = self::prepareResource(new File(), $type, $creator, $parent->getWorkspace(), $parent);
        $file->setName($name);
        $file->setSize(123);
        $file->setHashName($name);
        $file->setMimeType('file/mime');
        self::create($name, $file);
    }

    protected static function createText(
        $name,
        $revisionNumber,
        ResourceType $type,
        User $creator,
        Directory $parent
    )
    {
        $text = self::prepareResource(new Text(), $type, $creator, $parent->getWorkspace(), $parent);
        $text->setName($name);
        self::create($name, $text);

        $revision = new Revision();
        $revision->setVersion($revisionNumber);
        $revision->setContent($name . 'Content');
        $revision->setText($text);
        self::create("revision/{$text->getName()}-{$revisionNumber}", $revision);
    }

    protected static function createShortcut(
        $name,
        ResourceType $type,
        AbstractResource $target,
        User $creator,
        Directory $parent
    )
    {
        $shortcut = self::prepareResource(new ResourceShortcut(), $type, $creator, $parent->getWorkspace(), $parent);
        $shortcut->setName($name);
        $shortcut->setResource($target);
        $shortcut->setMimeType('shortcut/mime');
        self::create($name, $shortcut);
    }

    protected static function createActivity(
        $name,
        ResourceType $type,
        User $creator,
        array $resources,
        Directory $parent
    )
    {
        $activity = new Activity();
        $activity->setName($name);
        $activity->setInstructions('Some instructions...');
        $activity->setResourceType($type);
        $activity->setCreator($creator);
        $activity->setWorkspace($parent->getWorkspace());
        $activity->setParent($parent);
        self::$writer->suspendFlush();

        for ($i = 0, $count = count($resources); $i < $count; ++$i) {
            $activityResource = new ResourceActivity();
            $activityResource->setActivity($activity);
            $activityResource->setResource($resources[$i]);
            $activityResource->setSequenceOrder($i);
            self::create(
                'activityResource/' . $name . '-' . $resources[$i]->getName(),
                $activityResource
            );
            $activity->addResourceActivity($activityResource);
        }


        $activity->setCreationDate(self::$time);


        self::create($name, $activity);
        self::$writer->forceFlush();
    }

    protected static function createResourceRights(
        Role $role,
        AbstractResource $resource,
        array $allowedActions = array(),
        array $creatableResourceTypes = array()
    )
    {
        $rights = new ResourceRights();
        $rights->setRole($role);
        $rights->setResource($resource);

        foreach ($allowedActions as $action) {
            $method = 'setCan' . ucfirst($action);
            $rights->{$method}(true);
        }

        foreach ($creatableResourceTypes as $type) {
            $rights->addCreatableResourceType($type);
        }

        self::create("resource_right/{$role->getName()}-{$resource->getName()}" , $rights);
    }

    protected static function createTool($name)
    {
        $tool = new Tool();
        $tool->setName($name);
        $tool->setDisplayName($name);
        $tool->setClass($name . 'Class');
        self::create($name, $tool);
    }

    protected static function createWorkspaceTool(
        Tool $tool,
        AbstractWorkspace $workspace,
        array $roles,
        $position
    )
    {
        $orderedTool = new OrderedTool();
        $orderedTool->setName($tool->getName());
        $orderedTool->setTool($tool);
        $orderedTool->setWorkspace($workspace);
        $orderedTool->setOrder($position);

        foreach ($roles as $role) {
            $orderedTool->addRole($role);
        }

        self::create("orderedTool/{$workspace->getName()}-{$tool->getName()}", $orderedTool);
    }

    protected static function createDesktopTool(
        Tool $tool,
        User $user,
        $position
    )
    {
        $orderedTool = new OrderedTool();
        $orderedTool->setName($tool->getName());
        $orderedTool->setTool($tool);
        $orderedTool->setUser($user);
        $orderedTool->setOrder($position);

        self::create("orderedTool/{$user->getUsername()}-{$tool->getName()}", $orderedTool);
    }

    protected static function createMessage(
        $alias,
        User $sender,
        array $receivers,
        $object,
        $content,
        Message $parent = null,
        $removed = false
    )
    {
        $message = new Message();
        $message->setSender($sender);
        $message->setObject($object);
        $message->setContent($content);
        $message->setDate(self::$time);

        if ($parent) {
            $message->setParent($parent);
        }

        self::$writer->suspendFlush();
        self::create($alias, $message);

        $userMessage = new UserMessage();
        $userMessage->setIsSent(true);
        $userMessage->setUser($sender);
        $userMessage->setMessage($message);

        if ($removed) {
            $userMessage->markAsRemoved($removed);
        }

        self::create($alias . '/' . $sender->getUsername(), $userMessage);

        foreach ($receivers as $receiver) {
            $userMessage = new UserMessage();
            $userMessage->setUser($receiver);
            $userMessage->setMessage($message);
            self::create($alias . '/' . $receiver->getUsername(), $userMessage);
        }

        self::$writer->forceFlush();
    }

    protected static function createPlugin($vendor, $bundle)
    {
        $plugin = new Plugin();
        $plugin->setVendorName($vendor);
        $plugin->setBundleName($bundle);
        $plugin->setHasOptions(false);
        $plugin->setIcon('default');
        self::create($vendor . $bundle, $plugin);
    }

    protected static function createLog(User $doer, $action, AbstractWorkspace $workspace = null)
    {
        $log = new Log();
        $log->setDoer($doer);
        $log->setAction($action);
        $log->setDoerType(Log::doerTypeUser);
        $log->setDateLog(self::$time);

        if ($workspace) {
            $log->setWorkspace($workspace);
        }

        self::$writer->create($log);
    }

    protected static function createWorkspaceTag($name, User $user = null)
    {
        $tag = new WorkspaceTag();
        $tag->setName($name);
        $tag->setUser($user);

        self::create($name, $tag);
    }

    protected static function createWorkspaceTagRelation(WorkspaceTag $tag, AbstractWorkspace $workspace)
    {
        $tagRelation = new RelWorkspaceTag();
        $tagRelation->setTag($tag);
        $tagRelation->setWorkspace($workspace);

        self::$writer->create($tagRelation);
    }

    protected static function createWorkspaceTagHierarchy(
        WorkspaceTag $parent,
        WorkspaceTag $child,
        $level,
        User $user = null
    )
    {
        $tagHierarchy = new WorkspaceTagHierarchy();
        $tagHierarchy->setParent($parent);
        $tagHierarchy->setTag($child);
        $tagHierarchy->setLevel($level);
        $tagHierarchy->setUser($user);

        self::$writer->create($tagHierarchy);
    }

    /**
     * Sets the common properties of a resource.
     *
     * @param AbstractResource $resource
     * @param ResourceType $type
     * @param User $creator
     * @param AbstractWorkspace $workspace
     * @param Directory $parent
     *
     * @return AbstractResource
     */
    private static function prepareResource(
        AbstractResource $resource,
        ResourceType $type,
        User $creator,
        AbstractWorkspace $workspace,
        $parent = null
    )
    {
        $resource->setResourceType($type);
        $resource->setCreator($creator);
        $resource->setWorkspace($workspace);
        $resource->setCreationDate(self::$time);

        if ($parent) {
            $resource->setParent($parent);
        }

        return $resource;
    }

    /**
     * Disables the timestamp listener so that fixture methods are forced to set
     * dates explicitely.
     */
    private static function disableTimestampableListener()
    {
        $eventManager = self::$em->getConnection()->getEventManager();

        foreach ($eventManager->getListeners() as $listeners) {
            foreach ($listeners as $listener) {
                if ($listener instanceof TimestampableListener) {
                    $eventManager->removeEventSubscriber($listener);
                }
            }
        }
    }

    /**
     * Stores an entity in the reference collection.
     *
     * @param string $reference
     * @param object $entity
     *
     * @throws \InvalidArgumentException if the reference is already set
     */
    private static function set($reference, $entity)
    {
        if (isset(self::$references[$reference])) {
            throw new \InvalidArgumentException("Fixture reference '{$reference}' is already set");
        }

        self::$references[$reference] = $entity;
    }

    /**
     * Persists an entity and stores it in the reference collection.
     *
     * @param string $reference
     * @param object $entity
     */
    private static function create($reference, $entity)
    {
        self::$writer->create($entity);
        self::set($reference, $entity);
    }
}