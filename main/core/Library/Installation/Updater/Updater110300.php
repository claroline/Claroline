<?php

namespace Claroline\CoreBundle\Library\Installation\Updater;

use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater110300 extends Updater
{
    const BATCH_SIZE = 500;

    private $container;
    protected $logger;
    private $om;

    public function __construct(ContainerInterface $container, $logger = null)
    {
        $this->container = $container;
        $this->logger = $logger;
        $this->om = $container->get('claroline.persistence.object_manager');
    }

    public function postUpdate()
    {
        $this->updateWorkspaceSlugs();
    }

    private function updateWorkspaceSlugs()
    {
        $this->log('Initializing workspace slug...');
        $offset = 0;
        $i = 0;
        $total = intval($this->om
            ->getRepository('Claroline\CoreBundle\Entity\Workspace\Workspace')
            ->countWorkspaces());

        while ($i < $total) {
            $workspaces = $this->om
                ->getRepository('Claroline\CoreBundle\Entity\Workspace\Workspace')
                ->findBy([], [], self::BATCH_SIZE, $offset);
            $offset += self::BATCH_SIZE;
            foreach ($workspaces as $workspace) {
                ++$i;
                $this->log('Update workspace slug for '.$workspace->getCode().' '.$i.'/'.$total);
                $workspace->setSlug(null);
                $this->om->persist($workspace);
            }

            $this->om->flush();
            $this->om->clear();
        }

        $this->log('Workspace slugs initialized!');
    }
}
