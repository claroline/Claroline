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
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Security\PlatformRoles;
use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater120400 extends Updater
{
    const BATCH_SIZE = 500;

    protected $logger;

    /** @var ObjectManager */
    private $om;

    public function __construct(ContainerInterface $container, $logger = null)
    {
        $this->logger = $logger;
        $this->container = $container;
        $this->om = $container->get('claroline.persistence.object_manager');
    }

    public function postUpdate()
    {
        $userManager = $this->container->get('claroline.manager.user_manager');

        if (!$this->om->getRepository(Group::class)->findOneByName(PlatformRoles::USER)) {
            $role = $this->om->getRepository(Role::class)->findOneByName(PlatformRoles::USER);
            $group = new Group();
            $group->setName(PlatformRoles::USER);
            $group->setReadOnly(true);
            $group->addRole($role);
            $this->om->persist($group);
            $this->om->flush();
        }

        $userManager->setLogger($this->logger);
        $userManager->bindUserToGroup();
    }
}
