<?php

namespace Innova\PathBundle\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Innova\PathBundle\Entity\StepWho;

/**
 * Class LoadStepWhoData
 * @package Innova\PathBundle\DataFixtures\ORM
 */
class LoadStepWhoData extends AbstractFixture
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $stepWho1 = new stepWho();
        $stepWho1->setName("student");
        $manager->persist($stepWho1);

        $stepWho2 = new stepWho();
        $stepWho2->setName("group");
        $manager->persist($stepWho2);

        $stepWho3 = new stepWho();
        $stepWho3->setName("group");
        $manager->persist($stepWho3);

        $manager->flush();
    }
}
