<?php

namespace Innova\VideoRecorderBundle\Testing;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;

class Persister
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var Role
     */
    private $userRole;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * @param string $username
     *
     * @return User
     */
    public function user($username, $withWorkspace = false)
    {
        $user = new User();
        $user->setFirstName($username);
        $user->setLastName($username);
        $user->setUsername($username);
        $user->setPassword($username);
        $user->setEmail($username.'@email.com');
        $user->setGuid($username);
        $this->om->persist($user);
        if (!$this->userRole) {
            $this->userRole = $this->role('ROLE_USER');
            $this->om->persist($this->userRole);
        }
        $user->addRole($this->userRole);

        if ($withWorkspace) {
            $workspace = new Workspace();
            $workspace->setName($username);
            $workspace->setCreator($user);
            $workspace->setCode($username);
            $workspace->setGuid($username);
            $this->om->persist($workspace);
            $user->setPersonalWorkspace($workspace);
        }

        return $user;
    }

    /**
     * @param string $name
     *
     * @return Role
     */
    public function role($name)
    {
        $role = new Role();
        $role->setName($name);
        $role->setTranslationKey($name);
        $this->om->persist($role);

        return $role;
    }
}
