<?php

namespace Claroline\CoreBundle\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceType;

class LoadResourceTypeData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load (ObjectManager $manager)
    {
        $fileType = new ResourceType();
        $fileType->setType('file');
        $fileType->setBundle('CoreBundle');
        $fileType->setVendor('Claroline');
        $fileType->setService('claroline.file.manager');
        $fileType->setController('File');
        $fileType->setListable(true);
        $fileType->setNavigable(false);
            
        $dirType = new ResourceType();
        $dirType->setType('directory');
        $dirType->setBundle('CoreBundle');
        $dirType->setVendor('Claroline');
        $dirType->setController('Directory');
        $dirType->setService('claroline.directory.manager');
        $dirType->setListable(true);
        $dirType->setNavigable(true);
         
        $manager->persist($dirType);
        $manager->persist($fileType);
        $manager->flush();
        
        $this->addReference('resource_type/file', $fileType);
        $this->addReference('resource_type/directory', $dirType);
    }
    
    public function getOrder()
    {
        return 2;
    }
}