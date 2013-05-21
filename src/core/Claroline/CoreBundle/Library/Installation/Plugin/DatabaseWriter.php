<?php

namespace Claroline\CoreBundle\Library\Installation\Plugin;

use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Library\PluginBundle;
use Claroline\CoreBundle\Library\Resource\IconCreator;
use Claroline\CoreBundle\Library\Workspace\TemplateBuilder;
use Claroline\CoreBundle\Entity\Plugin;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Resource\ResourceIcon;
use Claroline\CoreBundle\Entity\Resource\IconType;
use Claroline\CoreBundle\Entity\Resource\ResourceTypeCustomAction;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\Widget\Widget;
use Claroline\CoreBundle\Entity\Widget\DisplayConfig;
use Symfony\Component\Filesystem\Filesystem;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * This class is used to save/delete a plugin an its possible dependencies (like
 * custom resource types) in the database.
 *
 * @DI\Service("claroline.plugin.recorder_database_writer")
 */
class DatabaseWriter
{
    private $em;
    private $im;
    private $fileSystem;
    private $kernelRootDir;
    private $templateBuilder;
    private $templateDir;

    /**
     * Constructor.
     *
     * @param SymfonyValidator  $validator
     * @param EntityManager     $em
     * @param IconCreator       $im
     * @param string            $kernelRootDir
     * @param string            $templateDir
     *
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "im" = @DI\Inject("claroline.resource.icon_creator"),
     *     "fileSystem" = @DI\Inject("filesystem"),
     *     "kernelRootDir" = @DI\Inject("%kernel.root_dir%"),
     *     "templateDir" = @DI\Inject("%claroline.param.templates_directory%")
     * })
     */
    public function __construct(
        EntityManager $em,
        IconCreator $im,
        Filesystem $fileSystem,
        $kernelRootDir,
        $templateDir
    )
    {
        $this->em = $em;
        $this->im = $im;
        $this->fileSystem = $fileSystem;
        $this->kernelRootDir = $kernelRootDir;
        $this->templateDir = $templateDir;
    }

    /**
     * Persists a plugin in the database.
     *
     * @param PluginBundle $plugin
     */
    public function insert(PluginBundle $plugin, array $pluginConfiguration)
    {
        $this->templateBuilder = TemplateBuilder::fromTemplate($this->templateDir."default.zip");
        $pluginEntity = new Plugin();
        $pluginEntity->setVendorName($plugin->getVendorName());
        $pluginEntity->setBundleName($plugin->getBundleName());
        $pluginEntity->setHasOptions($pluginConfiguration['has_options']);

        if (isset($pluginConfiguration['icon'])) {
            $ds = DIRECTORY_SEPARATOR;
            $iconWebDir = "bundles{$ds}{$plugin->getAssetsFolder()}{$ds}images{$ds}icons";
            $pluginEntity->setIcon("{$iconWebDir}{$ds}{$pluginConfiguration['icon']}");
        } else {
            $defaultIcon = $this->em
                ->getRepository('ClarolineCoreBundle:Resource\ResourceIcon')
                ->findOneBy(array('iconType' => IconType::DEFAULT_ICON));
            $pluginEntity->setIcon($defaultIcon->getRelativeUrl());
        }

        $this->em->persist($pluginEntity);
        $this->persistConfiguration($pluginConfiguration, $pluginEntity, $plugin);
        $this->em->flush();
        $this->templateBuilder->write();
    }

