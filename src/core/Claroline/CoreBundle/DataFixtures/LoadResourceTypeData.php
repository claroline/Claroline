<?php

namespace Claroline\CoreBundle\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\ResourceType;

class LoadResourceTypeData  extends AbstractFixture implements OrderedFixtureInterface
{
    public function load (ObjectManager $manager)
    {
        $fileType = new ResourceType();
        $fileType->setType('file');
        $fileType->setBundle('CoreBundle');
        $fileType->setController('File');
        $fileType->setListable(true);
        $fileType->setNavigable(false);
            
        $manager->persist($fileType);
        $manager->flush();
        
        $this->addReference('resource_type/file', $fileType);
    }
    
    public function getOrder()
    {
        return 50;
    }
}