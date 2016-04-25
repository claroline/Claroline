<?php

namespace Innova\VideoRecorderBundle\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Innova\VideoRecorderBundle\Entity\VideoRecorderConfiguration;

class DefaultData extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        $config = new VideoRecorderConfiguration();
        $config->setMaxRecordingTime(120);

        $manager->persist($config);
        $manager->flush();
    }
}
