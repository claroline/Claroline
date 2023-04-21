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
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="claro_workspace_options")
 * @ORM\Entity()
 */
class WorkspaceOptions
{
    use Id;

    /**
     * @ORM\OneToOne(targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace", mappedBy="options")
     * @ORM\JoinColumn(name="workspace_id", onDelete="CASCADE")
     *
     * @var Workspace
     */
    private $workspace;

    /**
     * The options of the workspace.
     *
     * @ORM\Column(type="json", nullable=true)
     *
     * @var array
     *
     * @todo split into multiple columns
     */
    private $details = [];

    /**
     * The list of items to display in the Workspace when shown.
     *
     * @ORM\Column(type="json", nullable=true)
     *
     * @var array
     */
    private $breadcrumbItems = ['desktop', 'workspaces', 'current', 'tool'];

    /**
     * Get workspace.
     *
     * @return Workspace
     */
    public function getWorkspace()
    {
        return $this->workspace;
    }

    /**
     * Set workspace.
     */
    public function setWorkspace(Workspace $workspace)
    {
        $this->workspace = $workspace;
    }

    /**
     * Get all Workspace options.
     *
     * @return array
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * Set all workspace options.
     */
    public function setDetails(array $details)
    {
        $this->details = $details;
    }

    public function getShowBreadcrumb()
    {
        return !isset($this->details['hide_breadcrumb']) || !$this->details['hide_breadcrumb'];
    }

    public function setShowBreadcrumb(bool $show)
    {
        $this->details['hide_breadcrumb'] = !$show;
    }

    public function getBreadcrumbItems()
    {
        return $this->breadcrumbItems;
    }

    public function setBreadcrumbItems(array $items)
    {
        $this->breadcrumbItems = $items;
    }
}
