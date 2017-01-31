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

use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Home\HomeTab;
use Claroline\CoreBundle\Entity\Home\HomeTabConfig;
use Claroline\CoreBundle\Entity\Log\Log;
use Claroline\CoreBundle\Entity\Plugin;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceRights;
use Claroline\CoreBundle\Entity\Resource\ResourceShortcut;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Resource\Revision;
use Claroline\CoreBundle\Entity\Resource\Text;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\Tool\ToolRights;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Widget\Widget;
use Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\CoreBundle\Entity\Workspace\RelWorkspaceTag;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceTag;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceTagHierarchy;
use Claroline\MessageBundle\Entity\Message;
use Claroline\MessageBundle\Entity\UserMessage;
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

    public static function setUpBeforeClass()
    {
        self::$client = static::createClient();
        self::$om = self::$client->getContainer()->get('claroline.persistence.object_manager');
        self::$persister = self::$client->getContainer()->get('claroline.library.testing.persister');
        self::$references = [];
        self::$time = new \DateTime();
        self::$client->beginTransaction();
        self::disableTimestampableListener();
    }

    public function tearDown()
    {
        //we don't want to tear down between each tests because we lose the container otherwise
        //and can't shut down everything properly afterwards
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
     * @param int $seconds
     */
    protected static function sleep($seconds)
    {
        self::$time->add(new \DateInterval("PT{$seconds}S"));
    }

    protected static function createUser($name, array $roles = [], Workspace $personalWorkspace = null)
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

    protected static function createGroup($name, array $users = [], array $roles = [])
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

    protected static function createRole($name, Workspace $workspace = null)
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
        $workspace = new Workspace();
        $workspace->setName($name);
        $workspace->setCode($name.'Code');
        $workspace->setGuid(self::$client->getContainer()->get('claroline.utilities.misc')->generateGuid());
        self::create($name, $workspace);
    }

    protected static function createDisplayableWorkspace($name, $selfRegistration)
    {
        $workspace = new Workspace();
        $workspace->setName($name);
        $workspace->setCode($name.'Code');
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
        Workspace $workspace,
        Directory $parent = null
    ) {
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
    ) {
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
        $revision->setContent($name.'Content');
        $revision->setText($text);
        self::create("revision/{$text->getName()}-{$revisionNumber}", $revision);
    }

    protected static function createShortcut(
        $name,
        ResourceType $type,
        AbstractResource $target,
        User $creator,
        Directory $parent
    ) {
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

    protected static function createResourceRights(
        Role $role,
        AbstractResource $resource,
        $mask,
        array $creatableResourceTypes = []
    ) {
        $rights = new ResourceRights();
        $rights->setRole($role);
        $rights->setResourceNode($resource->getResourceNode());
        $rights->setMask($mask);

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
        $tool->setClass($name.'Class');
        self::create($name, $tool);
    }

    protected static function createWorkspaceTool(
        Tool $tool,
        Workspace $workspace,
        array $roles,
        $position
    ) {
        $orderedTool = new OrderedTool();
        $orderedTool->setName($tool->getName());
        $orderedTool->setTool($tool);
        $orderedTool->setWorkspace($workspace);
        $orderedTool->setOrder($position);

        foreach ($roles as $role) {
            $rights = new ToolRights();
            $rights->setMask(63);
            $rights->setRole($role);
            $rights->setOrderedTool($orderedTool);
            self::$om->persist($rights);
        }

        self::create("orderedTool/{$workspace->getName()}-{$tool->getName()}", $orderedTool);
    }

    protected static function createDesktopTool(
        Tool $tool,
        User $user,
        $position
    ) {
        $orderedTool = new OrderedTool();
        $orderedTool->setName($tool->getName());
        $orderedTool->setTool($tool);
        $orderedTool->setUser($user);
        $orderedTool->setOrder($position);

        self::create("orderedTool/{$user->getUsername()}-{$tool->getName()}", $orderedTool);
    }

    protected static function createPlugin($vendor, $bundle)
    {
        $plugin = new Plugin();
        $plugin->setVendorName($vendor);
        $plugin->setBundleName($bundle);
        $plugin->setHasOptions(false);
        self::create($vendor.$bundle, $plugin);
    }

    protected static function createLog(User $doer, $action, Workspace $workspace = null)
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

    protected static function createWorkspaceTagRelation(WorkspaceTag $tag, Workspace $workspace)
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
    ) {
        $tagHierarchy = new WorkspaceTagHierarchy();
        $tagHierarchy->setParent($parent);
        $tagHierarchy->setTag($child);
        $tagHierarchy->setLevel($level);
        $tagHierarchy->setUser($user);

        self::$om->persist($tagHierarchy);
        self::$om->flush();
    }

    protected static function createAdminHomeTab($name, $type)
    {
        $homeTab = new HomeTab();
        $homeTab->setName($name);
        $homeTab->setType($type);

        self::create($name, $homeTab);
        self::$om->flush();
    }

    protected static function createDesktopHomeTab($name, User $user)
    {
        $homeTab = new HomeTab();
        $homeTab->setName($name);
        $homeTab->setType('desktop');
        $homeTab->setUser($user);

        self::create($name, $homeTab);
        self::$om->flush();
    }

    protected static function createWorkspaceHomeTab(
        $name,
        Workspace $workspace
    ) {
        $homeTab = new HomeTab();
        $homeTab->setName($name);
        $homeTab->setType('workspace');
        $homeTab->setWorkspace($workspace);

        self::create($name, $homeTab);
        self::$om->flush();
    }

    protected static function createAdminHomeTabConfig(
        $name,
        HomeTab $homeTab,
        $type,
        $visible,
        $locked,
        $tabOrder
    ) {
        $homeTabConfig = new HomeTabConfig();
        $homeTabConfig->setHomeTab($homeTab);
        $homeTabConfig->setType($type);
        $homeTabConfig->setVisible($visible);
        $homeTabConfig->setLocked($locked);
        $homeTabConfig->setTabOrder($tabOrder);

        self::create($name, $homeTabConfig);
        self::$om->flush();
    }

    protected static function createDesktopHomeTabConfig(
        $name,
        HomeTab $homeTab,
        User $user,
        $type,
        $visible,
        $locked,
        $tabOrder
    ) {
        $homeTabConfig = new HomeTabConfig();
        $homeTabConfig->setHomeTab($homeTab);
        $homeTabConfig->setUser($user);
        $homeTabConfig->setType($type);
        $homeTabConfig->setVisible($visible);
        $homeTabConfig->setLocked($locked);
        $homeTabConfig->setTabOrder($tabOrder);

        self::create($name, $homeTabConfig);
        self::$om->flush();
    }

    protected static function createWorkspaceHomeTabConfig(
        $name,
        HomeTab $homeTab,
        Workspace $workspace,
        $type,
        $visible,
        $locked,
        $tabOrder
    ) {
        $homeTabConfig = new HomeTabConfig();
        $homeTabConfig->setHomeTab($homeTab);
        $homeTabConfig->setWorkspace($workspace);
        $homeTabConfig->setType($type);
        $homeTabConfig->setVisible($visible);
        $homeTabConfig->setLocked($locked);
        $homeTabConfig->setTabOrder($tabOrder);

        self::create($name, $homeTabConfig);
        self::$om->flush();
    }

    protected static function createAdminWidgetHomeTabConfig(
        $name,
        Widget $widget,
        HomeTab $homeTab,
        $visible,
        $locked,
        $widgetOrder
    ) {
        $widgetHomeTabConfig = new WidgetHomeTabConfig();
        $widgetHomeTabConfig->setWidget($widget);
        $widgetHomeTabConfig->setHomeTab($homeTab);
        $widgetHomeTabConfig->setType('admin');
        $widgetHomeTabConfig->setVisible($visible);
        $widgetHomeTabConfig->setLocked($locked);
        $widgetHomeTabConfig->setWidgetOrder($widgetOrder);

        self::create($name, $widgetHomeTabConfig);
        self::$om->flush();
    }

    protected static function createDesktopWidgetHomeTabConfig(
        $name,
        Widget $widget,
        HomeTab $homeTab,
        User $user,
        $type,
        $visible,
        $locked,
        $widgetOrder
    ) {
        $widgetHomeTabConfig = new WidgetHomeTabConfig();
        $widgetHomeTabConfig->setWidget($widget);
        $widgetHomeTabConfig->setHomeTab($homeTab);
        $widgetHomeTabConfig->setUser($user);
        $widgetHomeTabConfig->setType($type);
        $widgetHomeTabConfig->setVisible($visible);
        $widgetHomeTabConfig->setLocked($locked);
        $widgetHomeTabConfig->setWidgetOrder($widgetOrder);

        self::create($name, $widgetHomeTabConfig);
        self::$om->flush();
    }

    protected static function createWorkspaceWidgetHomeTabConfig(
        $name,
        Widget $widget,
        HomeTab $homeTab,
        Workspace $workspace,
        $visible,
        $locked,
        $widgetOrder
    ) {
        $widgetHomeTabConfig = new WidgetHomeTabConfig();
        $widgetHomeTabConfig->setWidget($widget);
        $widgetHomeTabConfig->setHomeTab($homeTab);
        $widgetHomeTabConfig->setWorkspace($workspace);
        $widgetHomeTabConfig->setType('workspace');
        $widgetHomeTabConfig->setVisible($visible);
        $widgetHomeTabConfig->setLocked($locked);
        $widgetHomeTabConfig->setWidgetOrder($widgetOrder);

        self::create($name, $widgetHomeTabConfig);
        self::$om->flush();
    }

    protected static function createWidget($name, $configurable, $exportable, $icon)
    {
        $widget = new Widget();
        $widget->setName($name);
        $widget->setConfigurable($configurable);
        $widget->setExportable($exportable);
        $widget->setIcon($icon);

        self::create($name, $widget);
        self::$om->flush();
    }

    protected static function createWidgetInstance(
        Widget $widget,
        Workspace $workspace,
        $name,
        $isAdmin,
        $isDesktop
    ) {
        $instance = new WidgetInstance();
        $instance->setIsAdmin($isAdmin);
        $instance->setIsDesktop($isDesktop);
        $instance->setName($name);
        $instance->setWorkspace($workspace);
        $instance->setWidget($widget);

        self::$om->persist($instance);
        self::$om->flush();
    }

    protected static function createMessage(
        $alias,
        User $sender,
        array $receivers,
        $object,
        $content,
        Message $parent = null,
        $removed = false
    ) {
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
        self::create($alias.'/'.$sender->getUsername(), $userMessage);
        foreach ($receivers as $receiver) {
            $userMessage = new UserMessage();
            $userMessage->setUser($receiver);
            $userMessage->setMessage($message);
            self::create($alias.'/'.$receiver->getUsername(), $userMessage);
        }
        self::$om->endFlushSuite();
    }

    /**
     * Sets the common properties of a resource.
     *
     * @param AbstractResource $resource
     * @param ResourceType     $type
     * @param User             $creator
     * @param Workspace        $workspace
     * @param ResourceNode     $parent
     *
     * @return AbstractResource
     */
    private static function prepareResource(
        AbstractResource $resource,
        ResourceType $type,
        User $creator,
        Workspace $workspace,
        $name,
        $mimeType,
        $parent = null
    ) {
        $node = new ResourceNode();
        $node->setResourceType($type);
        $node->setCreator($creator);
        $node->setWorkspace($workspace);
        $node->setCreationDate(self::$time);
        $node->setClass('resourceClass');
        $node->setName($name);
        $node->setMimeType($mimeType);
        $node->setGuid(uniqid());
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
