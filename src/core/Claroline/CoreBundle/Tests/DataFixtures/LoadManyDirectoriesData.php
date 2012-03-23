<?php

namespace Claroline\CoreBundle\Tests\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadManyDirectoriesData extends LoadDirectoryData implements ContainerAwareInterface, OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        for ($i=0; $i<5; $i++)
        {
            //for normal users
            $this->createTree($this->getReference("user/manyUser{$i}"), $i+1, $manager);
            //for ws_creator
            $idWSCreator = $i+100;
            $this->createTree($this->getReference("user/manyUser{$idWSCreator}"), $i+1, $manager);
            //for admins
            $idAdmin = $i+120;
            $this->createTree($this->getReference("user/manyUser{$idAdmin}"), $i+1, $manager);
        }
        
        $manager->flush();
    }
   
    
    public function getOrder()
    {
        return 104;
    }
}