<?php

namespace Innova\AudioRecorderBundle\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Innova\AudioRecorderBundle\Entity\AudioRecorderConfiguration;

class DefaultData extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        $config = new AudioRecorderConfiguration();
        $config->setMaxRecordingTime(60);
        $config->setMaxTry(5);

        $manager->persist($config);
        $manager->flush();
    }
}
