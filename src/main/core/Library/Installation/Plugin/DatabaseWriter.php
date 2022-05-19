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

use Claroline\AppBundle\Log\LoggableTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\DataSource;
use Claroline\CoreBundle\Entity\Plugin;
use Claroline\CoreBundle\Entity\Resource\MaskDecoder;
use Claroline\CoreBundle\Entity\Resource\MenuAction;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Template\TemplateType;
use Claroline\CoreBundle\Entity\Tool\AdminTool;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\Widget\Widget;
use Claroline\CoreBundle\Manager\Resource\MaskManager;
use Claroline\CoreBundle\Manager\Tool\ToolManager;
use Claroline\CoreBundle\Manager\Tool\ToolMaskDecoderManager;
use Claroline\CoreBundle\Repository\PluginRepository;
use Claroline\KernelBundle\Bundle\PluginBundleInterface;
use Claroline\ThemeBundle\Entity\Theme;
use Claroline\ThemeBundle\Manager\IconSetManager;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Filesystem\Filesystem;

/**
 * This class is used to save/delete a plugin and its possible dependencies (like
 * custom resource types) in the database.
 *
 * @todo break me into multiple writers (one for each config section).
 */
class DatabaseWriter implements LoggerAwareInterface
{
    use LoggableTrait;

    /** @var ObjectManager */
    private $em;
    /** @var MaskManager */
    private $mm;
    /** @var Filesystem */
    private $fileSystem;
    /** @var ToolManager */
    private $toolManager;
    /** @var ToolMaskDecoderManager */
    private $toolMaskManager;
    /** @var IconSetManager */
    private $iconSetManager;

    /** @var PluginRepository */
    private $pluginRepository;

    public function __construct(
        ObjectManager $em,
        MaskManager $mm,
        Filesystem $fileSystem,
        ToolManager $toolManager,
        ToolMaskDecoderManager $toolMaskManager,
        IconSetManager $iconSetManager
    ) {
        $this->em = $em;
        $this->mm = $mm;
        $this->fileSystem = $fileSystem;
        $this->toolManager = $toolManager;
        $this->toolMaskManager = $toolMaskManager;
        $this->iconSetManager = $iconSetManager;

        $this->pluginRepository = $this->em->getRepository(Plugin::class);
    }

    /**
     * Persists a plugin in the database.
     *
     * @return Plugin
     */
    public function insert(PluginBundleInterface $pluginBundle, array $pluginConfiguration)
    {
        $namespaceParts = explode('\\', $pluginBundle->getNamespace());

        $pluginEntity = new Plugin();
        $pluginEntity->setVendorName($namespaceParts[0]);
        $pluginEntity->setBundleName($namespaceParts[1]);

        $this->em->persist($pluginEntity);
        $this->persistConfiguration($pluginConfiguration, $pluginEntity, $pluginBundle);
        $this->em->flush();

        return $pluginEntity;
    }

    /**
     * @return Plugin|null
     *
     * @throws \Exception
     */
    public function update(PluginBundleInterface $pluginBundle, array $pluginConfiguration)
    {
        $namespaceParts = explode('\\', $pluginBundle->getNamespace());

        /** @var Plugin $plugin */
        $plugin = $this->pluginRepository->findOneBy([
             'vendorName' => $namespaceParts[0],
             'bundleName' => $namespaceParts[1],
        ]);

        if (null === $plugin) {
            $this->log('Unable to retrieve plugin for updating its configuration.', LogLevel::ERROR);

            return null;
        }

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
            ->getRepository(ResourceType::class)
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
     * @param array $processedConfiguration
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
            $this->updateTool($tool, $plugin);
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
        $mimeTypes = [];

        foreach ($processedConfiguration['resource_icons'] as $iconConfig) {
            $mimeTypes[$iconConfig['name']] = $iconConfig['mime_types'];
        }
        $this->iconSetManager->setLogger($this->logger);
        $this->iconSetManager->generateIconSets($pluginBundle->getResourcesIconsSetsFolder(), $mimeTypes);
    }

    /**
     * @param array $processedConfiguration
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
        $installedWidgets = $this->em->getRepository(Widget::class)
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
        $installedSources = $this->em->getRepository(DataSource::class)
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
        $installedAdminTools = $this->em->getRepository(AdminTool::class)
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

        $mimeTypes = [];

        foreach ($processedConfiguration['resource_icons'] as $iconConfig) {
            $mimeTypes[$iconConfig['name']] = $iconConfig['mime_types'];
        }
        $this->iconSetManager->setLogger($this->logger);
        $this->iconSetManager->generateIconSets($pluginBundle->getResourcesIconsSetsFolder(), $mimeTypes);
    }

    /**
     * @param array $resourceConfiguration
     *
     * @return ResourceType
     */
    private function updateResourceType($resourceConfiguration, Plugin $plugin, PluginBundleInterface $pluginBundle)
    {
        $this->log('Update the resource type : "'.$resourceConfiguration['name'].'".');

        $resourceType = $this->em->getRepository(ResourceType::class)
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
        $defaults = $this->mm->getDefaultResourceActionsMask();
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

        return $resourceType;
    }

    /**
     * @param array $toolConfiguration
     */
    private function updateTool($toolConfiguration, Plugin $plugin)
    {
        $tool = $this->em
            ->getRepository(Tool::class)
            ->findOneBy(['name' => $toolConfiguration['name']]);

        if (null === $tool) {
            $tool = new Tool();
        }

        $this->persistTool($toolConfiguration, $plugin, $tool);
        $this->updateCustomToolRights($toolConfiguration['tool_rights'], $tool);
    }

