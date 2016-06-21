<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Log;

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
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $user;

    /**
     * Set workspaceId.
     *
     * @param int $workspaceId
     *
     * @return LogHiddenWorkspaceWidgetConfig
     */
    public function setWorkspaceId($workspaceId)
    {
        $this->workspaceId = $workspaceId;

        return $this;
    }

    /**
     * Get workspaceId.
     *
     * @return int
     */
    public function getWorkspaceId()
    {
        return $this->workspaceId;
    }

    /**
     * Set user.
     *
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return LogHiddenWorkspaceWidgetConfig
     */
    public function setUser(\Claroline\CoreBundle\Entity\User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return \Claroline\CoreBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }
}
