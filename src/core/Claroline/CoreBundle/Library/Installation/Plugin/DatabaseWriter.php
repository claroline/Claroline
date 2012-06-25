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
        // Complete deletion of all plugin db dependencies
        // is made via cascade mechanism
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

            foreach ($resources as $resource) {
                $resourceType = new ResourceType();

                if (isset($resource['class'])) {
                    $resourceType->setClass($resource['class']);
                }
                if (isset($resource['extends'])) {
                    //resource Type ex
                    $resourceExtended = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')->findOneBy(array('type' => $resource['extends']));
                    $resourceType->setParent($resourceExtended);
                }

                $resourceType->setType($resource['name']);
                $resourceType->setListable($resource['listable']);
                $resourceType->setNavigable($resource['navigable']);
                $resourceType->setPlugin($pluginEntity);

                $this->em->persist($resourceType);
            }
        }
    }
}
