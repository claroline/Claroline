<?php

namespace Claroline\CoreBundle\Entity\Tool;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_workspace_ordered_tool")
 * @DoctrineAssert\UniqueEntity({"translationKey", "workspace"})
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
     * @ORM\JoinColumn(name="workspace_id", referencedColumnName="id")
     */
    protected $workspace;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Tool\Tool",
     *     cascade={"persist"}, inversedBy="workspaceOrderedTools"
     * )
     * @ORM\JoinColumn(name="tool_id", referencedColumnName="id")
     */
    protected $tool;

    /**
     * @ORM\Column(name="display_order", type="integer")
     */
    protected $order;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Tool\WorkspaceToolRole",
     *     mappedBy="workspaceOrderedTool"
     * )
     */
    protected $workspaceToolRoles;

    /**
     * @Orm\Column(name="translation_key", type="string")
     */
    protected $translationKey;

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

    public function setTranslationKey($translationKey)
    {
        $this->translationKey = $translationKey;
    }

    public function getTranslationKey()
    {
        return $this->translationKey;
    }
}
