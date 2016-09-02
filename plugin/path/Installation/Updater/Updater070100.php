<?php

namespace Innova\PathBundle\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;
use Innova\PathBundle\Entity\Path\Path;

/**
 * Inject the ID of the Path into the JSON structure
 * This is not needed for the published ones as the structure is created from the real data.
 */
class Updater070100 extends Updater
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function postUpdate()
    {
        $om = $this->container->get('claroline.persistence.object_manager');

        $toPublishPaths = $om->getRepository('InnovaPathBundle:Path\Path')->findPlatformPaths(true);

        /** @var Path $path */
        foreach ($toPublishPaths as $path) {
            $structure = json_decode($path->getStructure());
            if (empty($structure->id)) {
                $structure->id = $path->getId();
                $path->setStructure(json_encode($structure));

                $om->persist($path);
            }
        }

        $om->flush();
    }
}