    /**
     * @param array $widgetConfiguration
     *
     * @return Widget
     */
    private function updateWidget($widgetConfiguration, Plugin $plugin)
    {
        /** @var Widget $widget */
        $widget = $this->em
            ->getRepository(Widget::class)
            ->findOneBy(['name' => $widgetConfiguration['name']]);

        if (is_null($widget)) {
            return $this->createWidget($widgetConfiguration, $plugin);
        } else {
            return $this->persistWidget($widgetConfiguration, $widget);
        }
    }

    public function persistResourceAction(array $action, Plugin $plugin)
    {
        // also remove duplicates if some are found
        $resourceType = null;
        if (!empty($action['resource_type'])) {
            /** @var ResourceType $resourceType */
            $resourceType = $this->em
                ->getRepository(ResourceType::class)
                ->findOneBy(['name' => $action['resource_type']]);
        }

        $this->log('Updating resource action '.$action['name']);

        // initializes the mask decoder if needed
        $this->mm->createDecoder($action['decoder'], $resourceType);

        /** @var MenuAction $resourceAction */
        $resourceAction = $this->em
            ->getRepository(MenuAction::class)
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

    public function updateResourceAction(array $action, Plugin $plugin)
    {
        $this->persistResourceAction($action, $plugin);
    }

    /**
     * @param array $resourceConfiguration
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

        return $resourceType;
    }

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
     * @param array $widgetConfiguration
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
     * @param array $widgetConfiguration
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
            ->getRepository(DataSource::class)
            ->findOneBy(['name' => $sourceConfiguration['name']]);

        if (is_null($source)) {
            return $this->createDataSource($sourceConfiguration, $plugin);
        } else {
            return $this->persistDataSource($sourceConfiguration, $source);
        }
    }

    /**
     * @param array $toolConfiguration
     */
    private function persistTool($toolConfiguration, Plugin $plugin, Tool $tool)
    {
        $this->log('Update the tool : "'.$toolConfiguration['name'].'".');

        $tool->setName($toolConfiguration['name']);
        $tool->setPlugin($plugin);
        $tool->setDisplayableInDesktop($toolConfiguration['is_displayable_in_desktop']);
        $tool->setDisplayableInWorkspace($toolConfiguration['is_displayable_in_workspace']);

        if (isset($toolConfiguration['class'])) {
            $tool->setClass("{$toolConfiguration['class']}");
        } else {
            $tool->setClass('tools');
        }

        $this->toolManager->setLogger($this->logger);
        $this->toolManager->create($tool);
        $this->persistCustomToolRights($toolConfiguration['tool_rights'], $tool);
    }

    /**
     * @param array $themeConfiguration
     */
    private function createTheme($themeConfiguration, Plugin $plugin)
    {
        $theme = new Theme();
        $this->persistTheme($themeConfiguration, $plugin, $theme);
    }

    /**
     * @param array $themeConfiguration
     */
    private function updateTheme($themeConfiguration, Plugin $plugin)
    {
        $theme = $this->em->getRepository(Theme::class)
            ->findOneBy(['name' => $themeConfiguration['name']]);

        if (null === $theme) {
            $theme = new Theme();
        }

        $this->persistTheme($themeConfiguration, $plugin, $theme);
    }

    /**
     * @param array $themeConfiguration
     */
    private function persistTheme($themeConfiguration, Plugin $plugin, Theme $theme)
    {
        $theme->setName($themeConfiguration['name']);
        $theme->setPlugin($plugin);
        $this->em->persist($theme);
    }

    /**
     * @param array $adminToolConfiguration
     */
    private function createAdminTool($adminToolConfiguration, Plugin $plugin)
    {
        $adminTool = new AdminTool();
        $this->persistAdminTool($adminToolConfiguration, $plugin, $adminTool);
    }

    /**
     * @param array $adminToolConfiguration
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
     * @param array $adminToolConfiguration
     */
    private function updateAdminTool($adminToolConfiguration, Plugin $plugin)
    {
        $adminTool = $this->em->getRepository(AdminTool::class)
            ->findOneBy(['name' => $adminToolConfiguration['name']]);

        if (null === $adminTool) {
            $adminTool = new AdminTool();
        }

        $this->persistAdminTool($adminToolConfiguration, $plugin, $adminTool);
    }

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
                    $value
                );
                ++$nb;
            }
        }
    }

    private function updateCustomToolRights(array $rights, Tool $tool)
    {
        $this->deleteCustomToolRights($tool);
        $this->persistCustomToolRights($rights, $tool);
    }

    private function deleteCustomToolRights(Tool $tool)
    {
        $customDecoders = $this->toolMaskManager->getCustomMaskDecodersByTool($tool);

        foreach ($customDecoders as $decoder) {
            $this->em->remove($decoder);
        }
        $this->em->flush();
    }

    /**
     * @param array $templateTypeConfiguration
     */
    private function createTemplateType($templateTypeConfiguration, Plugin $plugin)
    {
        $templateType = new TemplateType();
        $this->persistTemplateType($templateTypeConfiguration, $plugin, $templateType);
    }

    /**
     * @param array $templateTypeConfiguration
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
     * @param array $templateTypeConfiguration
     */
    private function persistTemplateType($templateTypeConfiguration, Plugin $plugin, TemplateType $templateType)
    {
        $templateType->setName($templateTypeConfiguration['name']);
        $templateType->setType($templateTypeConfiguration['type']);
        $templateType->setPlaceholders(isset($templateTypeConfiguration['placeholders']) ? $templateTypeConfiguration['placeholders'] : []);
        $templateType->setPlugin($plugin);
        $this->em->persist($templateType);
    }
}
