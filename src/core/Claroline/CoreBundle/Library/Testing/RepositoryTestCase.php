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
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Message;
use Claroline\CoreBundle\Entity\UserMessage;
use Claroline\CoreBundle\Entity\Plugin;
use Claroline\CoreBundle\Entity\Log\Log;

/**
 * Base test case for repository testing. Provides fixture methods intended to be
 * called during the test case class set up, so that all the writing operations are
 * done once per test case. Created objects are stored in a internal collection
 * and can be retrieved in every test using a getter.
 */
abstract class RepositoryTestCase extends WebTestCase
{
    private static $om;
    private static $client;
    private static $references;
    private static $time;

    public static function setUpBeforeClass()
    {
        self::$client = static::createClient();
        self::$om = self::$client->getContainer()->get('claroline.persistence.object_manager');
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
        return self::$om->getRepository($entityClass);
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
        $user->setMail($name . '@claroline.net');
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

    protected static function createDisplayableWorkspace($name, $selfRegistration)
    {
        $workspace = new SimpleWorkspace();
        $workspace->setName($name);
        $workspace->setCode($name . 'Code');
        $workspace->setDisplayable(true);
        $workspace->setSelfRegistration($selfRegistration);
        $workspace->setGuid(self::$client->getContainer()->get('claroline.utilities.misc')->generateGuid());
        self::create($name, $workspace);
    }

    protected static function createResourceType($name, $isExportable = true, Plugin $plugin = null)
    {
        $type = new ResourceType();
        $type->setName($name);
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
        if ($parent) {
            $parent = $parent->getResourceNode();
        }

        $directory = self::prepareResource(
            new Directory(),
            $type,
            $creator,
            $workspace,
            $name,
            'directory/mime',
            $parent
        );
        self::create($name, $directory);
    }

    protected static function createFile($name, ResourceType $type, User $creator, Directory $parent)
    {
        $file = self::prepareResource(
            new File(),
            $type,
            $creator,
            $parent->getResourceNode()->getWorkspace(),
            $name,
            'file/mime',
            $parent->getResourceNode()
        );
        $file->setSize(123);
        $file->setHashName($name);
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
        $text = self::prepareResource(
            new Text(),
            $type,
            $creator,
            $parent->getResourceNode()->getWorkspace(),
            $name,
            'text/mime',
            $parent->getResourceNode()
        );
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
        $shortcut = self::prepareResource(
            new ResourceShortcut(),
            $type, $creator,
            $parent->getResourceNode()->getWorkspace(),
            $name,
            'shortcut/mime',
            $parent->getResourceNode()
        );
        $shortcut->setTarget($target->getResourceNode());
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
        $activity = self::prepareResource(
            new Activity(),
            $type,
            $creator,
            $parent->getResourceNode()->getWorkspace(),
            $name,
            'mime/activity',
            $parent->getResourceNode()
        );
        $activity->setName($name);
        $activity->setInstructions('Some instructions...');
        self::$om->startFlushSuite();

        for ($i = 0, $count = count($resources); $i < $count; ++$i) {
            $activityResource = new ResourceActivity();
            $activityResource->setActivity($activity);
            $activityResource->setResourceNode($resources[$i]->getResourceNode());
            $activityResource->setSequenceOrder($i);
            self::create(
                'activityResource/' . $name . '-' . $resources[$i]->getName(),
                $activityResource
            );
            $activity->addResourceActivity($activityResource);
        }

        self::create($name, $activity);
        self::$om->endFlushSuite();
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
        $rights->setResourceNode($resource->getResourceNode());

        foreach ($allowedActions as $action) {
            $method = 'setCan' . ucfirst($action);
            $rights->{$method}(true);
        }

        foreach ($creatableResourceTypes as $type) {
            $rights->addCreatableResourceType($type);
        }

        self::create("resource_right/{$role->getName()}-{$resource->getResourceNode()->getName()}", $rights);
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
        $message->setTo('x1;x2;x3');

        if ($parent) {
            $message->setParent($parent);
        }

        self::$om->startFlushSuite();
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

        self::$om->endFlushSuite();
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

        self::$om->persist($log);
        self::$om->flush();
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

        self::$om->persist($tagRelation);
        self::$om->flush();
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

        self::$om->persist($tagHierarchy);
        self::$om->flush();
    }

    /**
     * Sets the common properties of a resource.
     *
     * @param AbstractResource  $resource
     * @param ResourceType      $type
     * @param User              $creator
     * @param AbstractWorkspace $workspace
     * @param ResourceNode      $parent
     *
     * @return AbstractResource
     */
    private static function prepareResource(
        AbstractResource $resource,
        ResourceType $type,
        User $creator,
        AbstractWorkspace $workspace,
        $name,
        $mimeType,
        $parent = null
    )
    {

        $node = new ResourceNode();
        $node->setResourceType($type);
        $node->setCreator($creator);
        $node->setWorkspace($workspace);
        $node->setCreationDate(self::$time);
        $node->setClass('resourceClass');
        $node->setName($name);
        $node->setMimeType($mimeType);

        if ($parent) {
            $node->setParent($parent);
        }

        self::$om->persist($node);
        $resource->setResourceNode($node);

        return $resource;
    }

    /**
     * Disables the timestamp listener so that fixture methods are forced to set
     * dates explicitely.
     */
    private static function disableTimestampableListener()
    {
        $eventManager = self::$om->getEventManager();

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
        self::$om->persist($entity);
        self::$om->flush();
        self::set($reference, $entity);
    }
}
