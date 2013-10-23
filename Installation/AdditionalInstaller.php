<?php

namespace Innova\PathBundle\Installation;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand;
use Claroline\CoreBundle\Library\Workspace\TemplateBuilder;
use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;
use Innova\PathBundle\Entity\NonDigitalResourceType;


class AdditionalInstaller extends BaseInstaller
{
    public function postInstall()
    {
        $this->insertNonDigitalResourceTypes();
    }
    
    public function postUpdate($currentVersion, $targetVersion)
    {
        if (version_compare($currentVersion, '1.1', '<')  && version_compare($targetVersion, '1.1', '>=') ) {
            $this->insertNonDigitalResourceTypes();
        }
    }

    protected function insertNonDigitalResourceTypes()
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $resourceTypes = array("text", "sound", "picture", "video", "simulation", "test", "other", "indifferent", "chat", "forum", "deposit_file");
        foreach ($resourceTypes as $type) {
            $nonDigitalResourceType = new NonDigitalResourceType();
            $nonDigitalResourceType->setName($type);
            $em->persist($nonDigitalResourceType);
        }
        $em->flush();
    }
}
