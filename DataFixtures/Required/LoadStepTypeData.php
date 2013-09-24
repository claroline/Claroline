<?php

namespace Innova\PathBundle\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Innova\PathBundle\Entity\StepType;

/**
 * Class LoadStepTypeData
 * @package Innova\PathBundle\DataFixtures\ORM
 */
class LoadStepTypeData extends AbstractFixture
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {

        $stepType1 = new stepType();
        $stepType1->setName("sequential");
        $manager->persist($stepType1);

        $stepType2 = new stepType();
        $stepType2->setName("parallel");
        $manager->persist($stepType2);

        $manager->flush();
    }
}
