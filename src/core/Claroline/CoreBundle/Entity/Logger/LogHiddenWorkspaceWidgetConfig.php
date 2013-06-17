<?php

namespace Claroline\CoreBundle\Entity\Logger;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_log_hidden_workspace_widget_config")
 */
class LogHiddenWorkspaceWidgetConfig
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace")
     * @ORM\JoinColumn(name="workspace_id", referencedColumnName="id")
     */
    protected $workspace;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * Set workspace
     *
     * @param \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace
     * @return LogHiddenWorkspaceConfig
     */
    public function setWorkspace(\Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace)
    {
        $this->workspace = $workspace;

        return $this;
    }

    /**
     * Get workspace
     *
     * @return \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace 
     */
    public function getWorkspace()
    {
        return $this->workspace;
    }

    /**
     * Set user
     *
     * @param \Claroline\CoreBundle\Entity\User $user
     * @return LogHiddenWorkspaceConfig
     */
    public function setUser(\Claroline\CoreBundle\Entity\User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Claroline\CoreBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }
}
