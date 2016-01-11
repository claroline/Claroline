<?php

namespace Innova\PathBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;

/**
 * Configuration of Widgets Path
 * @ORM\Table(name="innova_path_widget_config")
 * @ORM\Entity()
 */
class PathWidgetConfig
{
    /**
     * Unique identified of the Configuration
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Status
     * @var string
     *
     * @ORM\Column(type="simple_array", nullable=true)
     */
    protected $status;

    /**
     * Tags
     */
    protected $tags;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Widget\WidgetInstance")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $widgetInstance;

    /**
     * Get ID
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get status
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set status
     * @param $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getWidgetInstance()
    {
        return $this->widgetInstance;
    }

    public function setWidgetInstance(WidgetInstance $widgetInstance)
    {
        $this->widgetInstance = $widgetInstance;

        return $this;
    }
}