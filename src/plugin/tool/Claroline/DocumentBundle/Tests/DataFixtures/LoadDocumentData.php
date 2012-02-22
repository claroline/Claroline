<?php

namespace Claroline\DocumentBundle\Tests\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Claroline\DocumentBundle\Entity\Directory;
use Claroline\DocumentBundle\Entity\Document;
use Doctrine\Common\Persistence\ObjectManager;

class LoadDocumentData extends AbstractFixture
{
    /*
     * DIR_A
     *      Dir_B
     *          Dir_C
     *          Dir_D
     *      Dir_E
     *          Dir_F
     *              Dir_G
     */
    public function load(ObjectManager $manager)
    {     
        $dirA = new Directory();
        $dirB = new Directory();
        $dirC = new Directory();
        $dirD = new Directory();
        $dirE = new Directory();
        $dirF = new Directory();
        $dirG = new Directory();

        $dirA->setName("DIR_A");
        $dirB->setName("DIR_B");
        $dirC->setName("DIR_C");
        $dirD->setName("DIR_D");
        $dirE->setName("DIR_E");
        $dirF->setName("DIR_F");
        $dirG->setName("DIR_G");

        $dirB->setParent($dirA);
        $dirD->setParent($dirB);
        $dirC->setParent($dirB);
        $dirF->setParent($dirE);
        $dirE->setParent($dirA);
        $dirG->setParent($dirF);

        $manager->persist($dirA);
        $manager->persist($dirB);
        $manager->persist($dirC);
        $manager->persist($dirD);
        $manager->persist($dirE);
        $manager->persist($dirF);
        $manager->persist($dirG);
        
        $this->addReference('dir/dir_a', $dirA);
        $this->addReference('dir/dir_b', $dirB);
        $this->addReference('dir/dir_c', $dirC);
        $this->addReference('dir/dir_d', $dirD);
        $this->addReference('dir/dir_e', $dirE);
        $this->addReference('dir/dir_f', $dirF);
        $this->addReference('dir/dir_g', $dirG);

        $manager->flush();
    }
}