    /**
     * Removes a plugin from the database.
     *
     * @param string $pluginFqcn
     */
    public function delete($pluginFqcn)
    {
        $this->templateBuilder = TemplateBuilder::fromTemplate($this->templateDir."default.zip");
        $plugin = $this->getPluginEntity($pluginFqcn);
        // code below is for "re-parenting" the resources which depend on one
        // of the resource types the plugin might have declared
        $resourceTypes = $this->em
            ->getRepository('ClarolineCoreBundle:Resource\ResourceType')
            ->findByPlugin($plugin->getGeneratedId());

        foreach ($resourceTypes as $resourceType) {
            if (null !== $resourceType) {
                if (null !== $parentType = $resourceType->getParent()) {
                    $resources = $this->em
                        ->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
                        ->findByResourceType($resourceType->getId());

                    foreach ($resources as $resource) {
                        $resource->setResourceType($parentType);
                    }
                }
            }
        }

        foreach ($resourceTypes as $resourceType) {
            $this->templateBuilder->removeResourceType($resourceType->getName());
        }

        $tools = $this->em
            ->getRepository('ClarolineCoreBundle:Tool\Tool')
            ->findByPlugin($plugin->getGeneratedId());

        foreach ($tools as $tool) {
            $this->templateBuilder->removeTool($tool->getName());
        }

        $widgets = $this->em
            ->getRepository('ClarolineCoreBundle:Widget\Widget')
            ->findByPlugin($plugin->getGeneratedId());

        foreach ($widgets as $widget) {
            $this->templateBuilder->removeWidget($widget->getName());
        }

        // deletion of other plugin db dependencies is made via a cascade mechanism
        $this->em->remove($plugin);
        $this->em->flush();
        $this->templateBuilder->write();
    }

    /**
     * Checks if a plugin is persited in the database.
     *
     * @param string $pluginFqcn
     *
     * @return boolean
     */
    public function isSaved($pluginFqcn)
    {
        if ($this->getPluginEntity($pluginFqcn) !== null) {
            return true;
        }

        return false;
    }

    private function getPluginEntity($pluginFqcn)
    {
        $entity = $this->em
            ->getRepository('ClarolineCoreBundle:Plugin')
            ->findOneByBundleFQCN($pluginFqcn);

        return $entity;
    }

    private function persistConfiguration($processedConfiguration, $pluginEntity, $plugin)
    {
        foreach ($processedConfiguration['resources'] as $resource) {
            $this->persistResourceTypes($resource, $pluginEntity, $plugin);
        }

        foreach ($processedConfiguration['widgets'] as $widget) {
            $this->persistWidget($widget, $pluginEntity, $plugin);
        }

        foreach ($processedConfiguration['tools'] as $tool) {
            $this->persistTool($tool, $pluginEntity);
        }
    }

    private function persistIcons(array $resource, ResourceType $resourceType, PluginBundle $plugin)
    {
        $resourceIcon = new ResourceIcon();

        $defaultIcon = $this->em
            ->getRepository('ClarolineCoreBundle:Resource\ResourceIcon')
            ->findOneBy(array('iconType' => IconType::DEFAULT_ICON));
        $defaultIconType = $this->em
            ->getRepository('ClarolineCoreBundle:Resource\IconType')
            ->findOneBy(array('type' => 'type'));
        $resourceIcon->setIconType($defaultIconType);
        $resourceIcon->setType($resourceType->getName());
        $ds = DIRECTORY_SEPARATOR;

        if (isset($resource['icon'])) {
            $webBundleDir = "{$this->kernelRootDir}{$ds}..{$ds}web{$ds}bundles";
            $webPluginDir = "{$webBundleDir}{$ds}{$plugin->getAssetsFolder()}";
            $webPluginImgDir = "{$webPluginDir}{$ds}images";
            $webPluginIcoDir = "{$webPluginImgDir}{$ds}icons";
            $this->fileSystem->mkdir(array($webBundleDir, $webPluginDir, $webPluginImgDir, $webPluginIcoDir));
            $this->fileSystem->copy(
                "{$plugin->getImgFolder()}{$ds}{$resource['icon']}",
                "{$webPluginIcoDir}{$ds}{$resource['icon']}"
            );
            $resourceIcon->setIconLocation("{$webPluginIcoDir}{$ds}{$resource['icon']}");
            $resourceIcon->setRelativeUrl(
                "bundles/{$plugin->getAssetsFolder()}/images/icons/{$resource['icon']}"
            );
        } else {
            $resourceIcon->setIconLocation($defaultIcon->getIconLocation());
            $resourceIcon->setRelativeUrl($defaultIcon->getRelativeUrl());
        }

        $resourceIcon->setShortcut(false);
        $this->em->persist($resourceIcon);
        $this->im->createShortcutIcon($resourceIcon);
    }

