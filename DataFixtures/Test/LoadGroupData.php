<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\DataFixtures\Test;

use Claroline\CoreBundle\Entity\Group;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadGroupData extends AbstractFixture
{
    use ContainerAwareTrait;

    private $groups;

    /**
     * @param array $groups
     *
     * Example:
     *
     * array(
     *     array('name' => 'Group 1', 'role' => 'ROLE_ADMIN')
     *     array('name' => 'Group 2', 'role' => 'ROLE_USER')
     *     ...
     * )
     */
    public function __construct(array $groups)
    {
        $this->groups = $groups;
    }

    public function load(ObjectManager $manager)
    {
        $groupManager = $this->container->get('claroline.manager.group_manager');

        foreach ($this->groups as $properties) {
            $group = new Group();
            $group->setName($properties['name']);
            $role = $manager->getRepository('ClarolineCoreBundle:Role')
                ->findOneByName($properties['role']);
            $group->addRole($role);
            $groupManager->insertGroup($group);
        }
    }
}
