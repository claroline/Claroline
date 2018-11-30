<?php

namespace Claroline\CoreBundle\Library\Installation\Updater;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater120200 extends Updater
{
    protected $logger;
    private $conn;

    const BATCH_SIZE = 500;

    public function __construct(ContainerInterface $container, $logger = null)
    {
        $this->logger = $logger;
        $this->container = $container;
    }

    public function postUpdate()
    {
        $this->buildNewPaths();
    }

    private function buildNewPaths()
    {
        $om = $this->container->get('claroline.persistence.object_manager');
        $total = $om->count(ResourceNode::class);
        $this->log('Building resource paths ('.$total.')');

        $offset = 0;

        while ($offset < $total) {
            $nodes = $om->getRepository(ResourceNode::class)->findBy([], [], self::BATCH_SIZE, $offset);

            foreach ($nodes as $node) {
                $om->persist($node);
                ++$offset;
                $this->log('Building resource paths '.$offset.'/'.$total);
            }

            $this->log('Flush');
            $om->flush();
            $om->clear();
        }
    }
}
