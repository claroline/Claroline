<?php

namespace UJM\ExoBundle\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;

class Updater100600 extends Updater
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function postUpdate()
    {
        $this->log('Updating tags id to uuids...');
        //switch the ids from the tags to the uuids
        $om = $this->container->get('claroline.persistence.object_manager');
        $repo = $om->getRepository('Claroline\TagBundle\Entity\TaggedObject');
        $taggedObjects = $repo->findBy(['objectClass' => 'UJM\ExoBundle\Entity\Item\Item']);
        $i = 0;

        foreach ($taggedObjects as $taggedObject) {
            $this->log('Updating tag for '.$taggedObject->getTag()->getName());
            $item = $om->getRepository('UJM\ExoBundle\Entity\Item\Item')->find($taggedObject->getObjectId());
            if ($item) {
                $taggedObject->setObjectId($item->getUuid());
                $om->persist($taggedObject);
                ++$i;
            }

            if ($i % 200 === 0) {
                $om->flush();
            }
        }

        $om->flush();
    }
}
