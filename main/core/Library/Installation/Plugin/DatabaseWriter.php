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

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\BundleRecorder\Log\LoggableTrait;
use Claroline\CoreBundle\Entity\DataSource;
use Claroline\CoreBundle\Entity\Plugin;
use Claroline\CoreBundle\Entity\Resource\MaskDecoder;
use Claroline\CoreBundle\Entity\Resource\MenuAction;
use Claroline\CoreBundle\Entity\Resource\ResourceIcon;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Template\TemplateType;
use Claroline\CoreBundle\Entity\Theme\Theme;
use Claroline\CoreBundle\Entity\Tool\AdminTool;
use Claroline\CoreBundle\Entity\Tool\PwsToolConfig;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\Tool\ToolMaskDecoder;
use Claroline\CoreBundle\Entity\Widget\Widget;
use Claroline\CoreBundle\Library\PluginBundleInterface;
use Claroline\CoreBundle\Manager\IconSetManager;
use Claroline\CoreBundle\Manager\Resource\MaskManager;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CoreBundle\Manager\ToolMaskDecoderManager;
use Claroline\CoreBundle\Repository\PluginRepository;
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
 *
 * @todo break me into multiple writers (one for each config section).
 */
class DatabaseWriter
{
    use LoggableTrait;

    private $em;
    private $mm;
    private $fileSystem;
    private $kernelRootDir;
    private $toolManager;
    private $toolMaskManager;
    private $iconSetManager;

