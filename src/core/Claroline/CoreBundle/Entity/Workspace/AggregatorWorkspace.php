<?php

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
     *     joinColumns={
     *         @ORM\JoinColumn(
     *             name="aggregator_workspace_id",
     *             nullable=true
     *         )
     *     },
     *     inverseJoinColumns={@ORM\JoinColumn(nullable=true)}
     * )
     */
    protected $workspaces;

    public function __construct()
    {
        parent::__construct();
        $this->workspaces = new ArrayCollection();
    }

    /**
     * Method implemented only to match parent definition. As the aggregator
     * workspaces have no parents, they are always public
     */
    public function setPublic($isPublic)
    {
        $this->isPublic = true;
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
