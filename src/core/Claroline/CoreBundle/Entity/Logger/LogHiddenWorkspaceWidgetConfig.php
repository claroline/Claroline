<?php

namespace Claroline\CoreBundle\Entity\Logger;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_log_hidden_workspace_widget_config")
 */
class LogHiddenWorkspaceWidgetConfig
{
    /**
     * @ORM\Id
     * @ORM\Column(name="workspace_id", type="integer")
     */
    protected $workspaceId;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     */
    protected $user;

    /**
     * Set workspaceId
     *
     * @param  integer                        $workspaceId
     * @return LogHiddenWorkspaceWidgetConfig
     */
    public function setWorkspaceId($workspaceId)
    {
        $this->workspaceId = $workspaceId;

        return $this;
    }

    /**
     * Get workspaceId
     *
     * @return integer
     */
    public function getWorkspaceId()
    {
        return $this->workspaceId;
    }

    /**
     * Set user
     *
     * @param  \Claroline\CoreBundle\Entity\User $user
     * @return LogHiddenWorkspaceWidgetConfig
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
