<?php

namespace Claroline\CoreBundle\Library\Installation\Plugin;

use \RuntimeException;
use Symfony\Component\Yaml\Yaml;
use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Library\PluginBundle;
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

    /**
     * Constructor.
     *
     * @param SymfonyValidator  $validator
     * @param EntityManager     $em
     * @param Yaml              $yamlParser
     */
    public function __construct(
        Validator $validator,
        EntityManager $em,
        Yaml $yamlParser
    )
    {
        $this->validator = $validator;
        $this->em = $em;
        $this->yamlParser = $yamlParser;
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
            return $errors;
        }

        $processedConfiguration = $plugin->getProcessedConfiguration();
        $pluginEntity = new Plugin();
        $pluginEntity->setBundleFQCN(get_class($plugin));
        $pluginEntity->setVendorName($plugin->getVendorName());
        $pluginEntity->setBundleName($plugin->getBundleName());
        $pluginEntity->setDescriptionTranslationKey($plugin->getDescriptionTranslationKey());
        $pluginEntity->setHasOptions($processedConfiguration['has_options']);

        if(isset($processedConfiguration['icon'])){
            $pluginEntity->setIcon("bundles/{$plugin->getAssetsFolder()}/images/icons/{$processedConfiguration['icon']}");
        } else {
            $defaultIcon = $this->em
                ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceIcon')
                ->findOneBy(array('iconType' => IconType::DEFAULT_ICON));
            $pluginEntity->setIcon($defaultIcon->getLargeIcon());
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
        return $this->em
            ->getRepository('Claroline\CoreBundle\Entity\Plugin')
            ->findOneByBundleFQCN($pluginFqcn);
    }

    private function persistConfiguration($processedConfiguration, $pluginEntity, $plugin)
    {
        foreach ($processedConfiguration['resources'] as $resource) {
            $this->persistResourceTypes($resource, $pluginEntity, $plugin);
        }

        foreach ($processedConfiguration['widgets'] as $widget){
            $this->persistWidget($widget, $pluginEntity);
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
        $resourceIcon->setType($resourceType->getType());

        if (isset($resource['small_icon'])) {
            $resourceIcon->setSmallIcon("bundles/{$plugin->getAssetsFolder()}/images/icons/small/{$resource['small_icon']}");
        } else {
            $resourceIcon->setSmallIcon($defaultIcon->getSmallIcon());
        }

        if (isset($resource['large_icon'])) {
            $resourceIcon->setLargeIcon("bundles/{$plugin->getAssetsFolder()}/images/icons/large/{$resource['large_icon']}");
        } else {
            $resourceIcon->setLargeIcon($defaultIcon->getLargeIcon());
        }

        $this->em->persist($resourceIcon);
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
        $resourceType->setType($resource['name']);
        $resourceType->setListable($resource['is_visible']);
        $resourceType->setNavigable($resource['is_browsable']);
        $resourceType->setDownloadable($resource['is_downloadable']);
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

    private function persistWidget($widget, $pluginEntity)
    {
        $widgetEntity = new Widget();
        $widgetEntity->setName($widget['name']);
        $widgetEntity->setPlugin($pluginEntity);
        $this->em->persist($widgetEntity);
        //new default config entry
        $widgetConfig = new DisplayConfig();
        $widgetConfig->setWidget($widgetEntity);
        $widgetConfig->setAdminLock(true);
    }
}
