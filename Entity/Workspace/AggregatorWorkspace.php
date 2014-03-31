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

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_workspace")
 */
class AggregatorWorkspace extends AbstractWorkspace
{
    /**
     * @ORM\ManyToMany(targetEntity="Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace")
     * @ORM\JoinTable(
     *     name="claro_workspace_aggregation",
     *     joinColumns={@ORM\JoinColumn(name="aggregator_workspace_id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="simple_workspace_id")})
     * )
     */
    protected $workspaces;

    public function __construct()
    {
        parent::__construct();
        $this->workspaces = new ArrayCollection();
    }

    public function getWorkspaces()
    {
        return $this->workspaces;
    }

    public function addWorkspace(SimpleWorkspace $workspace)
    {
        $this->workspaces->add($workspace);
    }

    public function removeWorkspace(SimpleWorkspace $workspace)
    {
        $this->workspaces->removeElement($workspace);
    }
}
