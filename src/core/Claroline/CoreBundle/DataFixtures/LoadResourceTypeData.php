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
        
        $linkType = new ResourceType();
        $linkType->setType('link');
        $linkType->setListable(true);
        $linkType->setNavigable(false);
        
        $textType = new ResourceType();
        $textType->setType('text');
        $textType->setListable(true);
        $textType->setNavigable(false);
        
        $documentMeta = new MetaType();
        $documentMeta->setMetaType('document');

        $manager->persist($dirType);
        $manager->persist($fileType);
        $manager->persist($linkType);
        $manager->persist($textType);
        $manager->persist($documentMeta);
        
        $textType->addMetaType($documentMeta);
        $fileType->addMetaType($documentMeta);
        $dirType->addMetaType($documentMeta);
        $linkType->addMetaType($documentMeta);
         
        $manager->flush();
        
        $this->addReference('resource_type/file', $fileType);
        $this->addReference('resource_type/directory', $dirType);
    }
    
    public function getOrder()
    {
        return 2;
    }
}