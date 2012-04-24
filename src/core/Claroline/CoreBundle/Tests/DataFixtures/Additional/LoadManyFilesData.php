<?php

namespace Claroline\CoreBundle\Tests\DataFixtures\Additional;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\File;

class LoadManyFilesData extends LoadFileData implements ContainerAwareInterface, OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        parent::setLoader();
        for ($i=0; $i<5; $i++)
        { 
            //for normal users
            $this->addFiles($this->getReference("user/manyUser{$i}"), $manager);
            //for ws_creator
            $idWSCreator = $i+100;
            $this->addFiles($this->getReference("user/manyUser{$idWSCreator}"), $manager);
            //for admins
            $idAdmin = $i+120;
            $this->addFiles($this->getReference("user/manyUser{$idAdmin}"), $manager);
        }         
    }
     
    public function getOrder()
    {
        return 104;
    }
}