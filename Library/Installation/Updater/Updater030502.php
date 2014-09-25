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

class Updater030502
{
    private $logger;
    private $om;
    private $roleManager;
    private $userManager;

    public function __construct(ContainerInterface $container)
    {
        $this->om = $container->get('claroline.persistence.object_manager');
        $this->roleManager = $container->get('claroline.manager.role_manager');
        $this->userManager = $container->get('claroline.manager.user_manager');
    }

    public function postUpdate()
    {
        $this->createPersonalRoleForUsers();
    }

    private function createPersonalRoleForUsers()
    {
        $this->log('Creating personal role for each user ...');

        $users = $this->userManager->getUsersWithoutUserRole();

        $this->om->startFlushSuite();

        foreach ($users as $user) {
            $this->roleManager->createUserRole($user);
        }
        $this->om->endFlushSuite();
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
