<?php

namespace Claroline\CoreBundle\Entity\Widget;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="simple_text_workspace_widget_config")
 */
class SimpleTextWorkspaceConfig
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="is_default", type="boolean")
     */
    protected $isDefault = false;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $workspace;

    /**
     * @ORM\Column(type="text")
     */
    protected $content;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set isDefault
     *
     * @param  boolean                $isDefault
     * @return SimpleTextWorkspaceConfig
     */
    public function setIsDefault($isDefault)
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    /**
     * Get isDefault
     *
     * @return boolean
     */
    public function getIsDefault()
    {
        return $this->isDefault;
    }

    /**
     * Set amount
     *
     * @param  string $content
     * @return SimpleTextWorkspaceConfig
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get amount
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set user
     *
     * @param  \Claroline\CoreBundle\Entity\User $user
     * @return SimpleTextWorkspaceConfig
     */
    public function setWorkspace(\Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace = null)
    {
        $this->workspace = $workspace;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace
     */
    public function getWorkspace()
    {
        return $this->workspace;
    }
}