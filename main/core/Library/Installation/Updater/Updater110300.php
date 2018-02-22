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
        $workspaces = $this->om->getRepository('Claroline\CoreBundle\Entity\Workspace\Workspace')->findAll();
        $count = count($workspaces);
        $i = 0;

        foreach ($workspaces as $workspace) {
            ++$i;
            $this->log('Update workspace slug for '.$workspace->getCode().' '.$i.'/'.$count);
            $workspace->setSlug(null);
            $this->om->persist($workspace);

            if (0 === $i % self::BATCH_SIZE) {
                $this->om->flush();
            }
        }

        $this->om->flush();
    }
}
