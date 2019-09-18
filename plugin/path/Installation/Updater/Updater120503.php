<?php

namespace Innova\PathBundle\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\InstallationBundle\Updater\Updater;
use Innova\PathBundle\Entity\Path\Path;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater120503 extends Updater
{
    /** @var ContainerInterface */
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function postUpdate()
    {
        $this->initializeDefaultScoreConfig();
    }

    /**
     * Initializes default score to success.
     */
    private function initializeDefaultScoreConfig()
    {
        $this->log('Initializing default score to success of all paths...');

        /** @var ObjectManager $om */
        $om = $this->container->get('claroline.persistence.object_manager');
        $paths = $om->getRepository(Path::class)->findAll();

        $om->startFlushSuite();
        $i = 0;

        foreach ($paths as $path) {
            if (is_null($path->getSuccessScore())) {
                $path->setSuccessScore(50);
            }
            ++$i;

            if (0 === $i % 250) {
                $om->forceFlush();
            }
        }

        $om->endFlushSuite();
    }
}
