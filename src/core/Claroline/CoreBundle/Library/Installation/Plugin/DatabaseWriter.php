<?php

namespace Claroline\CoreBundle\Library\Installation\Plugin;

use \RuntimeException;
use Symfony\Component\Validator\Validator as SymfonyValidator;
use Symfony\Component\Yaml\Yaml;
use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Library\PluginBundle;
use Claroline\CoreBundle\Entity\Plugin;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Resource\ResourceIcon;
use Claroline\CoreBundle\Entity\Resource\IconType;
use Claroline\CoreBundle\Entity\Resource\ResourceTypeCustomAction;

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
     * @param string            $imgPath
     */
    public function __construct(
        SymfonyValidator $validator,
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
        $pluginEntity = new Plugin();
        $pluginEntity->setBundleFQCN(get_class($plugin));
        $pluginEntity->setVendorName($plugin->getVendorName());
        $pluginEntity->setBundleName($plugin->getBundleName());
        $pluginEntity->setNameTranslationKey($plugin->getNameTranslationKey());
        $pluginEntity->setDescriptionTranslationKey($plugin->getDescriptionTranslationKey());

        $errors = $this->validator->validate($pluginEntity);

        if (count($errors) > 0) {
            $pluginFqcn = get_class($plugin);

            throw new RuntimeException(
                "The plugin entity for '{$pluginFqcn}' cannot be validated. "
                . "Validation errors : {$errors->__toString()}."
            );
        }

        $this->em->persist($pluginEntity);
        $this->persistCustomResourceTypes($plugin, $pluginEntity);
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

    private function persistCustomResourceTypes(PluginBundle $plugin, Plugin $pluginEntity)
    {
        $resourceFile = $plugin->getCustomResourcesFile();

        if (is_string($resourceFile) && file_exists($resourceFile)) {
            $resources = (array) $this->yamlParser->parse($resourceFile);

            foreach ($resources as $name => $properties) {
                $resourceType = new ResourceType();

                if (isset($properties['class'])) {
                    $resourceType->setClass($properties['class']);
                }
                if (isset($properties['extends'])) {
                    $resourceExtended = $this->em
                        ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')
                        ->findOneBy(array('type' => $properties['extends']));
                    $resourceType->setParent($resourceExtended);
                }

                $resourceType->setType($name);
                $resourceType->setListable($properties['listable']);
                $resourceType->setNavigable($properties['navigable']);
                $resourceType->setDownloadable($properties['downloadable']);
                $resourceType->setPlugin($pluginEntity);
                $this->em->persist($resourceType);

                if (isset($properties['actions'])) {
                    $actions = $properties['actions'];

                    foreach ($actions as $key => $action) {
                        $rtca = new ResourceTypeCustomAction();
                        $rtca->setAsync($action);
                        $rtca->setAction($key);
                        $rtca->setResourceType($resourceType);
                        $this->em->persist($rtca);
                    }
                }

                $resourceIcon = new ResourceIcon();

                $defaultIcon = $this->em
                    ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceIcon')
                    ->findOneBy(array('iconType' => IconType::DEFAULT_ICON));
                $defaultIconType = $this->em
                    ->getRepository('Claroline\CoreBundle\Entity\Resource\IconType')
                    ->findOneBy(array('iconType' => 'type'));
                $resourceIcon->setIconType($defaultIconType);
                $resourceIcon->setType($resourceType->getType());

                if (isset($properties['small_icon'])) {
                    $resourceIcon->setSmallIcon("bundles/{$plugin->getAssetsFolder()}/images/icons/small/{$properties['small_icon']}");
                } else {
                    $resourceIcon->setSmallIcon($defaultIcon->getSmallIcon());
                }

                if (isset($properties['large_icon'])) {
                    $resourceIcon->setLargeIcon("bundles/{$plugin->getAssetsFolder()}/images/icons/large/{$properties['large_icon']}");
                } else {
                    $resourceIcon->setLargeIcon($defaultIcon->getLargeIcon());
                }

                $this->em->persist($resourceIcon);
            }
        }
    }
}
