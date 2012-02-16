<?php

namespace Claroline\DocumentBundle\Tests\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Claroline\DocumentBundle\Entity\Directory;
use Claroline\DocumentBundle\Entity\Document;

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
    public function load($manager)
    {
     /*   $docA = new Document();
        $docB = new Document();
        $docC = new Document();
        
        $docA->setName("doc_A.txt");
        $docB->setName("Doc_B.png");
        $docC->setName("Doc_C.mov"); 
        
        $hashA = hash("md5", $docA->getName().time());
        $hashB = hash("md5", $docB->getName().time());
        $hashC = hash("md5", $docC->getName().time());
        
        $docA->setHashName($hashA);
        $docB->setHashName($hashB);
        $docC->setHashName($hashC);
        
        $docA->setSize(42);
        $docC->setSize(110);
        $docB->setSize(150);
       */       
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
        /*
        $dirE->addDocument($docA);
        $dirE->addDocument($docB);
        $dirF->addDocument($docC);
         */ 
        //$manager->persist($docA);
        //$manager->persist($docB);
        //$manager->persist($docC);
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
        /*
        $this->addReference('doc/doc_a', $docA);
        $this->addReference('doc/doc_b', $docB);
        $this->addReference('doc/doc_c', $docC);*/

        $manager->flush();
    }
}

?>
