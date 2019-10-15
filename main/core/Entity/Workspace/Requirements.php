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
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="claro_workspace_requirements",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *             name="workspace_user_requirements",
 *             columns={"workspace_id", "user_id"}
 *         ),
 *         @ORM\UniqueConstraint(
 *             name="workspace_role_requirements",
 *             columns={"workspace_id", "role_id"}
 *         )
 *     }
 * )
 * @DoctrineAssert\UniqueEntity({"workspace", "user"})
 * @DoctrineAssert\UniqueEntity({"workspace", "role"})
 */
class Requirements
{
    use Id;
    use Uuid;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace")
     * @ORM\JoinColumn(name="workspace_id", nullable=false, onDelete="CASCADE")
     */
    protected $workspace;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", nullable=true, onDelete="CASCADE")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Role")
     * @ORM\JoinColumn(name="role_id", nullable=true, onDelete="CASCADE")
     */
    protected $role;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode"
     * )
     * @ORM\JoinTable(name="claro_workspace_required_resources")
     */
    protected $resources;

    public function __construct()
    {
        $this->refreshUuid();
        $this->resources = new ArrayCollection();
    }

    /**
     * @return Workspace
     */
    public function getWorkspace()
    {
        return $this->workspace;
    }

    /**
     * @param Workspace $workspace
     */
    public function setWorkspace(Workspace $workspace)
    {
        $this->workspace = $workspace;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;
    }

    /**
     * @return Role
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param Role $role
     */
    public function setRole(Role $role = null)
    {
        $this->role = $role;
    }

    /**
     * @return ArrayCollection
     */
    public function getResources()
    {
        return $this->resources;
    }

    /**
     * @param ResourceNode $resourceNode
     */
    public function addResource(ResourceNode $resourceNode)
    {
        if (!$this->resources->contains($resourceNode)) {
            $this->resources->add($resourceNode);
        }
    }

    /**
     * @param ResourceNode $resourceNode
     */
    public function removeResource(ResourceNode $resourceNode)
    {
        if ($this->resources->contains($resourceNode)) {
            $this->resources->removeElement($resourceNode);
        }
    }
}
