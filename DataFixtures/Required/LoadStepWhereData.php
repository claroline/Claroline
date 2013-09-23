<?php

namespace Innova\PathBundle\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Innova\PathBundle\Entity\StepWhere;

/**
 * Class LoadStepWhereData
 * @package Innova\PathBundle\DataFixtures\ORM
 */
class LoadStepWhere extends AbstractFixture
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
  
        $stepWhere1 = new stepWhere();
        $stepWhere1->setName("home");
        $manager->persist($stepWhere1);


        $stepWhere2 = new stepWhere();
        $stepWhere2->setName("classroom");
        $manager->persist($stepWhere2);

        $stepWhere3 = new stepWhere();
        $stepWhere3->setName("library");
        $manager->persist($stepWhere3);

        $stepWhere4 = new stepWhere();
        $stepWhere4->setName("anywhere");
        $manager->persist($stepWhere4);

        $manager->flush();
    }
}
