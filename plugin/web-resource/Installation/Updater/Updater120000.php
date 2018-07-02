<?php

namespace Claroline\WebResourceBundle\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater120000 extends Updater
{
    /** @var ContainerInterface */
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function postUpdate()
    {
        $this->updateWebResourceFilePath();
    }

    /**
     * Update file path.
     */
    private function updateWebResourceFilePath()
    {
        $this->log('Update resourceWeb file path ...');

        /** @var ObjectManager $om */
        $om = $this->container->get('claroline.persistence.object_manager');
        $resourceType = $om->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')->findOneBy(['name' => 'claroline_web_resource']);
        $resourceNodes = $om->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceNode')->findBy(['resourceType' => $resourceType]);
        $resourceManager = $this->container->get('claroline.manager.resource_manager');
        $fs = $this->container->get('filesystem');

        foreach ($resourceNodes as $resourceNode) {
            $file = $resourceManager->getResourceFromNode($resourceNode);
            $workspace = $resourceNode->getWorkspace();

            if (!empty($file)) {
                $hash = $file->getHashName();
                $uploadDir = $this->container->getParameter('claroline.param.uploads_directory');
                $filesDir = $this->container->getParameter('claroline.param.files_directory');

                if ($fs->exists($filesDir.DIRECTORY_SEPARATOR.$hash)) {
                    $fs->copy($filesDir.DIRECTORY_SEPARATOR.$hash, $filesDir.DIRECTORY_SEPARATOR.'webresource'.DIRECTORY_SEPARATOR.$workspace->getUuid().DIRECTORY_SEPARATOR.$hash);
                    $fs->remove($filesDir.DIRECTORY_SEPARATOR.$hash);
                }

                if ($fs->exists($uploadDir.DIRECTORY_SEPARATOR.$hash)) {
                    $fs->mirror($uploadDir.DIRECTORY_SEPARATOR.$hash, $uploadDir.DIRECTORY_SEPARATOR.'webresource'.DIRECTORY_SEPARATOR.$workspace->getUuid().DIRECTORY_SEPARATOR.$hash);
                    $fs->remove($uploadDir.DIRECTORY_SEPARATOR.$hash);
                }
            }
        }
    }
}
