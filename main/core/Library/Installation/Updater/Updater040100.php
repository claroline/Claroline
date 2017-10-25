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

use Claroline\CoreBundle\Entity\Tool\PwsToolConfig;
use Claroline\CoreBundle\Entity\Tool\ToolMaskDecoder;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater040100 extends Updater
{
    private $container;
    private $om;

    const MAX_BATCH_SIZE = 100;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->om = $container->get('claroline.persistence.object_manager');
    }

    public function postUpdate()
    {
        $this->updateWorkspaceFileLimit();
        $this->updateFileStorageDir();
        $this->updatePersonalWorkspaceToolConfig();
        $this->updatePersonalWorkspaceBoolean();
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

            ++$i;

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
            ++$i;

            if ($i % self::MAX_BATCH_SIZE === 0) {
                $em->flush();
                $i = 0;
            }
        }

        $em->flush();
    }

    private function updatePersonalWorkspaceToolConfig()
    {
        $this->log('Updating default workspace config...');
        $em = $this->container->get('doctrine.orm.entity_manager');
        $toolRepo = $em->getRepository('ClarolineCoreBundle:Tool\Tool');
        $tools = $toolRepo->findAll();
        $roleUser = $this->container->get('claroline.manager.role_manager')->getRoleByName('ROLE_USER');
        $mask = ToolMaskDecoder::$defaultValues['open'] + ToolMaskDecoder::$defaultValues['edit'];
        //count pwsToolConfigs
        $pwsToolConfigs = $em->getRepository('ClarolineCoreBundle:Tool\PwsToolConfig')->findAll();

        if (count($pwsToolConfigs) === 0) {
            foreach ($tools as $tool) {
                $pws = new PwsToolConfig();
                $pws->setTool($tool);
                $pws->setRole($roleUser);
                $pws->setMask($mask);
                $em->persist($pws);
            }
        }

        $em->flush();
    }

    private function updatePersonalWorkspaceBoolean()
    {
        $this->log('Updating personal workspaces...');
        $em = $this->container->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('ClarolineCoreBundle:Workspace\Workspace');
        $pws = $repo->findAllPersonalWorkspaces();
        $i = 0;

        foreach ($pws as $pw) {
            $pw->setIsPersonal(true);
            $em->persist($pw);
            ++$i;

            if ($i % self::MAX_BATCH_SIZE === 0) {
                $em->flush();
                $i = 0;
            }
        }

        $em->flush();
    }
}
