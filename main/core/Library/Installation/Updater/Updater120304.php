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

use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Security\PlatformRoles;
use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater120304 extends Updater
{
    const BATCH_SIZE = 500;

    protected $logger;

    public function __construct(ContainerInterface $container, $logger = null)
    {
        $this->logger = $logger;
        $this->container = $container;
        $this->userManager = $container->get('claroline.manager.user_manager');
        $this->workspaceManager = $container->get('claroline.manager.workspace_manager');
        $this->om = $container->get('Claroline\AppBundle\Persistence\ObjectManager');
    }

    public function postUpdate()
    {
        $this->userManager->setLogger($this->logger);
        $this->workspaceManager->setLogger($this->logger);
        $this->workspaceManager->setWorkspacesFlag();
        $this->userManager->bindUserToOrganization();
        $this->bindGroups();
    }

    private function bindGroups()
    {
        if (!$this->om->getRepository(Group::class)->findOneByName(PlatformRoles::USER)) {
            $role = $this->om->getRepository(Role::class)->findOneByName(PlatformRoles::USER);
            $group = new Group();
            $group->setName(PlatformRoles::USER);
            $group->setReadOnly(true);
            $group->addRole($role);
            $this->om->persist($group);
            $this->om->flush();
        }

        $this->userManager->setLogger($this->logger);
        $this->userManager->bindUserToGroup();
    }
}
