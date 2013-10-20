<?php

namespace Claroline\CoreBundle\Entity\Workspace;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\WorkspaceFavouriteRepository")
 * @ORM\Table(
 *     name="claro_workspace_favourite",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *             name="workspace_favourite_unique_combination",
 *             columns={"workspace_id", "user_id"}
 *         )
 *     }
 * )
 */
class WorkspaceFavourite
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace"
     * )
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    protected $workspace;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User"
     * )
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    protected $user;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getWorkspace()
    {
        return $this->workspace;
    }

    public function setWorkspace(AbstractWorkspace $workspace)
    {
        $this->workspace = $workspace;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }
}