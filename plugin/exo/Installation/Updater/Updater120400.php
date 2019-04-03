<?php

namespace UJM\ExoBundle\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;
use UJM\ExoBundle\Entity\Exercise;

class Updater120400 extends Updater
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function postUpdate()
    {
        $this->log('Initializing option to allow edition of answers of validated steps...');
        $om = $this->container->get('claroline.persistence.object_manager');
        $exercises = $om->getRepository(Exercise::class)->findAll();
        $i = 0;

        $om->startFlushSuite();

        foreach ($exercises as $exercise) {
            if ($exercise->getShowFeedback() && $exercise->isAnswersEditable()) {
                $exercise->setAnswersEditable(false);
                $om->persist($exercise);
            }
            ++$i;

            if (0 === $i % 200) {
                $om->forceFlush();
            }
        }

        $om->endFlushSuite();
        $this->log('Option initialized.');
    }
}
