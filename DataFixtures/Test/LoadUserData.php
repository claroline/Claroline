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

use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadUserData extends AbstractFixture
{
    use ContainerAwareTrait;

    private $users;

    /**
     * @param array $users
     *
     * Example:
     *
     * array(
     *     array('username' => 'John', 'role' => 'ROLE_ADMIN')
     *     array('username' => 'Jane', 'role' => 'ROLE_USER')
     *     ...
     * )
     */
    public function __construct(array $users)
    {
         $this->users = $users;
    }

    public function load(ObjectManager $manager)
    {
        $userManager = $this->container->get('claroline.manager.user_manager');

        foreach ($this->users as $properties) {
            $user = new User();
            $user->setUsername($properties['username']);
            $user->setPlainPassword($properties['username']);
            $user->setFirstName($properties['username']);
            $user->setLastName($properties['username']);
            $user->setMail($properties['username'] . '@claroline.net');
            $user->setLocale('en');
            $userManager->createUserWithRole($user, $properties['role']);
        }
    }
}
