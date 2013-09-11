<?php

namespace Claroline\CoreBundle\Library\Installation\Plugin;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\KernelInterface;
use Claroline\CoreBundle\Manager\MaskManager;
use Claroline\CoreBundle\Library\PluginBundle;
use Claroline\CoreBundle\Manager\IconManager;
use Claroline\CoreBundle\Library\Workspace\TemplateBuilder;
use Claroline\CoreBundle\Entity\Plugin;
use Claroline\CoreBundle\Entity\Theme\Theme;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Resource\ResourceIcon;
use Claroline\CoreBundle\Entity\Resource\MenuAction;
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
    private $mm;
    private $fileSystem;
    private $kernelRootDir;
    private $templateDir;
    private $templateBuilder;
    private $modifyTemplate = false;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "em"             = @DI\Inject("doctrine.orm.entity_manager"),
     *     "im"             = @DI\Inject("claroline.manager.icon_manager"),
     *     "mm"             = @DI\Inject("claroline.manager.mask_manager"),
     *     "fileSystem"     = @DI\Inject("filesystem"),
     *     "kernel"         = @DI\Inject("kernel"),
     *     "templateDir"    = @DI\Inject("%claroline.param.templates_directory%")
     * })
     */
    public function __construct(
        EntityManager $em,
        IconManager $im,
        Filesystem $fileSystem,
        KernelInterface $kernel,
        MaskManager $mm,
        $templateDir
    )
    {
        $this->em = $em;
        $this->im = $im;
        $this->mm = $mm;
        $this->fileSystem = $fileSystem;
        $this->kernelRootDir = $kernel->getRootDir();
        $this->templateDir = $templateDir;
        $this->modifyTemplate = $kernel->getEnvironment() !== 'test';
    }

    /**
     * Persists a plugin in the database.
     *
     * @param PluginBundle $plugin
     */
    public function insert(PluginBundle $plugin, array $pluginConfiguration)
    {
        if ($this->modifyTemplate) {
            $this->templateBuilder = TemplateBuilder::fromTemplate("{$this->templateDir}default.zip");
        }

        $pluginEntity = new Plugin();
        $pluginEntity->setVendorName($plugin->getVendorName());
        $pluginEntity->setBundleName($plugin->getBundleName());
        $pluginEntity->setHasOptions($pluginConfiguration['has_options']);

        if (isset($pluginConfiguration['icon'])) {
            $ds = DIRECTORY_SEPARATOR;
            $iconWebDir = "bundles{$ds}{$plugin->getAssetsFolder()}{$ds}images{$ds}icons";
            $pluginEntity->setIcon("{$iconWebDir}{$ds}{$pluginConfiguration['icon']}");
        } else {
            $defaultIcon = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceIcon')
                ->findOneByMimeType('custom/default');
            $pluginEntity->setIcon($defaultIcon->getRelativeUrl());
        }

        $this->em->persist($pluginEntity);
        $this->persistConfiguration($pluginConfiguration, $pluginEntity, $plugin);
        $this->em->flush();

        if ($this->modifyTemplate) {
            $this->templateBuilder->write();
        }
    }

    /**
     * Removes a plugin from the database.
     *
     * @param string $pluginFqcn
     */
    public function delete($pluginFqcn)
    {
        $plugin = $this->getPluginEntity($pluginFqcn);
        // code below is for "re-parenting" the resources which depend on one
        // of the resource types the plugin might have declared
        $resourceTypes = $this->em
            ->getRepository('ClarolineCoreBundle:Resource\ResourceType')
            ->findByPlugin($plugin->getGeneratedId());

        if ($this->modifyTemplate) {
            $this->templateBuilder = TemplateBuilder::fromTemplate("{$this->templateDir}default.zip");
            foreach ($resourceTypes as $resourceType) {
                $this->templateBuilder->removeResourceType($resourceType->getName());
            }

            $tools = $this->em
                ->getRepository('ClarolineCoreBundle:Tool\Tool')
                ->findByPlugin($plugin->getGeneratedId());

            foreach ($tools as $tool) {
                $this->templateBuilder->removeTool($tool->getName());
            }

            $config = $this->templateBuilder->getConfig();

            $widgets = $this->em
                ->getRepository('ClarolineCoreBundle:Widget\Widget')
                ->findByPlugin($plugin->getGeneratedId());

            foreach ($widgets as $widget) {
                $this->templateBuilder->removeWidget($widget->getName());
            }

            $config = $this->templateBuilder->getConfig();
            $this->templateBuilder->write();
        }

        // deletion of other plugin db dependencies is made via a cascade mechanism
        $this->em->remove($plugin);
        $this->em->flush();
    }

    /**
     * Checks if a plugin is persited in the database.
     *
     * @param string $pluginFqcn
     *
     * @return boolean
     */
    public function isSaved(PluginBundle $plugin)
    {
        if ($this->getPluginEntity(get_class($plugin)) !== null) {
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

        foreach ($processedConfiguration['themes'] as $theme) {
            $this->persistTheme($theme, $pluginEntity);
        }
    }

    private function persistIcons(array $resource, ResourceType $resourceType, PluginBundle $plugin)
    {
        $resourceIcon = new ResourceIcon();
        $resourceIcon->setMimeType('custom/' . $resourceType->getName());
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
            $defaultIcon = $this->em
                ->getRepository('ClarolineCoreBundle:Resource\ResourceIcon')
                ->findOneByMimeType('custom/default');
            $resourceIcon->setIconLocation($defaultIcon->getIconLocation());
            $resourceIcon->setRelativeUrl($defaultIcon->getRelativeUrl());
        }

        $resourceIcon->setShortcut(false);
        $this->em->persist($resourceIcon);
        $this->im->createShortcutIcon($resourceIcon);
    }

    private function persistCustomAction($actions, $resourceType)
    {
        $decoderRepo = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\MaskDecoder');
        $existingDecoders = $decoderRepo->findBy(array('resourceType' => $resourceType));
        $exp = count($existingDecoders);
        $newDecoders = array();

        foreach ($actions as $action) {
            $decoder = $decoderRepo->findOneBy(array('name'=> $action['name'], 'resourceType' => $resourceType));

            if (!$decoder) {
                if (array_key_exists($action['name'], $newDecoders)) {
                    $decoder = $newDecoders[$action['name']];
                } else {
                    $decoder = new \Claroline\CoreBundle\Entity\Resource\MaskDecoder();
                    $decoder->setName($action['name']);
                    $decoder->setResourceType($resourceType);
                    $decoder->setValue(pow(2, $exp));
                    $this->em->persist($decoder);
                    $newDecoders[$action['name']] = $decoder;
                    $exp++;
                }
            }

            if (isset($action['menu_name'])) {
                $rtca = new MenuAction();
                $rtca->setName($action['menu_name']);
                $rtca->setResourceType($resourceType);
                $rtca->setValue($decoder->getValue());
                $this->em->persist($rtca);
            }
        }
    }

    private function persistResourceTypes($resource, $pluginEntity, $plugin)
    {
        $resourceType = new ResourceType();
        $resourceType->setName($resource['name']);
        $resourceType->setExportable($resource['is_exportable']);
        $resourceType->setPlugin($pluginEntity);
        $this->em->persist($resourceType);
        $this->mm->addDefaultPerms($resourceType);
        $this->persistCustomAction($resource['actions'], $resourceType);
        $this->persistIcons($resource, $resourceType, $plugin);

        if ($this->modifyTemplate) {
            $this->templateBuilder->addResourceType($resource['name'], 'ROLE_WS_MANAGER');
        }

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

        if ($this->modifyTemplate) {
            $this->templateBuilder->addWidget($widget['name']);
        }
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
        $toolEntity->setIsConfigurableInWorkspace($tool['is_configurable_in_workspace']);
        $toolEntity->setIsConfigurableInDesktop($tool['is_configurable_in_desktop']);

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

        if ($tool['is_displayable_in_workspace'] && $this->modifyTemplate) {
            $this->templateBuilder->addTool($tool['name'], $tool['name']);
        }
    }

    private function persistTheme($theme, $pluginEntity)
    {
        $themeEntity = new Theme();
        $themeEntity->setName($theme['name']);
        $themeEntity->setPath(
            $pluginEntity->getVendorName().
            $pluginEntity->getBundleName().":".
            substr_replace($theme['path'], ":", strpos($theme['path'], "/"), 1)
        );

        $themeEntity->setPlugin($pluginEntity);
        $this->em->persist($themeEntity);
    }

    public function setModifyTemplate($bool)
    {
        $this->modifyTemplate = $bool;
    }
}
