<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Entity\Home\HomeTab;
use Claroline\CoreBundle\Entity\Home\HomeTabConfig;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceRights;
use Claroline\CoreBundle\Entity\Resource\ResourceShortcut;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Widget\WidgetDisplayConfig;
use Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Entity\Model\WorkspaceModel;
use Claroline\CoreBundle\Entity\Model\ResourceModel;
use Claroline\CoreBundle\Event\NotPopulatedEventException;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Manager\HomeTabManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\RightsManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CoreBundle\Manager\ToolRightsManager;
use Claroline\CoreBundle\Manager\WidgetManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\BundleRecorder\Log\LoggableTrait;
use Psr\Log\LoggerInterface;

/**
 * @DI\Service("claroline.manager.workspace_model_manager")
 */
class WorkspaceModelManager
{
    use LoggableTrait;

    private $dispatcher;
    private $homeTabManager;
    private $om;
    private $resourceManager;
    private $rightsManager;
    private $roleManager;
    private $toolManager;
    private $toolRightsManager;
    private $tokenStorage;
    private $widgetManager;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "dispatcher"        = @DI\Inject("claroline.event.event_dispatcher"),
     *     "homeTabManager"    = @DI\Inject("claroline.manager.home_tab_manager"),
     *     "om"                = @DI\Inject("claroline.persistence.object_manager"),
     *     "resourceManager"   = @DI\Inject("claroline.manager.resource_manager"),
     *     "rightsManager"     = @DI\Inject("claroline.manager.rights_manager"),
     *     "roleManager"       = @DI\Inject("claroline.manager.role_manager"),
     *     "toolManager"       = @DI\Inject("claroline.manager.tool_manager"),
     *     "toolRightsManager" = @DI\Inject("claroline.manager.tool_rights_manager"),
     *     "tokenStorage"      = @DI\Inject("security.token_storage"),
     *     "widgetManager"     = @DI\Inject("claroline.manager.widget_manager")
     * })
     */
    public function __construct(
        StrictDispatcher         $dispatcher,
        HomeTabManager           $homeTabManager,
        ObjectManager            $om,
        ResourceManager          $resourceManager,
        RightsManager            $rightsManager,
        RoleManager              $roleManager,
        ToolManager              $toolManager,
        ToolRightsManager        $toolRightsManager,
        TokenStorageInterface    $tokenStorage,
        WidgetManager            $widgetManager
    )
    {
        $this->dispatcher        = $dispatcher;
        $this->homeTabManager    = $homeTabManager;
        $this->om                = $om;
        $this->resourceManager   = $resourceManager;
        $this->rightsManager     = $rightsManager;
        $this->roleManager       = $roleManager;
        $this->toolManager       = $toolManager;
        $this->toolRightsManager = $toolRightsManager;
        $this->modelRepository   = $this->om->getRepository('ClarolineCoreBundle:Model\WorkspaceModel');
        $this->tokenStorage      = $tokenStorage;
        $this->widgetManager     = $widgetManager;
    }

    /**
     * @param $name
     * @param Workspace $workspace
     * @return WorkspaceModel
     */
    public function create($name, Workspace $workspace)
    {
        $model = new WorkspaceModel();
        $model->setName($name);
        $model->setWorkspace($workspace);

        if ($this->tokenStorage->getToken()->getUser() !== 'anon.') {
            $model->addUser($this->tokenStorage->getToken()->getUser());
        }
        $this->om->persist($model);
        $this->om->flush();

        return $model;
    }

    /**
     * @param WorkspaceModel $model
     * @param $name
     * @return WorkspaceModel
     */
    public function edit(WorkspaceModel $model, $name)
    {
        $model->setName($name);
        $this->om->persist($model);
        $this->om->flush();

        return $model;
    }

    /**
     * @param WorkspaceModel $model
     */
    public function delete(WorkspaceModel $model)
    {
        $this->om->remove($model);
        $this->om->flush();
    }

    /**
     * @param Workspace $workspace
     * @return mixed
     */
    public function getByWorkspace(Workspace $workspace)
    {
        return $this->modelRepository->findByWorkspace($workspace);
    }

    /**
     * @param WorkspaceModel $model
     * @param User $user
     */
    public function addUser(WorkspaceModel $model, User $user)
    {
        $model->addUser($user);
        $this->om->persist($model);
        $this->om->flush();
    }

    /**
     * @param WorkspaceModel $model
     * @param Group $group
     */
    public function addGroup(WorkspaceModel $model, Group $group)
    {
        $model->addGroup($group);
        $this->om->persist($model);
        $this->om->flush();
    }

    /**
     * @param WorkspaceModel $model
     * @param Group $group
     */
    public function removeGroup(WorkspaceModel $model, Group $group)
    {
        $group->removeModel($model);
        $this->om->persist($model);
        $this->om->flush();
    }

    /**
     * @param WorkspaceModel $model
     * @param User $user
     */
    public function removeUser(WorkspaceModel $model, User $user)
    {
        $user->removeModel($model);
        $this->om->persist($model);
        $this->om->flush();
    }

    /**
     * @param WorkspaceModel $model
     * @param array $users
     */
    public function addUsers(WorkspaceModel $model, array $users)
    {
        $this->om->startFlushSuite();

        foreach ($users as $user) {
            $this->addUser($model, $user);
        }

        $this->om->endFlushSuite();
    }

    /**
     * @param WorkspaceModel $model
     * @param array $groups
     */
    public function addGroups(WorkspaceModel $model, array $groups)
    {
        $this->om->startFlushSuite();

        foreach ($groups as $group) {
            $this->addGroup($model, $group);
        }

        $this->om->endFlushSuite();
    }

    /**
     * @param WorkspaceModel $model
     * @param array $resourceNodes
     * @param $isCopy
     * @return array
     */
    public function addResourceNodes(WorkspaceModel $model, array $resourceNodes, $isCopy)
    {
        $this->om->startFlushSuite();
        $resourceModels = [];

        foreach ($resourceNodes as $resourceNode) {
            $resourceModels[] = $this->addResourceNode($model, $resourceNode, $isCopy);
        }

        $this->om->endFlushSuite();

        return $resourceModels;
    }

    /**
     * @param WorkspaceModel $model
     * @param ResourceNode $resourceNode
     * @param $isCopy
     * @return ResourceModel
     */
    public function addResourceNode(WorkspaceModel $model, ResourceNode $resourceNode, $isCopy)
    {
        $resourceModel = new ResourceModel();
        $resourceModel->setModel($model);
        $resourceModel->setResourceNode($resourceNode);
        $resourceModel->setIsCopy($isCopy);
        $this->om->persist($resourceModel);
        $this->om->flush();

        return $resourceModel;
    }

    /**
     * @param ResourceModel $resourceModel
     */
    public function removeResourceModel(ResourceModel $resourceModel)
    {
        $this->om->remove($resourceModel);
        $this->om->flush();
    }

    /**
     * @param WorkspaceModel $model
     * @param array $homeTabs
     */
    public function addHomeTabs(WorkspaceModel $model, array $homeTabs)
    {
        $this->om->startFlushSuite();

        foreach ($homeTabs as $homeTab) {
            $this->addHomeTab($model, $homeTab);
        }

        $this->om->endFlushSuite();
    }

    /**
     * @param WorkspaceModel $model
     * @param HomeTab $homeTab
     */
    public function addHomeTab(WorkspaceModel $model, HomeTab $homeTab)
    {
        $model->addHomeTab($homeTab);
        $this->om->persist($model);
        $this->om->flush();
    }

    /**
     * @param WorkspaceModel $model
     * @param HomeTab $homeTab
     */
    public function removeHomeTab(WorkspaceModel $model, HomeTab $homeTab)
    {
        $model->removeHomeTab($homeTab);
        $this->om->persist($model);
        $this->om->flush();
    }

    /**
     * @param WorkspaceModel $model
     * @param array $homeTabs
     */
    public function updateHomeTabs(WorkspaceModel $model, array $homeTabs)
    {
        $this->om->startFlushSuite();
        $oldHomeTabs = $model->getHomeTabs();

        foreach ($oldHomeTabs as $oldHomeTab) {
            $search = array_search($oldHomeTab, $homeTabs, true);

            if ($search !== false) {
                unset($homeTabs[$search]);
            } else {
                $this->removeHomeTab($model, $oldHomeTab);
            }
        }

        $this->addHomeTabs($model, $homeTabs);
        $this->om->endFlushSuite();
    }

    /**
     * @param WorkspaceModel $model
     * @return array
     */
    public function toArray(WorkspaceModel $model)
    {
        $array = [];
        $array['name'] = $model->getName();

        return $array;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $source
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param \Claroline\CoreBundle\Entity\User $user
     */
    private function duplicateWorkspaceRoles(
        Workspace $source,
        Workspace $workspace,
        User $user
    )
    {
        $this->log('Duplicating roles...');
        $guid = $workspace->getGuid();
        $roles = $source->getRoles();
        $unusedRolePartName = '_' . $source->getGuid();

        foreach ($roles as $role) {
            $roleName = str_replace($unusedRolePartName, '', $role->getName());

            $createdRole = $this->roleManager->createWorkspaceRole(
                $roleName . '_' . $guid,
                $role->getTranslationKey(),
                $workspace,
                $role->isReadOnly()
            );



            if ($roleName === 'ROLE_WS_MANAGER') {
                $user->addRole($createdRole);
                $this->om->persist($user);
            }
        }
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $source
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     */
    private function duplicateOrderedTools(Workspace $source, Workspace $workspace)
    {
        $this->log('Duplicating tools...');
        $orderedTools = $source->getOrderedTools();
        $workspaceRoles = $this->getArrayRolesByWorkspace($workspace);

        foreach ($orderedTools as $orderedTool) {
            $workspaceOrderedTool = $this->toolManager->addWorkspaceTool(
                $orderedTool->getTool(),
                $orderedTool->getOrder(),
                $orderedTool->getName(),
                $workspace
            );

            $rights = $orderedTool->getRights();

            foreach ($rights as $right) {
                $role = $right->getRole();

                if ($role->getType() === 1) {
                    $this->toolRightsManager->createToolRights(
                        $workspaceOrderedTool,
                        $role,
                        $right->getMask()
                    );
                } else {
                    $key = $role->getTranslationKey();

                    if (isset($workspaceRoles[$key]) && !empty($workspaceRoles[$key])) {
                        $this->toolRightsManager->createToolRights(
                            $workspaceOrderedTool,
                            $workspaceRoles[$key],
                            $right->getMask()
                        );
                    }
                }
            }
        }
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $source
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param \Claroline\CoreBundle\Entity\User $user
     */
    private function duplicateRootDirectory(
        Workspace $source,
        Workspace $workspace,
        User $user
    )
    {
        //$this->log('Duplicating root directory...');
        $rootDirectory = new Directory();
        $rootDirectory->setName($workspace->getName());
        $directoryType = $this->resourceManager->getResourceTypeByName('directory');
        $resource = $this->resourceManager->create(
            $rootDirectory,
            $directoryType,
            $user,
            $workspace,
            null,
            null,
            array()
        );

        $workspaceRoles = $this->getArrayRolesByWorkspace($workspace);
        $root = $this->resourceManager->getWorkspaceRoot($source);
        $rights = $root->getRights();

        foreach ($rights as $right) {
            $role = $right->getRole();

            if ($role->getType() === 1) {
                $newRight = $this->rightsManager->getRightsFromIdentityMapOrScheduledForInsert(
                    $role->getName(),
                    $resource->getResourceNode()
                );
            } else {
                $newRight = new ResourceRights();
                $newRight->setResourceNode($resource->getResourceNode());

                if ($role->getWorkspace() === $source) {
                    $key = $role->getTranslationKey();

                    if (isset($workspaceRoles[$key]) && !empty($workspaceRoles[$key])) {
                        $newRight->setRole($workspaceRoles[$key]);
                    }
                } else {
                    $newRight->setRole($role);
                }
            }

            $newRight->setMask($right->getMask());
            $newRight->setCreatableResourceTypes($right->getCreatableResourceTypes()->toArray());
            $this->om->persist($newRight);
        }
        $this->om->flush();

        return $resource;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $source
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param array $homeTabs
     */
    private function duplicateHomeTabs(
        Workspace $source,
        Workspace $workspace,
        array $homeTabs,
        $resourceInfos,
        &$tabsInfos = array()
    )
    {
        $this->log('Duplicating home tabs...');
        $this->om->startFlushSuite();
        $homeTabConfigs = $this->homeTabManager
            ->getHomeTabConfigsByWorkspaceAndHomeTabs($source, $homeTabs);
        $order = 1;
        $widgetCongigErrors = array();
        $widgetDisplayConfigs = array();
        $widgets = array();

        foreach ($homeTabConfigs as $homeTabConfig) {
            $homeTab = $homeTabConfig->getHomeTab();
            $widgetHomeTabConfigs = $homeTab->getWidgetHomeTabConfigs();
            $wdcs = $this->widgetManager->getWidgetDisplayConfigsByWorkspaceAndWidgetHTCs(
                $source,
                $widgetHomeTabConfigs->toArray()
            );

            foreach ($wdcs as $wdc) {
                $widgetInstanceId = $wdc->getWidgetInstance()->getId();
                $widgetDisplayConfigs[$widgetInstanceId] = $wdc;
            }

            $newHomeTab = new HomeTab();
            $newHomeTab->setType('workspace');
            $newHomeTab->setWorkspace($workspace);
            $newHomeTab->setName($homeTab->getName());
            $this->om->persist($newHomeTab);
            $tabsInfos[] = array('original' => $homeTab, 'copy' => $newHomeTab);

            $newHomeTabConfig = new HomeTabConfig();
            $newHomeTabConfig->setHomeTab($newHomeTab);
            $newHomeTabConfig->setWorkspace($workspace);
            $newHomeTabConfig->setType('workspace');
            $newHomeTabConfig->setVisible($homeTabConfig->isVisible());
            $newHomeTabConfig->setLocked($homeTabConfig->isLocked());
            $newHomeTabConfig->setTabOrder($order);
            $this->om->persist($newHomeTabConfig);
            $order++;

            foreach ($widgetHomeTabConfigs as $widgetConfig) {
                $widgetInstance = $widgetConfig->getWidgetInstance();
                $widgetInstanceId = $widgetInstance->getId();
                $widget = $widgetInstance->getWidget();

                $newWidgetInstance = new WidgetInstance();
                $newWidgetInstance->setIsAdmin(false);
                $newWidgetInstance->setIsDesktop(false);
                $newWidgetInstance->setWorkspace($workspace);
                $newWidgetInstance->setWidget($widget);
                $newWidgetInstance->setName($widgetInstance->getName());
                $this->om->persist($newWidgetInstance);

                $newWidgetConfig = new WidgetHomeTabConfig();
                $newWidgetConfig->setType('workspace');
                $newWidgetConfig->setWorkspace($workspace);
                $newWidgetConfig->setHomeTab($newHomeTab);
                $newWidgetConfig->setWidgetInstance($newWidgetInstance);
                $newWidgetConfig->setVisible($widgetConfig->isVisible());
                $newWidgetConfig->setLocked($widgetConfig->isLocked());
                $newWidgetConfig->setWidgetOrder($widgetConfig->getWidgetOrder());
                $this->om->persist($newWidgetConfig);

                $newWidgetDisplayConfig = new WidgetDisplayConfig();
                $newWidgetDisplayConfig->setWorkspace($workspace);
                $newWidgetDisplayConfig->setWidgetInstance($newWidgetInstance);

                if (isset($widgetDisplayConfigs[$widgetInstanceId])) {
                    $newWidgetDisplayConfig->setColor(
                        $widgetDisplayConfigs[$widgetInstanceId]->getColor()
                    );
                    $newWidgetDisplayConfig->setRow(
                        $widgetDisplayConfigs[$widgetInstanceId]->getRow()
                    );
                    $newWidgetDisplayConfig->setColumn(
                        $widgetDisplayConfigs[$widgetInstanceId]->getColumn()
                    );
                    $newWidgetDisplayConfig->setWidth(
                        $widgetDisplayConfigs[$widgetInstanceId]->getWidth()
                    );
                    $newWidgetDisplayConfig->setHeight(
                        $widgetDisplayConfigs[$widgetInstanceId]->getHeight()
                    );
                } else {
                    $newWidgetDisplayConfig->setWidth($widget->getDefaultWidth());
                    $newWidgetDisplayConfig->setHeight($widget->getDefaultHeight());
                }
                
                $widgets[] = array('widget' => $widget, 'original' => $widgetInstance, 'copy' => $newWidgetInstance);
                $this->om->persist($newWidgetDisplayConfig);
            }
        }
        $this->om->endFlushSuite();
        $this->om->forceFlush();
        
        foreach ($widgets as $widget) {
            if ($widget['widget']->isConfigurable()) {
                try {
                    $this->dispatcher->dispatch(
                        'copy_widget_config_' . $widget['widget']->getName(),
                        'CopyWidgetConfiguration',
                        array($widget['original'], $widget['copy'], $resourceInfos, $tabsInfos)
                    );
                } catch (NotPopulatedEventException $e) {
                    $widgetCongigErrors[] = array(
                        'widgetName' => $widget['widget']->getName(),
                        'widgetInstanceName' => $widget['original']->getName(),
                        'error' => $e->getMessage()
                    );
                }
            }
        }
        
        

        return $widgetCongigErrors;
    }

    /**
     * @param array $resourcesModels
     * @param \Claroline\CoreBundle\Entity\Resource\Directory $rootDirectory
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param \Claroline\CoreBundle\Entity\User $user
     */
    private function duplicateResources(
        array $resourcesModels,
        Directory $rootDirectory,
        Workspace $workspace,
        User $user,
        &$resourcesInfos
    )
    {
        $this->log('Duplicating ' . count($resourcesModels) . ' resources...');
        $this->om->startFlushSuite();

        $copies = array();
        $resourcesErrors = array();
        $workspaceRoles = $this->getArrayRolesByWorkspace($workspace);

        foreach ($resourcesModels as $key => $resourceModel) {
            $resourceNode = $resourceModel->getResourceNode();

            if ($resourceModel->isCopy()) {

                try {
                    $this->log('Duplicating ' . $resourceNode->getName() . ' from type ' . $resourceNode->getResourceType()->getName());
                    $copy = $this->resourceManager->copy(
                        $resourceNode,
                        $rootDirectory->getResourceNode(),
                        $user,
                        false,
                        false
                    );
                    $copy->getResourceNode()->setIndex($resourceNode->getIndex());
                    $this->om->persist($copy->getResourceNode());
                    $resourcesInfos['copies'][] = array('original' => $resourceNode, 'copy' => $copy->getResourceNode());
                } catch (NotPopulatedEventException $e) {
                    $resourcesErrors[] = array(
                        'resourceName' => $resourceNode->getName(),
                        'resourceType' => $resourceNode->getResourceType()->getName(),
                        'type' => 'copy',
                        'error' => $e->getMessage()
                    );
                    continue;
                }

                /*** Copies rights ***/
                $this->duplicateRights(
                    $resourceNode,
                    $copy->getResourceNode(),
                    $workspaceRoles
                );

                /*** Copies content of a directory ***/
                if ($resourceNode->getResourceType()->getName() === 'directory') {
                    $errors = $this->duplicateDirectoryContent(
                        $resourceNode,
                        $copy->getResourceNode(),
                        $user,
                        $workspaceRoles,
                        $resourcesInfos
                    );
                    $resourcesErrors = array_merge_recursive($resourcesErrors, $errors);
                }
            } else {
                $shortcut = $this->resourceManager->makeShortcut(
                    $resourceNode,
                    $rootDirectory->getResourceNode(),
                    $user,
                    new ResourceShortcut()
                );
                $copies[] = $shortcut;
            }

            $position = $key + 1;

            $this->log('Resource [' . $resourceModel->getResourceNode()->getName() . '] ' . $position . '/' . count($resourcesModels) . ' copied');
        }

        /*** Sets previous and next for each copied resource ***/
        $this->linkResourcesArray($copies);

        $this->om->endFlushSuite();

        return $resourcesErrors;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     */
    private function getArrayRolesByWorkspace(Workspace $workspace)
    {
        $workspaceRoles = array();
        $uow = $this->om->getUnitOfWork();
        $wRoles = $this->roleManager->getRolesByWorkspace($workspace);
        $scheduledForInsert = $uow->getScheduledEntityInsertions();

        foreach ($scheduledForInsert as $entity) {
            if (get_class($entity) === 'Claroline\CoreBundle\Entity\Role') {
                if ($entity->getWorkspace()) {
                    if ($entity->getWorkspace()->getGuid() === $workspace->getGuid()) {
                        $wRoles[] = $entity;
                    }
                }
            }
        }

        //now we build the array
        foreach ($wRoles as $wRole) {
            $workspaceRoles[$wRole->getTranslationKey()] = $wRole;
        }

        return $workspaceRoles;
    }

    /**
     * @param array $resources
     */
    private function linkResourcesArray(array $resources)
    {
        for ($i = 1; $i < count($resources); $i++) {
            $node = $resources[$i]->getResourceNode();
            $node->setIndex($i);
            $this->om->persist($node);
        }
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $resourceNode
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $copy
     * @param array $workspaceRoles
     */
    private function duplicateRights(
        ResourceNode $resourceNode,
        ResourceNode $copy,
        array $workspaceRoles
    )
    {
        //$this->log('Duplicating rights...');
        $rights = $resourceNode->getRights();
        $workspace = $resourceNode->getWorkspace();

        foreach ($rights as $right) {
            $role = $right->getRole();
            $key = $role->getTranslationKey();

            $newRight = new ResourceRights();
            $newRight->setResourceNode($copy);
            $newRight->setMask($right->getMask());
            $newRight->setCreatableResourceTypes(
                $right->getCreatableResourceTypes()->toArray()
            );

            if ($role->getWorkspace() === $workspace &&
                isset($workspaceRoles[$key]) &&
                !empty($workspaceRoles[$key])) {

                $newRight->setRole($workspaceRoles[$key]);
            } else {
                $newRight->setRole($role);
            }
            $this->om->persist($newRight);
        }
        $this->om->flush();
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $directory
     * @param \Claroline\CoreBundle\Entity\Resource\ResourceNode $directoryCopy
     * @param \Claroline\CoreBundle\Entity\User $user
     * @param array $workspaceRoles
     */
    private function duplicateDirectoryContent(
        ResourceNode $directory,
        ResourceNode $directoryCopy,
        User $user,
        array $workspaceRoles,
        &$resourcesInfos
    )
    {
        $this->log('Duplicating directory content...');
        $children = $directory->getChildren();
        $copies = array();
        $resourcesErrors = array();

        foreach ($children as $child) {
           try {
                $copy = $this->resourceManager->copy(
                    $child,
                    $directoryCopy,
                    $user,
                    false,
                    false
                );
                $copies[] = $copy;
                $resourcesInfos['copies'][] = array('original' => $child, 'copy' => $copy->getResourceNode());
            } catch (NotPopulatedEventException $e) {
                $resourcesErrors[] = array(
                    'resourceName' => $child->getName(),
                    'resourceType' => $child->getResourceType()->getName(),
                    'type' => 'copy',
                    'error' => $e->getMessage()
                );
                continue;
            }

            /*** Copies rights ***/
            $this->duplicateRights(
                $child,
                $copy->getResourceNode(),
                $workspaceRoles
            );

            /*** Recursive call for a directory ***/
            if ($child->getResourceType()->getName() === 'directory') {
                $errors = $this->duplicateDirectoryContent(
                    $child,
                    $copy->getResourceNode(),
                    $user,
                    $workspaceRoles,
                    $resourcesInfos
                );
                $resourcesErrors = array_merge_recursive($resourcesErrors, $errors);
            }
        }

        $this->linkResourcesArray($copies);
        $this->om->flush();

        return $resourcesErrors;
    }

    public function addDataFromModel(WorkspaceModel $model, Workspace $workspace, User $user, &$errors)
    {
        $modelWorkspace = $model->getWorkspace();
        $resourcesModels = $model->getResourcesModel();
        $homeTabs = $model->getHomeTabs();
        $resourcesInfos = array();

        $this->duplicateWorkspaceRoles($modelWorkspace, $workspace, $user);
        $this->duplicateOrderedTools($modelWorkspace, $workspace);
        $rootDirectory = $this->duplicateRootDirectory($modelWorkspace, $workspace, $user);
        $errors['resourceErrors'] = $this->duplicateResources(
            $resourcesModels->toArray(),
            $rootDirectory,
            $workspace,
            $user,
            $resourcesInfos
        );
        $this->om->forceFlush();
        
        $errors['widgetConfigErrors'] = $this->duplicateHomeTabs($modelWorkspace, $workspace, $homeTabs->toArray(), $resourcesInfos);
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getLogger()
    {
        return $this->logger;
    }
}
