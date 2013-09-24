<?php

namespace Claroline\CoreBundle\Entity\Widget;

use Doctrine\ORM\Mapping as ORM;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;

/**
 * @ORM\Entity
 * @ORM\Table(name="simple_text_workspace_widget_config")
 */
class SimpleTextConfig
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="text")
     */
    protected $content;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Widget\WidgetInstance")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $displayConfig;

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

    public function setDisplayConfig(WidgetInstance $ds)
    {
        $this->displayConfig = $ds;
    }
    
    public function getDisplayConfig()
    {
        return $this->displayConfig;
    }
    
}
