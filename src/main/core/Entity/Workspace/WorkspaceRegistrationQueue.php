<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Workspace;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_workspace_registration_queue')]
#[ORM\UniqueConstraint(name: 'user_role_unique', columns: ['role_id', 'user_id'])]
#[ORM\Entity]
class WorkspaceRegistrationQueue
{
    use Id;

    /**
     *
     * @var Role
     */
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Role::class)]
    private $role;

    /**
     *
     * @var User
     */
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: User::class)]
    private $user;

    /**
     *
     * @var Workspace
     */
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Workspace::class)]
    private $workspace;

    /**
     * @param mixed $role
     */
    public function setRole($role)
    {
        $this->role = $role;
    }

    /**
     * @return mixed
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $workspace
     */
    public function setWorkspace($workspace)
    {
        $this->workspace = $workspace;
    }

    /**
     * @return mixed
     */
    public function getWorkspace()
    {
        return $this->workspace;
    }
}
