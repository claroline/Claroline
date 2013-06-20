<?php

namespace Claroline\CoreBundle\Entity\Tool;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="claro_workspace_ordered_tool",
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *          name="tool",columns={"tool_id","workspace_id"}
 *          ),
 *      @ORM\UniqueConstraint(
 *          name="display",columns={"workspace_id","display_order"}
 *          ),
 *      @ORM\UniqueConstraint(
 *          name="workspace",columns={"workspace_id","name"}
 *          )
 *      }
 * )
 * @DoctrineAssert\UniqueEntity({"name", "workspace"})
 * 
 */
class WorkspaceOrderedTool
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace",
     *     cascade={"persist"}, inversedBy="workspaceOrderedTools"
     * )
     * @ORM\JoinColumn(name="workspace_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $workspace;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Tool\Tool",
     *     cascade={"persist"}, inversedBy="workspaceOrderedTools"
     * )
     * @ORM\JoinColumn(name="tool_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $tool;

    /**
     * @ORM\Column(name="display_order", type="integer")
     */
    protected $order;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Tool\WorkspaceToolRole", 
     *     cascade={"persist"}, mappedBy="workspaceOrderedTool"
     * )
     */
    protected $workspaceToolRoles;

    /**
     * @Orm\Column(type="string")
     */
    protected $name;

    public function getId()
    {
        return $this->id;
    }

    public function setWorkspace($ws)
    {
        $this->workspace = $ws;
    }

    public function getWorkspace()
    {
        return $this->workspace;
    }

    public function setTool($tool)
    {
        $this->tool = $tool;
    }

    public function getTool()
    {
        return $this->tool;
    }

    public function setOrder($order)
    {
        $this->order = $order;
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}
