<?php

namespace Claroline\CoreBundle\Library\Installation\Plugin;

use \RuntimeException;
use Symfony\Component\Yaml\Yaml;
use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Library\PluginBundle;
use Claroline\CoreBundle\Library\Resource\IconCreator;
use Claroline\CoreBundle\Entity\Plugin;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Resource\ResourceIcon;
use Claroline\CoreBundle\Entity\Resource\IconType;
use Claroline\CoreBundle\Entity\Resource\ResourceTypeCustomAction;
use Claroline\CoreBundle\Entity\Widget\Widget;
use Claroline\CoreBundle\Entity\Widget\DisplayConfig;

/**
 * This class is used to save/delete a plugin an its possible dependencies (like
 * custom resource types) in the database.
 */
class DatabaseWriter
{
    private $validator;
    private $em;
    private $yamlParser;
    private $im;
    private $kernelRootDir;

    /**
     * Constructor.
     *
     * @param SymfonyValidator  $validator
     * @param EntityManager     $em
     * @param Yaml              $yamlParser
     * @param IconCreator       $im
     */
    public function __construct(
        Validator $validator,
        EntityManager $em,
        Yaml $yamlParser,
        IconCreator $im,
        $kernelRootDir
    )
    {
        $this->validator = $validator;
        $this->em = $em;
        $this->yamlParser = $yamlParser;
        $this->im = $im;
        $this->kernelRootDir = $kernelRootDir;
    }

    /**
     * Persists a plugin in the database.
     *
     * @param PluginBundle $plugin
     */
    public function insert(PluginBundle $plugin)
    {
        $errors = $this->validator->validate($plugin);

        if(0!= count($errors)){
            var_dump($errors);
            return $errors;
        }

        $processedConfiguration = $plugin->getProcessedConfiguration();
        $pluginEntity = new Plugin();
        $pluginEntity->setVendorName($plugin->getVendorName());
        $pluginEntity->setBundleName($plugin->getBundleName());
        $pluginEntity->setHasOptions($processedConfiguration['has_options']);

        if(isset($processedConfiguration['icon'])){
            $ds = DIRECTORY_SEPARATOR;
            $pluginEntity->setIcon("bundles{$ds}{$plugin->getAssetsFolder()}{$ds}images{$ds}icons{$ds}{$processedConfiguration['icon']}");
        } else {
            $defaultIcon = $this->em
                ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceIcon')
                ->findOneBy(array('iconType' => IconType::DEFAULT_ICON));
            $pluginEntity->setIcon($defaultIcon->getRelativeUrl());
        }

        $this->em->persist($pluginEntity);
        $this->persistConfiguration($processedConfiguration, $pluginEntity, $plugin);
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
        // TODO : this should be covered by a test
        $resourceTypes = $this->em
            ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')
            ->findByPlugin($plugin->getGeneratedId());

        foreach ($resourceTypes as $resourceType) {
            if (null !== $resourceType) {
                if (null !== $parentType = $resourceType->getParent()) {
                    $resources = $this->em
                        ->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource')
                        ->findByResourceType($resourceType->getId());

                    foreach ($resources as $resource) {
                        $resource->setResourceType($parentType);
                    }
                }
            }
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
            ->getRepository('Claroline\CoreBundle\Entity\Plugin')
            ->findOneByBundleFQCN($pluginFqcn);

        return $entity;
    }

    private function persistConfiguration($processedConfiguration, $pluginEntity, $plugin)
    {
        foreach ($processedConfiguration['resources'] as $resource) {
            $this->persistResourceTypes($resource, $pluginEntity, $plugin);
        }

        foreach ($processedConfiguration['widgets'] as $widget){
            $this->persistWidget($widget, $pluginEntity, $plugin);
        }
    }

    private function persistIcons($resource, $resourceType, $plugin)
    {
        $resourceIcon = new ResourceIcon();

        $defaultIcon = $this->em
            ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceIcon')
            ->findOneBy(array('iconType' => IconType::DEFAULT_ICON));
        $defaultIconType = $this->em
            ->getRepository('Claroline\CoreBundle\Entity\Resource\IconType')
            ->findOneBy(array('iconType' => 'type'));
        $resourceIcon->setIconType($defaultIconType);
        $resourceIcon->setType($resourceType->getName());
        $ds = DIRECTORY_SEPARATOR;

        if (isset($resource['icon'])) {
            $resourceIcon->setIconLocation("{$this->kernelRootDir}{$ds}..{$ds}web{$ds}bundles{$ds}{$plugin->getAssetsFolder()}{$ds}images{$ds}icons{$ds}{$resource['icon']}");
            $resourceIcon->setRelativeUrl("bundles{$ds}{$plugin->getAssetsFolder()}{$ds}images{$ds}icons{$ds}{$resource['icon']}");
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
        $resourceType->setVisible($resource['is_visible']);
        $resourceType->setBrowsable($resource['is_browsable']);
        $resourceType->setPlugin($pluginEntity);
        $resourceClass = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneBy(array ('class' => $resource['class']));

        if(null == $resourceClass){
            $resourceType->setClass($resource['class']);
        } else {
            $resourceType->setParent($resourceClass);
        }

        $this->em->persist($resourceType);
        $this->persistCustomAction($resource['actions'], $resourceType);
        $this->persistIcons($resource, $resourceType, $plugin);

        return $resourceType;
    }

    private function persistWidget($widget, $pluginEntity, $plugin)
    {
        $ds = DIRECTORY_SEPARATOR;

        $widgetEntity = new Widget();
        $widgetEntity->setName($widget['name']);
        $widgetEntity->setConfigurable($widget['is_configurable']);
        $widgetEntity->setPlugin($pluginEntity);


        if(isset($widget['icon'])){
            $widgetEntity->setIcon("bundles{$ds}{$plugin->getAssetsFolder()}{$ds}images{$ds}icons{$ds}{$widget['icon']}");
        } else {
            $defaultIcon = "bundles{$ds}clarolinecore{$ds}images{$ds}resources{$ds}icons{$ds}large{$ds}res_default.png";
            $widgetEntity->setIcon($defaultIcon);
        }

        $this->em->persist($widgetEntity);

        $wWidgetConfig = new DisplayConfig();
        $wWidgetConfig->setWidget($widgetEntity);
        $wWidgetConfig->setLock(true);
        $wWidgetConfig->setVisible(true);
        $wWidgetConfig->setParent(null);
        $wWidgetConfig->setDesktop(false);

        $dWidgetConfig = new DisplayConfig();
        $dWidgetConfig->setWidget($widgetEntity);
        $dWidgetConfig->setLock(true);
        $dWidgetConfig->setVisible(true);
        $dWidgetConfig->setParent(null);
        $dWidgetConfig->setDesktop(true);

        $this->em->persist($wWidgetConfig);
        $this->em->persist($dWidgetConfig);

        $this->em->flush();
    }
}
