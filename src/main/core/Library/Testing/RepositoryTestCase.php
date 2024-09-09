<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Testing;

use Claroline\CoreBundle\Entity\Plugin;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceRights;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Tool\ToolRights;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\Persistence\ObjectRepository;
use Gedmo\Timestampable\TimestampableListener;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Base test case for repository testing. Provides fixture methods intended to be
 * called during the test case class set up, so that all the writing operations are
 * done once per test case. Created objects are stored in a internal collection
 * and can be retrieved in every test using a getter.
 */
abstract class RepositoryTestCase extends WebTestCase
{
    private static $om;
    public static $client;
    private static $references;
    private static $time;
    private static $persister;
    private static $nodeIdx = 1;

    public static function setUpBeforeClass(): void
    {
        self::ensureKernelShutdown();
        self::$client = static::createClient();
        self::$om = self::$client->getContainer()->get('Claroline\AppBundle\Persistence\ObjectManager');
        self::$persister = self::$client->getContainer()->get('claroline.library.testing.persister');
        self::$references = [];
        self::$time = new \DateTime();
        self::$om->beginTransaction();
        self::disableTimestampableListener();
    }

    public function tearDown(): void
    {
        // we don't want to tear down between each tests because we lose the container otherwise
        // and can't shut down everything properly afterwards
    }

    public static function tearDownAfterClass(): void
    {
        self::$om->rollback();
    }

    /**
     * Returns the repository associated to an entity class.
     */
    protected static function getRepository(string $entityClass): ObjectRepository
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
     * @param int $seconds
     */
    protected static function sleep($seconds): void
    {
        self::$time->add(new \DateInterval("PT{$seconds}S"));
    }

    protected static function createUser($name, array $roles = [], Workspace $personalWorkspace = null): void
    {
        $user = self::$persister->user($name);

        foreach ($roles as $role) {
            $user->addRole($role);
        }

        if ($personalWorkspace) {
            $user->setPersonalWorkspace($personalWorkspace);
        }

        self::create($name, $user);
    }

    protected static function createGroup($name, array $users = [], array $roles = []): void
    {
        $group = self::$persister->group($name);

        foreach ($users as $user) {
            $group->addUser($user);
        }

        foreach ($roles as $role) {
            $group->addRole($role);
        }

        self::create($name, $group);
    }

    protected static function createRole($name, Workspace $workspace = null): void
    {
        $role = new Role();
        $role->setName($name);
        $role->setTranslationKey($name);
        $role->setType(Role::PLATFORM_ROLE);

        if ($workspace) {
            $role->setType(Role::WS_ROLE);
            $role->setWorkspace($workspace);
        }

        $exists = self::$om->getRepository(Role::class)->findOneByName($name);
        if ($exists) {
            self::set($name, $exists);
        } else {
            self::create($name, $role);
        }
    }

    protected static function createWorkspace($name): void
    {
        $workspace = new Workspace();
        $workspace->setName($name);
        $workspace->setCode($name.'Code');
        $workspace->setHidden(true);

        self::create($name, $workspace);
    }

    protected static function createDisplayableWorkspace($name, $selfRegistration): void
    {
        $workspace = new Workspace();
        $workspace->setName($name);
        $workspace->setCode($name.'Code');
        $workspace->setHidden(false);
        $workspace->setSelfRegistration($selfRegistration);

        self::create($name, $workspace);
    }

    protected static function createResourceType($name, $class, $isExportable = true, Plugin $plugin = null): void
    {
        $type = new ResourceType();
        $type->setName($name);
        $type->setClass($class);
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
        Workspace $workspace,
        Directory $parent = null
    ): void {
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

    protected static function createFile($name, ResourceType $type, User $creator, Directory $parent): void
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

    protected static function createResourceRights(
        Role $role,
        AbstractResource $resource,
        $mask,
        array $creatableResourceTypes = []
    ): void {
        $rights = new ResourceRights();
        $rights->setRole($role);
        $rights->setResourceNode($resource->getResourceNode());
        $rights->setMask($mask);

        foreach ($creatableResourceTypes as $type) {
            $rights->addCreatableResourceType($type);
        }

        self::create("resource_right/{$role->getName()}-{$resource->getResourceNode()->getName()}", $rights);
    }

    protected static function createWorkspaceTool(
        string $toolName,
        Workspace $workspace,
        array $roles,
        $position
    ): void {
        $orderedTool = new OrderedTool();
        $orderedTool->setName($toolName);
        $orderedTool->setContextName('workspace');
        $orderedTool->setContextId($workspace->getUuid());
        $orderedTool->setOrder($position);

        foreach ($roles as $role) {
            $rights = new ToolRights();
            $rights->setMask(63);
            $rights->setRole($role);
            $rights->setOrderedTool($orderedTool);
            self::$om->persist($rights);
        }

        self::create("orderedTool/{$workspace->getName()}-{$toolName}", $orderedTool);
    }

    protected static function createPlugin($vendor, $bundle): void
    {
        $plugin = new Plugin();
        $plugin->setVendorName($vendor);
        $plugin->setBundleName($bundle);
        self::create($vendor.$bundle, $plugin);
    }

    /**
     * Sets the common properties of a resource.
     */
    private static function prepareResource(
        AbstractResource $resource,
        ResourceType $type,
        User $creator,
        Workspace $workspace,
        $name,
        $mimeType,
        $parent = null
    ): AbstractResource {
        $node = new ResourceNode();
        $node->setResourceType($type);
        $node->setCreator($creator);
        $node->setWorkspace($workspace);
        $node->setCreationDate(self::$time);
        $node->setName($name);
        $node->setCode($name);
        $node->setMimeType($mimeType);
        $node->setUuid(uniqid());
        $node->setIndex(self::$nodeIdx);

        if ($parent) {
            $node->setParent($parent);
        }

        ++self::$nodeIdx;
        self::$om->persist($node);
        $resource->setResourceNode($node);

        return $resource;
    }

    /**
     * Disables the timestamp listener so that fixture methods are forced to set
     * dates explicitly.
     */
    private static function disableTimestampableListener(): void
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
    private static function set($reference, $entity): void
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
    private static function create($reference, $entity): void
    {
        self::$om->persist($entity);
        self::$om->flush();
        self::set($reference, $entity);
    }
}
