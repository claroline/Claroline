<?php
/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Installation\Updater;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Claroline\CoreBundle\Entity\Workspace\Workspace;

class Updater040100
{
    private $container;
    private $om;

    const MAX_BATCH_SIZE = 2;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->om = $container->get('claroline.persistence.object_manager');
    }

    public function postUpdate()
    {
        $this->updateWorkspaceFileLimit();
        $this->updateFileStorageDir();
    }

    private function updateWorkspaceFileLimit()
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $workspaces = $em->getRepository('ClarolineCoreBundle:Workspace\Workspace')
            ->findAll();
        $i = 0;

        $this->log('Updating workspace storage size...');

        foreach ($workspaces as $workspace) {
            $workspace->setMaxStorageSize(Workspace::DEFAULT_MAX_STORAGE_SIZE);
            $workspace->setMaxUploadResources(Workspace::DEFAULT_MAX_FILE_COUNT);
            $em->persist($workspace);

            $i++;

            if ($i % self::MAX_BATCH_SIZE === 0) {
                $em->flush();
                $i = 0;
            }
        }

        $em->flush();
    }

    private function updateFileStorageDir()
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $directories = $em->getRepository('ClarolineCoreBundle:Resource\Directory')
            ->findRootDirectories();

        $i = 0;

        $this->log('Updating root directories...');

        foreach ($directories as $directory) {
            $directory->setIsUploadDestination(true);
            $em->persist($directory);
            $i++;

            if ($i % self::MAX_BATCH_SIZE === 0) {
                $em->flush();
                $i = 0;
            }
        }

        $em->flush();
    }

    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    private function log($message)
    {
        if ($log = $this->logger) {
            $log('    ' . $message);
        }
    }
}
