<?php

namespace UJM\ExoBundle\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;

class Updater120000 extends Updater
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function postUpdate()
    {
        $this->log('Updating mandatory option of questions in quiz...');

        $om = $this->container->get('claroline.persistence.object_manager');
        $repo = $om->getRepository('UJM\ExoBundle\Entity\StepItem');
        $stepItems = $repo->findAll();
        $i = 0;

        $om->startFlushSuite();

        foreach ($stepItems as $stepItem) {
            if (is_null($stepItem->isMandatory())) {
                $question = $stepItem->getQuestion();

                if (!empty($question)) {
                    $stepItem->setMandatory($question->isMandatory());
                    ++$i;

                    if (0 === $i % 200) {
                        $om->forceFlush();
                    }
                }
            }
        }

        $om->endFlushSuite();
    }
}
