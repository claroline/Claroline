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

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater120542 extends Updater
{
    /** @var ObjectManager */
    private $om;

    public function __construct(ContainerInterface $container, $logger = null)
    {
        $this->logger = $logger;
        $this->om = $container->get(ObjectManager::class);
    }

    public function postUpdate()
    {
        $this->registerUsersToTheirWorkspaces();
    }

    private function registerUsersToTheirWorkspaces()
    {
        /** @var Workspace[] $workspaces */
        $workspaces = $this->om->getRepository(Workspace::class)->findBy(['personal' => true]);
        foreach ($workspaces as $workspace) {
            $user = $workspace->getPersonalUser();
            if ($user) {
                $role = $workspace->getManagerRole();

                $this->log(sprintf('- Register %s to its personal workspace (%s).', $user->getUsername(), $role));

                $role->addUser($user);
                $this->om->persist($user);
                $this->om->persist($role);
            }
        }

        $this->om->flush();
    }
}
