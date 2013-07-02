<?php

namespace Claroline\CoreBundle\Library\Testing;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\ResourceRights;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Entity\Message;
use Claroline\CoreBundle\Entity\UserMessage;

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

    protected static function createRole($name, AbstractWorkspace $workspace = null)
    {
        $role = new Role();
        $role->setName($name);
        $role->setTranslationKey($name);

        if ($workspace) {
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

    protected static function createResourceType($name)
    {
        $type = new ResourceType();
        $type->setName($name);
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

        if ($parent) {
            $directory->setParent($parent);
        }

        self::create($name, $directory);
    }

    protected static function createFile($name, ResourceType $type, User $creator,  Directory $parent)
    {
        $file = new File();
        $file->setName($name);
        $file->setCreator($creator);
        $file->setWorkspace($parent->getWorkspace());
        $file->setParent($parent);
        $file->setSize(123);
        $file->setHashName($name);
        $file->setResourceType($type);
        self::create($name, $file);
    }

    protected static function createResourceRights(
        Role $role,
        AbstractResource $resource,
        array $allowedActions = array()
    )
    {
        $rights = new ResourceRights();
        $rights->setRole($role);
        $rights->setResource($resource);

        foreach ($allowedActions as $action) {
            $method = 'setCan' . ucfirst($action);
            $rights->{$method}(true);
        }

        self::create("{resource_right/{$role->getName()}-{$resource->getName()}" , $rights);
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