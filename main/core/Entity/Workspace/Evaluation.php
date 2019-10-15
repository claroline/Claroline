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
use Claroline\CoreBundle\Entity\AbstractEvaluation;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="claro_workspace_evaluation",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *             name="workspace_user_evaluation",
 *             columns={"workspace_id", "user_id"}
 *         )
 *     }
 * )
 * @DoctrineAssert\UniqueEntity({"workspace", "user"})
 */
class Evaluation extends AbstractEvaluation
{
    use Uuid;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace")
     * @ORM\JoinColumn(name="workspace_id", onDelete="SET NULL")
     */
    protected $workspace;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", onDelete="SET NULL")
     */
    protected $user;

    /**
     * @ORM\Column(name="user_name")
     */
    protected $userName;

    /**
     * @ORM\Column(name="workspace_code")
     */
    protected $workspaceCode;

    public function __construct()
    {
        $this->refreshUuid();
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
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * @param string $userName
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;
    }

    /**
     * @return string
     */
    public function getWorkspaceCode()
    {
        return $this->workspaceCode;
    }

    /**
     * @param string $workspaceCode
     */
    public function setWorkspaceCode($workspaceCode)
    {
        $this->workspaceCode = $workspaceCode;
    }
}
