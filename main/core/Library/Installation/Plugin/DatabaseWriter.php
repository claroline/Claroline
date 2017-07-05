<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Installation\Plugin;

use Claroline\BundleRecorder\Log\LoggableTrait;
use Claroline\CoreBundle\Entity\Action\AdditionalAction;
use Claroline\CoreBundle\Entity\Activity\ActivityRuleAction;
use Claroline\CoreBundle\Entity\Plugin;
use Claroline\CoreBundle\Entity\Resource\MaskDecoder;
use Claroline\CoreBundle\Entity\Resource\MenuAction;
use Claroline\CoreBundle\Entity\Resource\ResourceIcon;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Theme\Theme;
use Claroline\CoreBundle\Entity\Tool\AdminTool;
use Claroline\CoreBundle\Entity\Tool\PwsToolConfig;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\Tool\ToolMaskDecoder;
use Claroline\CoreBundle\Entity\Widget\Widget;
use Claroline\CoreBundle\Library\PluginBundle;
use Claroline\CoreBundle\Manager\IconManager;
use Claroline\CoreBundle\Manager\IconSetManager;
use Claroline\CoreBundle\Manager\MaskManager;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CoreBundle\Manager\ToolMaskDecoderManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * This class is used to save/delete a plugin and its possible dependencies (like
 * custom resource types) in the database.
 *
 * @DI\Service("claroline.plugin.recorder_database_writer")
 */
class DatabaseWriter
{
    use LoggableTrait;

