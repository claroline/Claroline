<?php

namespace Claroline\CoreBundle\Entity\Home;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="claro_home_tab_main_config",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *             name="home_tab_main_config_unique_user_workspace",
 *             columns={"user_id", "workspace_id"}
 *         )
 *     }
 * )
 * @DoctrineAssert\UniqueEntity({"user", "workspace"})
 */
class HomeTabMainConfig
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User"
     * )
     * @ORM\JoinColumn(name="user_id", nullable=true, onDelete="CASCADE")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace"
     * )
     * @ORM\JoinColumn(name="workspace_id", nullable=true, onDelete="CASCADE")
     */
    protected $workspace;

    /**
     * @ORM\Column(type="boolean", name="allow_desktop_tab_creation", nullable=false)
     */
    protected $allowDesktopTabCreation;

    /**
     * @ORM\Column(type="boolean", name="allow_workspace_tab_creation", nullable=false)
     */
    protected $allowWorkspaceTabCreation;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getWorkspace()
    {
        return $this->workspace;
    }

    public function setWorkspace($workspace)
    {
        $this->workspace = $workspace;
    }

    public function getAllowDesktopTabCreation()
    {
        return $this->allowDesktopTabCreation;
    }

    public function setAllowDesktopTabCreation($allowDesktopTabCreation)
    {
        $this->allowDesktopTabCreation = $allowDesktopTabCreation;
    }

    public function getAllowWorkspaceTabCreation()
    {
        return $this->allowWorkspaceTabCreation;
    }

    public function setAllowWorkspaceTabCreation($allowWorkspaceTabCreation)
    {
        $this->allowWorkspaceTabCreation = $allowWorkspaceTabCreation;
    }
}