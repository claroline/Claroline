<?php

namespace Claroline\CoreBundle\Tests\DataFixtures\Alt;

use Claroline\CoreBundle\Entity\Role;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadRoleData extends AbstractFixture implements ContainerAwareInterface
{
    private $roles;

    /**
     * Constructor. Each key is a role name and each value is the role parent.
     * The role parent must exists or be null;
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
        foreach ($this->roles as $roles) {
            foreach($roles as $roleName => $parentName) {
                $role = new Role();
                $role->setName('ROLE_'.$roleName);
                $role->setTranslationKey($roleName);
                $role->setRoleType(Role::CUSTOM_ROLE);
                if ($parentName !== null) {
                    $role->setParent($this->getReference('role/'.$parentName));
                }
                $manager->persist($role);
                $this->addReference('role/'.$roleName, $role);
            }
        }

        $manager->flush();
    }
}