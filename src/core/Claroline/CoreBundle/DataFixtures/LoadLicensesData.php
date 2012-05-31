<?php

namespace Claroline\CoreBundle\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\License;

class LoadLicensesData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $other = new License();
        $other->setName("others");
        
        $dontKnow = new License();
        $dontKnow->setName("don't know");
        
        $publicDomain = new License();
        $publicDomain->setName("public domain");
        
        $allRightReserved = new License();
        $allRightReserved->setName("all right reserved");
        
        $manager->persist($other);
        $manager->persist($dontKnow);
        $manager->persist($publicDomain);
        $manager->persist($allRightReserved);
        
        $manager->flush();        
    }
    
    public function getOrder()
    {
        return 3;
    }
}