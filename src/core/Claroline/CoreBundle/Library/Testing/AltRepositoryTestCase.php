<?php

namespace Claroline\CoreBundle\Library\Testing;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace;
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

abstract class AltRepositoryTestCase extends WebTestCase
{
    protected static $writer;
    private static $client;
    private static $em;
    private static $references;

    public static function setUpBeforeClass()
    {
        self::$client = static::createClient();
        self::$em = self::$client->getContainer()->get('doctrine.orm.entity_manager');
        self::$writer = self::$client->getContainer()->get('claroline.database.writer');
        self::$references = array();
        self::$client->beginTransaction();
    }

    public static function tearDownAfterClass()
    {
        self::$client->shutdown();
    }

    protected static function getRepository($entityClass)
    {
        return self::$em->getRepository($entityClass);
    }

    protected static function get($reference)
    {
        if (isset(self::$references[$reference])) {
            return self::$references[$reference];
        }

        throw new \Exception("Unknown fixture reference '{$reference}'");
    }

    protected static function createUser($name, array $roles = array())
    {
        $user = new User();
        $user->setFirstName($name . 'FirstName');
        $user->setLastName($name . 'LastName');
        $user->setUsername($name . 'Username');
        $user->setPlainPassword($name . 'Password');

        foreach ($roles as $role) {
            $user->addRole($role);
        }

        self::create($name, $user);
    }

    protected static function createGroup($name, array $roles = array())
    {
        $group = new Group();
        $group->setName($name);

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
        $directory = new Directory();
        $directory->setName($name);
        $directory->setCreator($creator);
        $directory->setWorkspace($workspace);
        $directory->setResourceType($type);
        $directory->setMimeType('directory/mime');

        if ($parent) {
            $directory->setParent($parent);
        }

        self::create($name, $directory);
    }

    protected static function createFile($name, ResourceType $type, User $creator, Directory $parent)
    {
        $file = new File();
        $file->setName($name);
        $file->setCreator($creator);
        $file->setWorkspace($parent->getWorkspace());
        $file->setParent($parent);
        $file->setSize(123);
        $file->setHashName($name);
        $file->setResourceType($type);
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
        $text = new Text();
        $text->setName($name);
        $text->setResourceType($type);
        $text->setCreator($creator);
        $text->setWorkspace($parent->getWorkspace());
        $text->setParent($parent);
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
        $shortcut = new ResourceShortcut();
        $shortcut->setName($name);
        $shortcut->setCreator($creator);
        $shortcut->setWorkspace($parent->getWorkspace());
        $shortcut->setParent($parent);
        $shortcut->setResource($target);
        $shortcut->setResourceType($type);
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
        $message->setTo('some receiver string');
        $message->setReceiverString('some receiver string');

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

    private static function set($reference, $entity)
    {
        if (isset(self::$references[$reference])) {
            throw new \Exception("Fixture reference '{$reference}' is already set");
        }

        self::$references[$reference] = $entity;
    }

    private static function create($reference, $entity)
    {
        self::$writer->create($entity);
        self::set($reference, $entity);
    }
}