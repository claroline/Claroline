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

use Claroline\CoreBundle\Entity\Resource\ResourceIcon;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Library\Utilities\FileSystem;
use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater050112 extends Updater
{
    const MAX_BATCH_SIZE = 3;

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function postUpdate()
    {
        $this->updateRoles();
    }

    public function updateRoles()
    {
        $this->log('Checking roles integrity for resources... This may take a while');
        $om = $this->container->get('claroline.persistence.object_manager');
        $workspaceManager = $this->container->get('claroline.manager.workspace_manager');
        $roleManager = $this->container->get('claroline.manager.role_manager');
        $rightsManager = $this->container->get('claroline.manager.rights_manager');
        $rightsManager->setLogger($this->logger);
        $workspaces = $om->getRepository('Claroline\CoreBundle\Entity\Workspace\Workspace')->findAll();
        $om->startFlushSuite();
        $i = 0;

        foreach ($workspaces as $workspace) {
            $this->log('Checking ' . $workspace->getCode() . '...');
            $roles = $workspace->getRoles();
            $root = $this->container->get('claroline.manager.resource_manager')->getWorkspaceRoot($workspace);
            $collaboratorRole = $roleManager->getCollaboratorRole($workspace);

            if ($root) {
                $collaboratorFound = false;

                foreach ($root->getRights() as $right) {
                    if ($right->getRole()->getName() == $roleManager->getCollaboratorRole($workspace)->getName()) {
                        $collaboratorFound = true;
                    }
                }

                if (!$collaboratorFound) {
                    $this->log('Adding missing right on root for ' . $workspace->getCode() . '.');
                    $collaboratorRole = $roleManager->getCollaboratorRole($workspace);
                    $rightsManager->editPerms(5, $collaboratorRole, $root, true);
                    $i++;

                    if ($i % self::MAX_BATCH_SIZE === 0) {
                        $this->log('flushing...');
                        $om->forceFlush();
                        $om->clear();
                    }
                }
            }
        }

        $om->endFlushSuite();
    }
}
