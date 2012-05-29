<?php

namespace Claroline\CoreBundle\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Resource\MetaType;

class LoadResourceTypeData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load (ObjectManager $manager)
    {
        $fileType = new ResourceType();
        $fileType->setType('file');
        $fileType->setListable(true);
        $fileType->setNavigable(false);
            
        $dirType = new ResourceType();
        $dirType->setType('directory');
        $dirType->setListable(true);
        $dirType->setNavigable(true);
        
        $fileMeta = new MetaType();
        $fileMeta->setMetaType('file');
        
        $archiveMeta = new MetaType();
        $archiveMeta->setMetaType('archive');
        
        $eventMeta = new MetaType();
        $eventMeta->setMetaType('event');
         
        $manager->persist($dirType);
        $manager->persist($fileType);
        $manager->persist($fileMeta);
        $manager->persist($archiveMeta);
        $manager->persist($eventMeta);
        
        $fileType->addMetaType($fileMeta);
        $dirType->addMetaType($fileMeta);
        
        $manager->flush();
        
        $this->addReference('resource_type/file', $fileType);
        $this->addReference('resource_type/directory', $dirType);
    }
    
    public function getOrder()
    {
        return 2;
    }
}