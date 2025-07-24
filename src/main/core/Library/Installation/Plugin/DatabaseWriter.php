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
use Claroline\CoreBundle\Entity\Plugin;
use Claroline\CoreBundle\Entity\Resource\MaskDecoder;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\Tool\ToolMaskDecoder;
use Claroline\CoreBundle\Entity\Widget\Widget;
use Claroline\CoreBundle\Manager\Resource\MaskManager;
use Claroline\CoreBundle\Manager\Tool\ToolMaskDecoderManager;
use Claroline\CoreBundle\Repository\PluginRepository;
use Claroline\KernelBundle\Bundle\PluginBundleInterface;
use Claroline\ThemeBundle\Entity\Theme;
use Claroline\ThemeBundle\Manager\IconSetBuilderManager;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * This class is used to save/delete a plugin and its possible dependencies (like
 * custom resource types) in the database.
 */
class DatabaseWriter implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private PluginRepository $pluginRepository;

    public function __construct(
        private readonly ObjectManager $em,
        private readonly MaskManager $mm,
        private readonly ToolMaskDecoderManager $toolMaskManager,
        private readonly IconSetBuilderManager $iconSetManager
    ) {
        $this->pluginRepository = $this->em->getRepository(Plugin::class);
    }

    /**
     * Persists a plugin in the database.
     */
    public function insert(PluginBundleInterface $pluginBundle, array $pluginConfiguration): Plugin
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

    public function update(PluginBundleInterface $pluginBundle, array $pluginConfiguration): ?Plugin
    {
        $namespaceParts = explode('\\', $pluginBundle->getNamespace());

        /** @var Plugin $plugin */
        $plugin = $this->pluginRepository->findOneBy([
            'vendorName' => $namespaceParts[0],
            'bundleName' => $namespaceParts[1],
        ]);

        if (null === $plugin) {
            $this->logger->error('Unable to retrieve plugin for updating its configuration.');

            return null;
        }

        $this->logger->debug('Configuration was retrieved: updating...');

        $this->em->persist($plugin);
        $this->updateConfiguration($pluginConfiguration, $plugin, $pluginBundle);
        $this->em->flush();

        return $plugin;
    }

    /**
     * Removes a plugin from the database.
     */
    public function delete(string $pluginFqcn): void
    {
        $plugin = $this->pluginRepository->findOneByBundleFQCN($pluginFqcn);

        // deletion of other plugin db dependencies is made via a cascade mechanism
        $this->em->remove($plugin);
        $this->em->flush();
    }

    /**
     * Checks if a plugin is persisted in the database.
     */
    public function isSaved(PluginBundleInterface $plugin): bool
    {
        if (null !== $this->pluginRepository->findOneByBundleFQCN(get_class($plugin))) {
            return true;
        }

        return false;
    }

    private function persistConfiguration(array $processedConfiguration, Plugin $plugin, PluginBundleInterface $pluginBundle): void
    {
        foreach ($processedConfiguration['resources'] as $resource) {
            $this->persistResourceType($resource, $plugin);
        }

        foreach ($processedConfiguration['widgets'] as $widget) {
            $this->createWidget($widget, $plugin);
        }

        foreach ($processedConfiguration['tools'] as $tool) {
            $this->persistTool($tool, $plugin);
        }

        foreach ($processedConfiguration['themes'] as $theme) {
            $this->createTheme($theme, $plugin);
        }

        $mimeTypes = [];
        foreach ($processedConfiguration['resource_icons'] as $iconConfig) {
            $mimeTypes[$iconConfig['name']] = $iconConfig['mime_types'];
        }
        $this->iconSetManager->generateFromPlugin($pluginBundle->getPath(), $mimeTypes);
    }

    private function updateConfiguration(array $processedConfiguration, Plugin $plugin, PluginBundleInterface $pluginBundle): void
    {
        foreach ($processedConfiguration['resources'] as $resourceConfiguration) {
            $this->updateResourceType($resourceConfiguration, $plugin);
        }

        foreach ($processedConfiguration['widgets'] as $widgetConfiguration) {
            $this->updateWidget($widgetConfiguration, $plugin);
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
            $this->logger->debug('Removing widget '.$widget->getName());
            $this->em->remove($widget);
        }

        $mimeTypes = [];
        foreach ($processedConfiguration['resource_icons'] as $iconConfig) {
            $mimeTypes[$iconConfig['name']] = $iconConfig['mime_types'];
        }
        $this->iconSetManager->generateFromPlugin($pluginBundle->getPath(), $mimeTypes);
    }

    private function updateWidget(array $widgetConfiguration, Plugin $plugin): Widget
    {
        /** @var Widget $widget */
        $widget = $this->em
            ->getRepository(Widget::class)
            ->findOneBy(['name' => $widgetConfiguration['name']]);

        if (is_null($widget)) {
            return $this->createWidget($widgetConfiguration, $plugin);
        }

        return $this->persistWidget($widgetConfiguration, $widget);
    }

    private function createWidget(array $widgetConfiguration, Plugin $plugin): Widget
    {
        $widget = new Widget();
        $widget->setPlugin($plugin);

        $this->persistWidget($widgetConfiguration, $widget);

        return $widget;
    }

    private function persistWidget(array $widgetConfiguration, Widget $widget): Widget
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

    private function persistTool(array $toolConfiguration, Plugin $plugin): void
    {
        $this->logger->debug('Adding tool: '.$toolConfiguration['name']);

        $tool = new Tool();
        $tool->setName($toolConfiguration['name']);
        $tool->setPlugin($plugin);
        $tool->setIcon($toolConfiguration['icon']);
        $this->em->persist($tool);

        $this->toolMaskManager->createDefaultToolMaskDecoders($tool->getName());
        if (!empty($toolConfiguration['tool_rights'])) {
            $this->toolMaskManager->createCustomToolMaskDecoders($tool->getName(), $toolConfiguration['tool_rights']);
        }
    }

    private function updateTool(array $toolConfiguration, Plugin $plugin): void
    {
        $this->logger->debug(sprintf('Updating the tool: "%s".', $toolConfiguration['name']));

        $tool = $this->em
            ->getRepository(Tool::class)
            ->findOneBy(['name' => $toolConfiguration['name']]);

        if (null === $tool) {
            $tool = new Tool();
        }

        $tool->setName($toolConfiguration['name']);
        $tool->setPlugin($plugin);
        $tool->setIcon($toolConfiguration['icon']);

        $this->em->persist($tool);

        $this->toolMaskManager->createDefaultToolMaskDecoders($tool->getName());

        $newActions = [];
        if (!empty($toolConfiguration['tool_rights'])) {
            $newActions = $toolConfiguration['tool_rights'];
            $this->toolMaskManager->createCustomToolMaskDecoders($tool->getName(), $newActions);
        }

        $oldActions = [];
        foreach ($this->toolMaskManager->getDecoders($tool->getName()) as $decoder) {
            if (!in_array($decoder->getName(), ToolMaskDecoder::DEFAULT_ACTIONS)) {
                $oldActions[] = $decoder->getName();
            }
        }

        $toRemove = array_filter($oldActions, function (string $actionName) use ($newActions) {
            return !in_array($actionName, $newActions);
        });

        foreach ($toRemove as $el) {
            $this->logger->debug('Removing the tool right: '.$el);
            $this->toolMaskManager->removeToolMaskDecoder($tool->getName(), $el);
        }
    }

    private function persistResourceType(array $resourceConfiguration, Plugin $plugin): void
    {
        $this->logger->debug('Adding resource type: '.$resourceConfiguration['name']);

        $resourceType = new ResourceType();
        $resourceType->setName($resourceConfiguration['name']);
        $resourceType->setClass($resourceConfiguration['class']);
        $resourceType->setPlugin($plugin);
        $this->em->persist($resourceType);

        $this->mm->createDefaultResourceMaskDecoders($resourceType);

        if (!empty($resourceConfiguration['resource_rights'])) {
            $this->mm->createCustomResourceMaskDecoders($resourceType, $resourceConfiguration['resource_rights']);
        }
    }

    private function updateResourceType(array $resourceConfiguration, Plugin $plugin): void
    {
        $this->logger->debug(sprintf('Updating the resource type: %s.', $resourceConfiguration['name']));

        $resourceType = $this->em->getRepository(ResourceType::class)
            ->findOneBy(['name' => $resourceConfiguration['name']]);

        if (null === $resourceType) {
            $resourceType = new ResourceType();
            $resourceType->setName($resourceConfiguration['name']);
        }

        $resourceType->setClass($resourceConfiguration['class']);
        $resourceType->setPlugin($plugin);

        $this->em->persist($resourceType);

        $this->mm->createDefaultResourceMaskDecoders($resourceType);

        $newActions = [];
        if (!empty($resourceConfiguration['resource_rights'])) {
            $newActions = $resourceConfiguration['resource_rights'];
            $this->mm->createCustomResourceMaskDecoders($resourceType, $newActions);
        }

        $oldActions = [];
        foreach ($this->mm->getDecoders($resourceType) as $decoder) {
            if (!in_array($decoder->getName(), MaskDecoder::DEFAULT_ACTIONS)) {
                $oldActions[] = $decoder->getName();
            }
        }

        $toRemove = array_filter($oldActions, function (string $actionName) use ($newActions) {
            return !in_array($actionName, $newActions);
        });

        foreach ($toRemove as $el) {
            $this->logger->debug('Removing the resource right: '.$el);
            $this->mm->removeResourceMaskDecoder($resourceType, $el);
        }
    }

    private function createTheme(array $themeConfiguration, Plugin $plugin): void
    {
        $theme = new Theme();
        $this->persistTheme($themeConfiguration, $plugin, $theme);
    }

    private function updateTheme(array $themeConfiguration, Plugin $plugin): void
    {
        $theme = $this->em->getRepository(Theme::class)
            ->findOneBy(['name' => $themeConfiguration['name']]);

        if (null === $theme) {
            $theme = new Theme();
        }

        $this->persistTheme($themeConfiguration, $plugin, $theme);
    }

    private function persistTheme(array $themeConfiguration, Plugin $plugin, Theme $theme): void
    {
        $theme->setName($themeConfiguration['name']);
        $theme->setPlugin($plugin);
        $this->em->persist($theme);
    }
}
