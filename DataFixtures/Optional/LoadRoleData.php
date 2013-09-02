<?php

namespace Claroline\CoreBundle\DataFixtures\Optional;

use Claroline\CoreBundle\Entity\Role;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadRoleData extends AbstractFixture implements ContainerAwareInterface
{
    private $roles;

    /**
     * Constructor. Each key is a role name and each value is a parent role.
     *
     * @param array $roles
     */
    public function __construct(array $roles)
    {
        $this->roles = $roles;
    }

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $roleManager = $this->container->get('claroline.manager.role_manager');

        foreach ($this->roles as $role) {
            $entityRole = $roleManager->createCustomRole('ROLE_' . $role, $role);
            $this->addReference('role/' . $role, $entityRole);
        }

        $manager->flush();
    }
}
