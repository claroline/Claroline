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

use Claroline\CoreBundle\Entity\Resource\MaskDecoder;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Symfony\Component\HttpKernel\KernelInterface;
use Claroline\CoreBundle\Manager\MaskManager;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CoreBundle\Manager\ToolMaskDecoderManager;
use Claroline\CoreBundle\Library\PluginBundle;
use Claroline\CoreBundle\Manager\IconManager;
use Claroline\CoreBundle\Entity\Activity\ActivityRuleAction;
use Claroline\CoreBundle\Entity\Plugin;
use Claroline\CoreBundle\Entity\Theme\Theme;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Resource\ResourceIcon;
use Claroline\CoreBundle\Entity\Resource\MenuAction;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\Tool\AdminTool;
use Claroline\CoreBundle\Entity\Widget\Widget;
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
    private $modifyTemplate = false;
    private $toolManager;
    private $toolMaskManager;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "em"              = @DI\Inject("claroline.persistence.object_manager"),
     *     "im"              = @DI\Inject("claroline.manager.icon_manager"),
     *     "mm"              = @DI\Inject("claroline.manager.mask_manager"),
     *     "fileSystem"      = @DI\Inject("filesystem"),
     *     "kernel"          = @DI\Inject("kernel"),
     *     "templateDir"     = @DI\Inject("%claroline.param.templates_directory%"),
     *     "toolManager"     = @DI\Inject("claroline.manager.tool_manager"),
     *     "toolMaskManager" = @DI\Inject("claroline.manager.tool_mask_decoder_manager")
     * })
     */
    public function __construct(
        ObjectManager $em,
        IconManager $im,
        Filesystem $fileSystem,
        KernelInterface $kernel,
        MaskManager $mm,
        $templateDir,
        ToolManager $toolManager,
        ToolMaskDecoderManager $toolMaskManager
    )
    {
        $this->em = $em;
        $this->im = $im;
        $this->mm = $mm;
        $this->fileSystem = $fileSystem;
        $this->kernelRootDir = $kernel->getRootDir();
        $this->templateDir = $templateDir;
        $this->modifyTemplate = $kernel->getEnvironment() !== 'test';
        $this->toolManager = $toolManager;
        $this->toolMaskManager = $toolMaskManager;
    }

    /**
     * Persists a plugin in the database.
     *
     * @param PluginBundle $plugin
     * @param array        $pluginConfiguration
     */
    public function insert(PluginBundle $plugin, array $pluginConfiguration)
    {
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
    }

    public function update(PluginBundle $plugin, array $pluginConfiguration)
    {
        /** @var Plugin $pluginEntity */
        $pluginEntity = $this->em->getRepository('ClarolineCoreBundle:Plugin')->findOneBy(
            array(
                 'vendorName' => $plugin->getVendorName(),
                 'bundleName' => $plugin->getBundleName()
            )
        );

        if (null === $pluginEntity) {
            throw new \Exception('Unable to retrieve plugin for updating its configuration.');
        }

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
        $this->updateConfiguration($pluginConfiguration, $pluginEntity, $plugin);
        $this->em->flush();
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

        foreach ($resourceTypes as $resourceType) {
            $this->deleteActivityRules($resourceType);
        }

        // deletion of other plugin db dependencies is made via a cascade mechanism
        $this->em->remove($plugin);
        $this->em->flush();
    }

    /**
     * Checks if a plugin is persited in the database.
     *
     * @param \Claroline\CoreBundle\Library\PluginBundle $plugin
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
            $this->createTool($tool, $pluginEntity);
        }

        foreach ($processedConfiguration['themes'] as $theme) {
            $this->persistTheme($theme, $pluginEntity);
        }

        foreach ($processedConfiguration['admin_tools'] as $adminTool) {
            $this->persistAdminTool($adminTool, $pluginEntity);
        }
    }

    private function updateConfiguration($processedConfiguration, $pluginEntity, $plugin)
    {
        foreach ($processedConfiguration['resources'] as $resource) {
            $this->updateResourceTypes($resource, $pluginEntity, $plugin);
        }

        foreach ($processedConfiguration['tools'] as $tool) {
            $this->updateTool($tool, $pluginEntity);
        }
    }

    private function updateResourceTypes($resource, $pluginEntity, $plugin)
    {
        $resourceType = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceType')
            ->findOneByName($resource['name']);
        $isExistResourceType = true;

        if (null === $resourceType) {
            $resourceType = new ResourceType();
            $resourceType->setName($resource['name']);
            $resourceType->setPlugin($pluginEntity);

            $isExistResourceType = false;
        }

        $resourceType->setExportable($resource['is_exportable']);
        $this->em->persist($resourceType);

        if (!$this->mm->hasMenuAction($resourceType)) {
            $this->mm->addDefaultPerms($resourceType);
        }

        $this->updateCustomAction($resource['actions'], $resourceType);
        $this->updateIcons($resource, $resourceType, $plugin);
        $this->updateActivityRules($resource['activity_rules'], $resourceType);

        return $resourceType;
    }

    private function updateTool($tool, $pluginEntity)
    {
        $toolEntity = $this->em->getRepository('ClarolineCoreBundle:Tool\Tool')
            ->findOneByName($tool['name']);

        if ($toolEntity === null) {
            $toolEntity = new Tool();
        }

        $this->persistTool($tool, $pluginEntity, $toolEntity);
        $this->updateCustomToolRights($tool['tool_rights'], $toolEntity);
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
            $resourceIcon->setRelativeUrl(
                "bundles/{$plugin->getAssetsFolder()}/images/icons/{$resource['icon']}"
            );
        } else {
            $defaultIcon = $this->em
                ->getRepository('ClarolineCoreBundle:Resource\ResourceIcon')
                ->findOneByMimeType('custom/default');
            $resourceIcon->setRelativeUrl($defaultIcon->getRelativeUrl());
        }

        $resourceIcon->setShortcut(false);
        $this->em->persist($resourceIcon);
        $this->im->createShortcutIcon($resourceIcon);
    }

    private function updateIcons(array $resource, ResourceType $resourceType, PluginBundle $plugin)
    {
        $resourceIcon = $this->em
            ->getRepository('ClarolineCoreBundle:Resource\ResourceIcon')
            ->findOneByMimeType('custom/' . $resourceType->getName());

        if (null === $resourceIcon) {
            $resourceIcon = new ResourceIcon();
            $resourceIcon->setMimeType('custom/' . $resourceType->getName());
        }

        if (isset($resource['icon'])) {
            $ds = DIRECTORY_SEPARATOR;

            $webBundleDir = "{$this->kernelRootDir}{$ds}..{$ds}web{$ds}bundles";
            $webPluginDir = "{$webBundleDir}{$ds}{$plugin->getAssetsFolder()}";
            $webPluginImgDir = "{$webPluginDir}{$ds}images";
            $webPluginIcoDir = "{$webPluginImgDir}{$ds}icons";
            $this->fileSystem->mkdir(array($webBundleDir, $webPluginDir, $webPluginImgDir, $webPluginIcoDir));
            $this->fileSystem->copy(
                "{$plugin->getImgFolder()}{$ds}{$resource['icon']}",
                "{$webPluginIcoDir}{$ds}{$resource['icon']}"
            );
            $resourceIcon->setRelativeUrl("bundles/{$plugin->getAssetsFolder()}/images/icons/{$resource['icon']}");
        } else {
            $defaultIcon = $this->em
                ->getRepository('ClarolineCoreBundle:Resource\ResourceIcon')
                ->findOneByMimeType('custom/default');
            $resourceIcon->setRelativeUrl($defaultIcon->getRelativeUrl());
        }

        $resourceIcon->setShortcut(false);
        $this->em->persist($resourceIcon);
        $this->im->createShortcutIcon($resourceIcon);
    }

    private function persistCustomAction($actions, $resourceType)
    {
        $decoderRepo      = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\MaskDecoder');
        $existingDecoders = $decoderRepo->findBy(array('resourceType' => $resourceType));
        $exp              = count($existingDecoders);
        $newDecoders      = array();

        foreach ($actions as $action) {
            $decoder = $decoderRepo->findOneBy(array('name' => $action['name'], 'resourceType' => $resourceType));

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

        $this->em->flush();
    }

    private function updateCustomAction($actions, $resourceType)
    {
        $decoderRepo      = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\MaskDecoder');
        $existingDecoders = $decoderRepo->findBy(array('resourceType' => $resourceType));
        $exp              = count($existingDecoders);
        $newDecoders      = array();

        foreach ($actions as $action) {
            $decoder = $decoderRepo->findOneBy(array('name' => $action['name'], 'resourceType' => $resourceType));

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
                    $exp++;
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
                        ->setValue($decoder->getValue());

                    $this->em->persist($menuAction);
                }
            }
        }

        $this->em->flush();
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
        $this->setResourceTypeDefaultMask($resource['default_rights'], $resourceType);
        $this->persistIcons($resource, $resourceType, $plugin);
        $this->persistActivityRules($resource['activity_rules'], $resourceType);

        return $resourceType;
    }

    private function setResourceTypeDefaultMask(array $rightsName, ResourceType $resourceType)
    {
        $mask = count($rightsName) === 0 ? 1: 0;
        $permMap = $this->mm->getPermissionMap($resourceType);

        foreach ($rightsName as $rights) {
            foreach ($permMap as $value => $perm) {
                if ($perm == $rights['name']) {
                    $mask += $value;
                }
            }
        }

        $resourceType->setDefaultMask($mask);
        $this->em->persist($resourceType);

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
    }

    private function createTool($tool, $pluginEntity)
    {
        $toolEntity = new Tool();
        $this->persistTool($tool, $pluginEntity, $toolEntity);
    }

    private function persistTool($tool, $pluginEntity, $toolEntity)
    {
        $toolEntity->setName($tool['name']);
        $toolEntity->setDisplayableInDesktop($tool['is_displayable_in_desktop']);
        $toolEntity->setDisplayableInWorkspace($tool['is_displayable_in_workspace']);
        $toolEntity->setIsDesktopRequired(false);
        $toolEntity->setIsWorkspaceRequired(false);
        $toolEntity->setPlugin($pluginEntity);
        $toolEntity->setExportable($tool['is_exportable']);
        $toolEntity->setIsConfigurableInWorkspace($tool['is_configurable_in_workspace']);
        $toolEntity->setIsConfigurableInDesktop($tool['is_configurable_in_desktop']);
        $toolEntity->setIsLockedForAdmin($tool['is_locked_for_admin']);
        $toolEntity->setIsAnonymousExcluded($tool['is_anonymous_excluded']);

        if (isset($tool['class'])) {
            $toolEntity->setClass("{$tool['class']}");
        } else {
            $toolEntity->setClass("wrench");
        }

        $this->toolManager->create($toolEntity);
        $this->persistCustomToolRights($tool['tool_rights'], $toolEntity);
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

    private function persistAdminTool($adminTool, $pluginEntity)
    {
        $adminToolEntity = new AdminTool();
        $adminToolEntity->setName($adminTool['name']);
        $adminToolEntity->setClass($adminTool['class']);
        $adminToolEntity->setPlugin($pluginEntity);
        $this->em->persist($adminToolEntity);
    }

    private function persistActivityRules($rules, $resourceType)
    {
        $aRuleActionRepo = $this->em
            ->getRepository('Claroline\CoreBundle\Entity\Activity\ActivityRuleAction');

        foreach ($rules as $rule) {
            $ruleAction = $aRuleActionRepo->findOneBy(
                array('action' => $rule['action'], 'resourceType' => $resourceType)
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
    
    private function deleteActivityRules($resourceType)
    {
        $aRuleActionRepo = $this->em
            ->getRepository('Claroline\CoreBundle\Entity\Activity\ActivityRuleAction');
        $ruleActions = $aRuleActionRepo->findBy(array('resourceType' => $resourceType));

        foreach ($ruleActions as $ruleAction) {
            $this->em->remove($ruleAction);
        }
        $this->em->flush();
    }

    private function updateActivityRules($rules, $resourceType)
    {
        $this->deleteActivityRules($resourceType);
        $this->persistActivityRules($rules, $resourceType);
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
                    $value,
                    $right['granted_icon_class'],
                    $right['denied_icon_class']
                );
                $nb++;
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
}