    private $em;
    private $im;
    private $mm;
    private $fileSystem;
    private $kernelRootDir;
    private $modifyTemplate = false;
    private $toolManager;
    private $toolMaskManager;
    private $iconSetManager;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "em"              = @DI\Inject("claroline.persistence.object_manager"),
     *     "im"              = @DI\Inject("claroline.manager.icon_manager"),
     *     "mm"              = @DI\Inject("claroline.manager.mask_manager"),
     *     "fileSystem"      = @DI\Inject("filesystem"),
     *     "kernel"          = @DI\Inject("kernel"),
     *     "toolManager"     = @DI\Inject("claroline.manager.tool_manager"),
     *     "toolMaskManager" = @DI\Inject("claroline.manager.tool_mask_decoder_manager"),
     *     "iconSetManager"  = @DI\Inject("claroline.manager.icon_set_manager")
     * })
     */
    public function __construct(
        ObjectManager $em,
        IconManager $im,
        Filesystem $fileSystem,
        KernelInterface $kernel,
        MaskManager $mm,
        ToolManager $toolManager,
        ToolMaskDecoderManager $toolMaskManager,
        IconSetManager $iconSetManager
    ) {
        $this->em = $em;
        $this->im = $im;
        $this->mm = $mm;
        $this->fileSystem = $fileSystem;
        $this->kernelRootDir = $kernel->getRootDir();
        $this->modifyTemplate = $kernel->getEnvironment() !== 'test';
        $this->toolManager = $toolManager;
        $this->toolMaskManager = $toolMaskManager;
        $this->iconSetManager = $iconSetManager;
    }

    /**
     * Persists a plugin in the database.
     *
     * @param PluginBundle $pluginBundle
     * @param array        $pluginConfiguration
     */
    public function insert(PluginBundle $pluginBundle, array $pluginConfiguration)
    {
        $pluginEntity = new Plugin();
        $pluginEntity->setVendorName($pluginBundle->getVendorName());
        $pluginEntity->setBundleName($pluginBundle->getBundleName());
        $pluginEntity->setHasOptions($pluginConfiguration['has_options']);

        $this->em->persist($pluginEntity);
        $this->persistConfiguration($pluginConfiguration, $pluginEntity, $pluginBundle);
        $this->em->flush();

        return $pluginEntity;
    }

    /**
     * @param PluginBundle $pluginBundle
     * @param array        $pluginConfiguration
     *
     * @throws \Exception
     */
    public function update(PluginBundle $pluginBundle, array $pluginConfiguration)
    {
        /** @var Plugin $plugin */
        $plugin = $this->em->getRepository('ClarolineCoreBundle:Plugin')->findOneBy(
            [
                 'vendorName' => $pluginBundle->getVendorName(),
                 'bundleName' => $pluginBundle->getBundleName(),
            ]
        );

        if (null === $plugin) {
            $this->log('Unable to retrieve plugin for updating its configuration.', LogLevel::ERROR);

            return;
        }

        $plugin->setHasOptions($pluginConfiguration['has_options']);
        $this->em->persist($plugin);
        $this->log('Configuration was retrieved: updating...');
        $this->updateConfiguration($pluginConfiguration, $plugin, $pluginBundle);
        $this->em->flush();

        return $plugin;
    }

    /**
     * Removes a plugin from the database.
     *
     * @param string $pluginFqcn
     */
    public function delete($pluginFqcn)
    {
        $plugin = $this->getPluginByFqcn($pluginFqcn);
        // code below is for "re-parenting" the resources which depend on one
        // of the resource types the plugin might have declared
        $resourceTypes = $this->em
            ->getRepository('ClarolineCoreBundle:Resource\ResourceType')
            ->findByPlugin($plugin->getGeneratedId());

        foreach ($resourceTypes as $resourceType) {
            $this->deleteActivityRules($resourceType);
            // delete all icons for this resource type in icon sets
            $this->iconSetManager->deleteAllResourceIconItemsForMimeType('custom/'.$resourceType->getName());
        }

        // deletion of other plugin db dependencies is made via a cascade mechanism
        $this->em->remove($plugin);
        $this->em->flush();
    }

    /**
     * Checks if a plugin is persisted in the database.
     *
     * @param \Claroline\CoreBundle\Library\PluginBundle $plugin
     *
     * @return bool
     */
    public function isSaved(PluginBundle $plugin)
    {
        if ($this->getPluginByFqcn(get_class($plugin)) !== null) {
            return true;
        }

        return false;
    }

    /**
     * @param string $pluginFqcn
     *
     * @return Plugin
     */
    private function getPluginByFqcn($pluginFqcn)
    {
        $plugin = $this->em
            ->getRepository('ClarolineCoreBundle:Plugin')
            ->findOneByBundleFQCN($pluginFqcn);

        return $plugin;
    }

    /**
     * @param array        $processedConfiguration
     * @param Plugin       $plugin
     * @param PluginBundle $pluginBundle
     */
    private function persistConfiguration($processedConfiguration, Plugin $plugin, PluginBundle $pluginBundle)
    {
        foreach ($processedConfiguration['resources'] as $resource) {
            $this->persistResourceTypes($resource, $plugin, $pluginBundle);
        }

        foreach ($processedConfiguration['resource_actions'] as $resourceAction) {
            $this->persistResourceAction($resourceAction);
        }

        $roles = $this->em->getRepository('ClarolineCoreBundle:Role')
            ->findAllPlatformRoles();

        foreach ($processedConfiguration['widgets'] as $widget) {
            $this->createWidget($widget, $plugin, $pluginBundle, $roles);
        }

        foreach ($processedConfiguration['tools'] as $tool) {
            $this->createTool($tool, $plugin);
        }

        foreach ($processedConfiguration['themes'] as $theme) {
            $this->createTheme($theme, $plugin);
        }

        foreach ($processedConfiguration['admin_tools'] as $adminTool) {
            $this->createAdminTool($adminTool, $plugin);
        }

        foreach ($processedConfiguration['additional_action'] as $action) {
            $this->updateAdditionalAction($action, $plugin);
        }
    }

    /**
     * @param array        $processedConfiguration
     * @param Plugin       $plugin
     * @param PluginBundle $pluginBundle
     */
    private function updateConfiguration($processedConfiguration, Plugin $plugin, PluginBundle $pluginBundle)
    {
        foreach ($processedConfiguration['resources'] as $resourceConfiguration) {
            $this->updateResourceTypes($resourceConfiguration, $plugin, $pluginBundle);
        }

        foreach ($processedConfiguration['resource_actions'] as $resourceAction) {
            $this->updateResourceAction($resourceAction);
        }

        $roles = $this->em->getRepository('ClarolineCoreBundle:Role')
            ->findAllPlatformRoles();

        foreach ($processedConfiguration['widgets'] as $widgetConfiguration) {
            $this->updateWidget($widgetConfiguration, $pluginBundle, $plugin, $roles);
        }

        foreach ($processedConfiguration['tools'] as $toolConfiguration) {
            $this->updateTool($toolConfiguration, $plugin);
        }

        foreach ($processedConfiguration['themes'] as $themeConfiguration) {
            $this->updateTheme($themeConfiguration, $plugin);
        }

        foreach ($processedConfiguration['admin_tools'] as $adminTool) {
            $this->updateAdminTool($adminTool, $plugin);
        }

        foreach ($processedConfiguration['additional_action'] as $action) {
            $this->updateAdditionalAction($action, $plugin);
        }
    }

    /**
     * @param array        $resourceConfiguration
     * @param Plugin       $plugin
     * @param PluginBundle $pluginBundle
     *
     * @return ResourceType
     */
    private function updateResourceTypes($resourceConfiguration, Plugin $plugin, PluginBundle $pluginBundle)
    {
        $this->log('Update resource type '.$resourceConfiguration['name']);
        $resourceType = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceType')
            ->findOneByName($resourceConfiguration['name']);

        if (null === $resourceType) {
            $resourceType = new ResourceType();
            $resourceType->setName($resourceConfiguration['name']);
            $resourceType->setPlugin($plugin);
        }

        $resourceType->setExportable($resourceConfiguration['is_exportable']);
        $this->em->persist($resourceType);

        if (!$this->mm->hasMenuAction($resourceType)) {
            $this->mm->addDefaultPerms($resourceType);
        }

        $this->updateCustomAction($resourceConfiguration['actions'], $resourceType);
        $this->updateIcons($resourceConfiguration, $resourceType, $pluginBundle);
        $this->updateActivityRules($resourceConfiguration['activity_rules'], $resourceType);

        return $resourceType;
    }

    /**
     * @param array  $toolConfiguration
     * @param Plugin $plugin
     */
    private function updateTool($toolConfiguration, Plugin $plugin)
    {
        $tool = $this->em->getRepository('ClarolineCoreBundle:Tool\Tool')
            ->findOneByName($toolConfiguration['name']);

        if ($tool === null) {
            $tool = new Tool();
        }

        $this->persistTool($toolConfiguration, $plugin, $tool);
        $this->updateCustomToolRights($toolConfiguration['tool_rights'], $tool);
    }

    /**
     * @param array        $widgetConfiguration
     * @param PluginBundle $pluginBundle
     * @param Plugin       $plugin
     */
    private function updateWidget(
        $widgetConfiguration,
        PluginBundle $pluginBundle,
        Plugin $plugin,
        array $roles = []
    ) {
        $widget = $this->em->getRepository('ClarolineCoreBundle:Widget\Widget')
            ->findOneByName($widgetConfiguration['name']);
        $withDisplay = false;

        if (is_null($widget)) {
            $widget = new Widget();

            foreach ($roles as $role) {
                $widget->addRole($role);
            }
            $withDisplay = true;
        }

        $this->persistWidget($widgetConfiguration, $plugin, $pluginBundle, $widget, $withDisplay);
    }

    private function updateAdditionalAction(array $action, Plugin $plugin)
    {
        $this->log("Adding action  {$action['type']}:{$action['displayed_name']}");
        $aa = $this->em->getRepository('ClarolineCoreBundle:Action\AdditionalAction')->findOneBy([
            'action' => $action['action'],
            'type' => $action['type'],
        ]);

        if (!$aa) {
            $aa = new AdditionalAction();
        }

        $aa->setClass($action['class']);
        $aa->setAction($action['action']);
        $aa->setDisplayedName($action['displayed_name']);
        $aa->setType($action['type']);
        $this->em->persist($aa);
        $this->em->flush();
    }

    /**
     * @param array        $resource
     * @param ResourceType $resourceType
     * @param PluginBundle $pluginBundle
     */
    private function persistIcons(array $resource, ResourceType $resourceType, PluginBundle $pluginBundle)
    {
        $resourceIcon = new ResourceIcon();
        $resourceIcon->setMimeType('custom/'.$resourceType->getName());
        $ds = DIRECTORY_SEPARATOR;

        if (isset($resource['icon'])) {
            $webBundleDir = "{$this->kernelRootDir}{$ds}..{$ds}web{$ds}bundles";
            $webPluginDir = "{$webBundleDir}{$ds}{$pluginBundle->getAssetsFolder()}";
            $webPluginImgDir = "{$webPluginDir}{$ds}images";
            $webPluginIcoDir = "{$webPluginImgDir}{$ds}icons";
            $this->fileSystem->mkdir([$webBundleDir, $webPluginDir, $webPluginImgDir, $webPluginIcoDir]);
            $this->fileSystem->copy(
                "{$pluginBundle->getImgFolder()}{$ds}{$resource['icon']}",
                "{$webPluginIcoDir}{$ds}{$resource['icon']}"
            );
            $resourceIcon->setRelativeUrl(
                "bundles/{$pluginBundle->getAssetsFolder()}/images/icons/{$resource['icon']}"
            );
        } else {
            $defaultIcon = $this->em
                ->getRepository('ClarolineCoreBundle:Resource\ResourceIcon')
                ->findOneByMimeType('custom/default');
            $resourceIcon->setRelativeUrl($defaultIcon->getRelativeUrl());
        }

        $resourceIcon->setUuid(uniqid('', true));
        $resourceIcon->setShortcut(false);
        $this->em->persist($resourceIcon);
        $this->im->createShortcutIcon($resourceIcon);
        // Also add the new resource type icon to default resource icon set
        $this->iconSetManager->addOrUpdateIconItemToDefaultResourceIconSet($resourceIcon);
    }

    /**
     * @param array        $resource
     * @param ResourceType $resourceType
     * @param PluginBundle $pluginBundle
     */
    private function updateIcons(array $resource, ResourceType $resourceType, PluginBundle $pluginBundle)
    {
        $resourceIcon = $this->em
            ->getRepository('ClarolineCoreBundle:Resource\ResourceIcon')
            ->findOneByMimeType('custom/'.$resourceType->getName());
        $isNew = false;
        if (null === $resourceIcon) {
            $resourceIcon = new ResourceIcon();
            $resourceIcon->setUuid(uniqid('', true));
            $resourceIcon->setMimeType('custom/'.$resourceType->getName());
            $isNew = true;
        }

        if (isset($resource['icon'])) {
            $newRelativeUrl = "bundles/{$pluginBundle->getAssetsFolder()}/images/icons/{$resource['icon']}";
        } else {
            $defaultIcon = $this->em
                ->getRepository('ClarolineCoreBundle:Resource\ResourceIcon')
                ->findOneByMimeType('custom/default');
            $newRelativeUrl = $defaultIcon->getRelativeUrl();
        }
        // If icon is new, create it and persist it to db
        if ($isNew) {
            $resourceIcon->setRelativeUrl($newRelativeUrl);
            $resourceIcon->setShortcut(false);
            $this->em->persist($resourceIcon);
            $this->im->createShortcutIcon($resourceIcon);
        }
        // Also add/update the resource type icon to default resource icon set
        $this->iconSetManager->addOrUpdateIconItemToDefaultResourceIconSet(
            $resourceIcon,
            $newRelativeUrl
        );
    }

    /**
     * @param array $action
     */
    public function persistResourceAction(array $action)
    {
        //also remove duplicatas if some are found
        $resourceType = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneByName($action['resource_type']);
        $resourceActions = $this->em->getRepository('ClarolineCoreBundle:Resource\MenuAction')
          ->findBy(['name' => $action['name'], 'resourceType' => $resourceType]);

        if (count($resourceActions) > 1) {
            //keep the first one, remove the rest and then flush
            $this->log('Removing superfluous masks...', LogLevel::ERROR);

            for ($i = 1; $i < count($resourceActions); ++$i) {
                $this->em->remove($resourceActions[$i]);
            }

            $this->em->forceFlush();
        }

        $this->log('Updating resource action '.$action['name']);

        $maskType = ($action['resource_type']) ?
            $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneByName($action['resource_type']) :
            //this is some kind of hack for the current implementation. Each mask has a resourcetype so we can't pick null
            //and directory has all the default perms. Any other resource type would have done the trick anyway
            $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneByName('directory');

        $value = $this->mm->encodeMask([$action['value'] => true], $maskType);

        $resourceAction = $this->em->getRepository('ClarolineCoreBundle:Resource\MenuAction')
            ->findOneBy(['name' => $action['name'], 'resourceType' => $resourceType]);

        if (!$resourceAction) {
            $resourceAction = new MenuAction();
        }

        $resourceAction->setName($action['name']);
        $resourceAction->setAsync($action['is_async']);
        $resourceAction->setIsForm($action['is_form']);
        $resourceAction->setIsCustom($action['is_custom']);
        $resourceAction->setValue($value);
        $resourceAction->setGroup($action['group']);
        $resourceAction->setIcon($action['class']);
        $resourceAction->setResourceType($resourceType);

        $this->em->persist($resourceAction);
        $this->em->flush();
    }

    /**
     * @param array $action
     */
    public function updateResourceAction(array $action)
    {
        $this->persistResourceAction($action);
    }

    /**
     * @param array        $actions
     * @param ResourceType $resourceType
     */
    private function persistCustomAction($actions, ResourceType $resourceType)
    {
        $decoderRepo = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\MaskDecoder');
        $existingDecoders = $decoderRepo->findBy(['resourceType' => $resourceType]);
        $exp = count($existingDecoders);
        $newDecoders = [];

        foreach ($actions as $action) {
            $decoder = $decoderRepo->findOneBy(['name' => $action['name'], 'resourceType' => $resourceType]);

            if (!$decoder) {
                if (array_key_exists($action['name'], $newDecoders)) {
                    $decoder = $newDecoders[$action['name']];
                } else {
                    $decoder = new MaskDecoder();
                    $decoder->setName($action['name']);
                    $decoder->setResourceType($resourceType);
                    $decoder->setValue(pow(2, $exp));
                    $this->em->persist($decoder);
                    $newDecoders[$action['name']] = $decoder;
                    ++$exp;
                }
            }

            if (isset($action['menu_name'])) {
                $rtca = new MenuAction();
                $rtca->setName($action['menu_name']);
                $rtca->setResourceType($resourceType);
                $rtca->setValue($decoder->getValue());
                $rtca->setIsForm($action['is_form']);
                $this->em->persist($rtca);
            }
        }

        $this->em->flush();
    }

    /**
     * @param array        $actions
     * @param ResourceType $resourceType
     */
    private function updateCustomAction($actions, ResourceType $resourceType)
    {
        $decoderRepo = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\MaskDecoder');
        $existingDecoders = $decoderRepo->findBy(['resourceType' => $resourceType]);
        $exp = count($existingDecoders);
        $newDecoders = [];

        foreach ($actions as $action) {
            $decoder = $decoderRepo->findOneBy(['name' => $action['name'], 'resourceType' => $resourceType]);

            if (!$decoder) {
                if (array_key_exists($action['name'], $newDecoders)) {
                    $decoder = $newDecoders[$action['name']];
                } else {
                    $decoder = new MaskDecoder();
                    $decoder
                        ->setName($action['name'])
                        ->setResourceType($resourceType)
                        ->setValue(pow(2, $exp));

                    $this->em->persist($decoder);
                    $newDecoders[$action['name']] = $decoder;
                    ++$exp;
                }
            }

            if (isset($action['menu_name'])) {
                $menuAction = $this->em->getRepository('ClarolineCoreBundle:Resource\MenuAction')
                    ->findOneByName($action['menu_name']);

                if (null === $menuAction) {
                    $menuAction = new MenuAction();
                    $menuAction
                        ->setName($action['menu_name'])
                        ->setResourceType($resourceType)
                        ->setIsForm($action['is_form'])
                        ->setValue($decoder->getValue());

                    $this->em->persist($menuAction);
                }
            }
        }

        $this->em->flush();
    }

    /**
     * @param array        $resourceConfiguration
     * @param Plugin       $plugin
     * @param PluginBundle $pluginBundle
     *
     * @return ResourceType
     */
    private function persistResourceTypes($resourceConfiguration, Plugin $plugin, PluginBundle $pluginBundle)
    {
        $this->log('Adding resource type '.$resourceConfiguration['name']);
        $resourceType = new ResourceType();
        $resourceType->setName($resourceConfiguration['name']);
        $resourceType->setExportable($resourceConfiguration['is_exportable']);
        $resourceType->setPlugin($plugin);
        $this->em->persist($resourceType);
        $this->mm->addDefaultPerms($resourceType);
        $this->persistCustomAction($resourceConfiguration['actions'], $resourceType);
        $this->setResourceTypeDefaultMask($resourceConfiguration['default_rights'], $resourceType);
        $this->persistIcons($resourceConfiguration, $resourceType, $pluginBundle);
        $this->persistActivityRules($resourceConfiguration['activity_rules'], $resourceType);

        return $resourceType;
    }

    /**
     * @param array        $rightsName
     * @param ResourceType $resourceType
     */
    private function setResourceTypeDefaultMask(array $rightsName, ResourceType $resourceType)
    {
        $mask = count($rightsName) === 0 ? 1 : 0;
        $permMap = $this->mm->getPermissionMap($resourceType);

        foreach ($rightsName as $rights) {
            foreach ($permMap as $value => $perm) {
                if ($perm === $rights['name']) {
                    $mask += $value;
                }
            }
        }

        $resourceType->setDefaultMask($mask);
        $this->em->persist($resourceType);
    }

    /**
     * @param array        $widgetConfiguration
     * @param Plugin       $plugin
     * @param PluginBundle $pluginBundle
     */
    private function createWidget($widgetConfiguration, Plugin $plugin, PluginBundle $pluginBundle, array $roles = [])
    {
        $widget = new Widget();
        $widget->setPlugin($plugin);

        foreach ($roles as $role) {
            $widget->addRole($role);
        }
        $this->persistWidget($widgetConfiguration, $plugin, $pluginBundle, $widget);
    }

    /**
     * @param array        $widgetConfiguration
     * @param Plugin       $plugin
     * @param PluginBundle $pluginBundle
     * @param Widget       $widget
     */
    private function persistWidget($widgetConfiguration, Plugin $plugin, PluginBundle $pluginBundle, Widget $widget, $withDisplay = true)
    {
        $widget->setName($widgetConfiguration['name']);
        $widget->setConfigurable($widgetConfiguration['is_configurable']);
        $widget->setExportable($widgetConfiguration['is_exportable']);
        $widget->setDefaultWidth($widgetConfiguration['default_width']);
        $widget->setDefaultHeight($widgetConfiguration['default_height']);

        if ($withDisplay) {
            $widget->setIsDisplayableInDesktop($widgetConfiguration['is_displayable_in_desktop']);
            $widget->setIsDisplayableInWorkspace($widgetConfiguration['is_displayable_in_workspace']);
        }
        $this->em->persist($widget);
    }

    /**
     * @param array  $toolConfiguration
     * @param Plugin $plugin
     */
    private function createTool($toolConfiguration, Plugin $plugin)
    {
        $tool = new Tool();
        $this->persistTool($toolConfiguration, $plugin, $tool);
        $roleUser = $this->em->getRepository('ClarolineCoreBundle:Role')->findOneByName('ROLE_USER');
        $mask = ToolMaskDecoder::$defaultValues['open'] + ToolMaskDecoder::$defaultValues['edit'];
        $pws = new PwsToolConfig();
        $pws->setTool($tool);
        $pws->setRole($roleUser);
        $pws->setMask($mask);
        $this->em->persist($pws);
    }

    /**
     * @param array  $toolConfiguration
     * @param Plugin $plugin
     * @param Tool   $tool
     */
    private function persistTool($toolConfiguration, Plugin $plugin, Tool $tool)
    {
        $this->log('Update tool '.$toolConfiguration['name']);
        $tool->setName($toolConfiguration['name']);
        $tool->setDisplayableInDesktop($toolConfiguration['is_displayable_in_desktop']);
        $tool->setDisplayableInWorkspace($toolConfiguration['is_displayable_in_workspace']);
        $tool->setIsDesktopRequired(false);
        $tool->setIsWorkspaceRequired(false);
        $tool->setPlugin($plugin);
        $tool->setExportable($toolConfiguration['is_exportable']);
        $tool->setIsConfigurableInWorkspace($toolConfiguration['is_configurable_in_workspace']);
        $tool->setIsConfigurableInDesktop($toolConfiguration['is_configurable_in_desktop']);
        $tool->setIsDesktopRequired($toolConfiguration['is_desktop_required']);
        $tool->setIsWorkspaceRequired($toolConfiguration['is_workspace_required']);
        $tool->setIsLockedForAdmin($toolConfiguration['is_locked_for_admin']);
        $tool->setIsAnonymousExcluded($toolConfiguration['is_anonymous_excluded']);

        if (isset($toolConfiguration['class'])) {
            $tool->setClass("{$toolConfiguration['class']}");
        } else {
            $tool->setClass('wrench');
        }

        $this->toolManager->create($tool);
        $this->persistCustomToolRights($toolConfiguration['tool_rights'], $tool);
    }

    /**
     * @param array  $themeConfiguration
     * @param Plugin $plugin
     */
    private function createTheme($themeConfiguration, Plugin $plugin)
    {
        $theme = new Theme();
        $this->persistTheme($themeConfiguration, $plugin, $theme);
    }

    /**
     * @param array  $themeConfiguration
     * @param Plugin $plugin
     */
    private function updateTheme($themeConfiguration, Plugin $plugin)
    {
        $theme = $this->em->getRepository('ClarolineCoreBundle:Theme\Theme')
            ->findOneByName($themeConfiguration['name']);

        if ($theme === null) {
            $theme = new Theme();
        }

        $this->persistTheme($themeConfiguration, $plugin, $theme);
    }

    /**
     * @param array  $themeConfiguration
     * @param Plugin $plugin
     * @param Theme  $theme
     */
    private function persistTheme($themeConfiguration, Plugin $plugin, Theme $theme)
    {
        $theme->setName($themeConfiguration['name']);
        $theme->setPlugin($plugin);
        $this->em->persist($theme);
    }

    /**
     * @param array  $adminToolConfiguration
     * @param Plugin $plugin
     */
    private function createAdminTool($adminToolConfiguration, Plugin $plugin)
    {
        $adminTool = new AdminTool();
        $this->persistAdminTool($adminToolConfiguration, $plugin, $adminTool);
    }

    /**
     * @param array     $adminToolConfiguration
     * @param Plugin    $plugin
     * @param AdminTool $adminTool
     */
    private function persistAdminTool($adminToolConfiguration, Plugin $plugin, AdminTool $adminTool)
    {
        $this->log('Update admin tool '.$adminToolConfiguration['name']);
        $adminTool->setName($adminToolConfiguration['name']);
        $adminTool->setClass($adminToolConfiguration['class']);
        $adminTool->setPlugin($plugin);
        $this->em->persist($adminTool);
    }

    /**
     * @param array  $adminToolConfiguration
     * @param Plugin $plugin
     */
    private function updateAdminTool($adminToolConfiguration, Plugin $plugin)
    {
        $adminTool = $this->em->getRepository('ClarolineCoreBundle:Tool\AdminTool')
            ->findOneByName($adminToolConfiguration['name']);

        if ($adminTool === null) {
            $adminTool = new AdminTool();
        }

        $this->persistAdminTool($adminToolConfiguration, $plugin, $adminTool);
    }

    /**
     * @param array        $rules
     * @param ResourceType $resourceType
     */
    private function persistActivityRules($rules, ResourceType $resourceType)
    {
        $activityRuleActionRepository = $this->em
            ->getRepository('Claroline\CoreBundle\Entity\Activity\ActivityRuleAction');

        foreach ($rules as $rule) {
            $ruleAction = $activityRuleActionRepository->findOneBy(
                ['action' => $rule['action'], 'resourceType' => $resourceType]
            );

            if (is_null($ruleAction)) {
                $ruleAction = new ActivityRuleAction();
                $ruleAction->setResourceType($resourceType);
                $ruleAction->setAction($rule['action']);
            }

            $this->em->persist($ruleAction);
        }
        $this->em->flush();
    }

    /**
     * @param ResourceType $resourceType
     */
    private function deleteActivityRules(ResourceType $resourceType)
    {
        $aRuleActionRepo = $this->em
            ->getRepository('Claroline\CoreBundle\Entity\Activity\ActivityRuleAction');
        $ruleActions = $aRuleActionRepo->findBy(['resourceType' => $resourceType]);

        foreach ($ruleActions as $ruleAction) {
            $this->em->remove($ruleAction);
        }
        $this->em->flush();
    }

    /**
     * @param array        $rules
     * @param ResourceType $resourceType
     */
    private function updateActivityRules($rules, ResourceType $resourceType)
    {
        $this->deleteActivityRules($resourceType);
        $this->persistActivityRules($rules, $resourceType);
    }

    /**
     * @param array $rights
     * @param Tool  $tool
     */
    private function persistCustomToolRights(array $rights, Tool $tool)
    {
        $decoders = $this->toolMaskManager->getMaskDecodersByTool($tool);
        $nb = count($decoders);

        foreach ($rights as $right) {
            $maskDecoder = $this->toolMaskManager
                ->getMaskDecoderByToolAndName($tool, $right['name']);

            if (is_null($maskDecoder)) {
                $value = pow(2, $nb);
                $this->toolMaskManager->createToolMaskDecoder(
                    $tool,
                    $right['name'],
                    $value,
                    $right['granted_icon_class'],
                    $right['denied_icon_class']
                );
                ++$nb;
            }
        }
    }

    /**
     * @param array $rights
     * @param Tool  $tool
     */
    private function updateCustomToolRights(array $rights, Tool $tool)
    {
        $this->deleteCustomToolRights($tool);
        $this->persistCustomToolRights($rights, $tool);
    }

    /**
     * @param Tool $tool
     */
    private function deleteCustomToolRights(Tool $tool)
    {
        $customDecoders = $this->toolMaskManager->getCustomMaskDecodersByTool($tool);

        foreach ($customDecoders as $decoder) {
            $this->em->remove($decoder);
        }
        $this->em->flush();
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