    /** @var PluginRepository */
    private $pluginRepository;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "em"              = @DI\Inject("claroline.persistence.object_manager"),
     *     "mm"              = @DI\Inject("claroline.manager.mask_manager"),
     *     "fileSystem"      = @DI\Inject("filesystem"),
     *     "kernel"          = @DI\Inject("kernel"),
     *     "toolManager"     = @DI\Inject("claroline.manager.tool_manager"),
     *     "toolMaskManager" = @DI\Inject("claroline.manager.tool_mask_decoder_manager"),
     *     "iconSetManager"  = @DI\Inject("claroline.manager.icon_set_manager")
     * })
     *
     * @param ObjectManager          $em
     * @param Filesystem             $fileSystem
     * @param KernelInterface        $kernel
     * @param MaskManager            $mm
     * @param ToolManager            $toolManager
     * @param ToolMaskDecoderManager $toolMaskManager
     * @param IconSetManager         $iconSetManager
     */
    public function __construct(
        ObjectManager $em,
        Filesystem $fileSystem,
        KernelInterface $kernel,
        MaskManager $mm,
        ToolManager $toolManager,
        ToolMaskDecoderManager $toolMaskManager,
        IconSetManager $iconSetManager
    ) {
        $this->em = $em;
        $this->mm = $mm;
        $this->fileSystem = $fileSystem;
        $this->kernelRootDir = $kernel->getRootDir();
        $this->modifyTemplate = 'test' !== $kernel->getEnvironment();
        $this->toolManager = $toolManager;
        $this->toolMaskManager = $toolMaskManager;
        $this->iconSetManager = $iconSetManager;

        $this->pluginRepository = $this->em->getRepository('ClarolineCoreBundle:Plugin');
    }

    /**
     * Persists a plugin in the database.
     *
     * @param PluginBundleInterface $pluginBundle
     * @param array                 $pluginConfiguration
     *
     * @return Plugin
     */
    public function insert(PluginBundleInterface $pluginBundle, array $pluginConfiguration)
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
     * @param PluginBundleInterface $pluginBundle
     * @param array                 $pluginConfiguration
     *
     * @return Plugin|null
     *
     * @throws \Exception
     */
    public function update(PluginBundleInterface $pluginBundle, array $pluginConfiguration)
    {
        /** @var Plugin $plugin */
        $plugin = $this->pluginRepository->findOneBy([
             'vendorName' => $pluginBundle->getVendorName(),
             'bundleName' => $pluginBundle->getBundleName(),
        ]);

        if (null === $plugin) {
            $this->log('Unable to retrieve plugin for updating its configuration.', LogLevel::ERROR);

            return null;
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
        $plugin = $this->pluginRepository->findOneByBundleFQCN($pluginFqcn);
        // code below is for "re-parenting" the resources which depend on one
        // of the resource types the plugin might have declared

        /** @var ResourceType[] $resourceTypes */
        $resourceTypes = $this->em
            ->getRepository('ClarolineCoreBundle:Resource\ResourceType')
            ->findBy(['plugin' => $plugin->getGeneratedId()]);

        foreach ($resourceTypes as $resourceType) {
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
     * @param PluginBundleInterface $plugin
     *
     * @return bool
     */
    public function isSaved(PluginBundleInterface $plugin)
    {
        if (null !== $this->pluginRepository->findOneByBundleFQCN(get_class($plugin))) {
            return true;
        }

        return false;
    }

    /**
     * @param array                 $processedConfiguration
     * @param Plugin                $plugin
     * @param PluginBundleInterface $pluginBundle
     */
    private function persistConfiguration($processedConfiguration, Plugin $plugin, PluginBundleInterface $pluginBundle)
    {
        foreach ($processedConfiguration['resources'] as $resource) {
            $this->persistResourceType($resource, $plugin, $pluginBundle);
        }

        foreach ($processedConfiguration['resource_actions'] as $resourceAction) {
            $this->persistResourceAction($resourceAction, $plugin);
        }

        foreach ($processedConfiguration['widgets'] as $widget) {
            $this->createWidget($widget, $plugin);
        }

        foreach ($processedConfiguration['data_sources'] as $source) {
            $this->createDataSource($source, $plugin);
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

        foreach ($processedConfiguration['templates'] as $templateType) {
            $this->createTemplateType($templateType, $plugin);
        }
    }

    /**
     * @param array                 $processedConfiguration
     * @param Plugin                $plugin
     * @param PluginBundleInterface $pluginBundle
     */
    private function updateConfiguration($processedConfiguration, Plugin $plugin, PluginBundleInterface $pluginBundle)
    {
        foreach ($processedConfiguration['resources'] as $resourceConfiguration) {
            $this->updateResourceType($resourceConfiguration, $plugin, $pluginBundle);
        }

        foreach ($processedConfiguration['resource_actions'] as $resourceAction) {
            $this->updateResourceAction($resourceAction, $plugin);
        }

        foreach ($processedConfiguration['widgets'] as $widgetConfiguration) {
            $this->updateWidget($widgetConfiguration, $plugin);
        }

        foreach ($processedConfiguration['data_sources'] as $sourceConfiguration) {
            $this->updateDataSource($sourceConfiguration, $plugin);
        }

        foreach ($processedConfiguration['tools'] as $toolConfiguration) {
            $this->updateTool($toolConfiguration, $plugin);
        }

        foreach ($processedConfiguration['themes'] as $themeConfiguration) {
            $this->updateTheme($themeConfiguration, $plugin);
        }

        // cleans deleted widgets

        /** @var Widget[] $installedWidgets */
        $installedWidgets = $this->em->getRepository('ClarolineCoreBundle:Widget\Widget')
            ->findBy(['plugin' => $plugin]);
        $widgetNames = array_map(function ($widget) {
            return $widget['name'];
        }, $processedConfiguration['widgets']);

        $widgetsToDelete = array_filter($installedWidgets, function (Widget $widget) use ($widgetNames) {
            return !in_array($widget->getName(), $widgetNames);
        });

        foreach ($widgetsToDelete as $widget) {
            $this->log('Removing widget '.$widget->getName());
            $this->em->remove($widget);
        }

        // cleans deleted data sources
        $installedSources = $this->em->getRepository('ClarolineCoreBundle:DataSource')
            ->findBy(['plugin' => $plugin]);
        $sourceNames = array_map(function ($source) {
            return $source['name'];
        }, $processedConfiguration['data_sources']);

        $sourcesToDelete = array_filter($installedSources, function (DataSource $source) use ($sourceNames) {
            return !in_array($source->getName(), $sourceNames);
        });

        foreach ($sourcesToDelete as $source) {
            $this->log('Removing data source '.$source->getName());
            $this->em->remove($source);
        }

        // cleans deleted admin tools
        /** @var AdminTool[] $installedAdminTools */
        $installedAdminTools = $this->em->getRepository('ClarolineCoreBundle:Tool\AdminTool')
          ->findBy(['plugin' => $plugin]);
        $adminTools = $processedConfiguration['admin_tools'];
        $adminToolNames = array_map(function ($adminTool) {
            return $adminTool['name'];
        }, $adminTools);

        $toRemove = array_filter($installedAdminTools, function (AdminTool $adminTool) use ($adminToolNames) {
            return !in_array($adminTool->getName(), $adminToolNames);
        });

        foreach ($toRemove as $tool) {
            $this->log('Removing tool '.$tool->getName());
            $this->em->remove($tool);
        }

        foreach ($adminTools as $adminTool) {
            $this->updateAdminTool($adminTool, $plugin);
        }

        foreach ($processedConfiguration['templates'] as $templateType) {
            $this->updateTemplateType($templateType, $plugin);
        }
    }

    /**
     * @param array                 $resourceConfiguration
     * @param Plugin                $plugin
     * @param PluginBundleInterface $pluginBundle
     *
     * @return ResourceType
     */
    private function updateResourceType($resourceConfiguration, Plugin $plugin, PluginBundleInterface $pluginBundle)
    {
        $this->log('Update the resource type : "'.$resourceConfiguration['name'].'".');

        $resourceType = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceType')
            ->findOneBy(['name' => $resourceConfiguration['name']]);

        if (null === $resourceType) {
            $resourceType = new ResourceType();
            $resourceType->setName($resourceConfiguration['name']);
        }

        $resourceType->setClass($resourceConfiguration['class']);
        $resourceType->setTags($resourceConfiguration['tags']);
        $resourceType->setPlugin($plugin);
        $resourceType->setExportable($resourceConfiguration['exportable']);
        $this->em->persist($resourceType);

        if (!$this->mm->hasMenuAction($resourceType)) {
            $this->mm->addDefaultPerms($resourceType);
        }

        $newActions = [];
        if (!empty($resourceConfiguration['actions'])) {
            foreach ($resourceConfiguration['actions'] as $resourceAction) {
                $newActions[] = $resourceAction['decoder'];
                $this->updateResourceAction(array_merge($resourceAction, [
                    'resource_type' => $resourceType->getName(),
                ]), $plugin);
            }
        }

        $permissionMap = $this->mm->getPermissionMap($resourceType);
        $defaults = $this->mm->getDefaultResourceActionsMask($resourceType);
        $oldActions = array_filter($permissionMap, function ($name) use ($defaults) {
            return !in_array($name, array_keys($defaults));
        });

        $toRemove = array_filter($oldActions, function ($action) use ($newActions) {
            return !in_array($action, $newActions);
        });

        foreach ($toRemove as $el) {
            $mask = $this->em->getRepository(MaskDecoder::class)->findOneBy(['resourceType' => $resourceType, 'name' => $el]);
            $this->log('Remove mask decoder '.$el, LogLevel::ERROR);
            $this->em->remove($mask);
        }

        $this->em->flush();

        $this->updateIcons($resourceConfiguration, $resourceType, $pluginBundle);

        return $resourceType;
    }

    /**
     * @param array  $toolConfiguration
     * @param Plugin $plugin
     */
    private function updateTool($toolConfiguration, Plugin $plugin)
    {
        $tool = $this->em
            ->getRepository('ClarolineCoreBundle:Tool\Tool')
            ->findOneBy(['name' => $toolConfiguration['name']]);

        if (null === $tool) {
            $tool = new Tool();
        }

        $this->persistTool($toolConfiguration, $plugin, $tool);
        $this->updateCustomToolRights($toolConfiguration['tool_rights'], $tool);
    }

    /**
     * @param array  $widgetConfiguration
     * @param Plugin $plugin
     *
     * @return Widget
     */
    private function updateWidget($widgetConfiguration, Plugin $plugin)
    {
        /** @var Widget $widget */
        $widget = $this->em
            ->getRepository('ClarolineCoreBundle:Widget\Widget')
            ->findOneBy(['name' => $widgetConfiguration['name']]);

        if (is_null($widget)) {
            return $this->createWidget($widgetConfiguration, $plugin);
        } else {
            return $this->persistWidget($widgetConfiguration, $widget);
        }
    }

    /**
     * @param array                 $resource
     * @param ResourceType          $resourceType
     * @param PluginBundleInterface $pluginBundle
     */
    private function persistIcons(array $resource, ResourceType $resourceType, PluginBundleInterface $pluginBundle)
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
                ->findOneBy(['mimeType' => 'custom/default']);
            $resourceIcon->setRelativeUrl($defaultIcon->getRelativeUrl());
        }

        $resourceIcon->setUuid(uniqid('', true));
        $resourceIcon->setShortcut(false);
        $this->em->persist($resourceIcon);
        $this->em->flush(); // icon set manager requires the new icon to be flushed (it expects an id)

        // Also add the new resource type icon to default resource icon set
        $this->iconSetManager->addOrUpdateIconItemToDefaultResourceIconSet($resourceIcon);
    }

    /**
     * @param array                 $resource
     * @param ResourceType          $resourceType
     * @param PluginBundleInterface $pluginBundle
     */
    private function updateIcons(array $resource, ResourceType $resourceType, PluginBundleInterface $pluginBundle)
    {
        $resourceIcon = $this->em
            ->getRepository('ClarolineCoreBundle:Resource\ResourceIcon')
            ->findOneBy(['mimeType' => 'custom/'.$resourceType->getName()]);
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
                ->findOneBy(['mimeType' => 'custom/default']);
            $newRelativeUrl = $defaultIcon->getRelativeUrl();
        }
        // If icon is new, create it and persist it to db
        if ($isNew) {
            $resourceIcon->setRelativeUrl($newRelativeUrl);
            $resourceIcon->setShortcut(false);
            $this->em->persist($resourceIcon);
            $this->em->flush(); // icon set manager requires the new icon to be flushed (it expects an id)
        }
        // Also add/update the resource type icon to default resource icon set
        $this->iconSetManager->addOrUpdateIconItemToDefaultResourceIconSet(
            $resourceIcon,
            $newRelativeUrl
        );
    }

    /**
     * @param array  $action
     * @param Plugin $plugin
     */
    public function persistResourceAction(array $action, Plugin $plugin)
    {
        // also remove duplicates if some are found
        $resourceType = null;
        if (!empty($action['resource_type'])) {
            /** @var ResourceType $resourceType */
            $resourceType = $this->em
                ->getRepository('ClarolineCoreBundle:Resource\ResourceType')
                ->findOneBy(['name' => $action['resource_type']]);
        }

        $this->log('Updating resource action '.$action['name']);

        // initializes the mask decoder if needed
        $this->mm->createDecoder($action['decoder'], $resourceType);

        /** @var MenuAction $resourceAction */
        $resourceAction = $this->em
            ->getRepository('ClarolineCoreBundle:Resource\MenuAction')
            ->findOneBy(['name' => $action['name'], 'resourceType' => $resourceType]);

        if (!$resourceAction) {
            $resourceAction = new MenuAction();
        }

        $resourceAction->setName($action['name']);
        $resourceAction->setPlugin($plugin);
        $resourceAction->setDecoder($action['decoder']);
        $resourceAction->setGroup($action['group']);
        $resourceAction->setScope($action['scope']);
        $resourceAction->setApi($action['api']);
        $resourceAction->setDefault($action['default']);
        $resourceAction->setResourceType($resourceType);

        $this->em->persist($resourceAction);
        $this->em->flush();
    }

    /**
     * @param array  $action
     * @param Plugin $plugin
     */
    public function updateResourceAction(array $action, Plugin $plugin)
    {
        $this->persistResourceAction($action, $plugin);
    }

    /**
     * @param array                 $resourceConfiguration
     * @param Plugin                $plugin
     * @param PluginBundleInterface $pluginBundle
     *
     * @return ResourceType
     */
    private function persistResourceType($resourceConfiguration, Plugin $plugin, PluginBundleInterface $pluginBundle)
    {
        $this->log('Adding resource type '.$resourceConfiguration['name']);
        $resourceType = new ResourceType();
        $resourceType->setName($resourceConfiguration['name']);
        $resourceType->setClass($resourceConfiguration['class']);
        $resourceType->setExportable($resourceConfiguration['exportable']);
        $resourceType->setTags($resourceConfiguration['tags']);
        $resourceType->setPlugin($plugin);
        $this->em->persist($resourceType);
        $this->mm->addDefaultPerms($resourceType);

        if (!empty($resourceConfiguration['actions'])) {
            foreach ($resourceConfiguration['actions'] as $resourceAction) {
                $this->persistResourceAction(array_merge($resourceAction, [
                    'resource_type' => $resourceType->getName(),
                ]), $plugin);
            }
        }

        $this->setResourceTypeDefaultMask($resourceConfiguration['default_rights'], $resourceType);
        $this->persistIcons($resourceConfiguration, $resourceType, $pluginBundle);

        return $resourceType;
    }

    /**
     * @param array        $rightsName
     * @param ResourceType $resourceType
     */
    private function setResourceTypeDefaultMask(array $rightsName, ResourceType $resourceType)
    {
        $mask = 0 === count($rightsName) ? 1 : 0;
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
     * @param array  $widgetConfiguration
     * @param Plugin $plugin
     *
     * @return Widget
     */
    private function createWidget($widgetConfiguration, Plugin $plugin)
    {
        $widget = new Widget();
        $widget->setPlugin($plugin);

        $this->persistWidget($widgetConfiguration, $widget);

        return $widget;
    }

    /**
     * @param array  $widgetConfiguration
     * @param Widget $widget
     *
     * @return Widget
     */
    private function persistWidget($widgetConfiguration, Widget $widget)
    {
        $widget->setName($widgetConfiguration['name']);
        $widget->setContext(isset($widgetConfiguration['context']) ? $widgetConfiguration['context'] : []);
        $widget->setSources(isset($widgetConfiguration['sources']) ? $widgetConfiguration['sources'] : []);
        $widget->setClass(isset($widgetConfiguration['class']) ? $widgetConfiguration['class'] : null);
        $widget->setExportable($widgetConfiguration['exportable']);
        $widget->setTags(isset($widgetConfiguration['tags']) ? $widgetConfiguration['tags'] : []);

        $this->em->persist($widget);

        return $widget;
    }

    private function createDataSource($sourceConfiguration, Plugin $plugin)
    {
        $source = new DataSource();
        $source->setPlugin($plugin);

        $this->persistDataSource($sourceConfiguration, $source);

        return $source;
    }

    private function persistDataSource($sourceConfiguration, DataSource $source)
    {
        $source->setName($sourceConfiguration['name']);
        $source->setType($sourceConfiguration['type']);
        $source->setContext(isset($sourceConfiguration['context']) ? $sourceConfiguration['context'] : []);
        $source->setTags(isset($sourceConfiguration['tags']) ? $sourceConfiguration['tags'] : []);

        $this->em->persist($source);

        return $source;
    }

    private function updateDataSource($sourceConfiguration, Plugin $plugin)
    {
        /** @var DataSource $source */
        $source = $this->em
            ->getRepository('ClarolineCoreBundle:DataSource')
            ->findOneBy(['name' => $sourceConfiguration['name']]);

        if (is_null($source)) {
            return $this->createDataSource($sourceConfiguration, $plugin);
        } else {
            return $this->persistDataSource($sourceConfiguration, $source);
        }
    }

    /**
     * @param array  $toolConfiguration
     * @param Plugin $plugin
     */
    private function createTool($toolConfiguration, Plugin $plugin)
    {
        $tool = new Tool();
        $this->persistTool($toolConfiguration, $plugin, $tool);

        /** @var Role $roleUser */
        $roleUser = $this->em->getRepository('ClarolineCoreBundle:Role')->findOneBy(['name' => 'ROLE_USER']);
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
        $this->log('Update the tool : "'.$toolConfiguration['name'].'".');

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
            $tool->setClass('tools');
        }
        if (isset($toolConfiguration['desktop_category'])) {
            $tool->setDesktopCategory("{$toolConfiguration['desktop_category']}");
        }

        $this->toolManager->setLogger($this->logger);
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
            ->findOneBy(['name' => $themeConfiguration['name']]);

        if (null === $theme) {
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
        $this->log('Update the administration tool : "'.$adminToolConfiguration['name'].'".');
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
            ->findOneBy(['name' => $adminToolConfiguration['name']]);

        if (null === $adminTool) {
            $adminTool = new AdminTool();
        }

        $this->persistAdminTool($adminToolConfiguration, $plugin, $adminTool);
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

    /**
     * @param array  $templateTypeConfiguration
     * @param Plugin $plugin
     */
    private function createTemplateType($templateTypeConfiguration, Plugin $plugin)
    {
        $templateType = new TemplateType();
        $this->persistTemplateType($templateTypeConfiguration, $plugin, $templateType);
    }

    /**
     * @param array  $templateTypeConfiguration
     * @param Plugin $plugin
     */
    private function updateTemplateType($templateTypeConfiguration, Plugin $plugin)
    {
        $templateType = $this->em->getRepository(TemplateType::class)
            ->findOneBy(['name' => $templateTypeConfiguration['name']]);

        if (null === $templateType) {
            $templateType = new TemplateType();
        }

        $this->persistTemplateType($templateTypeConfiguration, $plugin, $templateType);
    }

    /**
     * @param array        $templateTypeConfiguration
     * @param Plugin       $plugin
     * @param TemplateType $templateType
     */
    private function persistTemplateType($templateTypeConfiguration, Plugin $plugin, TemplateType $templateType)
    {
        $templateType->setName($templateTypeConfiguration['name']);
        $templateType->setPlaceholders(isset($templateTypeConfiguration['placeholders']) ? $templateTypeConfiguration['placeholders'] : []);
        $templateType->setPlugin($plugin);
        $this->em->persist($templateType);
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
