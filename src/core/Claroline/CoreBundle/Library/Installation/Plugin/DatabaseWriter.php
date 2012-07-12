<?php

namespace Claroline\CoreBundle\Library\Installation\Plugin;

use Symfony\Component\Validator\Validator as SymfonyValidator;
use Symfony\Component\Yaml\Yaml;
use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Library\Plugin\ClarolinePlugin;
use Claroline\CoreBundle\Library\Plugin\ClarolineTool;
use Claroline\CoreBundle\Library\Plugin\ClarolineExtension;
use Claroline\CoreBundle\Entity\Plugin;
use Claroline\CoreBundle\Entity\Tool;
use Claroline\CoreBundle\Entity\Extension;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Resource\ResourceTypeCustomAction;
use Claroline\CoreBundle\Exception\InstallationException;

class DatabaseWriter
{
    private $validator;
    private $em;
    private $yamlParser;

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

    public function insert(ClarolinePlugin $plugin)
    {
        if ($plugin instanceof ClarolineTool) {
            $pluginEntity = $this->prepareToolEntity($plugin);
        } elseif ($plugin instanceof ClarolineExtension) {
            $pluginEntity = $this->prepareExtensionEntity($plugin);
        }

        $pluginEntity->setBundleFQCN(get_class($plugin));
        $pluginEntity->setVendorName($plugin->getVendorName());
        $pluginEntity->setBundleName($plugin->getBundleName());
        $pluginEntity->setType($plugin->getType());
        $pluginEntity->setNameTranslationKey($plugin->getNameTranslationKey());
        $pluginEntity->setDescriptionTranslationKey($plugin->getDescriptionTranslationKey());

        $errors = $this->validator->validate($pluginEntity);

        if (count($errors) > 0) {
            $pluginFQCN = get_class($plugin);

            throw new InstallationException(
                "The plugin entity for '{$pluginFQCN}' cannot be validated. "
                . "Validation errors : {$errors->__toString()}.",
                InstallationException::ENTIY_VALIDATION_ERROR
            );
        }

        $this->em->persist($pluginEntity);
        $this->persistCustomResourceTypes($plugin, $pluginEntity);
        $this->em->flush();
    }

    public function delete($pluginFQCN)
    {
        $plugin = $this->getPluginEntity($pluginFQCN);

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

    public function isSaved($pluginFQCN)
    {
        if ($this->getPluginEntity($pluginFQCN) !== null) {
            return true;
        }

        return false;
    }

    private function prepareToolEntity(ClarolineTool $tool)
    {
        return new Tool();
    }

    private function prepareExtensionEntity(ClarolineExtension $extension)
    {
        return new Extension();
    }

    private function getPluginEntity($pluginFQCN)
    {
        return $this->em
            ->getRepository('Claroline\CoreBundle\Entity\Plugin')
            ->findOneByBundleFQCN($pluginFQCN);
    }

    private function persistCustomResourceTypes(ClarolinePlugin $plugin, Plugin $pluginEntity)
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

            }
        }
    }
}
