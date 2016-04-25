<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Widget;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_simple_text_widget_config")
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
    protected $widgetInstance;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set amount.
     *
     * @param string $content
     *
     * @return SimpleTextWorkspaceConfig
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get amount.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    public function setWidgetInstance(WidgetInstance $ds)
    {
        $this->widgetInstance = $ds;
    }

    public function getWidgetInstance()
    {
        return $this->widgetInstance;
    }
}
