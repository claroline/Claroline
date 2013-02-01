<?php

namespace Claroline\CoreBundle\Entity\Tool;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\ToolRepository")
 * @ORM\Table(name="claro_tools")
 */
class Tool
{
    const WORKSPACE_ONLY = 1;
    const DESKTOP_ONLY = 2;
    const WORKSPACE_AND_DESKTOP = 3;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="name", type="string")
     */
    protected $name;

    /**
     * @ORM\Column(name="icon", type="string")
     */
    protected $icon;

    /**
     * @ORM\Column(name="is_workspace_required", type="boolean")
     */
    protected $isWorkspaceRequired;

    /**
     * @ORM\Column(name="displayability", type="integer")
     */
    protected $displayability;

    /**
     * @ORM\Column(name="translation_key", type="integer")
     */
    protected $translationKey;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Tool\WorkspaceTool",
     *     mappedBy="tool"
     * )
     */
    protected $workspaceTools;

    public function getId()
    {
        return $this->id;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setIcon($icon)
    {
        $this->icon = $icon;
    }

    public function getIcon()
    {
        return $this->icon;
    }

    public function setIsWorkspaceRequired($bool)
    {
        $this->isWorkspaceRequired = $bool;
    }

    public function isWorkspaceRequired()
    {
        return $this->isWorkspaceRequired;
    }

    public function setDisplayability($display)
    {
        $this->displayability = $display;
    }

    public function getDisplayability()
    {
        return $this->displayability;
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