    private function persistCustomAction($actions, $resourceType)
    {
        foreach ($actions as $action) {
            $rtca = new ResourceTypeCustomAction();
            $rtca->setAsync(!$action['is_action_in_new_page']);
            $rtca->setAction($action['name']);
            $rtca->setResourceType($resourceType);
            $this->em->persist($rtca);
        }
    }

    private function persistResourceTypes($resource, $pluginEntity, $plugin)
    {
        $resourceType = new ResourceType();
        $resourceType->setName($resource['name']);
        $resourceType->setBrowsable($resource['is_browsable']);
        $resourceType->setExportable($resource['is_exportable']);
        $resourceType->setPlugin($pluginEntity);
        $resourceClass = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceType')
            ->findOneBy(array('class' => $resource['class']));

        if (null === $resourceClass) {
            $resourceType->setClass($resource['class']);
        } else {
            $resourceType->setParent($resourceClass);
        }

        $this->em->persist($resourceType);
        $this->persistCustomAction($resource['actions'], $resourceType);
        $this->persistIcons($resource, $resourceType, $plugin);
        $this->templateBuilder->addResourceType($resource['name'], 'ROLE_WS_MANAGER');

        return $resourceType;
    }

    private function persistWidget($widget, $pluginEntity, $plugin)
    {
        $ds = DIRECTORY_SEPARATOR;

        $widgetEntity = new Widget();
        $widgetEntity->setName($widget['name']);
        $widgetEntity->setConfigurable($widget['is_configurable']);
        $widgetEntity->setExportable($widget['is_exportable']);
        $widgetEntity->setPlugin($pluginEntity);

        if (isset($widget['icon'])) {
            $widgetEntity->setIcon(
                "bundles{$ds}{$plugin->getAssetsFolder()}{$ds}images{$ds}icons{$ds}{$widget['icon']}"
            );
        } else {
            $widgetEntity->setIcon(
                "bundles{$ds}clarolinecore{$ds}images{$ds}resources{$ds}icons{$ds}res_default.png"
            );
        }

        $this->em->persist($widgetEntity);

        $wWidgetConfig = new DisplayConfig();
        $wWidgetConfig->setWidget($widgetEntity);
        $wWidgetConfig->setLock(false);
        $wWidgetConfig->setVisible(true);
        $wWidgetConfig->setParent(null);
        $wWidgetConfig->setDesktop(false);

        $dWidgetConfig = new DisplayConfig();
        $dWidgetConfig->setWidget($widgetEntity);
        $dWidgetConfig->setLock(false);
        $dWidgetConfig->setVisible(true);
        $dWidgetConfig->setParent(null);
        $dWidgetConfig->setDesktop(true);

        $this->em->persist($wWidgetConfig);
        $this->em->persist($dWidgetConfig);
        $this->em->flush();

        $this->templateBuilder->addWidget($widget['name']);
    }

    private function persistTool($tool, $pluginEntity)
    {
        $toolEntity = new Tool();
        $toolEntity->setName($tool['name']);
        $toolEntity->setDisplayableInDesktop($tool['is_displayable_in_desktop']);
        $toolEntity->setDisplayableInWorkspace($tool['is_displayable_in_workspace']);
        $toolEntity->setIsDesktopRequired(false);
        $toolEntity->setIsWorkspaceRequired(false);
        $toolEntity->setPlugin($pluginEntity);
        $toolEntity->setExportable($tool['is_exportable']);

        if (isset($tool['class'])) {
            $toolEntity->setClass(
                "{$tool['icon']}"
            );
        } else {
            $toolEntity->setClass(
                "icon-wrench"
            );
        }

        $this->em->persist($toolEntity);
        $this->em->flush();

        if ($tool['is_displayable_in_workspace']) {
            $this->templateBuilder->addTool($tool['name'], $tool['name']);
        }
    }
}
