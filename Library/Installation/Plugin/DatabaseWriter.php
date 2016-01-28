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
use Claroline\CoreBundle\Entity\Tool\ToolMaskDecoder;
use Claroline\CoreBundle\Entity\Tool\PwsToolConfig;
use Claroline\CoreBundle\Entity\Widget\Widget;
use Symfony\Component\Filesystem\Filesystem;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * This class is used to save/delete a plugin and its possible dependencies (like
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
            array(
                 'vendorName' => $pluginBundle->getVendorName(),
                 'bundleName' => $pluginBundle->getBundleName()
            )
        );

        if (null === $plugin) {
            throw new \Exception('Unable to retrieve plugin for updating its configuration.');
        }

        $plugin->setHasOptions($pluginConfiguration['has_options']);

        $this->em->persist($plugin);
        $this->updateConfiguration($pluginConfiguration, $plugin, $pluginBundle);
        $this->em->flush();
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
        }

        // deletion of other plugin db dependencies is made via a cascade mechanism
        $bundle = $this->em->getRepository('ClarolineCoreBundle:Bundle')->findOneByName($plugin->getBundleName());
        $this->em->remove($plugin);
        $this->em->remove($bundle);

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
        $resourceType = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceType')
            ->findOneByName($resourceConfiguration['name']);
        $isExistResourceType = true;

        if (null === $resourceType) {
            $resourceType = new ResourceType();
            $resourceType->setName($resourceConfiguration['name']);
            $resourceType->setPlugin($plugin);
            $isExistResourceType = false;
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
        array $roles = array()
    )
    {
        $widget = $this->em->getRepository('ClarolineCoreBundle:Widget\Widget')
            ->findOneByName($widgetConfiguration['name']);

        if ($widget === null) {
            $widget = new Widget();

            foreach ($roles as $role) {
                $widget->addRole($role);
            }
        }

        $this->persistWidget($widgetConfiguration, $plugin, $pluginBundle, $widget);
    }

    /**
     * @param array        $resource
     * @param ResourceType $resourceType
     * @param PluginBundle $pluginBundle
     */
    private function persistIcons(array $resource, ResourceType $resourceType, PluginBundle $pluginBundle)
    {
        $resourceIcon = new ResourceIcon();
        $resourceIcon->setMimeType('custom/' . $resourceType->getName());
        $ds = DIRECTORY_SEPARATOR;

        if (isset($resource['icon'])) {
            $webBundleDir = "{$this->kernelRootDir}{$ds}..{$ds}web{$ds}bundles";
            $webPluginDir = "{$webBundleDir}{$ds}{$pluginBundle->getAssetsFolder()}";
            $webPluginImgDir = "{$webPluginDir}{$ds}images";
            $webPluginIcoDir = "{$webPluginImgDir}{$ds}icons";
            $this->fileSystem->mkdir(array($webBundleDir, $webPluginDir, $webPluginImgDir, $webPluginIcoDir));
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

        $resourceIcon->setShortcut(false);
        $this->em->persist($resourceIcon);
        $this->im->createShortcutIcon($resourceIcon);
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
            ->findOneByMimeType('custom/' . $resourceType->getName());

        if (null === $resourceIcon) {
            $resourceIcon = new ResourceIcon();
            $resourceIcon->setMimeType('custom/' . $resourceType->getName());
        }

        if (isset($resource['icon'])) {
            $ds = DIRECTORY_SEPARATOR;

            $webBundleDir = "{$this->kernelRootDir}{$ds}..{$ds}web{$ds}bundles";
            $webPluginDir = "{$webBundleDir}{$ds}{$pluginBundle->getAssetsFolder()}";
            $webPluginImgDir = "{$webPluginDir}{$ds}images";
            $webPluginIcoDir = "{$webPluginImgDir}{$ds}icons";
            $this->fileSystem->mkdir(array($webBundleDir, $webPluginDir, $webPluginImgDir, $webPluginIcoDir));
            $this->fileSystem->copy(
                "{$pluginBundle->getImgFolder()}{$ds}{$resource['icon']}",
                "{$webPluginIcoDir}{$ds}{$resource['icon']}"
            );
            $resourceIcon->setRelativeUrl("bundles/{$pluginBundle->getAssetsFolder()}/images/icons/{$resource['icon']}");
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

    /**
     * @param array $action
     */
    public function persistResourceAction(array $action)
    {
        $resourceAction = new MenuAction();

        $resourceAction->setName($action['name']);
        $resourceAction->setAsync(1);
        $resourceAction->setIsForm($action['is_form']);
        $resourceAction->setIsCustom(1);
        $resourceAction->setValue(1);

        $this->em->persist($resourceAction);

        $this->em->flush();
    }

    /**
     * @param array $action
     */
    public function updateResourceAction(array $action)
    {
        $resourceAction = $this->em->getRepository('ClarolineCoreBundle:Resource\MenuAction')
            ->findOneBy(array('name' => $action['name'], 'resourceType' => null, 'isCustom' => true));

        if ($resourceAction === null) {
            $this->persistResourceAction($action);
        } else {
            $resourceAction->setIsForm($action['is_form']);
        }
    }

    /**
     * @param array $actions
     * @param ResourceType $resourceType
     */
    private function persistCustomAction($actions, ResourceType $resourceType)
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
                $rtca->setIsForm($action['is_form']);
                $this->em->persist($rtca);
            }
        }

        $this->em->flush();
    }

    /**
     * @param array $actions
     * @param ResourceType $resourceType
     */
    private function updateCustomAction($actions, ResourceType $resourceType)
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

    /**
     * @param array        $widgetConfiguration
     * @param Plugin       $plugin
     * @param PluginBundle $pluginBundle
     */
    private function createWidget($widgetConfiguration, Plugin $plugin, PluginBundle $pluginBundle, array $roles = array())
    {
        $widget = new Widget();

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
    private function persistWidget($widgetConfiguration, Plugin $plugin, PluginBundle $pluginBundle, Widget $widget)
    {
        $widget->setName($widgetConfiguration['name']);
        $widget->setConfigurable($widgetConfiguration['is_configurable']);
        $widget->setDisplayableInDesktop($widgetConfiguration['is_displayable_in_desktop']);
        $widget->setDisplayableInWorkspace($widgetConfiguration['is_displayable_in_workspace']);
        $widget->setExportable($widgetConfiguration['is_exportable']);
        $widget->setPlugin($plugin);
        $widget->setDefaultWidth($widgetConfiguration['default_width']);
        $widget->setDefaultHeight($widgetConfiguration['default_height']);
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
        $tool->setName($toolConfiguration['name']);
        $tool->setDisplayableInDesktop($toolConfiguration['is_displayable_in_desktop']);
        $tool->setDisplayableInWorkspace($toolConfiguration['is_displayable_in_workspace']);
        $tool->setIsDesktopRequired(false);
        $tool->setIsWorkspaceRequired(false);
        $tool->setPlugin($plugin);
        $tool->setExportable($toolConfiguration['is_exportable']);
        $tool->setIsConfigurableInWorkspace($toolConfiguration['is_configurable_in_workspace']);
        $tool->setIsConfigurableInDesktop($toolConfiguration['is_configurable_in_desktop']);
        $tool->setIsLockedForAdmin($toolConfiguration['is_locked_for_admin']);
        $tool->setIsAnonymousExcluded($toolConfiguration['is_anonymous_excluded']);

        if (isset($toolConfiguration['class'])) {
            $tool->setClass("{$toolConfiguration['class']}");
        } else {
            $tool->setClass("wrench");
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

    /**
     * @param ResourceType $resourceType
     */
    private function deleteActivityRules(ResourceType $resourceType)
    {
        $aRuleActionRepo = $this->em
            ->getRepository('Claroline\CoreBundle\Entity\Activity\ActivityRuleAction');
        $ruleActions = $aRuleActionRepo->findBy(array('resourceType' => $resourceType));

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
                $nb++;
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
}
