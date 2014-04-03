<?php

namespace Innova\PathBundle\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Innova\PathBundle\Entity\StepWhere;

/**
 * Class LoadStepWhereData
 * @package Innova\PathBundle\DataFixtures\ORM
 */
class LoadStepWhereData extends AbstractFixture
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {

        $stepWhere1 = new stepWhere();
        $stepWhere1->setName("at_home");
        $stepWhere1->setDefault(0);
        $manager->persist($stepWhere1);

        $stepWhere2 = new stepWhere();
        $stepWhere2->setName("classroom");
        $stepWhere2->setDefault(0);
        $manager->persist($stepWhere2);

        $stepWhere3 = new stepWhere();
        $stepWhere3->setName("library");
        $stepWhere3->setDefault(0);
        $manager->persist($stepWhere3);

        $stepWhere4 = new stepWhere();
        $stepWhere4->setName("anywhere");
        $stepWhere4->setDefault(1);
        $manager->persist($stepWhere4);

        $manager->flush();
    }
}
