<?php

namespace Claroline\CoreBundle\Entity\Workspace;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\RelWorkspaceTagRepository")
 * @ORM\Table(
 * name="claro_rel_workspace_tag",
 * uniqueConstraints={
 *      @ORM\UniqueConstraint(name="workspace_id", columns={"workspace_id", "tag_id"})}
 * )
 */
class RelWorkspaceTag
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
     * @ORM\JoinColumn(name="workspace_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $workspace;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\WorkspaceTag"
     * )
     * @ORM\JoinColumn(name="tag_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $tag;

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

    public function setWorkspace($workspace)
    {
        $this->workspace = $workspace;
    }

    public function getTag()
    {
        return $this->tag;
    }

    public function setTag($tag)
    {
        $this->tag = $tag;
    }
}